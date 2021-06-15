<?php

namespace FormHandler\Validator;

use Exception;
use FormHandler\Form;
use FormHandler\Field\AbstractFormField;

/**
 * This validator will validate if the value of two fields is the same
 */
class SameFieldsValidator extends AbstractValidator
{
    /**
     *
     * @var AbstractFormField|string|null
     */
    protected $field2;

    /**
     * Create a new SameFieldsValidator validator
     *
     * @param string|AbstractFormField $field2
     * @param string|null              $message
     * @param bool                     $required
     *
     * @throws \Exception
     */
    public function __construct($field2, ?string $message = null, bool $required = true)
    {
        if ($message === null) {
            $message = 'The given passwords are not the same.';
        }

        $this->setRequired($required);

        $this->setErrorMessage($message);

        // is good type?
        if (!is_string($field2) && !$field2 instanceof AbstractFormField) {
            throw new Exception('The first parameter of the SameFieldsValidator has to ' .
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
     * @throws \Exception
     */
    public function isValid(): bool
    {
        if ($this->required && $this->field->getValue() == "") {
            return false;
        }

        $field2 = $this->getField2();
        if (!$field2 instanceof AbstractFormField) {
            throw new Exception('Error, field2 does not exists in our SameFieldsValidator!');
        }

        // values not the same
        if ($this->field->getValue() != $field2->getValue()) {
            return false;
        }

        // if here, it's ok
        return true;
    }

    /**
     * Return the instance of the second passfield
     *
     * @return AbstractFormField|null
     * @throws \Exception
     */
    protected function getField2(): ?AbstractFormField
    {
        if (!$this->field2 instanceof AbstractFormField && is_string($this->field2) && !empty($this->field2)) {
            $this->field2 = $this->getForm()->getFieldByName($this->field2);
        }

        if ($this->field2 instanceof AbstractFormField) {
            return $this->field2;
        }

        return null;
    }

    /**
     * @return \FormHandler\Form
     * @throws \Exception
     */
    protected function getForm(): Form
    {
        $form = $this->field->getForm();
        if (!$form instanceof Form) {
            throw new Exception('Error, we got field ' . $this->field->getName() . ' but no Form object!');
        }

        return $form;
    }
}
