<?php
namespace FormHandler\Validator;

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
     * @param string $message
     *            (optional)
     */
    public function __construct($message = null)
    {
        if ($message === null) {
            $message = dgettext('d2frame', 'You have to select a value!');
        }

        $this->setErrorMessage($message);
    }

    /**
     * Set the field which should be validated.
     *
     * @param AbstractFormField $field
     */
    public function setField(AbstractFormField $field)
    {
        if (! ($field instanceof RadioButton)) {
            throw new Exception('The validator "' . get_class($this) . '" only works on radio buttons!');
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
        foreach ($fields as $field)
            if ($field instanceof RadioButton) {
                if ($field->getName() == $this->field->getName()) {
                    if ($field->isChecked()) {
                        return true;
                    }
                }
            }

        return false;
    }

    /**
     * Add javascript validation for this field.
     *
     * @param
     *            AbstractFormField &$field
     * @return string
     */
    public function addJavascriptValidation(AbstractFormField &$field)
    {
        static $addedJavascriptFunction = false;

        $script = '';
        if (! $addedJavascriptFunction) {
            $script .= 'function d2RadioButtonCheckedValidator( theName ) {' . PHP_EOL;
            $script .= '    // find all fields with the same name and check if selected' . PHP_EOL;
            $script .= '    var isChecked = $("input[type=radio][name=" + theName + "]:checked").length > 0;' . PHP_EOL;
            $script .= '    $("input[type=radio][name=" + theName + "]").each(function() {' . PHP_EOL;
            $script .= '        if( isChecked ) {' . PHP_EOL;
            $script .= '            $(this).removeClass("invalid");' . PHP_EOL;
            $script .= '            $("label[for=" + $(this).attr("id") + "]").removeClass("invalid");' . PHP_EOL;
            $script .= '        } else {' . PHP_EOL;
            $script .= '            $(this).addClass("invalid");' . PHP_EOL;
            $script .= '            $("label[for=" + $(this).attr("id") + "]").addClass("invalid");' . PHP_EOL;
            $script .= '        }' . PHP_EOL;
            $script .= '    });' . PHP_EOL;
            $script .= '    return isChecked;' . PHP_EOL;
            $script .= '}' . PHP_EOL;

            $addedJavascriptFunction = true;
        }

        $form = $field->getForm();
        if (! $form->getId()) {
            $form->setId(uniqid(get_class($form)));
        }

        $script .= '$(document).ready( function() {' . PHP_EOL;
        if (! ($field instanceof HiddenField)) {
            $script .= '    $("input[type=radio][name=' . $field->getName() . ']").blur(function() {' . PHP_EOL;
            $script .= '       d2RadioButtonCheckedValidator( "' . $field->getName() . '" );' . PHP_EOL;
            $script .= '    });' . PHP_EOL;
        }
        $script .= '    $("form#' . $form->getId() . '").bind( "validate", function( event ) {' . PHP_EOL;
        $script .= '        if( !d2RadioButtonCheckedValidator( "' . $field->getName() . '" )) {' . PHP_EOL;
        $script .= '            return false;' . PHP_EOL;
        $script .= '        } else {' . PHP_EOL;
        $script .= '            return event.result;' . PHP_EOL;
        $script .= '        }' . PHP_EOL;
        $script .= '    });' . PHP_EOL;
        $script .= '});' . PHP_EOL;

        return $script;
    }
}