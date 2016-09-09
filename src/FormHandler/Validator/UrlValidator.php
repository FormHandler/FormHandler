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
    protected $allowedSchemes = ['http', 'https'];

    /**
     * Max length for the URL
     *
     * @var int
     */
    protected $maxLength;

    /**
     * Can we skip the tld check?
     *
     * @var bool
     */
    protected $skipTldCheck = false;

    /**
     * Create a new URL validator
     *
     * @param boolean $required
     * @param string $message
     * @param null $maxLength
     * @param bool $skipTldCheck
     */
    public function __construct($required = true, $message = null, $maxLength = null, $skipTldCheck = false)
    {
        if ($message === null) {
            $message = 'URL is invalid.';
        }

        $this->setRequired($required);

        $this->setMaxLength($maxLength);

        $this->setErrorMessage($message);

        $this->setSkipTldCheck($skipTldCheck);
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
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
        if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_SCHEME_REQUIRED)) {
            return false;
        }

        // check if we have a tld
        if (!$this->isSkipTldCheck() && !preg_match('#\.[a-z]{2,}$#', parse_url($url, PHP_URL_HOST))) {
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
    public function getAllowedSchemes()
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
     * @throws \Exception
     */
    public function setAllowedSchemes($value)
    {
        if (!is_array($value)) {
            throw new \Exception('Tried to set allowed schemes to a value that was not an array.');
        }
        $this->allowedSchemes = $value;
    }

    /**
     * Check if the field is required or not.
     * If it's not required, and if its empty.
     *
     * @param $value
     * @return bool
     */
    protected function isNotRequiredAndEmpty($value)
    {
        return (!$this->required && !$value);
    }

    /**
     * Get the maximum length of this field.
     *
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * Set the maximum length of this field.
     *
     * @param int $length
     */
    public function setMaxLength($length)
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
    public function isSkipTldCheck()
    {
        return $this->skipTldCheck;
    }

    /**
     * Can we skip the tld check?
     *
     * Set the value for skipTldCheck
     *
     * @param boolean $value
     * @return UrlValidator
     */
    public function setSkipTldCheck($value)
    {
        $this->skipTldCheck = $value;
        return $this;
    }
}
