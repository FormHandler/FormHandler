<?php

namespace FormHandler\Validator;

use Exception;

/**
 * Validate a fields value by a regular expression
 */
class RegexValidator extends AbstractValidator
{
    /**
     * The regular expression which we will use to test the value
     *
     * @var string
     */
    protected string $regex = '';

    /**
     * If set to true, we will revert the value.
     * So, if the value does match the regex, the field will be *invalid*
     *
     * @var boolean
     */
    protected bool $not = false;

    /**
     * Create a new regular expression validator
     *
     * @param string      $regex    The regular expressen where to test on
     * @param boolean     $required Is the field required?
     * @param string|null $message  The message which should be displayed if the value was incorrect
     * @param boolean     $not      If set to true, the value should NOT match the regex.
     *                              If it does, the field will be set as incorrect.
     */
    public function __construct(string $regex, bool $required = true, ?string $message = null, bool $not = false)
    {
        if ($message === null) {
            $message = 'This value is incorrect.';
        }

        $this->setRegularExpression($regex);
        $this->setErrorMessage($message);
        $this->setRequired($required);
        $this->setNot($not);
    }

    /**
     * Set the regular expression to test the value with
     *
     * @param string $regex
     */
    public function setRegularExpression(string $regex): void
    {
        $this->regex = $regex;
    }

    /**
     * Get the "NOT" value.
     * If set to true, the field's value will be set as "correct" if the Regex DOES NOT match.
     * If set to false (default), the field will be "correct" if the regex DOES match.
     *
     * @return bool
     */
    public function isNot(): bool
    {
        return $this->not;
    }

    /**
     * Set the "NOT" value.
     * If set to true, the field's value will be set as "correct" if the Regex DOES NOT match.
     * If set to false (default), the field will be "correct" if the regex DOES match.
     *
     * @param boolean $not
     */
    public function setNot(bool $not = false): void
    {
        $this->not = $not;
    }

    /**
     * Gt the regular expression
     */
    public function getRegularExpression(): string
    {
        return $this->regex;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return bool
     * @throws \Exception
     */
    public function isValid(): bool
    {
        $value = $this->field->getValue();

        if (is_array($value) || is_object($value)) {
            throw new Exception("This validator only works on scalar types!");
        }

        // required but not given
        if ($this->required && $value == null) {
            return false;
        } // if the field is not required and the value is empty, then it's also valid
        elseif (!$this->required && $value == "") {
            return true;
        }

        $match = (bool)preg_match($this->regex, $value);

        return $this->not ? !$match : $match;
    }
}
