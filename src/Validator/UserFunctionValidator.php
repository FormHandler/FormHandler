<?php

namespace FormHandler\Validator;

/**
 * This validator will validate a field based on a function of the user.
 */
class UserFunctionValidator extends AbstractValidator
{
    /**
     * The function which should be called (Executed) to validate this field.
     *
     * @var callable
     */
    protected $userFunction;

    /**
     * Create a new "user function" validator
     *
     * @param callable $functionName
     *
     * @throws \Exception
     */
    public function __construct(callable $functionName)
    {
        $this->setRequired(false);

        $this->userFunction = $functionName;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        $response = call_user_func_array($this->userFunction, [
            &$this->field,
        ]);

        if ($response === true) {
            return true;
        }

        if (is_string($response)) {
            $this->setErrorMessage($response);
        }

        return false;
    }
}
