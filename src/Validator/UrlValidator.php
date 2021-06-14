<?php

namespace FormHandler\Validator;

/**
 * This validator checks if the value of the given field is a valid URL.
 *
 * You can check for specific url schemes (http, https, ftp, etc).
 *
 */
class UrlValidator extends AbstractValidator
{
    /**
     * Determines which schemes are allowed for this validator.
     * (e.g. http/https/ftp)
     * Must be an array. Defaults to both HTTP and HTTPS.
     *
     * @var array
     */
    protected array $allowedSchemes = ['http', 'https'];

    /**
     * Max length for the URL
     *
     * @var int|null
     */
    protected ?int $maxLength = null;

    /**
     * Can we skip the tld check?
     *
     * @var bool
     */
    protected bool $skipTldCheck = false;

    /**
     * Create a new URL validator
     *
     * @param boolean     $required
     * @param string|null $message
     * @param int|null    $maxLength
     * @param bool        $skipTldCheck
     */
    public function __construct(
        bool $required = true,
        ?string $message = null,
        ?int $maxLength = null,
        bool $skipTldCheck = false
    ) {
        if ($message === null) {
            $message = 'URL is invalid.';
        }

        $this->setRequired($required);

        if ($maxLength !== null) {
            $this->setMaxLength($maxLength);
        }

        $this->setErrorMessage($message);

        $this->setSkipTldCheck($skipTldCheck);
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        // these schemes are allowed
        $allowedSchemes = $this->getAllowedSchemes();

        // get the field value
        $url = $this->field->getValue();

        // not required and empty? Then its valid
        if ($this->isNotRequiredAndEmpty($url)) {
            return true;
        }

        if ($this->getMaxLength() && strlen($url) > $this->getMaxLength()) {
            // Over maximum length.
            return false;
        }

        // do simple validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $hostname = (string)parse_url($url, PHP_URL_HOST);

        // check if we have a tld
        if (!$this->isSkipTldCheck() && !preg_match('#\.[a-z]{2,}$#', $hostname)) {
            return false;
        }

        // further validation is required.
        if (in_array(parse_url($url, PHP_URL_SCHEME), $allowedSchemes)) {
            return true;
        }

        return false;
    }

    /**
     * Determines which schemes are allowed for this validator.
     * (e.g. http/https/ftp)
     * Must be an array. Defaults to both HTTP and HTTPS.
     *
     * Get the value for allowedSchemes
     *
     * @return array
     */
    public function getAllowedSchemes(): array
    {
        return $this->allowedSchemes;
    }

    /**
     * Determines which schemes are allowed for this validator.
     * (e.g. http/https/ftp)
     * Must be an array (will throw an exception if not). Defaults to both HTTP and HTTPS.
     *
     * Set the value for allowedSchemes
     *
     * @param array $value
     *
     * @throws \Exception
     */
    public function setAllowedSchemes(array $value): void
    {
        $this->allowedSchemes = $value;
    }

    /**
     * Check if the field is required or not.
     * If it's not required, and if it's empty.
     *
     * @param string|null $value
     *
     * @return bool
     */
    protected function isNotRequiredAndEmpty(?string $value): bool
    {
        return (!$this->required && empty($value));
    }

    /**
     * Get the maximum length of this field.
     *
     * @return int|null
     */
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    /**
     * Set the maximum length of this field.
     *
     * @param int|null $length
     */
    public function setMaxLength(?int $length): void
    {
        $this->maxLength = $length;
    }

    /**
     * Can we skip the tld check?
     *
     * Get the value for skipTldCheck
     *
     * @return boolean
     */
    public function isSkipTldCheck(): bool
    {
        return $this->skipTldCheck;
    }

    /**
     * Can we skip the tld check?
     *
     * Set the value for skipTldCheck
     *
     * @param boolean $value
     */
    public function setSkipTldCheck(bool $value): void
    {
        $this->skipTldCheck = $value;
    }
}
