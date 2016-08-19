<?php
namespace FormHandler\Field;

use FormHandler\Form;
use FormHandler\Validator\AbstractValidator;
use FormHandler\Validator\UserFunctionValidator;
use FormHandler\Validator\UserMethodValidator;

/**
 * Base class for all form fields.
 *
 * This class contains some basic functionality which applies
 * for all form fields.
 */
abstract class AbstractFormField extends Element
{
    /**
     * List of all validators for this Form Field.
     *
     * @var array
     */
    protected $validators = [];

    /**
     * The name of this Form Field.
     *
     * @var string
     */
    protected $name;

    /**
     * The Form object
     * @var Form
     */
    protected $form;

    /**
     * Check if this field is disabled or not.
     * @var bool
     */
    protected $disabled = false;

    /**
     * A list of error messages
     * @var array
     */
    protected $errors = [];

    /**
     * Remember if this field is valid or not.
     * @var bool
     */
    protected $valid = null;

    /**
     * A container for setting some help text, if used by the formatter.
     * @var string
     */
    protected $helpText = "";

    /**
     * Return the value of this field.
     * @return mixed
     */
    abstract public function getValue();

    /**
     * Set the value for this field
     * @param mixed $value
     */
    abstract public function setValue($value);

    /**
     * Set if this field is valid or not
     *
     * @param boolean $value
     * @return AbstractFormField
     */
    public function setValid($value)
    {
        $this->valid = $value;
        return $this;
    }

    /**
     * Add a error message
     *
     * @param string $message
     * @param boolean $setToInvalid
     * @return AbstractFormField
     */
    public function addErrorMessage($message, $setToInvalid = true)
    {
        $this->errors[] = $message;

        if ($setToInvalid) {
            $this->setValid(false);
        }

        return $this;
    }

    /**
     * Set a small description which will be displayed
     * next to the field to notify the user what to enter in the field.
     *
     * Please note that this value is only used if the formatter
     * uses it. Otherwise it will be ignored!
     *
     * @param string $text
     * @return AbstractFormField
     */
    public function setHelpText($text)
    {
        $this->helpText = $text;
        return $this;
    }

    /**
     * Returns the help text; a small description for
     * the user to notify him about what to enter in the field.
     *
     * Please note that this value is only used if the formatter
     * uses it. Otherwise it will be ignored!
     *
     * @return string
     */
    public function getHelpText()
    {
        return $this->helpText;
    }

    /**
     * Check if this field is valid or not.
     * It will cache it's result.
     * If a new validator is added, it's cached value is reset.
     *
     * @return boolean
     */
    public function isValid()
    {
        if ($this->valid === null) {
            $this->valid = true;

            if (sizeof($this->validators) > 0) {
                foreach ($this->validators as $validator) {
                    if (! $validator->isValid()) {
                        $this->errors[] = $validator->getErrorMessage();
                        $this->valid = false;
                    }
                }
            }
        }

        return $this->valid;
    }

    /**
     * Get the validation errors for this field
     *
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errors;
    }

    /**
     * Add a validator.
     * A validator can be:
     * - A class which implements the AbstractValidator
     * - A function which return's true if the value is valid, false (or a string = error message) otherwise
     * - An array with the class object as first element, and the method name to execute in it's second element.
     * The return value works the same as a function; true if valid, false or string with error message otherwise.
     *
     * Example:
     * ```php
     * function myValidator( FormHandler\Field\AbstractFormField $field )
     * {
     *     // check the field
     *     if( $field -> getValue() == 'test' ) {
     *         return true; // field is valid
     *     } else {
     *        // the error message which will be used
     *        return 'The value is incorrect!';
     *     }
     * }
     * ```
     *
     * Or with a closure:
     * ```php
     * $field -> addValidator( function( FormHandler\Field\AbstractFormField &$field ) {
     *     // Here we return either true or false.
     *     // In case of false, the default "invalid" error message is shown.
     *     return $field -> getValue() == 'agree';
     * });
     * ```
     *
     * @param mixed $validator
     * @return AbstractFormField
     * @throws \Exception
     */
    public function addValidator($validator)
    {
        if (is_string($validator)) {
            $validator = new UserFunctionValidator($validator);
        } elseif (is_array($validator) || $validator instanceof \Closure) {
            $validator = new UserMethodValidator($validator);
        } elseif ($validator instanceof AbstractValidator) {
            // clone it, because the same validator could be used on an other field,
            // which leaves us with a reference problem.
            $validator = clone $validator;
        }

        if (! ($validator instanceof AbstractValidator)) {
            throw new \Exception('Only validators of types "AbstractValidator" are allowed!');
        }

        $validator->setField($this);
        $this->validators[] = $validator;
        $this->valid = null; // this will trigger that the validation will be executed again
        return $this;
    }

    /**
     * Return the validators for this field.
     * All validators implement the AbstractValidator
     *
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Return the form instance of this field
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set the name of the field and return the TextField reference
     *
     * @param string $name
     * @return AbstractFormField
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Return the name of the textfield
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set if this field is disabled and return the TextField reference
     *
     * @param bool $disabled
     * @return AbstractFormField
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Return if this field is disabled
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * Return the HTML field formatted
     */
    public function __toString()
    {
        $formatter = $this->getForm()->getFormatter();
        if ($formatter) {
            return $formatter->format($this);
        }

        return $this->render();
    }
}
