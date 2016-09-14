<?php
namespace FormHandler\Validator;

/**
 * Validates a float
 */
class FloatValidator extends AbstractValidator
{

    /**
     * We allow a number which has a point as decimal separator
     */
    const DECIMAL_POINT = 1;

    /**
     * We allow a number which has a comma as decimal separator
     */
    const DECIMAL_COMMA = 2;

    /**
     * We allow a number which has a point or a comma as decimal separator
     */
    const DECIMAL_POINT_OR_COMMA = 3;

    /**
     * The minimum value which is allowed
     * @var float
     */
    protected $min = null;

    /**
     * The maximum value which is allowed
     * @var float
     */
    protected $max = null;

    /**
     * Set the type of decimal point which is allowed. Default a dot (.). Use one of the DECIMAL_* constants
     * @var int
     */
    protected $decimalPoint = self::DECIMAL_POINT;

    /**
     * Create a new float validator
     *
     * Possible default values can be given directly (all are optional)
     *
     * @param float $min
     * @param float $max
     * @param boolean $required
     * @param string $message
     * @param int $decimalPoint
     */
    public function __construct(
        $min = null,
        $max = null,
        $required = true,
        $message = null,
        $decimalPoint = self::DECIMAL_POINT
    ) {
    
        if ($message === null) {
            $message = 'This value is incorrect.';
        }

        $this->setMax($max);
        $this->setMin($min);
        $this->setRequired($required);
        $this->setErrorMessage($message);
        $this->setDecimalPoint($decimalPoint);
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

        // required but not given.
        if ($this->required && strval($value) === "") {
            return false;
        } // if the field is not required and the value is empty, then it's also valid
        elseif (!$this->required && strval($value) === '') {
            return true;
        }

        // check if the field contains a valid number value.
        if (!preg_match($this->getRegex(), $value)) {
            return false;
        }

        // check if the value is not to low.
        if ($this->isValueTooLow($value)) {
            return false;
        }

        // check if the value is not to high.
        if ($this->isValueTooHigh($value)) {
            return false;
        }

        return true;
    }

    /**
     * Return the regex used for matching valid float
     *
     * @return string
     */
    public function getRegex()
    {
        switch ($this->decimalPoint) {
            case self::DECIMAL_COMMA:
                $regex = '/^-?\d+(,\d+)?$/';
                break;

            case self::DECIMAL_POINT_OR_COMMA:
                $regex = '/^-?\d+((\.|,)\d+)?$/';
                break;

            case self::DECIMAL_POINT:
            default:
                $regex = '/^-?\d+(\.\d+)?$/';
                break;
        }

        return $regex;
    }

    /**
     * Check if the given value is too low.
     * @param $value
     * @return bool
     */
    protected function isValueTooLow($value)
    {
        if ($this->min !== null) {
            if ((function_exists('bcsub') && floatval(bcsub($this->min, $value, 4)) > 0) || $value < $this->min) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the given value is too high
     * @param $value
     * @return bool
     */
    protected function isValueTooHigh($value)
    {
        // check if the value is not to high.
        if ($this->max !== null) {
            if (((function_exists('bcsub') && floatval(bcsub(
                $this->max,
                $value,
                4
            )) < 0) || $value > $this->max) && ((string)$this->max) != ((string)$value)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the const DECIMAL_POINT, DECIMAL_COMMA or DECIMAL_POINT_OR_COMMA
     *
     * @return string
     */
    public function getDecimalPoint()
    {
        return $this->decimalPoint;
    }

    /**
     * Set the decimal point
     *
     * @param int|string $decimalPoint
     * @return FloatValidator
     */
    public function setDecimalPoint($decimalPoint)
    {
        switch ($decimalPoint) {
            case self::DECIMAL_COMMA:
            case ',':
                $this->decimalPoint = self::DECIMAL_COMMA;
                break;

            case self::DECIMAL_POINT_OR_COMMA:
            case '.,':
            case ',.':
                $this->decimalPoint = self::DECIMAL_POINT_OR_COMMA;
                break;

            case self::DECIMAL_POINT:
            case '.':
            default:
                $this->decimalPoint = self::DECIMAL_POINT;
        }

        return $this;
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
        $this->max = $max === null ? null : floatval($max);
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
        $this->min = $min === null ? null : floatval($min);
    }
}
