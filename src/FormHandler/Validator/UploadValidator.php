<?php
namespace FormHandler\Validator;

use FormHandler\Field\AbstractFormField;
use FormHandler\Field\UploadField;

/**
 * Upload validator, will validate an uploaded file.
 *
 * A file can be filtered by extension, mime type and file size.
 *
 * The extension and mime type can be used by white listing and black listing methods.
 */
class UploadValidator extends AbstractValidator
{
    /**
     * A list of allowed extensions (without leading dot!)
     * @var array
     */
    protected $allowedExtensions = [];

    /**
     * A list of allowed mime types
     * @var array
     */
    protected $allowedMimeTypes = [];

    /**
     * A list of denied extensions (without leading dot!)
     * @var array
     */
    protected $deniedExtensions = [];

    /**
     * A list of denied mime types
     * @var array
     */
    protected $deniedMimeTypes = [];

    /**
     * The max filesize in bytes;
     * @var int
     */
    protected $maxFilesize;

    /**
     * The minimum filesize in bytes
     * @var int
     */
    protected $minFilesize;

    /**
     * All error messages which can be used
     * @var array
     */
    protected $messages = [
        'required' => 'You have to upload a file.',
        'file_too_big' => 'The uploaded file exceeds the maximum allowed upload file size.',
        'incomplete' => 'The file was not completly uploaded. Please try again.',
        'cannot_write' => 'Failed to save the uploaded file to disk. Please try again.',
        'error' => 'Failed to upload this file due to an error. Please try again.',
        'wrong_extension' => 'The uploaded file extension is not allowed. Allowed is: %s',
        'wrong_type' => 'The uploaded file type is not allowed.',
        'file_larger_then' => 'The uploaded file is larger then the maximum allowed size of %d kb.',
        'file_smaller_then' => 'The uploaded file is smaller then the minimum allowed size of %d kb.',
    ];

    /**
     * Create a new upload validator
     *
     * @param boolean $required Set this field as required or not
     * @param array $messages A list of messages which should be used as error message when validating this upload.
     */
    public function __construct($required = true, array $messages = [])
    {
        $this->setRequired($required);
        if (sizeof($messages) > 0) {
            $this->messages = array_merge($this->messages, $messages);
        }
    }

    /**
     * Set the field which should be validated.
     *
     * @param AbstractFormField $field
     * @return static
     * @throws \Exception
     */
    public function setField(AbstractFormField $field)
    {
        if (!($field instanceof UploadField)) {
            throw new \Exception('The validator "' . get_class($this) . '" only works on upload fields!');
        }

        $this->field = $field;
        return $this;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return bool
     */
    public function isValid()
    {
        $value = $this->field->getValue();

        // no file uploaded?
        if (!$value || !isset($value['error']) || $value['error'] == UPLOAD_ERR_NO_FILE) {
            $this->setErrorMessage($this->messages['required']);
            // required ?
            return !$this->required;
        }

        // check the uploaded file
        switch ($value['error']) {
            case UPLOAD_ERR_OK:
                break;

            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_INI_SIZE:
                $this->setErrorMessage($this->messages['file_too_big']);
                return false;

            case UPLOAD_ERR_PARTIAL:
                $this->setErrorMessage($this->messages['incomplete']);
                return false;

            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
                $this->setErrorMessage($this->messages['cannot_write']);
                return false;

            case UPLOAD_ERR_EXTENSION:
            default:
                $this->setErrorMessage($this->messages['error']);
                return false;
        }

        /**
         * if here, the file was uploaded.
         * Validate the uploaded file against our settings (blacklist, whitelist, etc)
         */

        // retrieve the extension
        if (!$this->isExtensionAllowed($value['name'])) {
            $this->setErrorMessage(sprintf($this->messages['wrong_extension'], implode(',', $this->allowedExtensions)));
            return false;
        }

        if (!$this->isMimetypeAllowed($value['tmp_name'], $value['type'])) {
            $this->setErrorMessage($this->messages['wrong_type']);
            return false;
        }

        $size = filesize($value['tmp_name']);

        // validate the upload file size
        if ($this->maxFilesize && $size > $this->maxFilesize) {
            $this->setErrorMessage(sprintf($this->messages['file_larger_then'], $this->maxFilesize / 1024));
            return false;
        }
        if ($this->minFilesize && $size < $this->minFilesize) {
            $this->setErrorMessage(sprintf($this->messages['file_smaller_then'], $this->minFilesize / 1024));
            return false;
        }

        // if here, the extension and the mime type are validated! The file is good!
        return true;
    }

    /**
     * Check if the extension  of the given filename is allowed.
     *
     * @param string $filename
     * @return boolean
     */
    protected function isExtensionAllowed($filename)
    {
        // retrieve the extension
        $pos = strrpos($filename, '.');
        $extension = '';
        if ($pos !== false) {
            $extension = strtolower(substr($filename, $pos + 1));
        }

        // if we have an extension, validate it agains the black and white lists
        if ($extension) {
            // not in whitelist?
            if (sizeof($this->allowedExtensions) > 0 && !in_array($extension, $this->allowedExtensions)) {
                return false;
            }

            // in blacklist ?
            if (sizeof($this->deniedExtensions) && in_array($extension, $this->deniedExtensions)) {
                return false;
            }
        } elseif (sizeof($this->allowedExtensions) > 0) {
            // no extension given, thus not in the whitelist!
            return false;
        }

        return true;
    }

    /**
     * Check if the mime type of the given file is allowed.
     *
     * If we cannot fetch the mime type of the given filename, the $default will be used.
     *
     * @param string $filename
     * @param string $default
     * @return bool
     */
    protected function isMimetypeAllowed($filename, $default = "")
    {
        $numAllowed = sizeof($this->allowedMimeTypes);
        $numDenied = sizeof($this->deniedMimeTypes);

        if ($numAllowed == 0 && $numDenied == 0) {
            return true;
        }

        /**
         * Try to retrieve the mime type of the file
         */
        // first, try as an image. May cause exception if not an image, so do not trigger errors
        if (@($data = getimagesize($filename))) {
            $mimetype = $data['mime'];
        } else {
            if (function_exists('finfo_open') && function_exists('finfo_file')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
                $mimetype = finfo_file($finfo, $filename);
                finfo_close($finfo);
            } else {
                // this is deprecated
                $mimetype = function_exists('mime_content_type') ? mime_content_type($filename) : $default;
            }
        }

        // validate the mime type agains the white and blacklists
        if ($numAllowed > 0 && !in_array($mimetype, $this->allowedMimeTypes)) {
            return false;
        }
        if ($numDenied > 0 && in_array($mimetype, $this->deniedMimeTypes)) {
            return false;
        }

        return true;
    }

    /**
     * Return the min filesize in bytes.
     * Returns null if no minimum is set.
     *
     * @return integer
     */
    public function getMinFilesize()
    {
        return $this->minFilesize;
    }

    /**
     * Set the min filesize in bytes.
     * Set to null to skip the
     * min filesize check. The filesize needs to be a positive integer
     *
     * @param int $filesize
     * @throws \Exception
     */
    public function setMinFilesize($filesize)
    {
        if ($filesize < 0 && $filesize !== null) {
            throw new \Exception('The minimal filesize cannot be a negative integer!');
        }

        $this->minFilesize = $filesize;
    }

    /**
     * Return the max filesize.
     *
     * @return int
     */
    public function getMaxFilesize()
    {
        return $this->maxFilesize;
    }

    /**
     * Set the max filesize in bytes.
     * Set to null to skip the max filesize check.
     *
     * @param int $filesize
     */
    public function setMaxFilesize($filesize)
    {
        $this->maxFilesize = $filesize;
    }

    /**
     * Get the allowed mime types.
     *
     * @return array
     */
    public function getAllowedMimeTypes()
    {
        return $this->allowedMimeTypes;
    }

    /**
     * Set the mime type or types which are allowed for uploading.
     *
     * @param array $types
     * @throws \Exception
     */
    public function setAllowedMimeTypes(array $types)
    {
        $this->allowedMimeTypes = $types;
    }

    /**
     * Add an allowed mime type
     *
     * @param string $type
     */
    public function addAllowedMimeType($type)
    {
        $this->allowedMimeTypes[] = $type;
    }

    /**
     * Remove an allowed mime type from the list.
     *
     * @param string $type
     * @return boolean true if found and removed, false otherwise
     */
    public function removeAllowedMimeType($type)
    {
        $key = array_search($type, $this->allowedMimeTypes);
        if ($key !== false) {
            unset($this->allowedMimeTypes[$key]);
            return true;
        }


        return false;
    }

    /**
     * Get all allowed extensions.
     * Returns an array with all extensions in it (without leading dot ".")
     *
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    /**
     * Set the extensions which are allowed.
     * The extensions should be in an array. The extension should NOT contain a dot (.) in front of it.
     * Example:
     *
     * ```php
     * $validator -> setAllowedExtensions( ['pdf', 'txt', 'zip', 'jpg' ] );
     * ```
     *
     * @param array $extensions
     */
    public function setAllowedExtensions(array $extensions)
    {
        $this->allowedExtensions = $extensions;
    }

    /**
     * Add an extension which is allowed.
     * Pass the extension without a leading dot ".".
     * Example:
     *
     * ```php
     * $validator -> addAllowedExtension("pdf");
     * $validator -> addAllowedExtension("jpg");
     * ```
     * @param string $extension
     */
    public function addAllowedExtension($extension)
    {
        $this->allowedExtensions[] = $extension;
    }

    /**
     * Remove an allowed extension from the list.
     *
     * @param string $extension
     * @return boolean true if found and removed, false otherwise
     */
    public function removeAllowedExtension($extension)
    {
        $key = array_search($extension, $this->allowedExtensions);
        if ($key !== false) {
            unset($this->allowedExtensions[$key]);
            return true;
        }

        return false;
    }

    /**
     * Get the denied mime types, or an empty array if none
     *
     * @return array
     */
    public function getDeniedMimeTypes()
    {
        return $this->deniedMimeTypes;
    }

    /**
     * Set the mime type or types which are denied for uploading.
     *
     * @param array $types
     * @throws \Exception
     */
    public function setDeniedMimeTypes(array $types)
    {
        $this->deniedMimeTypes = $types;
    }

    /**
     * Add an denied mime type
     *
     * @param string $type
     */
    public function addDeniedMimeType($type)
    {
        $this->deniedMimeTypes[] = $type;
    }

    /**
     * Remove an denied mime type from the list.
     *
     * @param string $type
     * @return boolean true if found and removed, false otherwise
     */
    public function removeDeniedMimeType($type)
    {
        $key = array_search($type, $this->deniedMimeTypes);
        if ($key !== false) {
            unset($this->deniedMimeTypes[$key]);
            return true;
        }

        return false;
    }

    /**
     * Get all denied extensions.
     * Returns an array with all extensions in it (without leading dot ".")
     *
     * @return array
     */
    public function getDeniedExtensions()
    {
        return $this->deniedExtensions;
    }

    /**
     * Set the extensions which are denied.
     * The extensions should be in an array. The extension should NOT contain a loading dot "."
     * Example:
     *
     * <code>
     * $validator -> setDeniedExtensions( array( 'exe', 'php', 'sh' ) );
     * </code>
     *
     * @param array $extensions
     */
    public function setDeniedExtensions(array $extensions)
    {
        $this->deniedExtensions = $extensions;
    }

    /**
     * Add an extension which is denied.
     * Pass the extension without a leading dot ".".
     * Example:
     *
     * <code>
     * $validator -> addDeniedExtension("pdf");
     * $validator -> addDeniedExtension("jpg");
     * </code>
     * @param string $extension
     */
    public function addDeniedExtension($extension)
    {
        $this->deniedExtensions[] = $extension;
    }

    /**
     * Remove an denied extension from the list.
     *
     * @param string $extension
     * @return boolean true if found and removed, false otherwise
     */
    public function removeDeniedExtension($extension)
    {

        $key = array_search($extension, $this->deniedExtensions);
        if ($key !== false) {
            unset($this->deniedExtensions[$key]);
            return true;
        }

        return false;
    }
}
