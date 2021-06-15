<?php

namespace FormHandler\Validator;

/**
 * This validator will validate a field and make sure it is a valid IPv4 IP address.
 */
class Ipv4Validator extends AbstractValidator
{
    /**
     *
     * @param bool        $required
     * @param string|null $message
     */
    public function __construct(bool $required = true, ?string $message = null)
    {
        if ($message === null) {
            $message = 'This value is incorrect.';
        }

        $this->setRequired($required);
        $this->setErrorMessage($message);
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        $value = $this->field->getValue();

        if ($value == '' && $this->required == false) {
            return true;
        }

        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }

        return false;
    }
}
