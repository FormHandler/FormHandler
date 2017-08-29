<?php
namespace FormHandler\Field;

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
    use TraitFormAware;

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
    protected $helpText = '';

    /**
     * The value of this field
     * @var mixed
     */
    protected $value;

    /**
     * Return the value for this field
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value for this field and return the reference of this field
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        // trim the value we dont want leading and trailing spaces
        if (is_string($value)) {
            $value = trim($value);
        }
        $this->value = $value;

        // also clear cache of possible validations if the value is changed.
        $this->clearCache();

        return $this;
    }

    /**
     * Clear abstract cache .
     * We cache for example the result of the isValid method, so that for a second call, we do not
     * have to validate the field again. This method clears such caches.
     * @return $this
     */
    public function clearCache()
    {
        $this->valid = null;
        return $this;
    }

    /**
     * Add a error message
     *
     * @param string $message
     * @param boolean $setToInvalid
     * @return $this
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
     * Set a small description which will be displayed
     * next to the field to notify the user what to enter in the field.
     *
     * Please note that this value is only used if the formatter
     * uses it. Otherwise it will be ignored!
     *
     * @param string $text
     * @return $this
     */
    public function setHelpText($text)
    {
        $this->helpText = $text;
        return $this;
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
                    if (!$validator->isValid()) {
                        $this->errors[] = $validator->getErrorMessage();
                        $this->valid = false;
                    }
                }
            }
        }

        return $this->valid;
    }

    /**
     * Set if this field is valid or not
     *
     * @param boolean $value
     * @return $this
     */
    public function setValid($value)
    {
        $this->valid = $value;
        return $this;
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
     * Set the validator to the given validator.
     * *WARNING*: this will overwrite the current validators and only set the given validator.
     * Most of the times you probably want to use ```addValidator```
     *
     * @param mixed $validator
     * @return $this
     */
    public function setValidator($validator)
    {
        $this->clearCache();
        $this->validators = [];
        $this->addValidator($validator);

        return $this;
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
     * @return $this
     * @throws \Exception
     */
    public function addValidator($validator)
    {
        if (is_string($validator)) {
            $validator = new UserFunctionValidator($validator);
        } elseif (is_array($validator) || $validator instanceof \Closure) {
            $validator = new UserMethodValidator($validator);
        } else {
            if ($validator instanceof AbstractValidator) {
                // clone it, because the same validator could be used on an other field,
                // which leaves us with a reference problem.
                $validator = clone $validator;
            }
        }

        if (!$validator instanceof AbstractValidator) {
            throw new \Exception('Only validators of types "AbstractValidator" are allowed!');
        }

        $validator->setField($this);
        $this->validators[] = $validator;
        $this->valid = null; // this will trigger that the validation will be executed again
        return $this;
    }

    /**
     * Remove all validators from this field.
     * @return $this
     */
    public function clearValidators()
    {
        $this->validators = [];
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
     * Return the name of this field
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of the field and return this Field
     *
     * This method is abstract because each field should implement this logic and also
     * fetch it's value from the form's submitted data by this name. This varies per field.
     *
     * @param string $name
     * @return $this
     */
    abstract public function setName($name);

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
     * Set if this field is disabled and return this Field
     *
     * @param bool $disabled
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Return the HTML field formatted
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Return string representation of this field
     *
     * @return string
     */
    public function render()
    {
        return $this->getForm()->getRenderer()->render($this);
    }

    /**
     * Check if one of the validators on this field requires that this field should be filled in.
     * @return bool
     */
    public function isRequired()
    {
        foreach ($this->validators as $validator) {
            if ($validator->isRequired()) {
                return true;
            }
        }

        return false;
    }
}
