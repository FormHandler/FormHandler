<?php
namespace FormHandler\Validator;

/**
 * This validator will validate a field and make sure it is a valid IPv4 IP address.
 */
class Ipv4Validator extends AbstractValidator
{

    protected $required;

    /**
     * Var to remember if the value was valid or not
     *
     * @var boolean
     */
    protected $valid = null;

    /**
     *
     * @param bool $required
     * @param null $message
     */
    public function __construct($required = true, $message = null)
    {
        if ($message === null) {
            $message = dgettext('formhandler', 'This value is incorrect.');
        }

        $this->setRequired($required);
        $this->setErrorMessage($message);
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        $value = $this->field->getValue();

        if ($this->valid === null) {
            if ($value == '' && $this->required == false) {
                $this->valid = true;
                return $this->valid;
            }

            if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $this->valid = true;
                return $this->valid;
            }

            $this->valid = false;
            return $this->valid;
        }

        return $this->valid;
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
