<?php
namespace FormHandler\Validator;

use FormHandler\Field\AbstractFormField;
use FormHandler\Field\RadioButton;

/**
 * This validator will check if one of the radio buttons is checked.
 * It will walk all fields with the same name of the field where this validator
 * was added, and then check if at least one field was checked.
 */
class RadioButtonCheckedValidator extends AbstractValidator
{
    /**
     * Create a IsExistingOptionValidator
     *
     * @param string $message (optional)
     */
    public function __construct($message = null)
    {
        if ($message === null) {
            $message = 'You have to select a value!';
        }

        $this->setRequired(true);

        $this->setErrorMessage($message);
    }

    /**
     * Set the field which should be validated.
     *
     * @param AbstractFormField $field
     * @throws \Exception
     */
    public function setField(AbstractFormField $field)
    {
        if (!($field instanceof RadioButton)) {
            throw new \Exception('The validator "' . get_class($this) . '" only works on radio buttons!');
        }

        $this->field = $field;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        $form = $this->field->getForm();

        $fields = $form->getFields();
        foreach ($fields as $field) {
            if ($field instanceof RadioButton) {
                if ($field->getName() == $this->field->getName()) {
                    if ($field->isChecked()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
