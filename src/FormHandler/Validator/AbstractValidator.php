<?php
namespace FormHandler\Validator;

use FormHandler\Field\AbstractFormField;

/**
 */
abstract class AbstractValidator
{

    /**
     * The field to validate
     *
     * @var AbstractFormField
     */
    protected $field;

    /**
     * The error message to show when the value is invalid
     *
     * @var string
     */
    protected $error;

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    abstract public function isValid();

    /**
     * Set the error message which should be displayed if the field is invalid
     *
     * @param string $message
     */
    public function setErrorMessage($message)
    {
        $this->error = $message;
    }

    /**
     * Return the error message to display
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error;
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
}
