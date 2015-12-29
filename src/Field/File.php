<?php

/**
 * Copyright (C) 2015 FormHandler
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 *
 * @package FormHandler
 * @subpackage Field
 */

namespace FormHandler\Field;

use \Exception;
use \FormHandler\Button\Button;
use \FormHandler\FormHandler;
use \SplFileObject;

/**
 * class File
 *
 * File class
 *
 * @author Teye Heimans
 * @author Marien den Besten
 * @package FormHandler
 * @subpackage Field
 */
class File extends \FormHandler\Field\Field
{
    const STATE_EMPTY = 1;
    const STATE_ERROR_FILE_SIZE = 2;
    const STATE_ERROR_TRANSMISSION = 3;
    const STATE_ERROR_SYSTEM = 4;
    const STATE_UPLOADING = 5;
    const STATE_UPLOADED = 6;

    private $async;
    private $accept;
    private $state;
    private $token;
    private $filename;
    private $button_upload;
    private $button_edit;
    private $drop_zone_enabled;
    private $drop_zone_language;

    /**
     * Constructor
     *
     * Create a new file field
     *
     * @param FormHandler $form The form where this field is located on
     * @param string $name The name of the field
     * @return \FormHandler\Field\File
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function __construct(FormHandler $form, $name)
    {
        static $bSetJS = false;

        // needed javascript included yet ?
        if(!$bSetJS)
        {
            // include the needed javascript
            $bSetJS = true;
            $form->_setJS(\FormHandler\Configuration::get('fhtml_dir') . "js/upload.js", true);
        }

        parent::__construct($form, $name);

        $form->setEncoding(FormHandler::ENCODING_MULTIPART);

        //upload fields are required by default
        $this->setRequired(true);
        
        $this->accept = array();
        $this->drop_zone_enabled = false;
        $this->drop_zone_language = array(
            'drop_file' => 'Drop your file here',
            'too_large' => 'Given file is too large (Given: %given%, allowed: %allowed%)',
        );
        $this->async = !is_null(filter_input(INPUT_POST, $name .'_submit'));

        $this->button_upload = new Button($form, $name .'_button');
        $this
            ->button_upload
            ->setCaption('Choose File')
            ->setExtra('style="display:none;" class="upload"');

        $this->button_edit = new \FormHandler\Button\Submit($form, $name .'_change');
        $this->button_edit
            ->setCaption('Change file')
            ->setExtra('class="upload"')
            ->onClick(function(FormHandler $form) use ($name)
            {
                $form->setValidationDisabled(true);
                $form->setIsCorrect(false);

                $form
                    ->getField($name)
                    ->updateState(\FormHandler\Field\File::STATE_EMPTY, null, null);
            });

        //read all open uploads
        $status = $this->readOpenUploads();

        //by default generate a new token
        $generated_token = $this->generateToken();

        //create field
        $this->token = new \FormHandler\Field\Hidden($form, $name .'_token');
        $this->token->setDefaultValue($generated_token);
        $this->filename = new \FormHandler\Field\Hidden($form, $name .'_filename');
        $this->filename->setDefaultValue('');
        $this->state = new \FormHandler\Field\Hidden($form, $name .'_state');
        $this->state->setDefaultValue(self::STATE_EMPTY);

        //process token
        $unsecure_token = $this->token->getValue();
        $token = ($this->form_object->isPosted() && !array_key_exists($unsecure_token, $status))
            ? $generated_token
            : $unsecure_token;
        $this->token->setValue($token);

        $data = $this->readState();

        try
        {
            $result = $this->processUpload();
        }
        catch(Exception $e)
        {
            $result = false;
            $data = $this->updateState($e->getCode(), null, null);
        }

        if($result !== false && is_array($result))
        {
            $data = $this->updateState(self::STATE_UPLOADED, $result[0], $result[1]);
        }

        if($this->async)
        {
            $ajax = array(
                'state' => $data['state'],
                'name' => $data['last_processed'],
            );

            //there was an javascript file submit, return state
            //do not send json header because IE will behave strange
            echo json_encode($ajax);
            exit();
        }

        //incorporate max file size
        if(!$form->fieldExists('MAX_FILE_SIZE'))
        {
            \FormHandler\Field\Hidden::set($form, 'MAX_FILE_SIZE')
                ->setValue($this->getMaxUploadSize(), true)
                ->hideFromOnCorrect();
        }

        return $this;
    }

    /**
     * Class destructor
     *
     * @author Marien den Besten
     */
    public function __destruct()
    {
        $this->garbageClean();

        //clean token when form was correct
        if($this->form_object->isPosted()
            && $this->form_object->isCorrect()
            && !$this->async)
        {
            $this->clean($this->token->getValue());
        }
    }

    /**
     * Set if this file field has a dropzone enabled
     *
     * Works only in HTML5 browsers, when enabled and no html5 browser file field will fallback to non html5 version
     *
     * @param boolean $boolean
     * @param string $drop_your_file
     * @param string $file_too_large
     * @return \FormHandler\Field\File
     */
    public function setDropZoneEnabled($boolean, $drop_your_file = null, $file_too_large = null)
    {
        if(is_string($drop_your_file))
        {
            $this->drop_zone_language['drop_file'] = $drop_your_file;
        }
        if(is_string($file_too_large))
        {
            $this->drop_zone_language['too_large'] = $file_too_large;
        }
        $this->drop_zone_enabled = (bool) $boolean;
        return $this;
    }

    /**
     * Get if dropzone is enabled
     *
     * @return boolean
     */
    public function getDropZoneEnabled()
    {
        return $this->drop_zone_enabled;
    }

    /**
     * Handle functionality onPost
     *
     * Will be called by FormHandler
     *
     * @param FormHandler $form
     * @author Marien den Besten
     */
    public function onPost(FormHandler $form)
    {
        parent::onPost($form);
        $this->button_edit->onPost($form);
    }

    /**
     * Set the caption for the file button
     * 
     * @param string $caption
     * @return \FormHandler\Field\File
     * @author Marien den Besten
     */
    public function setCaption($caption)
    {
        $this->button_upload->setCaption($caption);
        return $this;
    }

    /**
     * Read file state
     *
     * @return array Keys are: 'state', 'last_processed', 'files' (array)
     * @author Marien den Besten
     */
    public function readState()
    {
        $uploads = $this->readOpenUploads();
        $token = $this->token->getValue();

        if(!array_key_exists($token, $uploads))
        {
            $uploads[$token] = array(
                'state' => self::STATE_EMPTY,
                'last_processed' => null,
                'files' => array()

            );
            $this->writeOpenUploads($uploads);
        }
        return $uploads[$token];
    }

    /**
     * Update current state
     *
     * @param integer $state
     * @param string $name
     * @param string $path
     * @return array Current state
     * @author Marien den Besten
     */
    public function updateState($state, $name = null, $path = null)
    {
        $this->state->setValue($state, true);
        $data = $this->readState();

        $data['state'] = (int) $state;

        if(!is_null($name))
        {
            $data['last_processed'] = $name;

            if(!array_key_exists($name, $data['files']))
            {
                $data['files'][$name] = $path;
            }
        }

        $this->writeState($data);
        return $data;
    }

    /**
     * Write file state
     *
     * @param array $data
     * @author Marien den Besten
     */
    private function writeState($data)
    {
        $uploads = $this->readOpenUploads();
        $token = $this->token->getValue();
        $uploads[$token] = $data;
        $this->writeOpenUploads($uploads);
    }

    /**
     * Get the directory where uploads are processed
     *
     * @return string
     * @author Marien den Besten
     */
    private function getWorkingDirectory()
    {
        $token = $this->token->getValue();
        $token_dir = $this->getTempDir() . $token . DIRECTORY_SEPARATOR;

        if(!is_dir($token_dir))
        {
            //create dir
            $result = @mkdir($token_dir);

            if($result === false)
            {
                trigger_error('Given upload directory is not writable');
            }
        }
        else
        {
            //update the directory to post pone garbage cleaning
            touch($token_dir);
        }

        return $token_dir;
    }

    /**
     * Read out tokens currently open for download
     *
     * @return array
     * @author Marien den Besten
     */
    private function readOpenUploads()
    {
        $status_file = $this->getTempDir() . 'FormHandlerUploads.json';

        if(!file_exists($status_file))
        {
            return array();
        }

        $contents = file_get_contents($status_file);

        $result = json_decode($contents, true);
        return (is_array($result)) ? $result : array();
    }

    /**
     * Write a token to current open downloads
     *
     * @author Marien den Besten
     */
    private function writeOpenUploads($status)
    {
        $status_file = $this->getTempDir() . 'FormHandlerUploads.json';

        if(!is_writable($status_file))
        {
            trigger_error('Unable to write upload to TEMP directory, please adjust TEMP directory configuration');
        }

        file_put_contents($status_file, json_encode($status));
    }

    /**
     * Clean up all tokens which exceeds the GC timeout
     *
     * @author Marien den Besten
     */
    private function garbageClean()
    {
        //1 hour
        $treshold = 60*60*1;

        $open_uploads = array();
        foreach($this->readOpenUploads() as $token => $data)
        {
            $directory = $this->getTempDir() . $token . DIRECTORY_SEPARATOR;

            if(!is_dir($directory))
            {
                //directory is already gone
                continue;
            }

            //modification time of a directory can be read from the single dot 'directory' inside the directory
            $modification_time = filemtime($directory . '.');

            if($modification_time + $treshold > time())
            {
                //not to be garbage cleaned
                $open_uploads[$token] = $data;
                continue;
            }

            $this->clean($token);
        }
        $this->writeOpenUploads($open_uploads);
    }

    /**
     * Remove a given token
     *
     * @param string $token
     * @return boolean
     * @author Marien den Besten
     */
    private function clean($token)
    {
        $directory = $this->getTempDir() . $token . DIRECTORY_SEPARATOR;
        $open = $this->readOpenUploads();

        if(!is_dir($directory)
            || !array_key_exists($token, $open))
        {
            return false;
        }

        if(PHP_OS === 'Windows' || PHP_OS === 'WINNT' || PHP_OS === 'WIN32')
        {
            exec("rd /s /q " . escapeshellarg($directory));
        }
        else
        {
            exec("rm -rf " . escapeshellarg($directory));
        }
        return true;
    }

    /**
     * Get the temporary dir
     *
     * Will always end with a directory separator
     *
     * @return string File path
     * @author Marien den Besten
     */
    private function getTempDir()
    {
        $temp = sys_get_temp_dir();
        if(trim($temp) == '')
        {
            trigger_error('Temp directory is empty');
        }
        return $temp . DIRECTORY_SEPARATOR;
    }

    /**
     * Set value for field
     *
     * Give file will not be touched
     *
     * @param SplFileObject|string $value String can be a full path name
     * @param type $force
     * @return \FormHandler\Field\File
     * @author Marien den Besten
     */
    public function setValue($value, $force = false)
    {
        if(is_string($value) && (!file_exists($value) || is_dir($value)))
        {
            return false;
        }
        if(!is_object($value) || !$value instanceof SplFileObject || !$value->isFile())
        {
            return false;
        }

        $processed = is_string($value) ? new SplFileObject($value) : $value;

        //write given file to current state
        $this->updateState(self::STATE_UPLOADED, $processed->getFilename(), $processed->getRealPath());

        return $this;
    }

    /**
     * Return the current value
     *
     * @return string the current file
     * @author Marien den Besten
     */
    public function getValue()
    {
        $data = $this->readState();

        if($data['state'] == self::STATE_UPLOADED
            && !is_null($data['last_processed'])
            && array_key_exists($data['last_processed'], $data['files'])
            && file_exists($data['files'][$data['last_processed']])
            && !is_dir($data['files'][$data['last_processed']]))
        {
            return new SplFileObject($data['files'][$data['last_processed']]);
        }
        return null;
    }

    /**
     * Set which file types are accepted
     *
     * Accepted:
     * file_extension	A file extension starting with the STOP character, e.g: .gif, .jpg, .png, .doc
     * audio/*	All sound files are accepted
     * video/*	All video files are accepted
     * image/*	All image files are accepted
     * media_type	A valid media type, with no parameters.
     *
     * @param string[] $format
     * @return \FormHandler\Field\File
     * @author Marien den Besten
     */
    public function setAcceptFileType($format)
    {
        $result = (is_string($format)) ? array($format) : array();
        $result = (is_array($format)) ? $format : $result;

        $this->accept = array_merge($this->accept, $result);
        return $this;
    }

    /**
     * Return the value as link to the file
     *
     * @return string
     * @author Marien den Besten
     */
    public function _getViewValue()
    {
        $value = $this->getValue();
        return (is_object($value) && method_exists($value, 'getFilename')) ? $value->getFilename() : '';
    }

    /**
     * Return the HTML of the field
     *
     * @return string the html of the field
     * @author Marien den Besten
     */
    public function getField()
    {
        // view mode enabled ?
        if($this->getViewMode())
        {
            // get the view value..
            return $this->_getViewValue();
        }
        $current_value = $this->getValue();

        if(is_null($current_value))
        {
            $this->button_edit->setExtra('style="display:none;"', true);
            $value = '';
        }
        else
        {
            $value = $this->form_object->truncateString($current_value->getFilename());
            $this->button_upload->setExtra('style="display:none;"', true);
            $this->setExtra('style="display:none;"', true);
        }
        $status = '<span id="'. $this->name .'_status" class="upload" data-no-upload="No file chosen">'
            . \FormHandler\Utils::html($value) .'</span>';
        $return = $this->token->getField()
            . $this->filename->getField()
            . $this->state->getField()
            . $this->button_edit->getButton()
            . $this->button_upload->getButton()
            . $status;

        //process accept
        if(count($this->accept) !== 0)
        {
            $this->setExtra('accept="'. implode(', ', $this->accept) .'"', true);
        }

        $dropzone_start = '';
        $dropzone_stop = '';
        if($this->getDropZoneEnabled())
        {
            $dropzone_start = '<span'
                . ' id="'. $this->name .'_dropzone"'
                . ' data-drop-here="'. \FormHandler\Utils::html($this->drop_zone_language['drop_file']) .'"'
                . ' data-too-large="'. \FormHandler\Utils::html($this->drop_zone_language['too_large']) .'"'
                . ' class="FH_dropzone">';
            $dropzone_stop = '</span>';
        }

        //return the field
        return $dropzone_start . $return
            . sprintf(
                '<input type="file" name="%s" id="%1$s" %s' . \FormHandler\Configuration::get('xhtml_close') . '>%s',
                $this->name,
                (isset($this->tab_index) ? 'tabindex="' . $this->tab_index . '" ' : '')
                    . (isset($this->extra) ? $this->extra . ' ' : '')
                    . ($this->getDisabled() && !$this->getDisabledInExtra() ? 'disabled="disabled" ' : ''),
                (isset($this->extra_after) ? $this->extra_after : '')
            ) . $dropzone_stop;
    }

    /**
     * Generate unique token
     *
     * @return string Unique token
     * @author Marien den Besten
     */
    private function generateToken()
    {
        return 'FH_' . sha1($this->name .'_'. time());
    }

    /**
     * Upload the file
     *
     * @return array | bool: array with name and path, false on not submitted in form
     * @author Marien den Besten
     * @throws Exception Processing errors
     */
    protected function processUpload()
    {
        $state = $this->readState();

        if($this->form_object->isPosted()
            && !isset($_FILES[$this->name])
            && $this->filename->getValue() != '')
        {
            $filename_to_check = $this->filename->getValue();


            if(array_key_exists($filename_to_check, $state['files']))
            {
                $state['last_processed'] = $filename_to_check;
            }

            $this->writeState($state);
        }

        //check if we can process the upload
        if(!$this->form_object->isPosted()
            || !isset($_FILES[$this->name])
            || connection_status() != CONNECTION_NORMAL
            || ($_FILES[$this->name]['error'] === UPLOAD_ERR_NO_FILE && $state['state'] === self::STATE_UPLOADED))
        {
            return false;
        }

        //process possible errors
        switch($_FILES[$this->name]['error'])
        {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('Maximum file size exceeded', self::STATE_ERROR_FILE_SIZE);

            case UPLOAD_ERR_PARTIAL:
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('File transmission error', self::STATE_ERROR_TRANSMISSION);

            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
                throw new Exception('Error in system configuration', self::STATE_ERROR_SYSTEM);
        }

        if(!is_uploaded_file($_FILES[$this->name]['tmp_name']))
        {
            throw new Exception('File was not uploaded', self::STATE_ERROR_SYSTEM);
        }

        move_uploaded_file($_FILES[$this->name]['tmp_name'], $this->getWorkingDirectory() . $_FILES[$this->name]['name']);

        return array($_FILES[$this->name]['name'], $this->getWorkingDirectory() . $_FILES[$this->name]['name']);
    }

    /**
     * Get the max uploadsize
     *
     * @return integer the max upload size
     * @author Teye Heimans
     */
    private function getMaxUploadSize()
    {
        static $iIniSize = false;
        if(!$iIniSize)
        {
            $iPost = intval($this->iniSizeToBytes(ini_get('post_max_size')));
            $iUpl = intval($this->iniSizeToBytes(ini_get('upload_max_filesize')));
            $iIniSize = floor(min($iPost,$iUpl));
        }
        return $iIniSize;
    }

    /**
     * Get the given size in bytes
     *
     * @param string $ini_size The size we have to make to bytes
     * @return integer the size in bytes
     * @author Teye Heimans
     */
    private function iniSizeToBytes($ini_size)
    {
        $aIniParts = array();
        if(!is_string($ini_size))
        {
            trigger_error('Argument A is not a string! dump: ' . $ini_size, E_USER_NOTICE);
            return false;
        }
        if(!preg_match('/^(\d+)([bkm]*)$/i', $ini_size, $aIniParts))
        {
            trigger_error('Argument A is not a valid php.ini size! dump: ' . $ini_size, E_USER_NOTICE);
            return false;
        }

        $iSize = $aIniParts[1];
        $sUnit = strtolower($aIniParts[2]);

        switch($sUnit)
        {
            case 'm':
                return (int) ($iSize * 1048576);
            case 'k':
                return (int) ($iSize * 1024);
            case 'b':
            default:
                return (int) $iSize;
        }
    }
}