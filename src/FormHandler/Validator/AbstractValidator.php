<?php
namespace FormHandler\Validator;

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
    public abstract function isValid();

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
     * Add javascript validation for this field.
     *
     * This method is called just before the field is rendered.
     * Here you can add some javascript to the fields html.
     * This method should return some valid javascript (surround it with valid <script> tags!).
     *
     * @param AbstractFormField $field
     * @return string Javascript which is needed for this field validation or null if none.
     */
    public function addJavascriptValidation(AbstractFormField &$field)
    {}

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