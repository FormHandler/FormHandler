<?php
namespace FormHandler\Validator;

/**
 * This validator will validate a field based on a function of the user.
 */
class UserFunctionValidator extends AbstractValidator
{

    protected $userFunction;

    /**
     * Var to remember if the value was valid or not
     *
     * @var boolean
     */
    protected $valid = null;

    /**
     * Create a new "user function" validator
     *
     * @param string $functionName
     */
    public function __construct($functionName)
    {
        if (! function_exists($functionName)) {
            throw new Exception('Error, function with the name "' . $functionName . '" does not exists!');
        }

        $this->userFunction = $functionName;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        if ($this->valid === null) {
            $response = call_user_func_array($this->userFunction, array(
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