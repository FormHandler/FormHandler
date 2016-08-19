<?php
namespace FormHandler\Validator;

/**
 */
class NumberValidator extends AbstractValidator
{

    protected $min = null;

    protected $max = null;

    protected $required = true;

    /**
     * Create a new number validator
     *
     * Possible default values can be given directly (all are optional)
     *
     * @param int $min
     * @param int $max
     * @param boolean $required
     * @param string $message
     */
    public function __construct($min = null, $max = null, $required = true, $message = null)
    {
        if ($message === null) {
            $message = dgettext('formhandler', 'This value is incorrect.');
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
        elseif (! $this->required && $value == "") {
            return true;
        }

        // check if the field contains a valid number value.
        if (! preg_match('/^-?\d+$/', $value)) {
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
     * Set if this field is required or not.
     *
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;
    }

    /**
     * Get if this field is required or not.
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
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
     * Return the min allowed value.
     *
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }
}
