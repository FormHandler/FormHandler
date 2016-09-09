<?php
namespace FormHandler\Validator;

use FormHandler\Field\AbstractFormField;
use FormHandler\Field\PassField;

/**
 * This validator will validate if the value of two fields is the same
 */
class SameFieldsValidator extends AbstractValidator
{
    /**
     *
     * @var AbstractFormField|string
     */
    protected $field2;

    /**
     * Create a new SameFieldsValidator validator
     *
     * @param string|AbstractFormField $field2
     * @param string $message
     * @param bool $required
     * @throws \Exception
     */
    public function __construct($field2, $message = null, $required = true)
    {
        if ($message === null) {
            $message = 'The given passwords are not the same.';
        }

        $this->setRequired($required);

        $this->setErrorMessage($message);

        // is good type?
        if (!($field2 instanceof AbstractFormField) && !is_string($field2)) {
            throw new \Exception('The first parameter of the SameFieldsValidator has to ' .
                'be a AbstractFormField object or the name of an existing field!');
        }

        $this->field2 = $field2;
    }

    /**
     * Set the field which should be validated.
     *
     * @param AbstractFormField $field
     */
    public function setField(AbstractFormField $field)
    {
        $this->field = $field;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        if ($this->required && $this->field->getValue() == "") {
            return false;
        }

        // values not the same
        if ($this->field->getValue() != $this->getField2()->getValue()) {
            return false;
        }

        // if here, it's ok
        return true;
    }

    /**
     * Return the instance of the second passfield
     *
     * @return PassField
     */
    protected function getField2()
    {
        if (!$this->field2 instanceof AbstractFormField) {
            $this->field2 = $this->field->getForm()->getFieldByName($this->field2);
        }

        return $this->field2;
    }
}
