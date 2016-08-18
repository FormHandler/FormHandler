<?php
namespace FormHandler\Validator;

/**
 * This validator will validate a field by calling a method made by the user.
 * The methodName can also be a Closure
 */
class UserMethodValidator extends AbstractValidator
{

    protected $userMethod;

    /**
     * Var to remember if the value was valid or not
     *
     * @var boolean
     */
    protected $valid = null;

    /**
     * Create a new "user method" validator
     *
     * @param array|Closure $methodName
     */
    public function __construct($methodName)
    {
        $this->userMethod = $methodName;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        if ($this->valid === null) {
            $response = call_user_func_array($this->userMethod, array(
                &$this->field
            ));

            if ($response === true) {
                $this->valid = true;
            } else {
                if (is_string($response)) {
                    $this->setErrorMessage($response);
                }
                $this->valid = false;
            }
        }
        return $this->valid;
    }
}