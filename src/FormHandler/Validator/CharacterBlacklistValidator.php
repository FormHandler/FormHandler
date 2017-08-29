<?php
namespace FormHandler\Validator;

/**
 * Validate the field by checking all characters against a blacklist.
 * If the value is in the blacklist, its considered incorrect.
 */
class CharacterBlacklistValidator extends AbstractValidator
{
    /**
     * Blacklist of strings
     * @var array
     */
    protected $blacklist = [];

    /**
     * Create a new blacklist validator
     *
     * This validates if the field contains only characters/strings which are NOT in the blacklist.
     *
     * @param array|string $blacklist
     * @param boolean $required
     * @param string $message
     */
    public function __construct($blacklist, $required = true, $message = null)
    {
        if ($message === null) {
            $message = 'This value is incorrect.';
        }

        $this->setBlacklist($blacklist);
        $this->setRequired($required);
        $this->setErrorMessage($message);
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
        elseif (!$this->required && $value == "") {
            return true;
        }

        // now, walk all chars and check if they are in the blacklist
        for ($i = 0; $i < strlen($value); $i++) {
            if (in_array(strval($value[$i]), $this->blacklist, true)) {
                // not in the blacklist!
                return false;
            }
        }

        // if here, everything is ok!
        return true;
    }

    /**
     * Return the blacklist
     *
     * @return array
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }

    /**
     * Set the blacklist of characters which are allowed for this field.
     * This can either be an array or a string.
     *
     * @param array|\ArrayObject|string $blacklist
     * @throws \Exception
     */
    public function setBlacklist($blacklist)
    {
        if (is_array($blacklist)) {
            $this->blacklist = array_map('strval', $blacklist);
        } elseif ($blacklist instanceof \ArrayObject && true) {
            $this->blacklist = array_map('strval', $blacklist->getArrayCopy());
        } elseif (is_string($blacklist)) {
            $this->blacklist = [];
            for ($i = 0; $i < strlen($blacklist); $i++) {
                $this->blacklist[] = $blacklist[$i];
            }
        } else {
            throw new \Exception('Incorrect blacklist given. Allowed blacklist are: string, array or ArrayObject.');
        }
    }
}
