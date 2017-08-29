<?php
namespace FormHandler\Validator;

/**
 */
class NumberValidator extends AbstractValidator
{
    /**
     * The minimal value of this field. Allowed is this number or higher
     * @var int
     */
    protected $min = null;

    /**
     * The maximum value of this field. Allowed is this number or lower.
     * @var int
     */
    protected $max = null;

    /**
     * Create a new number validator
     *
     * Possible default values can be given directly (all are optional)
     *
     * @param int $min The minimum allowed number. So this number or higher are allowed, not lower.
     * @param int $max The maximum allowed number. So this number or lower are allowed, not higher.
     * @param boolean $required
     * @param string $message
     */
    public function __construct($min = null, $max = null, $required = true, $message = null)
    {
        if ($message === null) {
            $message = 'This value is incorrect.';
        }

        $this->setMax($max);
        $this->setMin($min);
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

        // check if the field contains a valid number value.
        if (!preg_match('/^-?\d+$/', $value)) {
            return false;
        }

        // check if the value is not to low.
        if ($this->min !== null) {
            if ($value < $this->min) {
                return false;
            }
        }

        // check if the value is not to high.
        if ($this->max !== null) {
            if ($value > $this->max) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return the max allowed value
     *
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Set the max length number which the value of this field can be.
     * The $max number itsself is also allowed.
     * Set to null to have no max.
     *
     * @param int $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * Return the min allowed value.
     *
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Set the minimum value of this field.
     * The $min value
     * is also allowed.
     * Set to null to have no min.
     *
     * @param int $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }
}
