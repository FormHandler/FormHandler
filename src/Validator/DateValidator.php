<?php

namespace FormHandler\Validator;

/**
 * This validator will validate a field and make sure it is a proper date.
 * Anything which can be parsed by strtotime will be valid.
 */
class DateValidator extends AbstractValidator
{
    /**
     * Create a new Date validator. We will check if the date can be parsed by strtotime.
     *
     * @param bool        $required
     * @param string|null $message
     */
    public function __construct(bool $required = true, ?string $message = null)
    {
        if ($message === null) {
            $message = 'This value is incorrect.';
        }

        $this->setRequired($required);
        $this->setErrorMessage($message);
    }

    /**
     * Check if the given field is valid or not. The date should be parsable by strtotime.
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        $value = $this->field->getValue();

        // field is empty and its not required. It's thus valid.
        if ($value == '' && $this->required == false) {
            return true;
        }

        $parsedDate = (array)date_parse($value);

        if (isset($parsedDate['warning_count']) &&
            isset($parsedDate['error_count']) &&
            $parsedDate['warning_count'] == 0 &&
            $parsedDate['error_count'] == 0 &&
            isset($parsedDate['year']) &&
            isset($parsedDate['month'])
        ) {
            return true;
        }

        return false;
    }
}
