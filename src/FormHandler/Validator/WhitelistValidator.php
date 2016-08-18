<?php
namespace FormHandler\Validator;

/**
 */
class WhitelistValidator extends AbstractValidator
{

    protected $whitelist = array();

    protected $required = true;

    /**
     * Create a new whitelist validator
     *
     * This validates if the field contains only characters which are in the whitelist.
     *
     * @param array|string $whitelist
     * @param boolean $required
     * @param string $message
     */
    public function __construct($whitelist, $required = true, $message = null)
    {
        if ($message === null) {
            $message = dgettext('d2frame', 'This value is incorrect.');
        }

        $this->setWhitelist($whitelist);
        $this->setRequired($required);
        $this->setErrorMessage($message);
    }

    /**
     * Add javascript validation for this validator.
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
            $script .= 'function d2WhitelistValidator( field, whitelist ) {' . PHP_EOL;
            $script .= '    var value = $(field).val();' . PHP_EOL;
            $script .= '    if( !$(field).hasClass("required")) {' . PHP_EOL;
            $script .= '        // the field is not required. Skip the validation if the field is empty.' . PHP_EOL;
            $script .= '        if( $.trim( value ) == "" ) { ' . PHP_EOL;
            $script .= '            $(field).removeClass("invalid");' . PHP_EOL;
            $script .= '            return true;' . PHP_EOL;
            $script .= '        }' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    // check all chars' . PHP_EOL;
            $script .= '    for( i = 0; i < value.length; i++ ) {' . PHP_EOL;
            $script .= '        //console.debug( value.charAt(i), whitelist, jQuery.inArray(value.charAt(i), whitelist ) );' . PHP_EOL;
            $script .= '        if( jQuery.inArray(value.charAt(i), whitelist ) == -1 ) {' . PHP_EOL;
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
            $script .= '       d2WhitelistValidator( $("#' . $field->getId() . '"), ' . json_encode($this->whitelist) . ' );' . PHP_EOL;
            $script .= '    });' . PHP_EOL;
        }
        $script .= '    $("form#' . $form->getId() . '").bind( "validate", function( event ) {' . PHP_EOL;
        $script .= '        if( !d2WhitelistValidator( $("#' . $field->getId() . '"), ' . json_encode($this->whitelist) . ' )) {' . PHP_EOL;
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

        // now, walk all chars and check if they are in the whitelist
        for ($i = 0; $i < strlen($value); $i ++) {
            if (! in_array($value[$i], $this->whitelist)) {
                // not in the whitelist!
                return false;
            }
        }

        // if here, everything is ok!
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
     * Set the whitelist of characters which are allowed for this field.
     * This can either be an array or a string.
     *
     * @param array|ArrayObject|string $whitelist
     */
    public function setWhitelist($whitelist)
    {
        if (is_array($whitelist)) {
            $this->whitelist = $whitelist;
        } else
            if ($whitelist instanceof ArrayObject) {
                $this->whitelist = $whitelist->getArrayCopy();
            } else
                if (is_string($whitelist)) {
                    $this->whitelist = array();
                    for ($i = 0; $i < strlen($whitelist); $i ++) {
                        $this->whitelist[] = $whitelist[$i];
                    }
                } else {
                    throw new Exception('Incorrect whitelist given. Allowed whitelist are: string, array or ArrayObject.');
                }
    }

    /**
     * Return the whitelist
     *
     * @return array
     */
    public function getWhitelist()
    {
        return $this->whitelist;
    }
}