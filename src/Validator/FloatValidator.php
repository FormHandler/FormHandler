<?php

namespace FormHandler\Validator;

use Exception;

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
     *
     * @var float|null
     */
    protected ?float $min = null;

    /**
     * The maximum value which is allowed
     *
     * @var float|null
     */
    protected ?float $max = null;

    /**
     * Set the type of decimal point which is allowed. Default a dot (.). Use one of the DECIMAL_* constants
     *
     * @var int
     */
    protected int $decimalPoint = self::DECIMAL_POINT;

    /**
     * Create a new float validator
     *
     * Possible default values can be given directly (all are optional)
     *
     * @param float|null  $min
     * @param float|null  $max
     * @param boolean     $required
     * @param string|null $message
     * @param int|string  $decimalPoint
     */
    public function __construct(
        ?float $min = null,
        ?float $max = null,
        bool $required = true,
        ?string $message = null,
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
        if ($this->isValueTooLow(floatval($value))) {
            return false;
        }

        // check if the value is not to high.
        if ($this->isValueTooHigh(floatval($value))) {
            return false;
        }

        return true;
    }

    /**
     * Return the regex used for matching valid float
     *
     * @return string
     */
    public function getRegex(): string
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
     *
     * @param float $value
     *
     * @return bool
     */
    protected function isValueTooLow(float $value): bool
    {
        if ($this->min !== null) {
            if ($value < $this->min) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the given value is too high
     *
     * @param float $value
     *
     * @return bool
     */
    protected function isValueTooHigh(float $value): bool
    {
        // check if the value is not to high.
        if ($this->max !== null) {
            if ($value > $this->max && ((string)$this->max) != ((string)$value)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the const DECIMAL_POINT, DECIMAL_COMMA or DECIMAL_POINT_OR_COMMA
     *
     * @return int
     */
    public function getDecimalPoint(): int
    {
        return $this->decimalPoint;
    }

    /**
     * Set the decimal point
     *
     * @param int|string $decimalPoint
     *
     * @return FloatValidator
     */
    public function setDecimalPoint($decimalPoint): self
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
     * @return float|null
     */
    public function getMax(): ?float
    {
        return $this->max;
    }

    /**
     * Set the max length number which the value of this field can be.
     * The $max number itself is also allowed.
     * Set to null to have no max.
     *
     * @param float|null $max
     */
    public function setMax(?float $max): void
    {
        $this->max = $max === null ? null : $max;
    }

    /**
     * Return the min allowed value.
     *
     * @return float|null
     */
    public function getMin(): ?float
    {
        return $this->min;
    }

    /**
     * Set the minimum value of this field.
     * The $min value
     * is also allowed.
     * Set to null to have no min.
     *
     * @param float|null $min
     */
    public function setMin(?float $min): void
    {
        $this->min = $min === null ? null : $min;
    }
}
