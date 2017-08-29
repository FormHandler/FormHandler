<?php
namespace FormHandler\Validator;

/**
 */
class StringValidator extends AbstractValidator
{
    /**
     * Set the minimum allowed length of the string. Allowed is this length or longer.
     * @var int
     */
    protected $minLength = 0;

    /**
     * Set the maximum lenght of the string. Allowed is this value or smaller.
     * @var int
     */
    protected $maxLength = 0;

    /**
     * Create a new string validator
     *
     * Possible default values can be given directly (all are optional)
     *
     * @param int $minLength Set the minimum allowed length of the string. Allowed is this length or longer.
     * @param int $maxLength Set the maximum lenght of the string. Allowed is this value or smaller.
     * @param boolean $required
     * @param string $message
     */
    public function __construct($minLength = 0, $maxLength = 0, $required = true, $message = null)
    {
        if ($message === null) {
            $message = 'This value is incorrect.';
        }

        $this->setMaxLength($maxLength);
        $this->setMinLength($minLength);
        $this->setRequired($required);
        $this->setErrorMessage($message);
    }

    /**
     * Check if the given field is valid or not.
     * @return bool
     * @throws \Exception
     */
    public function isValid()
    {
        $value = $this->field->getValue();

        if (is_array($value) || is_object($value)) {
            throw new \Exception("This validator only works on scalar types!");
        }

        // required but not given
        if ($this->required && $value == null) {
            return false;
        } // if the field is not required and the value is empty, then it's also valid
        elseif (!$this->required && $value == "") {
            return true;
        }

        $len = strlen($value);

        // shorter then min length
        if ($len < $this->getMinLength()) {
            return false;
        }

        // bigger then the given length
        if ($this->getMaxLength() > 0 && $len > $this->getMaxLength()) {
            return false;
        }

        return true;
    }

    /**
     * Return the min lenght allowed for this validation
     *
     * @return int
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * Set the minimum length of this string.
     * Default it's zero.
     *
     * @param int $length
     */
    public function setMinLength($length)
    {
        $this->minLength = $length;
    }

    /**
     * Return the max lenght allowed for this validation
     *
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * Set the max length the string.
     * Set to zero (or null) if no
     * max is defined.
     *
     * @param int $length
     */
    public function setMaxLength($length)
    {
        $this->maxLength = $length;
    }
}
