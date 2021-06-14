<?php

namespace FormHandler\Validator;

/**
 * This validator will validate a field and make sure it is a valid IP address.
 */
class IpValidator extends AbstractValidator
{
    /**
     * Set the validation flags. Use FILTER_FLAG_IPV4, FILTER_FLAG_IPV6, FILTER_FLAG_NO_PRIV_RANGE or
     * FILTER_FLAG_NO_RES_RANGE
     *
     * @var int
     */
    protected int $flags = 0;

    /**
     *
     * @param bool        $required
     * @param int         $flags Optional FILTER_FLAG_IPV4, FILTER_FLAG_IPV6, FILTER_FLAG_NO_PRIV_RANGE or
     *                           FILTER_FLAG_NO_RES_RANGE
     * @param string|null $message
     */
    public function __construct(bool $required = true, int $flags = FILTER_FLAG_IPV4, ?string $message = null)
    {
        if ($message === null) {
            $message = 'This value is incorrect.';
        }

        $this->setFlags($flags);
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

        if (filter_var($value, FILTER_VALIDATE_IP, $this->flags)) {
            return true;
        }

        return false;
    }

    /**
     * Set the validation flags value.
     * Use FILTER_FLAG_IPV4, FILTER_FLAG_IPV6, FILTER_FLAG_NO_PRIV_RANGE or FILTER_FLAG_NO_RES_RANGE
     *
     * @param int $flags
     *
     * @return IpValidator
     */
    public function setFlags(int $flags): self
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Get the validation flags.
     * FILTER_FLAG_IPV4, FILTER_FLAG_IPV6, FILTER_FLAG_NO_PRIV_RANGE or FILTER_FLAG_NO_RES_RANGE
     *
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }
}
