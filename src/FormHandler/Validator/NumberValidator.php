<?php
namespace FormHandler\Validator;

/**
 */
class NumberValidator extends AbstractValidator
{

    protected $min = null;

    protected $max = null;

    protected $required = true;

    /**
     * Create a new number validator
     *
     * Possible default values can be given directly (all are optional)
     *
     * @param int $min
     * @param int $max
     * @param boolean $required
     * @param string $message
     */
    public function __construct($min = null, $max = null, $required = true, $message = null)
    {
        if ($message === null) {
            $message = dgettext('d2frame', 'This value is incorrect.');
        }

        $this->setMax($max);
        $this->setMin($min);
        $this->setRequired($required);
        $this->setErrorMessage($message);
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

        // check if the field contains a valid number value.
        if (! preg_match('/^-?\d+$/', $value)) {
            return false;
        }

        // check if the value is not to low.
        if ($this->min !== null) {
            if ($value < $this->min) {
                return false;
            }
        }

        // check if the value is not to high.
        if ($this->max !== null) {
            if ($value > $this->max) {
                return false;
            }
        }

        return true;
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
            $script .= 'function d2NumberValidator( field, min, max ) {' . PHP_EOL;
            $script .= '    if( !$(field).hasClass("required")) {' . PHP_EOL;
            $script .= '        // the field is not required. Skip the validation if the field is empty.' . PHP_EOL;
            $script .= '        if( $.trim($(field).val()) == "" ) { ' . PHP_EOL;
            $script .= '            $(field).removeClass("invalid");' . PHP_EOL;
            $script .= '            return true;' . PHP_EOL;
            $script .= '        }' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    // check if the value is a number (possible signed)' . PHP_EOL;
            $script .= '    if( !/^-?\d+$/.test( $(field).val() )) {' . PHP_EOL;
            $script .= '        $(field).addClass("invalid");' . PHP_EOL;
            $script .= '        return false;' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    var value = $(field).val() * 1; // make numeric' . PHP_EOL;
            $script .= '    // check the min value' . PHP_EOL;
            $script .= '    if( min !== null && value < min ) {' . PHP_EOL;
            $script .= '        $(field).addClass("invalid");' . PHP_EOL;
            $script .= '        return false;' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    // check the max value' . PHP_EOL;
            $script .= '    if( max !== null && value > max ) {' . PHP_EOL;
            $script .= '        $(field).addClass("invalid");' . PHP_EOL;
            $script .= '        return false;' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    // if here, the field is valid' . PHP_EOL;
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
            $script .= '       d2NumberValidator( $("#' . $field->getId() . '"), ' . ($this->getMin() === null ? 'null' : $this->getMin()) . ', ' . ($this->getMax() === null ? 'null' : $this->getMax()) . ');' . PHP_EOL;
            $script .= '    });' . PHP_EOL;
        }
        $script .= '    $("form#' . $form->getId() . '").bind( "validate", function( event ) {' . PHP_EOL;
        $script .= '        if( !d2NumberValidator( $("#' . $field->getId() . '"), ' . ($this->getMin() === null ? 'null' : $this->getMin()) . ', ' . ($this->getMax() === null ? 'null' : $this->getMax()) . ')) {' . PHP_EOL;
        $script .= '            return false;' . PHP_EOL;
        $script .= '        } else {' . PHP_EOL;
        $script .= '            return event.result;' . PHP_EOL;
        $script .= '        }' . PHP_EOL;
        $script .= '    });' . PHP_EOL;
        $script .= '});' . PHP_EOL;

        return $script;
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
     * Set the max length number which the value of this field can be.
     * The $max number itsself is also allowed.
     * Set to null to have no max.
     *
     * @param int $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * Set the minimum value of this field.
     * The $min value
     * is also allowed.
     * Set to null to have no min.
     *
     * @param int $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }

    /**
     * Return the max allowed value
     *
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Return the min allowed value.
     *
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }
}