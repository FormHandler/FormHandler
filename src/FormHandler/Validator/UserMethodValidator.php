<?php
namespace FormHandler\Validator;

/**
 * This validator will validate a field by calling a method made by the user.
 * The methodName can also be a Closure
 */
class UserMethodValidator extends AbstractValidator
{
    /**
     * The method / colsure which should be executed to validate the field.
     * @var array|\Closure
     */
    protected $userMethod;

    /**
     * Create a new "user method" validator
     *
     * @param array|\Closure $methodName
     */
    public function __construct($methodName)
    {
        $this->setRequired(false);
        $this->userMethod = $methodName;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        $response = call_user_func_array($this->userMethod, array(
            &$this->field
        ));

        if ($response === true) {
            return true;
        }

        if (is_string($response)) {
            $this->setErrorMessage($response);
        }

        return false;
    }
}
