<?php
namespace FormHandler\Validator;

/**
 * Upload validator, will validate an uploaded file.
 *
 * A file can be filtered by extension, mime type and file size.
 *
 * The extension and mime type can be used by white listing and black listing methods.
 */
class UploadValidator extends AbstractValidator
{

    protected $required = true;

    protected $allowedExtensions;

    protected $allowedMimeTypes;

    protected $deniedExtensions;

    protected $deniedMimeTypes;

    protected $maxFilesize;

    protected $minFilesize;

    /**
     * Create a new upload validator
     *
     * @param boolean $required
     * @param string $message
     */
    public function __construct($required = true, $message = null)
    {
        $this->setRequired($required);
        if ($message) {
            $this->setErrorMessage($message);
        }
    }

    /**
     * Set the field which should be validated.
     *
     * @param AbstractFormField $field
     */
    public function setField(AbstractFormField $field)
    {
        if (! ($field instanceof UploadField)) {
            throw new Exception('The validator "' . get_class($this) . '" only works on upload fields!');
        }

        $this->field = $field;
    }

    /**
     * Add javascript validation for this field.
     *
     * @param
     *            AbstractFormField &$field
     * @return string
     */
    public function addJavascriptValidation(AbstractFormField &$field)
    {
        static $addedJavascriptFunction = false;

        $script = '';
        if (! $addedJavascriptFunction) {
            $script .= 'function d2UploadValidator( field, whitelist, blacklist ) {' . PHP_EOL;
            $script .= '    var value = $(field).val();' . PHP_EOL;
            $script .= '    if( !$(field).hasClass("required")) {' . PHP_EOL;
            $script .= '        // the field is not required. Skip the validation if the field is empty.' . PHP_EOL;
            $script .= '        if( $.trim( value ) == "" ) { ' . PHP_EOL;
            $script .= '            $(field).removeClass("invalid");' . PHP_EOL;
            $script .= '            return true;' . PHP_EOL;
            $script .= '        }' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    var i = value.lastIndexOf(".");' . PHP_EOL;
            $script .= '    var ext = "";' . PHP_EOL;
            $script .= '    if( i != -1 ) {' . PHP_EOL;
            $script .= '        ext = $(field).val().substring( i + 1 );' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    if( whitelist != null && $.isArray( whitelist) ) {' . PHP_EOL;
            $script .= '        if( ext == "" || $.inArray( ext, whitelist ) == -1 ) {' . PHP_EOL;
            $script .= '            $(field).addClass("invalid");' . PHP_EOL;
            $script .= '            return false;' . PHP_EOL;
            $script .= '        }' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    if( blacklist != null && $.isArray( blacklist) && ext != "" ) {' . PHP_EOL;
            $script .= '        if( $.inArray( ext, blacklist ) != -1 ) {' . PHP_EOL;
            $script .= '            $(field).addClass("invalid");' . PHP_EOL;
            $script .= '            return false;' . PHP_EOL;
            $script .= '        }' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    $(field).removeClass("invalid");' . PHP_EOL;
            $script .= '    return true;' . PHP_EOL;
            $script .= '}' . PHP_EOL;

            $addedJavascriptFunction = true;
        }

        if ($this->required) {
            $field->addClass('required');
        }

        $form = $field->getForm();
        if (! $form->getId()) {
            $form->setId(uniqid(get_class($form)));
        }

        if (! $field->getId()) {
            $field->setId(uniqid(get_class($field)));
        }

        $script .= '$(document).ready( function() {' . PHP_EOL;
        if (! ($field instanceof HiddenField)) {
            $script .= '    $("#' . $field->getId() . '").blur(function() {' . PHP_EOL;
            $script .= '       d2UploadValidator( $("#' . $field->getId() . '"), ' . str_replace('"', "'", json_encode($this->allowedExtensions) . ', ' . json_encode($this->deniedExtensions)) . ' );' . PHP_EOL;
            $script .= '    });' . PHP_EOL;
        }
        $script .= '    $("form#' . $form->getId() . '").bind( "validate", function( event ) {' . PHP_EOL;
        $script .= '        if( !d2UploadValidator( $("#' . $field->getId() . '"), ' . str_replace('"', "'", json_encode($this->allowedExtensions) . ', ' . json_encode($this->deniedExtensions)) . ' )) {' . PHP_EOL;
        $script .= '            return false;' . PHP_EOL;
        $script .= '        } else {' . PHP_EOL;
        $script .= '            return event.result;' . PHP_EOL;
        $script .= '        }' . PHP_EOL;
        $script .= '    });' . PHP_EOL;
        $script .= '});' . PHP_EOL;

        return $script;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        $value = $this->field->getValue();

        // no file uploaded?
        if (! $value || ! isset($value['error']) || $value['error'] == UPLOAD_ERR_NO_FILE) {
            $this->setErrorMessage(dgettext('d2frame', 'You have to upload a file.'));
            // required ?
            return ! $this->required;
        }

        // check the uploaded file
        switch ($value['error']) {
            case UPLOAD_ERR_OK:
                break;

            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_INI_SIZE:
                $this->setErrorMessage(dgettext('d2frame', 'The uploaded file exceeds the maximum allowed upload file size.'));
                return false;

            case UPLOAD_ERR_PARTIAL:
                $this->setErrorMessage(dgettext('d2frame', 'The file was not completly uploaded. Please try again.'));
                return false;

            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
                $this->setErrorMessage(dgettext('d2frame', 'Failed to save the uploaded file to disk. Please try again.'));
                return false;

            case UPLOAD_ERR_EXTENSION:
            default:
                $this->setErrorMessage(dgettext('d2frame', 'Failed to upload this file due to an error. Please try again.'));
                return false;
        }

        /**
         * if here, the file was uploaded.
         * Validate the uploaded file against our settings (blacklist, whitelist, etc)
         */

        // retrieve the extension
        if (! $this->isExtensionAllowed($value['name'])) {
            $this->setErrorMessage(dgettext('d2frame', 'The uploaded file extension is not allowed.'));
            return false;
        }

        if (! $this->isMimetypeAllowed($value['tmp_name'], $value['type'])) {
            $this->setErrorMessage(dgettext('d2frame', 'The uploaded file type is not allowed.'));
            return false;
        }

        if (! $this->isSizeAllowed(filesize($value['tmp_name']))) {
            $this->setErrorMessage(dgettext('d2frame', 'The uploaded file exceeds the maximum allowed upload file size.'));
            return false;
        }

        // if here, the extension and the mime type are validated! The file is good!
        return true;
    }

    /**
     * Check if the given file extension is allowed.
     *
     * @param string $filename
     * @return boolean
     */
    protected function isExtensionAllowed(string $filename)
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
            if (is_array($this->allowedExtensions) && sizeof($this->allowedExtensions) > 0 && ! in_array($extension, $this->allowedExtensions)) {
                return false;
            }

            // in blacklist ?
            if (is_array($this->deniedExtensions) && sizeof($this->deniedExtensions) && in_array($extension, $this->deniedExtensions)) {
                return false;
            }
        } elseif (is_array($this->allowedExtensions) && sizeof($this->allowedExtensions) > 0) {
            // no extension given, thus not in the whitelist!
            return false;
        }

        return true;
    }

    protected function isMimetypeAllowed(string $filename, $default = "")
    {
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
        if (is_array($this->allowedMimeTypes) && sizeof($this->allowedMimeTypes) > 0 && ! in_array($mimetype, $this->allowedMimeTypes)) {
            return false;
        }
        if (is_array($this->deniedMimeTypes) && sizeof($this->deniedMimeTypes) > 0 && in_array($mimetype, $this->deniedMimeTypes)) {
            return false;
        }

        return true;
    }

    protected function isSizeAllowed($size)
    {
        // validate the upload file size
        if ($this->maxFilesize && $size > $this->maxFilesize) {
            return false;
        }
        if ($this->minFilesize && $size < $this->minFilesize) {
            return false;
        }

        return true;
    }

    /**
     * Set if this field is required or not.
     *
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;
    }

    /**
     * Get if this field is required or not.
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set the min filesize in bytes.
     * Set to null to skip the
     * min filesize check. The filesize needs to be a positive integer
     *
     * @param
     *            $filesize
     */
    public function setMinFilesize($filesize)
    {
        if ($filesize < 0) {
            throw new Exception('The minimal filesize cannot be a negative integer!');
        }

        $this->minFilesize = $filesize;
    }

    /**
     * Return the min filesize.
     * Returns null if
     * no minimum is set.
     *
     * @return integer
     */
    public function getMinFilesize()
    {
        return $this->minFilesize;
    }

    /**
     * Set the max filesize in bytes.
     * Set to null to skip the
     * max filesize check.
     *
     * @param
     *            $filesize
     */
    public function setMaxFilesize($filesize)
    {
        $this->maxFilesize = $filesize;
    }

    /**
     * Return the max filesize.
     *
     * @return integer
     */
    public function getMaxFilesize()
    {
        return $this->maxFilesize;
    }

    /**
     * Set the mime type or types which are allowed for uploading.
     * This can either be an array, or null to disable the mime type checking.
     *
     * @param array $types
     */
    public function setAllowedMimeTypes($types)
    {
        if (! is_array($types) && $types !== null) {
            throw new Exception("You can only set an array as allowed mime types, or pass 'null' to disable the allowed mime types");
        }
        $this->allowedMimeTypes = $types;
    }

    /**
     * Get the allowed mime types.
     * If no mime type check is given, null is returned
     *
     * @return array|null
     */
    public function getAllowedMimeTypes()
    {
        return $this->allowedMimeTypes;
    }

    /**
     * Add an allowed mime type
     *
     * @param string $type
     */
    public function addAllowedMimeType($type)
    {
        if (! is_array($this->allowedMimeTypes)) {
            $this->allowedMimeTypes = array();
        }

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
        if (is_array($this->allowedMimeTypes)) {
            $key = array_search($type, $this->allowedMimeTypes);
            if ($key !== false) {
                unlink($this->allowedMimeTypes[$key]);
                return true;
            }
        }

        return false;
    }

    /**
     * Set the extensions which are allowed.
     * The extensions should be in an array. The extension should NOT contain a dot (.) in front of it.
     * Example:
     *
     * <code>
     * $validator -> setAllowedExtensions( array('pdf', 'txt', 'zip', 'jpg' ) );
     * </code>
     *
     * If you set 'null' as value, this check will be disabled.
     *
     * @param array $extensions
     */
    public function setAllowedExtensions($extensions)
    {
        if (! is_array($extensions) && $extensions !== null) {
            throw new Exception("You can only set an array as allowed extensions, or pass 'null' to disable the allowed extensions check.");
        }
        $this->allowedExtensions = $extensions;
    }

    /**
     * Get all allowed extensions.
     * Returns an array with all extensions in it (without leading dot "."),
     * or null if none are set.
     *
     * @return array|null
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    /**
     * Add an extension which is allowed.
     * Pass the extension without a leading dot ".".
     * Example:
     *
     * <code>
     * $validator -> addAllowedExtension("pdf");
     * $validator -> addAllowedExtension("jpg");
     * </code>
     */
    public function addAllowedExtension($extension)
    {
        if (! is_array($this->allowedExtensions)) {
            $this->allowedExtensions = array();
        }

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
        if (is_array($this->allowedExtensions)) {
            $key = array_search($extension, $this->allowedExtensions);
            if ($key !== false) {
                unlink($this->allowedExtensions[$key]);
                return true;
            }
        }

        return false;
    }

    /**
     * Set the mime type or types which are denied for uploading.
     * This can either be an array, or null to disable the mime type checking.
     *
     * @param array $types
     */
    public function setDeniedMimeTypes($types)
    {
        if (! is_array($types) && $types !== null) {
            throw new Exception("You can only set an array as denied mime types, or pass 'null' to disable the denial by mime types");
        }
        $this->deniedMimeTypes = $types;
    }

    /**
     * Get the denied mime types.
     * If no mime type check is given, null is returned
     *
     * @return array|null
     */
    public function getDeniedMimeTypes()
    {
        return $this->deniedMimeTypes;
    }

    /**
     * Add an denied mime type
     *
     * @param string $type
     */
    public function addDeniedMimeType($type)
    {
        if (! is_array($this->deniedMimeTypes)) {
            $this->deniedMimeTypes = array();
        }

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
        if (is_array($this->deniedMimeTypes)) {
            $key = array_search($type, $this->deniedMimeTypes);
            if ($key !== false) {
                unlink($this->deniedMimeTypes[$key]);
                return true;
            }
        }

        return false;
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
     * If you set 'null' as value, this check will be disabled.
     *
     * @param array $extensions
     */
    public function setDeniedExtensions($extensions)
    {
        if (! is_array($types) && $types !== null) {
            throw new Exception("You can only set an array as denied extensions, or pass 'null' to disable the denial of extensions");
        }
        $this->deniedExtensions = $extensions;
    }

    /**
     * Get all denied extensions.
     * Returns an array with all extensions in it (without leading dot "."),
     * or null if none are set.
     *
     * @return array|null
     */
    public function getDeniedExtensions()
    {
        return $this->deniedExtensions;
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
     */
    public function addDeniedExtension($extension)
    {
        if (! is_array($this->deniedExtensions)) {
            $this->deniedExtensions = array();
        }

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
        if (is_array($this->deniedExtensions)) {
            $key = array_search($extension, $this->deniedExtensions);
            if ($key !== false) {
                unlink($this->deniedExtensions[$key]);
                return true;
            }
        }

        return false;
    }
}