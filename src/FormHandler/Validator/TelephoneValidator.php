<?php
namespace FormHandler\Validator;

/**
 */
class TelephoneValidator extends StringValidator
{

    /**
     * Create a new email validator
     *
     * @param string $regex
     * @param boolean $required
     * @param string $message
     */
    public function __construct($required = true, $message = null)
    {
        if ($message === null) {
            $message = dgettext('d2frame', 'Invalid email address.');
        }

        $this->setErrorMessage($message);
        $this->setRequired($required);
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
            $script .= 'function d2TelephoneValidator( field ) {' . PHP_EOL;
            $script .= '    if( !$(field).hasClass("required")) {' . PHP_EOL;
            $script .= '        // the field is not required. Skip the validation if the field is empty.' . PHP_EOL;
            $script .= '        if( $.trim($(field).val()) == "" ) { ' . PHP_EOL;
            $script .= '            $(field).removeClass("invalid");' . PHP_EOL;
            $script .= '            return true;' . PHP_EOL;
            $script .= '        }' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    if( /^[0-9-\+ \(\)]+$/i.test( $(field).val() )) {' . PHP_EOL;
            $script .= '        $(field).removeClass("invalid");' . PHP_EOL;
            $script .= '        return true;' . PHP_EOL;
            $script .= '    } else {' . PHP_EOL;
            $script .= '        $(field).addClass("invalid");' . PHP_EOL;
            $script .= '        return false;' . PHP_EOL;
            $script .= '    } ' . PHP_EOL;
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
            $script .= '       d2TelephoneValidator( this );' . PHP_EOL;
            $script .= '    });' . PHP_EOL;
        }
        $script .= '    $("form#' . $form->getId() . '").bind( "validate", function( event ) {' . PHP_EOL;
        $script .= '        if( !d2TelephoneValidator( $("#' . $field->getId() . '") )) {' . PHP_EOL;
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

        // if regex fails...
        if (! preg_match('/^[0-9-\+ \(\)]+$/i', $value)) {
            return false;
        }

        return true;
    }
}