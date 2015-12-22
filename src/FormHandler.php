<?php
/**
 * FormHandler v4.0
 *
 * Look for more info at http://www.formhandler.net
 *
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
 */

namespace FormHandler;

/* * ***** BUILD IN VALIDATOR FUNCTIONS ****** */
// any string that doesn't have control characters (ASCII 0 - 31) but spaces are allowed
define('FH_STRING', 'IsString', true);
// only letters a-z and A-Z
define('FH_ALPHA', 'IsAlpha', true);
// only numbers 0-9
define('FH_DIGIT', 'IsDigit', true);
// letters and numbers
define('FH_ALPHA_NUM', 'IsAlphaNum', true);
// only numbers 0-9 and an optional - (minus) sign (in the beginning only)
define('FH_INTEGER', 'IsInteger', true);
// like FH_INTEGER, only with , (comma)
define('FH_FLOAT', 'IsFloat', true);
// a valid file name (including dots but no slashes and other forbidden characters)
define('FH_FILENAME', 'IsFilename', true);
// a boolean (TRUE is either a case-insensitive "true" or "1". Everything else is FALSE)
define('FH_BOOL', 'IsBool', true);
// a valid variable name (letters, digits, underscore)
define('FH_VARIABLE', 'IsVariabele', true);
// a valid password (alphanumberic + some other characters but no spaces. Only allow ASCII 33 - 126)
define('FH_PASSWORD', 'IsPassword', true);
// a valid URL
define('FH_URL', 'IsURL', true);
// a valid URL (http connection is used to check if url exists!)
define('FH_URL_HOST', 'IsURLHost', true);
// a valid email address (only checks for valid format: xxx@xxx.xxx)
define('FH_EMAIL', 'IsEmail', true);
// like FH_EMAIL only with host check
define('FH_EMAIL_HOST', 'IsEmailHost', true);
// like FH_STRING, but newline characters are allowed
define('FH_TEXT', 'IsText', true);
// check if the value is not empty
define('FH_NOT_EMPTY', 'notEmpty', true);
// check if the value does not contain html
define('FH_NO_HTML', 'NoHTML', true);
// check if the value is a valid ip adres (xxx.xxx.xxx.xxx:xxxx)
define('FH_IP', 'IsIp', true);

// for dutch people
// valid dutch postcode (eg. 9999 AA)
define('FH_POSTCODE', 'IsPostcode', true);
// valid dutch phone-number(eg. 058-2134778)
define('FH_PHONE', 'IsPhone', true);
// same as above, but with these the value is not required
define('_FH_STRING', '_IsString', true);
define('_FH_ALPHA', '_IsAlpha', true);
define('_FH_DIGIT', '_IsDigit', true);
define('_FH_ALPHA_NUM', '_IsAlphaNum', true);
define('_FH_INTEGER', '_IsInteger', true);
define('_FH_FLOAT', '_IsFloat', true);
define('_FH_FILENAME', '_IsFilename', true);
define('_FH_BOOL', '_IsBool', true);
define('_FH_VARIABLE', '_IsVariabele', true);
define('_FH_PASSWORD', '_IsPassword', true);
define('_FH_URL', '_IsURL', true);
define('_FH_URL_HOST', '_IsURLHost', true);
define('_FH_EMAIL', '_IsEmail', true);
define('_FH_EMAIL_HOST', '_IsEmailHost', true);
define('_FH_TEXT', '_IsText', true);
define('_FH_POSTCODE', '_IsPostcode', true);
define('_FH_PHONE', '_IsPhone', true);
define('_FH_NO_HTML', '_NoHTML', true);
define('_FH_IP', '_IsIp', true);

/**
 * class FormHandler
 *
 * @author Teye Heimans
 * @author Marien den Besten
 * @link http://www.formhandler.net
 */
class FormHandler
{
    const ENCODING_URLENCODED = 0;
    const ENCODING_MULTIPART = 1;

    protected $fields;
    private $fieldsBuffer;
    private $fieldsHidden;
    private $mask;
    private $posted;
    private $name;
    private $action;
    private $displayErrors;
    private $onCorrect;
    private $onReturn;
    private $onReturnParameter;
    private $focus;
    private $focusBuffer;
    private $bufferViewmode;
    private $languageArray;
    private static $languageExclusionArray = array();
    private $languageActive;
    private $extra;
    private $pageCounter;
    private $pageCurrent;
    private $tabIndexes;
    private $js;
    private $css;
    private $viewMode;
    private $fieldLinks;
    private $fieldLinksBuffer;
    private $rememberFormPosition;
    private $encoding;
    private $attachSelect;
    private $validationDisabled;
    private $isCorrect;
    public $buffer;
    public $edit;
    private $clicked;
    private $onCorrectFields;


    /**
     * Constructor
     *
     * @param string $name the name for the form (used in the <form> tag
     * @param string $action the action for the form (used in <form action="xxx">)
     * @param string $extra extra css or js which is included in the <form> tag
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function __construct($name = null, $action = null, $extra = null)
    {
        //initialize object
        $this->resetObject();

        // try to disable caching from the browser if possible
        if(!headers_sent())
        {
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            header("Cache-control: private");
        }

        // set the name of the form (the user has submitted one)
        if(!empty($name))
        {
            $this->name = $name;
        }
        else
        {
            $form_name = Configuration::get('default_form_name');
            // get a unique form name because the user did not give one
            $i = null;
            while(defined('FH_' . $form_name . $i))
            {
                $i = is_null($i) ? 1 : ($i + 1);
            }

            define('FH_' . $form_name . $i, 1);
            $this->name = $form_name . $i;
            $i = null;
        }

        // set the action of the form if none is given
        if(!empty($action))
        {
            $this->action = $action;
        }
        else
        {
            $this->action = $_SERVER['PHP_SELF'];
            if(!empty($_SERVER['QUERY_STRING']))
            {
                $this->action .= '?' . $_SERVER['QUERY_STRING'];
            }
        }

        // get the $extra (JS, css, etc..) to put into the <form> tag
        $extra = !is_string($extra) ? '' : $extra;
        $extra = (strpos($extra, 'accept-charset') === false)? trim($extra) . ' accept-charset="utf-8"' : $extra;
        $this->extra = $extra;

        // set the default mask
        $this->setMask(Configuration::get('default_row_mask'));

        // check if the form is posted
        $this->setPosted(
            ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST[$this->name . '_submit']))
        );

        // make a hidden field so we can identify the form
        Field\Hidden::set($this, $this->name . '_submit')
            ->setValue(1, true)
            ->hideFromOnCorrect();

        // get the current page
        $this->pageCurrent = isset($_POST[$this->name . '_page']) ? $_POST[$this->name . '_page'] : 1;

        // set the language...
        $this->setLanguage();

        //set forms javascript
        $this->_setJS(\FormHandler\Configuration::get('fhtml_dir') . "js/main.js", true);
    }

    /**
     * Reset object
     *
     * @author Marien den Besten
     */
    private function resetObject()
    {
        // initialisation
        $this->viewMode = false;
        $this->fields = array();
        $this->js = array();
        $this->css = array();
        $this->buffer = array();
        $this->bufferViewmode = array();
        $this->tabIndexes = array();
        $this->fieldsBuffer = array();
        $this->displayErrors = true;
        $this->focus = Configuration::get('set_focus');
        $this->focusBuffer = null;
        $this->pageCounter = 1;
        $this->attachSelect = array();
        $this->onCorrect = array();
        $this->onReturn = false;
        $this->onReturnParameter = '';
        $this->fieldLinks = array();
        $this->fieldLinksBuffer = array();
        $this->fieldsHidden = array();
        $this->rememberFormPosition = false;
        $this->encoding = self::ENCODING_URLENCODED;
        $this->edit = false;
        $this->validationDisabled = false;
        $this->isCorrect = true;
        $this->onCorrectFields = array();
    }

    /**
     * Set form encoding
     *
     * @param integer $encoding The ENCODING_ constants
     * @return \FormHandler
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * Remember the form position when form is in error state or resubmitted
     *
     * @param boolean $bool
     * @return \FormHandler
     */
    public function rememberPagePosition($bool = true)
    {
        Field\Hidden::set($this, $this->name . '_position')
            ->setValue(0)
            ->hideFromOnCorrect();

        $this->rememberFormPosition = (bool) $bool;
        return $this;
    }

    /**
     * Set the field name to read the value from to be pushed to the onCorrect handlers
     *
     * @param string $field
     * @return \FormHandler
     */
    public function setOnCorrectField($field)
    {
        if($this->fieldExists($field))
        {
            //we use the key for performance reasons and to avoid doubles
            $this->onCorrectFields[$field] = true;
        }
        return $this;
    }

    /**
     * Removes given field from onCorrect data
     *
     * @param string $field
     * @return \FormHandler
     */
    public function unsetOnCorrectField($field)
    {
        unset($this->onCorrectFields[$field]);
        return $this;
    }

    /**
     * Move a field before give target field
     *
     * @param string $target_field
     * @param string $field_to_move
     * @return boolean
     */
    public function moveFieldBefore($target_field, $field_to_move)
    {
        return $this->moveFieldByOffset($target_field, $field_to_move, -1);
    }

    /**
     *
     * @param Field\Field|string $target_field
     * @param Field\Field|string $field_to_move
     * @return boolean
     */
    public function moveFieldAfter($target_field, $field_to_move)
    {
        return $this->moveFieldByOffset($target_field, $field_to_move, 0);
    }

    /**
     * Move the field an X offset after target.
     * Use negative values to insert before
     *
     * @param Field\Field|string $target_field
     * @param Field\Field|string $field_to_move
     * @param integer $offset
     * @return boolean
     */
    public function moveFieldByOffset($target_field, $field_to_move, $offset)
    {
        if(!$this->fieldExists($target_field) || !$this->fieldExists($field_to_move))
        {
            return false;
        }

        //stringify names
        $target_name = ($target_field instanceof Field\Field) ? $target_field->getName() : $target_field;
        $move_name = ($field_to_move instanceof Field\Field) ? $field_to_move->getName() : $field_to_move;

        //retrieve field data
        $move_data = $this->fields[$move_name];

        //unset field
        unset($this->fields[$move_name]);

        //find key position
        $position = $this->getFieldPositionInForm($target_name);

        //target position not found
        if($position === -1)
        {
            return false;
        }

        $before_elements = array_slice($this->fields, 0, $position + $offset, true);
        $after_elements = array_slice($this->fields, $position + $offset, null, true);

        $before_elements[$move_name] = $move_data;

        $this->fields = array_merge($before_elements, $after_elements);
        return true;
    }

    /**
     * Get numberic field position
     *
     * @param Field\Field|string $field
     * @return int -1 when not found
     */
    protected function getFieldPositionInForm($field)
    {
        $name = ($field instanceof Field\Field) ? $field->getName() : $field;
        $keys = array_keys($this->fields);
        $i = 1;
        $position = -1;
        foreach($keys as $key)
        {
            if($key === $name)
            {
                $position = $i;
                break;
            }
            $i++;
        }
        return $position;
    }

    /**
     * Set the mask for buttons, easy to group buttons
     *
     * @param integer $button_count
     * @param array|string $classes
     * @param string $html
     */
    public function set_button_mask($button_count, $classes = false, $html = false)
    {
        $classes = ($classes === false) ? array() : $classes;
        $classes = (!is_array($classes)) ? array($classes) : $classes;

        $classes[] = 'button-row';

        $fields = str_repeat('%field%', $button_count);

        $html = ($html === false) ? '' : $html;

        $this->setMask(
            '<div class="' . implode(' ', $classes) . '">' . $html . $fields . '</div><div class="clear"></div>', false
        );
    }

    /*     * ***************************************************** */
    /*     * *********** LOOK & FEEL ***************************** */
    /*     * ***************************************************** */

    /**
     * FormHandler::parseErrorStyle()
     *
     * Set the style class on a by %error_style% specified element
     *
     * @param string $mask html for the field
     * @return string
     * @author Ronald Hulshof
     * @since 07-01-2009
     */
    private function parseErrorStyle($mask)
    {
        // Get element containing %error_style%
        $pattern = '/<[^<>]*%error_style%[^<>]*>/';

        if(preg_match($pattern, $mask, $result))
        {
            $element = $result[0];

            // Check if class-attribute already exists in element
            if(preg_match('/class=\"[^"]*"/', $element))
            {
                // Class-attribute exists; add style
                $pattern = array('/class="/', '/\s*%error_style%\s*/');
                $replace = array('class="error ', '');
                $new_elem = preg_replace($pattern, $replace, $element);
                $mask = str_replace($element, $new_elem, $mask);
            }
            else
            {
                // Class-attribute does not exist; create it
                $new_elem = preg_replace('/%error_style%/', 'class="error"', $element);
                $mask = str_replace($element, $new_elem, $mask);
            }
        }
        return $mask;
    }

    /**
     * Formhandler::parseErrorFieldStyle
     *
     * Set the error class to the field itself
     *
     * @param string $field
     * @return string
     * @author Johan Wiegel
     * @since 25-08-2009
     */
    private function parseErrorFieldStyle($field)
    {
        // Check if class-attribute already exists in element
        if(preg_match('/class=\"[^"]*"/', $field) || preg_match('/class=\'[^"]*\'/', $field))
        {
            // Class-attribute exists; add style
            $pattern = array('/class="/', '/class=\'/');
            $replace = array('class="error ', 'class=\'error ');
            return preg_replace($pattern, $replace, $field);
        }
        elseif(preg_match('/class=[^"]*/', $field))
        {
            // Class-attribute exists; add style
            $pattern = array('/class=/');
            $replace = array('class=error ');
            return preg_replace($pattern, $replace, $field);
        }
        else
        {
            // Class-attribute does not exist; create it
            if(\FormHandler\Configuration::get('xhtml_close') != '' && !preg_match('/\<select /', $field) && !preg_match('/\<textarea name/', $field))
            {
                return preg_replace('/\/>/', 'class="error" />', $field);
            }

            if(preg_match('/\<textarea name/', $field))
            {
                return preg_replace('/<textarea /', '<textarea class="error" ', $field);
            }
            elseif(preg_match('/\<select name/', $field))
            {
                return preg_replace('/<select /', '<select class="error" ', $field);
            }
            else
            {
                return preg_replace('/>/', ' class="error">', $field);
            }
        }
    }

    /**
     * FormHandler::addHTML()
     *
     * Add some HTML to the form
     *
     * @param string $html The HTML we have to add to the form
     * @return FormHandler
     * @author Teye Heimans
     */
    public function addHTML($html)
    {
        $this->fields[] = array('__HTML__', $html);
        return $this;
    }

    /**
     * FormHandler::addLine()
     *
     * Add a new row to the form.
     *
     * @param string $text Possible data to set into the row (line)
     * @return FormHandler
     * @access public
     * @author Teye Heimans
     */
    public function addLine($text = null)
    {
        $this->fields[] = array('__LINE__', sprintf(Configuration::get('line_mask'), $text));
        return $this;
    }

    /**
     * FormHandler::borderStart()
     *
     * Begin a new fieldset
     *
     * @param string $caption The caption of the fieldset
     * @param string $name The name of the fieldset
     * @param string $extra Extra css or javascript which should be placed in the fieldset tag
     * @return FormHandler
     * @author Teye Heimans
     */
    public function borderStart($caption = null, $name = null, $extra = '')
    {
        static $i = 1;

        if(empty($name))
        {
            $i++;
            $name = 'fieldset' . $i;
        }

        $this->fields[] = array(
            '__FIELDSET__',
            array($name, $caption, $extra)
        );
        return $this;
    }

    /**
     * FormHandler::borderStop()
     *
     * Stops a fieldset
     *
     * @return FormHandler
     * @author Teye Heimans
     */
    public function borderStop()
    {
        $this->fields[] = array('__FIELDSET-END__', true);
        return $this;
    }

    /**
     * FormHandler::setMask()
     *
     * Sets a mask for the new row of fields
     *
     * @param string $mask The mask we have to use
     * @param int|bool $repeat If we have to repeat the mask. When a integer is given, it will be countdown
     * @return FormHandler
     * @author Teye Heimans
     * @since 14-02-2008 Changed in order to also parse php as a template by Johan Wiegel
     */
    public function setMask($mask = null, $repeat = true)
    {
        // when no mask is given, set the default mask
        if(is_null($mask))
        {
            $mask = Configuration::get('default_row_mask');
        }
        // a mask is given.. is it a file ?
        // double check of PHP bug in file_exists
        elseif(file_exists($mask) && is_file($mask))
        {
            // is the file readable ?
            if(is_readable($mask))
            {
                // get the contents of the file and parse php code in it
                $mask = $this->getIncludeContents($mask);
            }
            // the file is not readable!
            else
            {
                trigger_error('Could not read template ' . $mask, E_USER_WARNING);
            }
        }

        // save the mask
        $this->fields[] = array('__MASK__', array($mask, $repeat));
        return $this;
    }

    /**
     * Get the file contents by including it, to enable parsing of php files
     *
     * @param string $filename the file to get/parse
     * @return boolean|string
     * @author sid benachenhou
     * @since 14-02-2008 added by Johan Wiegel
     */
    private function getIncludeContents($filename)
    {
        if(is_file($filename))
        {
            ob_start();
            include $filename;
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
        return false;
    }

    /**
     * FormHandler::setErrorMessage()
     *
     * @return FormHandler
     * @deprecated
     */
    public function setErrorMessage($field,$message)
    {
        $fld = $this->getField($field);
        if(!is_null($fld))
        {
            $fld->setErrorMessage($message);
        }
        return $this;
    }

    /**
     * FormHandler::newPage()
     *
     * Put the following fields on a new page
     *
     * @return FormHandler
     * @author Teye Heimans
     */
    public function newPage()
    {
        $this->pageCounter++;

        $this->fields[] = array('__PAGE__', $this->pageCounter);
        return $this;
    }

    /**
     * FormHandler::setTabIndex()
     *
     * Set the tab index for the fields
     *
     * @param mixed $tabs array or comma seperated string with the field names.
     * When an array is given the array index will set as tabindex
     * @return FormHandler
     * @author Teye Heimans
     */
    public function setTabIndex($tabs)
    {
        // is the given value a string?
        if(is_string($tabs))
        {
            // split the commas
            $tabs = explode(',', $tabs);

            // add an empty value so that the index 0 isnt used
            array_unshift($tabs, '');
        }
        // is the given value an array
        elseif(is_array($tabs) && isset($tabs[0]))
        {
            // is set element 0, then move all elements
            // (0 is not a valid tabindex, it starts with 1)
            ksort($tabs);
            $new = array();

            foreach($tabs as $key => $value)
            {
                while(array_key_exists($key, $new) || $key <= 0)
                {
                    $key++;
                }
                $new[$key] = $value;
            }
            $tabs = $new;
        }

        // array with tabs set ?
        if(isset($tabs))
        {
            // walk each tabindex
            foreach($tabs as $key => $value)
            {
                // if there is a field..
                if(!empty($value))
                {
                    $tabs[$key] = trim($value);
                }
                // no field is given, remove it's index
                else
                {
                    unset($tabs);
                }
            }

            // save the tab indexes
            $this->tabIndexes = $this->tabIndexes + $tabs;
        }
        return $this;
    }

    /**
     * FormHandler::setLanguage()
     *
     * Set the language we should use for error messages etc.
     * If no language is given, try to get the language defined by the visitors browser.
     *
     * @param string $language The language we should use
     * @return FormHandler
     * @author Teye Heimans
     */
    public function setLanguage($language = null)
    {
        // if nog language is given, try to get it from the visitors browser if wanted
        if(is_null($language))
        {
            // auto detect language ?
            $isset = false;
            if(Configuration::get('auto_detect_language') == true)
            {
                // get all accepted languages by the browser
                $lang = array();
                if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
                {
                    foreach(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $sValue)
                    {
                        if(strpos($sValue, ';') !== false)
                        {
                            list($sValue, ) = explode(';', $sValue);
                        }
                        if(strpos($sValue, '-') !== false)
                        {
                            list($sValue, ) = explode('-', $sValue);
                        }
                        $lang[] = $sValue;
                    }
                }

                // set the language which formhandler supports
                foreach($lang as $l)
                {
                    // check if the language file exists
                    if(file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . strtolower($l) . '.php'))
                    {
                        // set the language
                        $this->setLanguage($l);
                        $isset = true;
                        break;
                    }
                }
            }

            // no language is set yet.. set the default configured language
            if(!$isset
                && !is_null(Configuration::get('default_language')))
            {
                return $this->setLanguage(Configuration::get('default_language'));
            }
            
            //we default to hardcoded english
            return $this->setLanguage('en');

        }

        // make sure that the language is set in lower case
        $language = strtolower($language);

        // check if the language does not contain any slashes or dots
        if(preg_match('/\.|\/|\\\/', $language))
        {
            if($language != Configuration::get('default_language'))
            {
                $this->setLanguage(Configuration::get('default_language'));
            }
            return $this;
        }

        // check if the file exists
        if(file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $language . '.php'))
        {
            // include the language file
            include __DIR__ . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $language . '.php';

            // load the array from the text file
            $this->languageArray = $fh_lang;

            // save the language
            $this->languageActive = $language;
        }
        elseif($language != Configuration::get('default_language'))
        {
            $this->setLanguage(Configuration::get('default_language'));
        }
        // language file does not exists
        else
        {
            trigger_error(
                'Unknown language: ' . $language . '. Can not find ' .
                'file ./language/' . $language . '.php!', E_USER_ERROR
            );
        }
        return $this;
    }

    /**
     * FormHandler::catchErrors()
     *
     * Get the errors occoured in the form
     *
     * @param boolean $display If we still have to display the errors in the form (default this is disabled)
     * @return array of errors or an empty array if none occoured
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function catchErrors($display = false)
    {
        // only return the errors when the form is posted
        // and the form is not correct
        if(!($this->isPosted() && !$this->isCorrect()))
        {
            return array();
        }
        $this->displayErrors = $display;

        // walk each field and get the error of the field
        $errors = array();
        foreach($this->fields as $field => $obj)
        {
            /** @var Field */
            $fld = $this->getField($field);

            //check error
            if(is_null($fld)
                || !method_exists($fld, 'getErrorState')
                || !method_exists($fld, 'getErrorMessage')
                || !method_exists($fld, 'processValidators'))
            {
                continue;
            }

            if(is_null($fld->getErrorState()))
            {
                //run the validators
                $fld->processValidators();
            }

            if($fld->getErrorState())
            {
                $errors[$field] = $fld->getErrorMessage();
            }
        }
        return $errors;
    }

    /**
     * FormHandler::setFocus()
     *
     * Set the focus to a specific field
     *
     * Set to false to disable focus
     *
     * @param string $field The field which should get the focus
     * @return boolean: true if the focus could be set, false if not
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setFocus($field)
    {
        // if the field is false, no focus has to be set...
        if($field === false)
        {
            $this->focus = false;
            return true;
        }

        // check if the field exists
        if(!$this->fieldExists($field))
        {
            $this->focusBuffer = $field;
            return true;
        }

        //check if we can ask for focus
        if(method_exists($this->getField($field),'getFocus'))
        {
            //register field name when focus is available
            $this->focus = !is_null($this->getField($field)->getFocus()) ? $field : null;

            if(!is_null($this->focus))
            {
                $this->focusBuffer = null;
                return true;
            }
        }
        return false;
    }

    /**
     * FormHandler::enableViewMode()
     *
     * Set all fields in view mode
     *
     * @param boolean $mode The new state of the Forms View Mode
     * @return FormHandler
     */
    public function enableViewMode($mode = true)
    {
        $this->viewMode = (bool) $mode;
        return $this;
    }

    /**
     * FormHandler::isViewMode()
     *
     * Gets the ViewMode state
     *
     * @return boolean
     * @author Teye Heimans
     */
    public function isViewMode()
    {
        return $this->viewMode;
    }

    /**
     * FormHandler::setFieldViewMode()
     *
     * Sets and indiviual fields display mode
     *
     * @param string|array $field The name of the field to set the display mode for
     * @param boolean $mode True = field is view only
     * @return FormHandler
     * @author Ruben de Vos
     * @author Marien den Besten
     */
    public function setFieldViewMode($field, $mode = true)
    {
        //check if input is array
        if(is_array($field) && count($field != 0))
        {
            foreach($field as $f)
            {
                $this->setFieldViewMode($f);
            }
            return $this;
        }

        // does the field exist?
        if($this->fieldExists($field))
        {
            // set the new modes
            $this->getfield($field)->setViewMode($mode);
        }
        // the field does not exists! buffer the field
        else
        {
            $this->bufferViewmode[$field] = $mode;
        }
        return $this;
    }

    /**
     * FormHandler::isFieldViewMode()
     *
     * Check if the field should be displayed as view only
     *
     * @param string $field The field to check
     * @return boolean
     */
    public function isFieldViewMode($field)
    {
        // does the field exists?
        if($this->fieldExists($field)
            && method_exists($this->getField($field), 'getViewMode'))
        {
            return $this->getField($field)->getViewMode();
        }
        elseif(array_key_exists($field,$this->bufferViewmode))
        {
            //field is buffered
            return $this->bufferViewmode[$field];
        }
        // the field does not exists! error!
        else
        {
            trigger_error(
                'Error, could not find field "' . $field . '"! Please define the field first!',
                E_USER_NOTICE
            );
        }
    }
    /*     * ***************************************************** */
    /*     * *********** DATA HANDLING *************************** */
    /*     * ***************************************************** */

    /**
     * FormHandler::getValue()
     *
     * @param string $field The field which value we have to return
     * @return string|false
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function getValue($field)
    {
        $value = false;
        $fld = $this->getfield($field);

        // is it a field?
        if(!is_null($fld) && method_exists($fld, 'getValue'))
        {
            $value = $fld->getValue();
        }
        // _cache contains the values of the fields after flush() is called
        // (because then all objects are removed from the memory)
        elseif(isset($this->fieldsBuffer[$field]))
        {
            $value = $this->fieldsBuffer[$field];
        }
        // is it a set value of a field which does not exists yet ?
        elseif(isset($this->buffer[$field]))
        {
            $value = $this->buffer[$field][1];
        }
        // is it a value from the $_POST array ?
        elseif(isset($_POST[$field]))
        {
            $value = $_POST[$field];
        }

        return $value;
    }

    /**
     * FormHandler::value()
     *
     * @param string $field The field which value we have to return
     * @return string|false
     * @author Teye Heimans
     * @author Marien den Besten
     * @deprecated Please use getValue
     */
    public function value($field)
    {
        return $this->getValue($field);
    }

    /**
     * FormHandler::setValue()
     *
     * Set the value of the spicified field
     *
     * @param string $field The field which value we have to set
     * @param string $value The value we have to set
     * @param boolean $overwriteCurrentValue Do we have to overwrite the current value of the field (posted value)
     * @return FormHandler
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setValue($field, $value, $overwriteCurrentValue = false)
    {
        // the field does not exists. Save the value in the buffer.
        // the field will check this buffer and use it value when it's created
        if(!$this->fieldExists($field))
        {
            $this->buffer[$field] = array($overwriteCurrentValue, $value);
            return $this;
        }

        $this->getField($field)->setValue($value, $overwriteCurrentValue);

        $this->callProvider($field, true);
        return $this;
    }

    /**
     * FormHandler::onCorrect()
     *
     * Set the function/callback which has to be called when the form is correct
     *
     * Callback will retrieve 2 parameters: data[] and FormHandler()
     *
     * Following return values will be processed from the callback:
     *
     * true = Default, hide form after submit
     * false = Shows form again after submit
     * string = Hide form and show the given string (HTML allowed)
     *
     * Registering multiple onCorrect callbacks is also possible. All functions
     * return values will be processed, false overrules true and a string overrules a false.
     *
     * @param callable $callback The name of the function
     * @return FormHandler
     * @author Teye Heimans
     */
    public function onCorrect($callback)
    {
        if(!is_callable($callback))
        {
            trigger_error('Error, the onCorrect function ' . json_encode($callback) . ' is not callable', E_USER_ERROR);
        }
        $this->onCorrect[] = $callback;
        return $this;
    }

    /**
     * Set a location to which the form needs to return if correct
     *
     * @param string $location
     * @return FormHandler
     */
    public function onReturn($location)
    {
        if(is_string($location))
        {
            $this->onReturn = $location;
        }
        return $this;
    }

    /**
     * Get on return
     *
     * @author Marien den Besten
     * @return string|boolean
     */
    public function getOnReturn()
    {
        return $this->onReturn;
    }

    /**
     * Set additional parameters to the onReturn url (if set)
     *
     * It will be directly appended to the onReturn value
     *
     * @author Marien den Besten
     * @return FormHandler
     */
    public function onReturnParameter($extra)
    {
        if(is_string($extra))
        {
            $this->onReturnParameter = $extra;
        }
        return $this;
    }

    /**
     * FormHandler::setError()
     *
     * Set a field to error state, possible with custom error message
     *
     * @param string $field The field to set the error for
     * @param string $error The error message to use
     * @return FormHandler
     * @author Filippo Toso - filippotoso@libero.it
     * @author Marien den Besten
     */
    public function setError($field, $error = false)
    {
        if($this->fieldExists($field))
        {
            $this->getField($field)->setErrorMessage($error);
            $this->getField($field)->setErrorState(true);
        }
        return $this;
    }

    /*     * ***************************************************** */
    /*     * *********** GENERAL ********************************* */
    /*     * ***************************************************** */

    /**
     * FormHandler::getLastSubmittedPage()
     *
     * Returns the page number of the last submitted page of the form
     *
     * @return integer
     * @author Remco van Arkelen & Johan Wiegel
     * @since 21-08-2009
     */
    public function getLastSubmittedPage()
    {
        return $this->getPage();
    }

    /**
     * FormHandler::getPage()
     *
     * Returns the page number of the last submitted page the form (when getPage is called)
     *
     * @return integer
     * @author Teye Heimans
     */
    public function getPage()
    {
        return $this->pageCounter;
    }

    /**
     * FormHandler::getCurrentPage()
     *
     * Returns the current page number of the current form (used when newPage is used!)
     *
     * @return integer
     * @author Teye Heimans
     */
    public function getCurrentPage()
    {
        return $this->pageCurrent;
    }

    /**
     * FormHandler::setCurrentPage()
     *
     * Set the current page number of the current form (used when newPage is used!)
     *
     * @param int $integer
     * @author marien
     * @return FormHandler
     */
    public function setCurrentPage($integer)
    {
        $this->pageCurrent = (int) $integer;
        return $this;
    }

    /**
     * Link fields
     *
     * Handler will be called with the following parameters:
     * - latest value of from field
     * - instance of the current form
     * - name from the from field
     * - possible extra fields: array with key - value
     * - boolean indicating initial request
     *
     * @author Marien den Besten
     * @param string $field_from
     * @param string $field_to
     * @param callable $value_handler Must return with the self::returnDynamic() or self::returnDynamicOther() function
     * @param array $extra
     * @return static
     */
    public function link(
        $field_from, $field_to, $value_handler, $extra = false)
    {
        if(is_string($extra))
        {
            $extra = array($extra);
        }

        $this->fieldLinks[$field_from . '_' . $field_to] = array(
            'from'    => $field_from,
            'to'      => $field_to,
            'handler' => $value_handler,
            'extra'   => $extra
        );

        if($this->fieldExists($field_from) && ($this->fieldExists($field_to) || substr($field_to, 0, 1) == '#'))
        {
            $this->linkNow($field_from, $field_to);
        }
        else
        {
            $this->fieldLinksBuffer[$field_from] = $field_to;
        }

        return $this;
    }

    /**
     * Removes a link when exists
     *
     * @param string $field_from
     * @param string $field_to
     * @return static
     */
    public function unlink($field_from, $field_to)
    {
        $check = $field_from . '_' . $field_to;

        if(!array_key_exists($check, $this->fieldLinks))
        {
            return $this;
        }
        unset($this->fieldLinks[$check]);

        if(array_key_exists($field_from, $this->attachSelect))
        {
            $search = array_search($field_to, $this->attachSelect[$field_from]);

            if($search !== false)
            {
                unset($this->attachSelect[$field_from][$search]);
            }

            if(empty($this->attachSelect[$field_from]))
            {
                unset($this->attachSelect[$field_from]);
            }
        }

        if(array_key_exists($field_from, $this->fieldLinksBuffer)
            && $this->fieldLinksBuffer[$field_from] == $field_to)
        {
            unset($this->fieldLinksBuffer[$field_from]);
        }
        return $this;
    }

    /**
     * Truncate string
     *
     * Also available in the Javascript library under the name FormHandler.truncateString();
     *
     * @param string $n
     * @param integer $length
     * @return string
     */
    public function truncateString($n, $length = null)
    {
        $lngth_processed = (is_null($length)) ? 30 : $length;
        $chunk = ($lngth_processed-2 > 2) ? round(($lngth_processed-2)/2) : 0;

        if(strlen($n)-2 > $lngth_processed
            && $chunk > 2)
        {
            $start = substr($n, 0, $chunk);
            $end = substr($n, strlen($n)-$chunk, strlen($n));
            $n = $start .'...'. $end;
        }
        return $n;
    }

    /**
     * Set the needed javascript (once) to enable correct usage of the datepicker
     *
     * @author marien
     */
    protected function setJsDatePicker()
    {
        $first_time = $this->loadJsLibrary('datepicker');
        if($first_time)
        {
            //detect proper date formatting
            $js_format = array(
                'j-n-Y' => 'e-n-Y',
                'd-m-Y' => 'd-m-Y',
                'd.m.Y' => 'd.m.Y',
                'd.M.Y' => 'd.b.Y',
                'n/j/Y' => 'n/e/Y',
                'n/j/y' => 'n/e/y',
                'm/d/y' => 'm/d/y',
                'm/d/Y' => 'm/d/Y',
                'Y/m/d' => 'Y/m/d',
                'Y-m-d' => 'Y-m-d',
            );
            $preference = (class_exists('account_person'))
                ? account_person::get_localization_preference('date_short')
                : null;
            $user_js_format = (!array_key_exists($preference, $js_format))
                ? (defined('DEFAULT_DATE_SHORT') ? $js_format[DEFAULT_DATE_SHORT] : 'd-m-Y')
                : $js_format[$preference];
            $this->_setJS('var FH__DatePickerFormat = "' . $user_js_format . '";' . "\n");
        }
    }

    /**
     * Sets AJAX header, displays value and stops page execution
     * @param mixed $value
     */
    public static function returnAjaxResponse($value)
    {
        if(ob_get_contents() !== false)
        {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($value);
        exit();
    }

    /**
     * Return html type
     *
     * @param string $html
     * @return array
     */
    public static function returnDynamicOther($html)
    {
        return array('other' => $html);
    }

    /**
     * To be used at the link function to return values
     *
     * @param mixed $value
     * @param array $options
     * @param boolean $disabled
     * @param string $field_type
     * @param boolean $use_array_key_as_value
     * @return array
     */
    public static function returnDynamic(
        $value = null, $options = null, $disabled = null, $hide = null, $field_type = 'select',
        $use_array_key_as_value = true)
    {
        $return = array();

        if(is_array($options))
        {
            $new = array();
            $new_given = array();
            // generate a javascript array from the given array
            foreach($options as $key => $v)
            {
                $key = ($use_array_key_as_value === true) ? $key : $v;
                $new[] = array(
                    'key' => (string) $key,
                    'value' => (string) \FormHandler\Utils::html($v, ENT_NOQUOTES | ENT_IGNORE)
                );
                $new_given[$key] = $v;
            }

            $return['options'] = $new_given;
            $return['new_options'] = $new;
        }

        if(!is_null($hide))
        {
            $return['hide'] = $hide;
        }

        if(is_bool($disabled))
        {
            $return['disabled'] = $disabled;
        }

        if(!is_null($field_type)
            && ($field_type == 'select'
                || $field_type == 'text'
                || $field_type == 'textarea'
                || $field_type == 'date'
                || $field_type == 'integer'
                || $field_type == 'checkbox'
                || $field_type == 'password'))
        {
            $return['field_type'] = $field_type;
        }

        if(!is_null($value))
        {
            $return['value'] = (is_string($value)) ? trim($value) : $value;

            if($field_type == 'checkbox' && is_array($return['value']))
            {
                foreach($return['value'] as $key => $value)
                {
                    $return['value'][(string) $key] = (string) $value;
                }
            }
        }
        return $return;
    }

    /**
     * FormHandler::getTitle()
     *
     * Return the title of the given field name
     *
     * @param string $field The fieldname where to retrieve the title from
     * @return string|null
     * @author Teye Heimans
     */
    public function getTitle($field)
    {
        $fld = $this->getField($field);
        // check if the field exists
        if(!is_null($fld) && is_object($fld))
        {
            // check if the field is a child of the "field" class
            if(is_subclass_of($fld, 'field'))
            {
                // return the title
                return $this->fields[$field][0];
            }
            else
            {
                // print an error message
                $class_name = strtolower(get_class($fld));
                trigger_error(
                    'Error, cannot retrieve title of this kind of field: ' . $class_name, E_USER_WARNING
                );
            }
        }
        // the given field does not exists!
        else
        {
            trigger_error(
                'Could not find field "' . $field . '"', E_USER_WARNING
            );
        }
        return null;
    }

    /**
     * Disable validation of the form
     *
     * May be used with 'setIsCorrect'
     * s
     * @param boolean $boolean
     * @return static
     */
    public function setValidationDisabled($boolean = true)
    {
        $this->validationDisabled = (bool) $boolean;
        return $this;
    }

    /**
     * FormHandler::getLanguage()
     *
     * Return the language used for the form
     *
     * @return string: the language
     * @author Teye Heimans
     */
    public function getLanguage()
    {
        return $this->languageActive;
    }

    /**
     * FormHandler::fieldExists()
     *
     * Check if the field exists in the form
     *
     * @param Field|string $field The field to check if it exists in the form or not
     * @return boolean
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function fieldExists($field)
    {
        $name = ($field instanceof Field\Field) ? $field->getName() : $field;
        return array_key_exists($name, $this->fields);
    }

    /**
     * FormHandler::getFormName()
     *
     * Return the name of the form
     *
     * @return string: the name of the form
     * @author Teye Heimans
     */
    public function getFormName()
    {
        return $this->name;
    }

    /**
     * Return the current defined css items
     *
     * @return string
     * @author Marien den Besten <marien@color-base.com>
     */
    private function getCssCode()
    {
        $return = '';
        foreach($this->css as $code)
        {
            $return .= $code . "\n";
        }
        return $return;
    }

    /**
     * FormHandler::getJavascriptCode()
     *
     * Return the needed javascript code for this form
     *
     * @param $header Return the javascript code for in the header (otherwise the javascript code which has
     *  to be beneath the form will be returned)
     * @return string: the needed javascript code for this form
     * @author Teye Heimans
     *
     * @since 17-08-2009 removed static before $return in order to handle multiple forms on a page. JW
     */
    public function getJavascriptCode($header = true)
    {
        $return = array(0 => false, 1 => false);
        $s = $header ? 0 : 1;

        // if the javascript is not retrieved yet..
        if(!$return[$s])
        {
            // generate the js "files" script
            $result = '';
            if(isset($this->js[$s]['file']) && is_array($this->js[$s]['file']))
            {
                foreach($this->js[$s]['file'] as $line)
                {
                    $result .= '<script type="text/javascript" src="' . $line . '"></script>' . "\n";
                }
            }
            // generate the other js script
            if(isset($this->js[$s]['code']) && is_array($this->js[$s]['code']))
            {
                $result .= '<script type="text/javascript">' . "\n";
                foreach($this->js[$s]['code'] as $code)
                {
                    $result .= $code;
                }
                $result .= "</script>\n";
            }

            $return[$s] = true;
            return $result;
        }

        return '';
    }

    /**
     * FormHandler::checkPassword()
     *
     * Preform a password check on 2 password fields:
     * - both values are the same
     * - the values are longer then a minimum length (configured in the config file)
     * - on an add-form, the fields are required
     * - on an edit-form, the fields can be left empty, and the old password will stay (no changes will take place)
     *
     * @param string $field1 The first password field we should check
     * @param string $field2 The second password field we should check
     * @param boolean $setEditMsg Should a message beeing displayed in an edit
     * form that when leaving the fields blank the current passwords will be kept?
     * @return void
     * @author Teye Heimans
     */
    public function checkPassword($field1, $field2, $setEditMsg = true)
    {
        // check if the fields exists and that they are both password fields
        if(!$this->fieldExists($field1)
            || !$this->fieldExists($field2)
            || !$this->getField($field1) instanceof Field\Password
            || !$this->getfield($field2) instanceof Field\Password)
        {
            trigger_error('Error: unknown field used in checkPassword!');
            return;
        }

        // add some text to notify the user that he only has to enter his
        // password when he wants to change it
        if(isset($this->edit) && $this->edit && $setEditMsg)
        {
            $this->getField($field1)->setPre($this->_text(25));
        }

        // is the form posted and this page is posted in case of mulitple page form.
        if($this->isPosted() && ($this->getPage() == $this->getCurrentPage()))
        {
            // let password field 1 check if it matches password field 2
            $this->getField($field1)->checkPassword($this->getField($field2));
            $this->getField($field2)->checkPassword($this->getField($field1));
        }
    }

    /**
     * FormHandler::isPosted()
     *
     * If the form is posted
     *
     * @return boolean
     * @author Teye Heimans
     */
    public function isPosted()
    {
        return $this->posted;
    }

    /**
     * Option to change posted behavior
     *
     * This is mainly used when using a postback to update the form
     *
     * @param boolean $posted
     */
    public function setPosted($posted)
    {
        $this->posted = $posted;
    }

    /**
     * This function checks if all fields are valid and
     * changes the field status based upon that
     *
     * It also sets the focus when no focus preference is set in the form
     */
    public function runValidation()
    {
        $result = true;
        foreach($this->fields as $id => $data)
        {
            if($data[0] == '__PAGE__' && $this->pageCurrent == $data[1])
            {
                break;
            }

            if($this->fieldIsHidden($id) || !is_object($data[1]) || !method_exists($data[1], 'processValidators')
                || $data[1]->getViewMode())
            {
                continue;
            }

            if(is_null($data[1]->getErrorState()))
            {
                $data[1]->processValidators();
            }

            if($data[1]->getErrorState())
            {
                // the field is not valid. If the focus is not set yet, set the focus to this field
                if(is_null($this->focus) && is_null($this->focusBuffer))
                {
                    $this->setFocus($id);
                }
                $result = false;
            }
        }
        $this->isCorrect = $result;
    }

    /**
     * FormHandler::isCorrect()
     *
     * Return if the form is filled correctly (for the fields which are set!)
     *
     * @return boolean
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function isCorrect()
    {
        if($this->validationDisabled === false)
        {
            $this->runValidation();
        }
        return $this->isCorrect;
    }

    /**
     * Check if button is clicked
     *
     * @param string $button
     * @return boolean
     */
    public function isButtonClicked($button)
    {
        return ((string) $button === $this->clicked);
    }

    /**
     * Set which button is clicked
     *
     * Gets automatically called with the clicked button
     *
     * @param string $button
     * @return static
     */
    public function setButtonClicked($button)
    {
        if($this->fieldExists($button)
            && $this->getField($button) instanceof Button\Button)
        {
            $this->clicked = $button;
        }
        return $this;
    }

    /**
     * Get which button is clicked
     *
     * @return string|null
     */
    public function getButtonClicked()
    {
        return $this->clicked;
    }

    /**
     * Set the field to a (in)correct state
     *
     * May be used in combination with 'setValidationDisabled'
     *
     * @param boolean $boolean
     * @return static
     */
    public function setIsCorrect($boolean)
    {
        $this->isCorrect = (bool) $boolean;
        return $this;
    }

    /**
     * FormHandler::flush()
     *
     * Returns the form
     *
     * @return string The form
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function flush()
    {
        //check if no linked fields are undefined
        if(count($this->fieldLinksBuffer) != 0)
        {
            $temp_field_list = $this->fieldLinksBuffer;
            $field_links = array_map(function($value) use ($temp_field_list)
            {
                return key($temp_field_list) .':'. $value;
            }, $temp_field_list);

            trigger_error(
                'Not all linked fields are defined: ' . implode(', ', $field_links), E_USER_WARNING
            );
        }

        //load libraries when fields are linked
        if(count($this->fieldLinks) != 0)
        {
            $this->loadJsLibrary('linked_select');
            $this->loadJsLibrary('utils');
            $this->setJsDatePicker();
        }

        if(isset($_POST['appearance'])
            && isset($_POST['values'])
            && isset($_POST['form_name'])
            && $_POST['form_name'] == $this->getFormName())
        {
            $values = json_decode($_POST['values'], true);

            if(!is_array($values))
            {
                exit();
            }

            $result = array();
            foreach($values as $for_field => $vs)
            {
                $field = $this->getField($for_field);

                if(is_null($field))
                {
                    continue;
                }

                foreach($vs as $to_field => $value)
                {
                    if(substr($value, 0, 11) == '__FH_JSON__')
                    {
                        $vs[$to_field] = json_decode(substr($value, 11), true);
                    }
                }

                $values_key = array_keys($vs);

                $conditions = $field->getAppearanceCondition();
                foreach($conditions as $condition)
                {
                    list($watches, $callback) = $condition;
                    $check = array_diff($values_key,$watches);

                    if(!empty($check))
                    {
                        continue;
                    }

                    $result[$for_field] = call_user_func_array($callback, array(
                        $vs,
                        $for_field,
                        $this
                    ));
                    break;
                }
            }

            self::returnAjaxResponse($result);
            exit();
        }

        //check if there is an incoming XHR request
        if(isset($_POST['linkselect'])
            && isset($_POST['fields'])
            && isset($_POST['filter'])
            && isset($_POST['field_from'])
            && isset($_POST['form_name'])
            && $_POST['form_name'] == $this->getFormName())
        {
            $fields = explode(',', $_POST['fields']);
            $field_from = $_POST['field_from'];
            $return = array();

            $filter = $_POST['filter'];
            if(substr($filter, 0, 11) == '__FH_JSON__')
            {
                $filter = json_decode(substr($filter, 11), true);
            }

            foreach($fields as $field_to)
            {
                if(array_key_exists($field_from . '_' . $field_to, $this->fieldLinks))
                {
                    $link = $this->fieldLinks[$field_from . '_' . $field_to];
                    $extra = false;

                    if(is_array($link['extra']))
                    {
                        $extra = array();
                        foreach($link['extra'] as $fld_to)
                        {
                            $extra[$fld_to] = $filter;
                            if(!array_key_exists($fld_to, $_POST))
                            {
                                continue;
                            }

                            $v = $_POST[$fld_to];
                            if(substr($v, 0, 11) == '__FH_JSON__')
                            {
                                $v = json_decode(substr($v, 11), true);
                            }
                            $extra[$fld_to] = $v;
                        }
                    }

                    $result = call_user_func_array(
                        $link['handler'],
                        array(
                            $filter,
                            $this,
                            $_POST['field_from'],
                            $extra,
                            (isset($_POST['initial'])),
                            $field_to
                        )
                    );

                    if(!is_array($result))
                    {
                        trigger_error(
                            'Linked function does not return a correct format: ' . $_POST['field_from'], E_USER_WARNING
                        );
                    }

                    if(array_key_exists('options', $result))
                    {
                        unset($result['options']);
                    }

                    $field = $this->getField($field_to);

                    if(!is_null($field))
                    {
                        $result['selector_field_to'] = $field->getJsSelectorValue();
                    }

                    $return[$field_to] = $result;
                }
            }

            self::returnAjaxResponse($return);
        }


        foreach($this->fields as $id => $data)
        {
            if($data[0] == '__PAGE__' && $this->pageCurrent == $data[1])
            {
                break;
            }

            //call on post processor if posted
            if($this->isPosted()
                && is_object($this->getField($id))
                && method_exists($this->getField($id), 'onPost'))
            {
                $this->getField($id)->onPost($this);
            }

            //process appearance conditions
            $conditions = (is_object($this->getField($id)) && method_exists($this->getField($id), 'getAppearanceCondition'))
                ? $this->getField($id)->getAppearanceCondition()
                : array();

            foreach($conditions as $tmp)
            {
                list($watches, $callback) = $tmp;

                $values = array();
                foreach($watches as $watch)
                {
                    //convert to object
                    $fieldObject = (!is_object($watch)) ? $this->getField($watch) : $watch;

                    //determine name
                    $name = ($fieldObject instanceof Field\Field) ? $fieldObject->getName() : $watch;

                    //determine value
                    $value = ($fieldObject instanceof Field\Field) ? $fieldObject->getValue() : null;

                    //assign value
                    $values[$name] = $value;
                }

                //call callback
                $show = call_user_func_array(
                    $callback,
                    array(
                        $values,
                        $this
                    )
                );
                $this->fieldHide($id, !$show);

                if(!$this->isViewMode())
                {
                    $this->setFieldAppearanceWatch($values, $id);
                }
            }
        }

        // when the form is not posted or the form is not valid
        if(!$this->isPosted() || !$this->isCorrect())
        {
            //merge buffers
            $check = array_keys($this->buffer) + array_keys($this->bufferViewmode);

            //will only be looped if there is something in the buffer
            foreach($check as $field)
            {
                trigger_error(
                    'Error, could not find field "' . $field . '" to set the viewmode or value! '
                    . 'Please define the field first!',
                    E_USER_NOTICE
                );
            }

            // get the form
            $form = $this->getForm();
        }
        // when the form is not totaly completed yet (multiple pages)
        elseif($this->pageCurrent < $this->pageCounter)
        {
            // get the next form
            $form = $this->getForm($this->pageCurrent + 1);
        }
        elseif($this->isViewMode() == true)
        {
            $form = $this->getForm();
        }
        // when the form is valid
        else
        {
            // generate the data array
            $data = array();
            reset($this->onCorrectFields);
            foreach($this->onCorrectFields as $field => $foo)
            {
                $fld = $this->getField($field);
                if(is_object($fld) && method_exists($fld, 'getValue'))
                {
                    $data[$field] = $fld->getValue();
                }
            }

            // call the users oncorrect function
            if(count($this->onCorrect) != 0)
            {
                $tmp = 0;
                $txt = '';
                foreach($this->onCorrect as $callback)
                {
                    $hideForm = call_user_func_array($callback, array($data, &$this));

                    $val = ($hideForm === false) ? 2 : 0;
                    $val = (is_string($hideForm)) ? 1 : $val;

                    $txt = (is_string($hideForm)) ? $hideForm : $txt;

                    $tmp = max($tmp, $val);
                }
                $hideForm = $tmp;
            }

            // display the form again if wanted..
            if(isset($hideForm) && $hideForm === 2)
            {
                $form = $this->getForm();
            }
            // the user want's to display something else..
            elseif(isset($hideForm) && $hideForm == 1)
            {
                $form = $txt;
            }
            // dont display the form..
            else
            {
                if($this->onReturn !== false && is_string($this->onReturn) && !headers_sent())
                {
                    header('Location: ' . $this->onReturn . $this->onReturnParameter);
                    echo '<a href="' . \FormHandler\Utils::html($this->onReturn . $this->onReturnParameter) . '">continue</a>';
                    exit();
                }
                else
                {
                    $form = '';
                }
            }
        }

        // cache all the fields values for the function value()
        reset($this->fields);
        while(list($fld, ) = each($this->fields))
        {
            // check if it's a field
            if(!is_null($this->getField($fld))
                && method_exists($this->getField($fld), "getValue"))
            {
                $this->fieldsBuffer[$fld] = $this->getField($fld)->getValue();
            }
        }
        return $form;
    }

    /**
     * Load a specific FH JS library
     *
     * Library is the js file name
     *
     * @param string $library
     * @return boolean True when not loaded before
     */
    private function loadJsLibrary($library)
    {
        if(!array_key_exists('__FH_JS_'. $library, $GLOBALS))
        {
            $GLOBALS['__FH_JS_'. $library] = true;
            $this->_setJS(\FormHandler\Configuration::get('fhtml_dir') . 'js/'. $library .'.js', true);
            return true;
        }
        return false;
    }

    /**
     * Do all task to link fields
     *
     * Both fields need to exist
     *
     * @param string $field_from
     * @param string $field_to
     * @author Marien den Besten
     */
    private function linkNow($field_from, $field_to)
    {
        //add fields
        if(!array_key_exists($field_from, $this->attachSelect))
        {
            $this->attachSelect[$field_from] = array();
        }

        $this->attachSelect[$field_from][] = $field_to;

        $this->callProvider($field_from, true);
    }

    /**
     * Call all provider to update data
     * @param string $field_from
     * @param boolean $initial
     * @return void
     * @author Marien den Besten
     */
    private function callProvider($field_from, $initial = false)
    {
        //update data
        if(!array_key_exists($field_from, $this->attachSelect))
        {
            return;
        }

        foreach($this->attachSelect[$field_from] as $field_to)
        {
            $find = $field_from . '_' . $field_to;
            if(!array_key_exists($find, $this->fieldLinks))
            {
                return;
            }
            $link = $this->fieldLinks[$find];

            $extra = array();
            if(is_array($link['extra']))
            {
                //remove from field from the extra array when exists
                if(in_array($field_from, $link['extra']))
                {
                    $key = array_search($field_from, $link['extra']);
                    unset($link['extra'][$key]);
                }

                //get values for linked fields
                foreach($link['extra'] as $field)
                {
                    if($this->fieldExists($field))
                    {
                        $extra[$field] = $this->getValue($field);
                    }
                }
            }

            //call value handler
            $result = call_user_func_array(
                $link['handler'],
                array(
                    $this->getValue($link['from']),
                    $this,
                    $field_from,
                    $extra,
                    $initial,
                    $field_to
                )
            );

            if(!is_array($result))
            {
                trigger_error('Linked function does not return a correct format: ' . $field_from, E_USER_WARNING);
            }

            //when handler gives result
            //we are not able to assign the results to html buffers
            if(is_array($result) && substr($field_to, 0, 1) != '#')
            {
                $to = $this->getField($link['to']);
                //set options if available
                if(array_key_exists('options', $result)
                    && !is_null($to)
                    && method_exists($to, 'setOptions'))
                {
                    $to->setOptions($result['options']);
                }

                if(array_key_exists('value', $result))
                {
                    $to->setValue($result['value']);
                }

                if(array_key_exists('hide', $result) && $result['hide'] === true)
                {
                    $this->fieldHide($link['to']);
                }

                if(array_key_exists('disabled', $result))
                {
                    $to->setDisabled($result['disabled']);
                }
            }
            $this->callProvider($field_to);
        }
    }

    /**
     * Visually hide a field
     *
     * @param string $field
     * @param boolean $hide
     * @author Marien den Besten <marien@color-base.com>
     * @return boolean Only true when field is hidden
     */
    public function fieldHide($field, $hide = true)
    {
        if($hide === true)
        {
            $this->fieldsHidden[$field] = true;
            return true;
        }
        unset($this->fieldsHidden[$field]);
        return false;
    }

    /**
     * Check if the given field is hidden
     *
     * @author Marien den Besten <marien@color-base.com>
     * @param string $field
     * @return boolean
     */
    public function fieldIsHidden($field)
    {
        return array_key_exists($field, $this->fieldsHidden);
    }

    /**
     * FormHandler::getNewButtonName()
     *
     * when no button name is given, get a unique button name
     *
     * @author Teye Heimans
     * @return string the new unique button name
     */
    public function getNewButtonName()
    {
        static $counter = 1;
        $counter++;
        return 'button' . $counter;
    }

    /**
     * FormHandler::_setJS()
     *
     * Set the javascript needed for the fields
     *
     * @param string $js The javascript to set
     * @param boolean $isFile Is the setted javascript a file?
     * @param boolean $before should the javascript be placed before or after the form?
     * @return FormHandler
     * @author Teye Heimans
     */
    public function _setJS($js, $isFile = false, $before = true)
    {
        $this->js[$before ? 0 : 1][$isFile ? 'file' : 'code'][] = $js;
        return $this;
    }

    /**
     * Set needed CSS for this form
     *
     * Note: try to use functional and dynamic CSS such as hiding fields
     *
     * @param string $css Anything accepted between style tags
     * @return boolean
     * @author Marien den Besten <marien@color-base.com>
     */
    public function setCss($css)
    {
        $this->css[] = $css;
        return true;
    }

    /**
     * FormHandler::_text()
     *
     * Return the given text in the correct language
     *
     * @param integer $index the index of the text in the textfile
     * @return string the text in the correct language
     * @author Teye Heimans
     */
    public function _text($index)
    {
        // is a language set?
        if(!is_array($this->languageArray))
        {
            trigger_error('No language file set!', E_USER_ERROR);
            return false;
        }

        $languageList = self::$languageExclusionArray + $this->languageArray;

        // does the index exists in the language file ?
        if(!array_key_exists($index, $languageList))
        {
            trigger_error('Unknown index ' . $index . ' to get language string!', E_USER_NOTICE);
            return '';
        }

        // return the language string
        return $languageList[$index];
    }

    /**
     * Add an exclusion to the language array
     *
     * @param integer $index
     * @param string $string
     */
    public static function languageExclusionSet($index, $string)
    {
        self::$languageExclusionArray[(int) $index] = (string) $string;
    }

    /**
     * Extend or update the FormHandler language list
     *
     * @param integer $index
     * @param string $string
     */
    public static function languageExclusionUnset($index)
    {
        if(array_key_exists((int) $index, self::$languageExclusionArray))
        {
            unset(self::$languageExclusionArray[(int) $index]);
        }
    }

    /**
     * FormHandler::registerField()
     *
     * Register a field or button at FormHandler
     *
     * @param string $name The name of the field (or button)
     * @param object $field The object of the field or button
     * $param string $title The titlt of the field. Leave blank for a button
     * @return \FormHandler
     * @author Teye Heimans
     */
    public function registerField($name, $field, $title = null)
    {
        if($field instanceof Button\Button)
        {
            $title = '__BUTTON__';

            //register click handler to store clicked button
            $field->onClick(function(FormHandler $form) use ($name)
            {
                $form->setButtonClicked($name);
            });
        }

        $this->fields[$name] = array($title, $field);

        if(array_key_exists($name, $this->bufferViewmode))
        {
            $mode = $this->bufferViewmode[$name];
            unset($this->bufferViewmode[$name]);
            $field->setViewMode($mode);
        }

        // check if the user got another value for this field.
        if(array_key_exists($name, $this->buffer))
        {
            list($bOverwrite, $value) = $this->buffer[$name];
            unset($this->buffer[$name]);

            $this->setValue($name, $value, $bOverwrite);
        }

        //check if field is in link buffer
        if(array_key_exists($name, $this->fieldLinksBuffer) || in_array($name, $this->fieldLinksBuffer))
        {
            //determine parent field
            $key = (array_key_exists($name, $this->fieldLinksBuffer))
                ? $name
                : array_search($name, $this->fieldLinksBuffer);

            //check if both fields exist
            if($this->fieldExists($key)
                && ($this->fieldExists($this->fieldLinksBuffer[$key])
                    || substr($this->fieldLinksBuffer[$key], 0, 1) == '#'))
            {
                //link
                $this->linkNow($key, $this->fieldLinksBuffer[$key]);
                //remove from buffer
                unset($this->fieldLinksBuffer[$key]);
            }
        }
        return $this;
    }

    /**
     * Get field
     *
     * @param string $name
     * @return Field\Field|null
     * @author Marien den Besten
     */
    public function getField($name)
    {
        if(array_key_exists($name, $this->fields)
            && is_object($this->fields[$name][1]))
        {
            return $this->fields[$name][1];
        }
        return null;
    }

    /**
     * Checks if the field is a hidden field
     *
     * @param string $name Field name
     * @return boolean True if it is a hidden field
     */
    public function isFieldHidden($name)
    {
        $field = $this->getField($name);
        return (!is_null($field) && $field instanceof Field\Hidden);
    }

    /**
     * Get linked select values
     *
     * @param string $field
     * @return array
     */
    private function getLinkedSelectValues($field)
    {
        $return = array();

        if(!is_array($field))
        {
            $next_value = $this->getValue($field);
            $return[$field] = $next_value;

            if(array_key_exists($field, $this->attachSelect))
            {
                $return = array_merge($return, $this->getLinkedSelectValues($this->attachSelect[$field]));
            }
        }
        elseif(is_array($field))
        {
            foreach($field as $fld)
            {
                $return = array_merge($return, $this->getLinkedSelectValues($fld));
            }
        }

        return $return;
    }

    /**
     * Get the locator for a given field name which can be used in jQuery
     *
     * Returns an empty string when field does not exist
     *
     * @param string $field_name
     * @return string
     */
    private function getFieldHtmlLocator($field_name)
    {
        if(!is_string($field_name) || !$this->fieldExists($field_name))
        {
            return '';
        }

        return $this->getField($field_name)->getJsSelectorValue();
    }

    /**
     * Get the trigger for a given field which can be used in jQuery
     *
     * @param string $field_name
     * @return string
     */
    private function getFieldTrigger($field_name)
    {
        if(!is_string($field_name) || !$this->fieldExists($field_name))
        {
            return '';
        }

        $trigger = 'change';

        $field = $this->getField($field_name);
        if($field instanceof Field\CheckBox)
        {
            $trigger = 'click';
        }

        if($field instanceof Field\Text || $field instanceof Field\Number || $field instanceof Field\Email)
        {
            $trigger = 'change keyup';
        }
        return $trigger;
    }

    /**
     * Process help string for a given field
     *
     * @param string $field
     * @return string
     * @author Marien den Besten
     */
    private function processHelp($field)
    {
        $fld = $this->getField($field);

        if(is_null($fld)
            || !method_exists($fld, 'getHelp'))
        {
            return '';
        }

        list($text, $title) = $fld->getHelp();

        if(is_null($text))
        {
            return '';
        }

        $field_title = $this->getTitle($field);

        // escape the values from dangerous characters
        $title = is_null($title)
            ? $field_title . ' - ' . $this->_text(41)
            : \FormHandler\Utils::html($title, ENT_NOQUOTES | ENT_IGNORE);

        return str_replace(
            array(
                '%helptext%',
                '%helptitle%',
                '%helpid%'
            ),
            array(
                $text,
                $title,
                $field . '_help'
            ),
            FH_HELP_MASK
        );
    }

    private function setFieldAppearanceWatch($fieldsToWatch, $forField)
    {
        $this->loadJsLibrary('appearance');
        $this->loadJsLibrary('utils');

        $processed = array();
        foreach($fieldsToWatch as $field => $value)
        {
            $processed[$field] = array(
                $this->getField($field)->getJsSelectorValue(),
                $value
            );
        }

        $this->_setJS(
            'FormHandler.appearanceWatch(\''. $this->getFormName() .'\','
                . json_encode($processed) .',\'' . $forField . '\');'."\n",
            false,
            false
        );
    }

    /**
     * FormHandler::_getForm()
     *
     * Private: get the form
     *
     * @return string: the generated form
     * @author Teye Heimans
     * @author Marien den Besten
     */
    private function getForm($iDisplayPage = null)
    {
        //process page
        // is no specific page requested, then get the "current" page
        $iDisplayPage = ( is_null($iDisplayPage) ) ? $this->pageCurrent : $iDisplayPage;
        // make sure that the requested page cannot be negative
        $iDisplayPage = ( $iDisplayPage <= 0) ? 1 : $iDisplayPage;
        // set the tab indexes for the fields...
        reset($this->tabIndexes);
        ksort($this->tabIndexes);
        while(list( $index, $field ) = each($this->tabIndexes))
        {
            // check if the field exists in the form ?
            if($this->fieldExists($field))
            {
                // set the tab index
                $this->getField($field)->setTabIndex($index);
            }
            // tab index set for unknown field... trigger_error
            else
            {
                trigger_error(
                    'Error, try to set the tabindex of an unknown field "' . $field . '"!', E_USER_NOTICE
                );
            }
        }

        // set the focus to the first (tab index) field if no focus is set yet
        // and are there tab indexes set ?
        if(is_null($this->focus) && is_null($this->focusBuffer) && sizeof($this->tabIndexes) > 0)
        {
            // set the focus to the element with the lowest positive tab index
            reset($this->tabIndexes);
            while(list( $key, $field ) = each($this->tabIndexes))
            {
                if($key >= 0 && $this->setFocus($field))
                {
                    break;
                }
            }
        }

        // no focus set yet. Set the focus to the first field
        if(is_null($this->focus) && is_null($this->focusBuffer))
        {
            // is it a object (only fields + buttons are objects)
            $ft = array_keys($this->fields);
            foreach($ft as $name)
            {
                if(!is_null($this->getField($name))
                    && method_exists($this->getField($name), 'getViewMode')
                    && $this->getField($name)->getViewMode() === false
                    && $this->setFocus($name))
                {
                    break;
                }
            }
        }

        // initialize the used vars
        $hidden = '';
        $form = '';
        $buffer = array();
        $repeat = true;
        $page = 1;
        $fields_displayed = array();

        // start a new mask loader
        $mask = new MaskLoader();

        // set the seach values
        $mask->setSearch(
            array(
                '/%field%/',
                '/%error%/',
                '/%title%/',
                '/%seperator%/',
                '/%name%/',
                '/%error_id%/',
                '/%value%/',
                '/%help%/',
                '/%field_wrapper%/'
            )
        );

        // walk trought the fields array
        foreach($this->fields as $id => $field)
        {
            list($title, $obj) = $field;

            if($title == '__PAGE__')
            {
                $page++;
                continue;
            }

            if($title == '__HIDDEN__')
            {
                $hidden .= $obj->getField() . "\n";
                $hidden .= $obj->getErrorMessage() . "\n";

                $fields_displayed[] = $id;
                continue;
            }

            if($title == '__MASK__')
            {
                // new mask to set
                if(!isset($this->mask) || is_null($this->mask) || $page == $iDisplayPage)
                {
                    list($this->mask, $repeat) = $obj;
                }
                continue;
            }

            if($title == '__HTML__' || $title == '__LINE__')
            {
                // but only if the html or line is on this page!
                if($page == $iDisplayPage)
                {
                    $form .= $obj;
                }
                continue;
            }

            if($title == '__FIELDSET__')
            {
                // begin new fieldset
                if($page == $iDisplayPage)
                {
                    array_unshift($obj, $form);
                    array_push($buffer, $obj);
                    $form = '';
                }
                continue;
            }

            if($title == '__FIELDSET-END__')
            {
                // end new fieldset
                if($page == $iDisplayPage)
                {
                    if(sizeof($buffer) > 0)
                    {
                        $d = array_pop($buffer);
                        $form = $d[0] .
                            str_replace(
                                array('%name%', '%caption%', '%content%', '%extra%'),
                                array($d[1], $d[2], $form, $d[3]),
                                Configuration::get('fieldset_mask')
                        );
                    }
                    else
                    {
                        trigger_error('Fieldset is closed while there is not an open fieldset!');
                    }
                }
                continue;
            }

            // the fields are not displayed in this page..
            // set them as hidden fields in the form
            if($page != $iDisplayPage)
            {
                // put the data of the field in a hidden field
                // buttons are just ignored
                if($title != '__BUTTON__')
                {
                    // create a new hidden field to set the field's value in
                    $value = $obj->getValue();
                    $h = new Field\Hidden($this, $id);
                    $h->setValue($value);
                    $hidden .= $h->getField() . "\n";
                    unset($h);
                }
                break;
            }
            // the field is on the current page of the form
            //create array to know which fields are displayed
            //used for linked fields
            $fields_displayed[] = $id;

            // set the mask which should be filled
            $mask->setMask($this->mask);

            // buttons don't have a title :-)
            if($title == '__BUTTON__')
            {
                $title = '';
            }

            //From this point, we are collecting the data to fill the mask.

            // Get the field or button value
            // can we get a field ?
            if(is_object($obj) && method_exists($obj, 'getField'))
            {
                $fld = $obj->getField();
            }
            // can we get a button ?
            elseif(is_object($obj) && method_exists($obj, 'getButton'))
            {
                $fld = $obj->getButton();
            }
            // ai, not a field and not a button..
            else
            {
                $fld = '';
            }

            // escape dangerous characters
            $fld = str_replace('%', '____FH-percent____', $fld);

            // get possible error message
            $error = '';
            if($this->displayErrors
                && is_object($obj)
                && method_exists($obj, 'getErrorState')
                && $obj->getErrorState()
                && method_exists($obj, 'getErrorMessage')
                && $obj->getErrorMessage() != '')
            {
                $error = $obj->getErrorMessage();
            }

            // save the error messages
            // (when the user wants to use his own error displayer)
            $this->errors[$id] = $error;

            /**
             * Get the value for of the field
             */
            $value = '';
            if(is_object($obj) && method_exists($obj, 'getValue'))
            {
                if(is_array($obj->getValue()))
                {
                    $value = '__FH_JSON__'. json_encode($obj->getValue());
                }
                else
                {
                    $value = $obj->getValue();
                }
            }

            //process help
            $is_view = (method_exists($obj, 'getViewMode') && $obj->getViewMode());
            $help = (!$this->isViewMode() && !$is_view) ? $this->processHelp($id) : '';

            // give the field a class error added 25-08-2009 in order to give the field the error mask
            if($this->isPosted() == true && $error != '')
            {
                $fld = $this->parseErrorFieldStyle($fld);
            }

            // now, put all the replace values into an array
            $replace = array(
                /* %field% */
                $fld,
                /* %error% */
                $error,
                /* %title% */
                !empty($title) ? $title : "",
                /* %seperator% */
                (!strlen($title) ? '' : ':' ),
                /* %name% */
                (!empty($id) ? $id : '' ),
                /* %error_id% */
                (!empty($id) ? 'error_' . $id : '' ),
                /* %value% */
                $value,
                /* %help% */
                $help,
                /* %field_wrapper% */
                $id . '_field'
            );

            // fill the mask
            $html = $mask->fill($replace);

            // added 07-01-2009 in order to specify which element should get the error class
            if($this->isPosted() == true && $error != '')
            {
                $html = $this->parseErrorStyle($html);
            }
            else
            {
                $html = str_replace('%error_style%', '', $html);
            }

            // is the mask filled ?
            if($html)
            {
                // add it the the form HTML
                $form .= str_replace('____FH-percent____', '%', $html);

                // if we don't have to repeat the current mask, use the original
                if(!$repeat)
                {
                    $this->mask = Configuration::get('default_row_mask');
                }
                // if we have to repeat the mask, repeat it and countdown
                elseif(is_numeric($repeat))
                {
                    $repeat--;
                }
            }
        }

        //there are linked select fields to create javascript
        if(count($this->attachSelect) > 0)
        {
            //set variables
            $groups = array();
            $group_parents = array();
            $item_parents = array();

            //loop through all linked fields
            foreach($this->attachSelect as $fld => $fields)
            {
                //loop through all chained fields
                foreach($fields as $chained)
                {
                    //when both fields are in viewmode exclude from attaching
                    if($this->fieldExists($chained) && $this->isFieldViewMode($chained))
                    {
                        continue;
                    }

                    //find parent for this field
                    $check = $fld;

                    //do not go deeper than 500 iterations
                    for($i = 1; $i <= 500; $i++)
                    {
                        if(!array_key_exists($check, $group_parents))
                        {
                            break;
                        }
                        $check = $group_parents[$check];
                    }

                    //add to lookup table
                    $group_parents[$chained] = $check;

                    //when no group is available, create on
                    if(!array_key_exists($check, $groups))
                    {
                        $groups[$check] = array();
                    }

                    //only add to group if not added before
                    if(!in_array($chained, $groups[$check]))
                    {
                        //fill
                        $groups[$check][] = $chained;
                    }

                    $item_parents[$chained . '_' . $check] = $fld;

                    //convert master into chained field
                    if(array_key_exists($chained, $groups))
                    {
                        foreach($groups[$chained] as $old)
                        {
                            //only add to the group if not already existing
                            if(!in_array($old, $groups[$check]))
                            {
                                //add to existing parent
                                $groups[$check][] = $old;
                            }
                            //change parent lookup
                            $group_parents[$old] = $check;
                            $item_parents[$old . '_' . $check] = $chained;
                            unset($item_parents[$old . '_' . $chained]);
                        }
                        //remove old
                        unset($groups[$chained]);
                    }
                }
            }

            $new_js = '';

            //attach loaders
            foreach($this->attachSelect as $field_from => $fields)
            {
                $extras = array();
                $checked_fields = array();
                $already_processed = array();

                foreach($fields as $field_to)
                {
                    //don't include field when field is in view mode
                    if(substr($field_to, 0, 1) != '#'
                        && ($this->isFieldViewMode($field_to)
                            || array_search($field_to, $fields_displayed) === false))
                    {
                        continue;
                    }

                    //field can be used
                    $checked_fields[] = $field_to;

                    $extraCheck = $this->fieldLinks[$field_from . '_' . $field_to]['extra'];
                    $extraList = (!is_array($extraCheck)) ? array() : $extraCheck;

                    //process extra's
                    foreach($extraList as $extra)
                    {
                        if(in_array($extra, $already_processed))
                        {
                            continue;
                        }

                        // is this argument a field? Then load the value
                        if($this->fieldExists($extra) == true)
                        {
                            $extra_value = "'+ FormHandler.getValue($('"
                                . $this->getFieldHtmlLocator($extra) . "')) +'";

                            if($this->isFieldViewMode($extra))
                            {
                                //when in viewmode the value is not present in the form
                                $extra_value = urlencode($this->getValue($extra));
                            }
                            $extras[] = $extra . '=' . $extra_value;
                        }
                        else
                        {
                            // just load the extra argument, it's a js string
                            $extras[] = $extra;
                        }
                        $already_processed[] = $extra;
                    }
                }

                if(count($checked_fields) == 0)
                {
                    continue;
                }

                $function_name = str_replace('-', '_', $field_from);
                $field = $this->getField($field_from);
                $field_class = get_class($this->fields[$field_from][1]);
                $view_mode = ($field instanceof Field\Hidden || $this->isFieldViewMode($field_from));

                //determine value
                $from_value = "FormHandler.getValue($('". $this->getFieldHtmlLocator($field_from) ."'),true)";
                if($view_mode)
                {
                    $from_value = json_encode($this->getValue($field_from));
                    if(is_array($this->getValue($field_from)))
                    {
                        $from_value = "'__FH_JSON__" . $from_value . "'";
                    }
                }

                $new_js .= 'var field_' . $function_name . ' = $(\''
                    . $this->getFieldHtmlLocator($field_from) . '\');' . "\n";

                if(!$view_mode)
                {
                    $new_js .= "field_" . $function_name;
                    $new_js .= ".on('" . $this->getFieldTrigger($field_from) . "',function(event,values)\n";

                    if($field instanceof Field\Text || $field instanceof Field\Email || $field instanceof Field\Number)
                    {
                        //on text fields we want a delay to prevent server load
                        $new_js .= "{\n";
                        $new_js .= "clearTimeout($.data(this, 'timer'));\n";
                        $new_js .= "    if(event.keyCode == 13) search_" . $function_name . "(values);\n";
                        $new_js .= "    else $(this).data('timer', setTimeout(function() { "
                            . "search_" . $function_name . "(values); },500));\n";
                        $new_js .= "});\n";

                        $new_js .= "function search_" . $function_name . "(values)\n";
                    }
                }
                else
                {
                    $new_js .= "function load_" . $function_name . "(values)\n";
                }
                $protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    ? 'https'
                    : 'http';

                $new_js .= "{\n";
                $new_js .= "    FormHandler.load(\n";
                $new_js .= "        '" . $protocol .'://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "',\n";
                $new_js .= "        " . $from_value . ",\n";
                $new_js .= "        " . json_encode($checked_fields) . ",\n";
                $new_js .= "        '" . implode('&', $extras) . "',\n";
                $new_js .= "        values,\n";
                $new_js .= "        field_" . $function_name . ",\n";
                $new_js .= "        " . json_encode($this->getFormName()) . ",\n";
                $new_js .= "        " . json_encode($field_from) . "\n";
                $new_js .= "    );\n";
                $new_js .= "}";

                if(!$view_mode
                    && !$field instanceof Field\Text
                    && !$field instanceof Field\Email
                    && !$field instanceof Field\Number)
                {
                    $new_js .= ");";
                }
                $new_js .= "\n";
            }

            //call loaders
            foreach($groups as $parent => $childs)
            {
                if(array_search($parent, $fields_displayed) === false)
                {
                    continue;
                }

                $values = array("fh_initial" => 1);
                foreach($childs as $child)
                {
                    $values[$child] = $this->getValue($child);
                }

                if(!$this->isFieldViewMode($parent) && !get_class($this->fields[$parent][1]) instanceof Field\Hidden)
                {
                    $trigger = $this->getFieldTrigger($parent);
                    $trigger = explode(' ', $trigger);

                    $new_js .= "$('" . $this->getFieldHtmlLocator($parent) . "')";
                    $new_js .= ".triggerHandler('" . $trigger[0] . "'," . json_encode($values) . ");\n";
                }
                else
                {
                    $new_js .= "load_" . str_replace('-', '_', $parent) . "(" . json_encode($values) . ");\n";
                }
            }

            $this->_setJS($new_js, false, false);
        }

        // add the page number to the forms HTML
        if($this->pageCounter > 1)
        {
            $h = new Field\Hidden($this, $this->name . '_page');
            $h->setValue($iDisplayPage);
            $hidden .= $h->getField() . "\n";
            unset($h);
        }

        // get a possible half filled mask and add it to the html
        $form .= str_replace('____FH-percent____', '%', $mask->fill(null));

        // delete the mask loader
        unset($mask);

        // set the javascript needed for setting the focus
        if((!empty($this->focus) || !empty($this->focusBuffer)) && $this->focus !== false)
        {
            $focus = (!empty($this->focus)) ? $this->focus : $this->focusBuffer;
            $this->_setJS(
                "var elem = $('#" . $this->getField($focus)->getFocus() . "') \n" .
                "if(elem.length != 0 && elem.is(':hidden') == false)  elem.focus();\n", 0, 0
            );
        }

        if($this->rememberFormPosition === true)
        {
            $this->_setJS("if($('#" . $this->name . "_position').val() != 0) setTimeout(function()"
                . "{ $(document).scrollTop($('#" . $this->name . "_position').val()); },200);\n", false, false);
            $this->_setJS("$('#" . $this->name . "').on('submit',function()"
                . "{ $('#" . $this->name . "_position').val($(document).scrollTop()); });\n", false, false);
        }

        if(count($this->fieldsHidden) != 0)
        {
            foreach(array_keys($this->fieldsHidden) as $field)
            {
                $this->setCss('#' . $field . '_field {display:none;}');
            }
        }

        //get defined CSS
        $css_defined = $this->getCssCode();
        $css = (trim($css_defined) != '') ? "<style>\n" . $css_defined . "</style>\n" : '';

        // NOTE!!
        // DO NOT REMOVE THIS!
        // You can remove the line "This form is generated by FormHandler" with the config!!
        // DONT REMOVE THE HTML CODE BELOW! 
        // Just use FormHandler::configuration::set('expose', false); before using any FormHandler object
        $sHeader = 
            "<!--\n" .
            "  This form is automaticly being generated by FormHandler v4.\n" .
            "  See for more info: http://www.formhandler.net\n" .
            "  This credit MUST stay intact for use\n" .
            "-->\n" .
            $css .
            $this->getJavascriptCode(true) 
            . '<form data-fh="true" id="' . $this->name . '" method="post" action="'
            . \FormHandler\Utils::html($this->action) . '"' .
            ($this->encoding === self::ENCODING_MULTIPART ? ' enctype="multipart/form-data"' : '') .
            (!empty($this->extra) ? " " . $this->extra : "" ) . ">\n" .
            '<ins>' . "\n" . $hidden . '</ins>';

        $sFooter = (Configuration::get('expose') ?
            "<p><span style='font-family:tahoma;font-size:10px;color:#B5B5B5;font-weight:normal;'>" .
                'This form is generated by </span><a href="http://www.formhandler.net" >' .
                '<span style="font-family:Tahoma;font-size:10px;color:#B5B5B5;">' .
                '<strong>FormHandler</strong></span></a></p>' : '') .
            "</form>\n" .
            "<!--\n" .
            "  This form is automaticly being generated by FormHandler v4.\n" .
            "  See for more info: http://www.formhandler.net\n" .
            "-->" . $this->getJavascriptCode(false);

        $search = array('%header%', '%footer%');
        $replace = array($sHeader, $sFooter);

        $new_form = str_replace($search, $replace, $form, $num_replaced);

        if($num_replaced === 2)
        {
            return $new_form;
        }
        else
        {
            return $sHeader . $form . $sFooter;
        }
    }
}
