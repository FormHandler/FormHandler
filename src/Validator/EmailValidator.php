<?php

namespace FormHandler\Validator;

use Exception;

/**
 * Check if the value of the field is a valid email address
 */
class EmailValidator extends AbstractValidator
{
    /**
     * Should we also check if the domain of the email address exists?
     *
     * @var bool
     */
    protected bool $checkIfDomainExists = false;

    /**
     * Create a new email validator
     *
     * @param bool        $required
     * @param string|null $message
     * @param bool        $checkIfDomainExists
     */
    public function __construct(bool $required = true, ?string $message = null, bool $checkIfDomainExists = false)
    {
        if ($message === null) {
            $message = 'Invalid email address.';
        }

        $this->setErrorMessage($message);
        $this->setRequired($required);
        $this->setCheckIfDomainExist($checkIfDomainExists);
    }

    /**
     * Store if we should check if the domain name of the email address exists
     *
     * @param bool $value
     */
    public function setCheckIfDomainExist(bool $value): void
    {
        $this->checkIfDomainExists = $value;
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

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $lastPart = strstr($value, '@');
        if ($lastPart === false) {
            return false;
        }

        if ($this->checkIfDomainExists) {
            $host = substr($lastPart, 1);

            if (function_exists('getmxrr')) {
                $tmp = [];
                if (!getmxrr($host, $tmp)) {
                    // this will catch dns do not have an mx record.
                    if (!checkdnsrr($host, 'ANY')) {
                        // invalid!
                        return false;
                    }
                }
            } else {
                // tries to fetch the ip address,
                // but it returns a string containing the unmodified hostname on failure.
                if ($host == gethostbyname($host)) {
                    // host is still the same, thus invalid
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Return if we should also check if the domain name exists or not.
     *
     * @return bool
     */
    public function isCheckIfDomainExist(): bool
    {
        return $this->checkIfDomainExists;
    }
}
