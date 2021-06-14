<?php

namespace FormHandler\Validator;

/**
 * This validator will validate a field by calling a method made by the user.
 * The methodName can also be a Closure
 */
class UserMethodValidator extends AbstractValidator
{
    /**
     * The method / closure which should be executed to validate the field.
     *
     * @var callable
     */
    protected $userMethod;

    /**
     * Create a new "user method" validator
     *
     * @param callable $methodName
     */
    public function __construct(callable $methodName)
    {
        $this->setRequired(false);
        $this->userMethod = $methodName;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        $response = call_user_func_array($this->userMethod, [
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
