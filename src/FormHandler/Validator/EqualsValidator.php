<?php
namespace FormHandler\Validator;

use FormHandler\Field\CheckBox;
use FormHandler\Field\RadioButton;

/**
 * This validator will check if the given value equals the fields value.
 */
class EqualsValidator extends AbstractValidator
{
    /**
     * The value where the fields value should match with
     *
     * @var string
     */
    protected $compareValue;

    /**
     * Set if we should revert the working if this equalsTo validator.
     * So, the value should NOT match to the $compareValue.
     * @var bool
     */
    protected $not = false;

    /**
     * Create a new EqualsToValidator validator
     *
     * @param string $compareValue
     * @param boolean $required
     * @param string $message
     * @param bool $not
     */
    public function __construct($compareValue, $required = true, $message = null, $not = false)
    {
        if ($message === null) {
            $message = 'The value is incorrect.';
        }

        $this->setCompareToValue($compareValue);
        $this->setRequired($required);
        $this->setErrorMessage($message);
        $this->setNot($not);
    }

    /**
     * Set the value where the field should be compared with
     *
     * @param boolean $value
     */
    public function setCompareToValue($value)
    {
        $this->compareValue = $value;
    }

    /**
     * Get the value where the fields value should compared with
     */
    public function getCompareToValue()
    {
        return $this->compareValue;
    }

    /**
     * Get the "NOT" value.
     * If set to true, the field's value will be set as "correct" if the value DOES NOT match.
     * If set to false (default), the field will be "correct" if the value DOES match.
     * @return bool
     */
    public function isNot()
    {
        return $this->not;
    }

    /**
     * Set the "NOT" value.
     * If set to true, the field's value will be set as "correct" if the value DOES NOT match.
     * If set to false (default), the field will be "correct" if the value DOES match.
     *
     * @param boolean $not
     */
    public function setNot($not = false)
    {
        $this->not = $not;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        $value = $this->field->getValue();

        // required but not given
        if ($this->required && !$value) {
            return false;
        } // if the field is not required and the value is empty, then it's also valid
        elseif (!$this->required && $value == "") {
            return true;
        }

        // radio button or checkbox
        if ($this->field instanceof CheckBox || $this->field instanceof RadioButton) {
            if (!$this->field->isChecked()) {
                return false;
            }
        }

        // values not the same
        if ($value != $this->compareValue) {
            return $this->not ? true : false;
        }

        // if here, it's ok
        return $this->not ? false : true;
    }
}
