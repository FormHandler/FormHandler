<?php
namespace FormHandler\Validator;

/**
 * This validator will validate a field based on a function of the user.
 */
class UserFunctionValidator extends AbstractValidator
{
    /**
     * The function which should be called (Executed) to validate this field.
     * @var string
     */
    protected $userFunction;

    /**
     * Create a new "user function" validator
     *
     * @param string $functionName
     * @throws \Exception
     */
    public function __construct($functionName)
    {
        $this->setRequired(false);

        if (!function_exists($functionName)) {
            throw new \Exception('Error, function with the name "' . $functionName . '" does not exists!');
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
        $response = call_user_func_array($this->userFunction, array(
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
