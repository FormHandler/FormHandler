<?php
namespace FormHandler\Validator;

/**
 * This validator checks if the value in the field is a valid dutch bank account number.
 */
class BankNumberValidator extends AbstractValidator
{
    /**
     * Check if this field should be required or not
     *
     * @var bool
     */
    protected $required = true;

    /**
     * Create a new email validator
     *
     * @param boolean $required
     * @param string $message
     */
    public function __construct($required = true, $message = null)
    {
        if ($message === null) {
            $message = dgettext('formhandler', 'Invalid banknumber.');
        }

        $this->setErrorMessage($message);
        $this->setRequired($required);
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

        if (! preg_match('/^(\d)+$/', $value)) {
            return false;
        }

        $length = strlen($value);
        $total = 0;
        $count = 9;

        for ($i = 0; $i < $length; $i ++) {
            $temp = substr($value, $i, 1);
            $total = $total + ($temp * $count);
            $count --;
        }

        $postbank = ($count > 2 && $count <= 7);

        if (($total % 11) == 0 || $postbank) {
            return true;
        }
        return false;
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
}
