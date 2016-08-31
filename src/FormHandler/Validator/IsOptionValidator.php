<?php
namespace FormHandler\Validator;

use FormHandler\Field\AbstractFormField;
use FormHandler\Field\Optgroup;
use FormHandler\Field\Option;
use FormHandler\Field\SelectField;

/**
 * This validator will check if the value of the submitted select field
 * do exists in the options of that field
 */
class IsOptionValidator extends AbstractValidator
{
    /**
     * The field to validate
     *
     * @var SelectField;
     */
    protected $field;

    /**
     * Create a IsExistingOptionValidator
     *
     * @param boolean $required (optional)
     * @param string $message (optional)
     */
    public function __construct($required = true, $message = null)
    {
        if ($message === null) {
            $message = 'This value is incorrect.';
        }

        $this->setRequired($required);
        $this->setErrorMessage($message);
    }

    /**
     * Set the field which should be validated.
     *
     * @param AbstractFormField $field
     * @throws \Exception
     */
    public function setField(AbstractFormField $field)
    {
        if (!($field instanceof SelectField)) {
            throw new \Exception('The validator "' . get_class($this) . '" only works on select fields!');
        }

        $this->field = $field;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        // get the submitted value
        $value = $this->field->getForm()->getFieldValue($this->field->getName());

        // required but not given
        if ($this->required && $value == null) {
            return false;
        } // if the field is not required and the value is empty, then it's also valid
        elseif (!$this->required && $value == "") {
            return true;
        }

        // check if multiple values are returned and if this is allowed.
        if (is_array($value) && !$this->field->isMultiple()) {
            return false;
        }

        // check if the submitted value(s) are in the options value's
        $options = $this->field->getOptions();

        if (!is_array($value)) {
            $value = (array)$value;
        }

        // check if the selected options exist
        if (!$this->checkIfOptionsExists($value, $options)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the given value(s) exists in the options of the selectfield.
     * @param array $values
     * @param array $options
     * @return bool
     */
    protected function checkIfOptionsExists(array $values, array $options)
    {
        foreach ($values as $selected) {
            $found = false;

            // walk all options
            foreach ($options as $option) {
                if ($option instanceof Option) {
                    if ($option->getValue() == $selected) {
                        $found = true;
                        break;
                    }
                } elseif ($option instanceof Optgroup) {
                    $options2 = $option->getOptions();
                    foreach ($options2 as $option2) {
                        if ($option2->getValue() == $selected) {
                            $found = true;
                            break 2;
                        }
                    }
                }
            }

            // if not found, then error!
            if (!$found) {
                // troubles? Then you want to probably log something here
                return false;
            }
        }

        return true;
    }
}
