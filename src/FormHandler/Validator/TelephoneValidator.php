<?php
namespace FormHandler\Validator;

/**
 */
class TelephoneValidator extends StringValidator
{

    /**
     * Create a new email validator
     *
     * @param boolean $required
     * @param string $message
     */
    public function __construct($required = true, $message = null)
    {
        if ($message === null) {
            $message = dgettext('formhandler', 'Invalid email address.');
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

        // if regex fails...
        if (! preg_match('/^[0-9-\+ \(\)]+$/i', $value)) {
            return false;
        }

        return true;
    }
}
