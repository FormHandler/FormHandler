<?php
namespace FormHandler\Validator;

/**
 */
class StringValidator extends AbstractValidator
{

    protected $minLength = 0;

    protected $maxLength = 0;

    protected $required = true;

    /**
     * Create a new string validator
     *
     * Possible default values can be given directly (all are optional)
     *
     * @param int $minLength
     * @param int $maxLength
     * @param boolean $required
     * @param string $message
     */
    public function __construct($minLength = 0, $maxLength = 0, $required = true, $message = null)
    {
        if ($message === null) {
            $message = dgettext('d2frame', 'This value is incorrect.');
        }

        $this->setMaxLength($maxLength);
        $this->setMinLength($minLength);
        $this->setRequired($required);
        $this->setErrorMessage($message);
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
            $script .= 'function d2StringValidator( field, minLength, maxLength ) {' . PHP_EOL;
            $script .= '    var value = $(field).val();' . PHP_EOL;
            $script .= '    if( !$(field).hasClass("required")) {' . PHP_EOL;
            $script .= '        // the field is not required. Skip the validation if the field is empty.' . PHP_EOL;
            $script .= '        if( $.trim( value ) == "" ) { ' . PHP_EOL;
            $script .= '            $(field).removeClass("invalid");' . PHP_EOL;
            $script .= '            return true;' . PHP_EOL;
            $script .= '        }' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    // shorter then min length?' . PHP_EOL;
            $script .= '    if( value.length < minLength) {' . PHP_EOL;
            $script .= '        $(field).addClass("invalid");' . PHP_EOL;
            $script .= '        return false;' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    // check if value too long' . PHP_EOL;
            $script .= '    if( maxLength != 0 && value.length > maxLength ) {' . PHP_EOL;
            $script .= '        $(field).addClass("invalid");' . PHP_EOL;
            $script .= '        return false;' . PHP_EOL;
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
            $script .= '       d2StringValidator( $("#' . $field->getId() . '"), ' . ((int) $this->getMinLength()) . ', ' . ((int) $this->getMaxLength()) . ' );' . PHP_EOL;
            $script .= '    });' . PHP_EOL;
        }
        $script .= '    $("form#' . $form->getId() . '").bind( "validate", function( event ) {' . PHP_EOL;
        $script .= '        if( !d2StringValidator( $("#' . $field->getId() . '"), ' . ((int) $this->getMinLength()) . ', ' . ((int) $this->getMaxLength()) . ' )) {' . PHP_EOL;
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

        if (is_array($value) || is_object($value)) {
            throw new Exception("This validator only works on scalar types!");
        }

        // required but not given
        if ($this->required && $value == null) {
            return false;
        }  // if the field is not required and the value is empty, then it's also valid
else
            if (! $this->required && $value == "") {
                return true;
            }

        $len = strlen($value);

        // shorter then min length
        if ($len < $this->getMinLength()) {
            return false;
        }

        // bigger then the given length
        if ($this->getMaxLength() > 0 && $len > $this->getMaxLength()) {
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
     * Set the max length the string.
     * Set to zero (or null) if no
     * max is defined.
     *
     * @param int $length
     */
    public function setMaxLength($length)
    {
        $this->maxLength = $length;
    }

    /**
     * Set the minimum length of this string.
     * Default it's zero.
     *
     * @param int $length
     */
    public function setMinLength($length)
    {
        $this->minLength = $length;
    }

    /**
     * Return the max lenght allowed for this validation
     *
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * Return the min lenght allowed for this validation
     *
     * @return int
     */
    public function getMinLength()
    {
        return $this->minLength;
    }
}