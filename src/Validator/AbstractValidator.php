<?php

namespace FormHandler\Validator;

use FormHandler\Field\AbstractFormField;

/**
 */
abstract class AbstractValidator
{
    /**
     * Set if this field is required or not
     *
     * @var bool
     */
    protected bool $required = false;

    /**
     * The field to validate
     *
     * @var AbstractFormField
     */
    protected AbstractFormField $field;

    /**
     * The error message to show when the value is invalid
     *
     * @var string
     */
    protected string $error = '';

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    abstract public function isValid(): bool;

    /**
     * Set the error message which should be displayed if the field is invalid
     *
     * @param string $message
     */
    public function setErrorMessage(string $message)
    {
        $this->error = $message;
    }

    /**
     * Return the error message to display
     *
     * @return string
     */
    public function getErrorMessage(): string
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

    /**
     * Get if this field is required or not.
     *
     * @return boolean
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Set if this field is required or not.
     *
     * @param boolean $required
     */
    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }
}
