<?php
namespace FormHandler\Validator;

/**
 * This validator will validate if the passwords
 * match in two fields
 */
class SamePasswordValidator extends AbstractValidator
{

    /**
     *
     * @var PassField|string
     */
    protected $field2;

    /**
     * Create a new SamePasswordValidator validator
     *
     * @param string|PassField $field2
     * @param
     *            string message
     */
    public function __construct($field2, $message = null)
    {
        if ($message === null) {
            $message = dgettext('d2frame', 'The given passwords are not the same.');
        }

        $this->setErrorMessage($message);

        // is good type?
        if (! ($field2 instanceof PassField) && ! is_string($field2)) {
            throw new Exception('The first parameter of the SamePasswordValidator has to be a PassField object or the name of an PassField field!');
        }

        $this->field2 = $field2;
    }

    /**
     * Set the field which should be validated.
     *
     * @param AbstractFormField $field
     */
    public function setField(AbstractFormField $field)
    {
        if (! ($field instanceof PassField)) {
            throw new Exception('The validator "' . get_class($this) . '" only works on a PassField!');
        }

        $this->field = $field;
    }

    /**
     * Return the instance of the second passfield
     *
     * @return PassField
     * @throws Exception
     */
    protected function getField2()
    {
        if (! $this->field2 instanceof PassField) {
            $this->field2 = $this->field->getForm()->getFieldByName($this->field2);
            if (! $this->field2 instanceof PassField) {
                throw new Exception('The given name of the second PassField seems to be invalid; PassField not found!');
            }
        }

        return $this->field2;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        // values not the same
        if ($this->field->getValue() != $this->getField2()->getValue()) {
            return false;
        }

        // if here, it's ok
        return true;
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

        // make sure we have the instance of the second field
        if (! $this->field2) {
            return;
        }

        $script = '';
        if (! $addedJavascriptFunction) {
            $script .= 'function d2SamePasswordValidator( field, field2 ) {' . PHP_EOL;
            $script .= '    // make sure that the passwords are the same' . PHP_EOL;
            $script .= '    if( $(field).val() != $(field2).val()) {' . PHP_EOL;
            $script .= '        $(field).addClass("invalid");' . PHP_EOL;
            $script .= '        return false;' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    // if here, the field is valid' . PHP_EOL;
            $script .= '    $(field).removeClass("invalid");' . PHP_EOL;
            $script .= '    return true;' . PHP_EOL;
            $script .= '}' . PHP_EOL;

            $addedJavascriptFunction = true;
        }

        /**
         * Make sure that all needed elements have id's
         */
        $form = $field->getForm();
        if (! $form->getId()) {
            $form->setId(uniqid(get_class($form)));
        }

        if (! $field->getId()) {
            $field->setId(uniqid(get_class($field)));
        }

        $field2 = $this->getField2();
        if (! $field2->getId()) {
            $field2->setId(uniqid(get_class($field2)));
        }

        $script .= '$(document).ready( function() {' . PHP_EOL;
        if (! ($field instanceof HiddenField)) {
            $script .= '    $("#' . $field->getId() . '").blur(function() {' . PHP_EOL;
            $script .= '       d2SamePasswordValidator( $("#' . $field->getId() . '"), $("#' . $field2->getId() . '"));' . PHP_EOL;
            $script .= '    });' . PHP_EOL;
        }
        $script .= '    $("form#' . $form->getId() . '").bind( "validate", function( event ) {' . PHP_EOL;
        $script .= '        if( !d2SamePasswordValidator( $("#' . $field->getId() . '"), $("#' . $field2->getId() . '") )) {' . PHP_EOL;
        $script .= '            return false;' . PHP_EOL;
        $script .= '        } else {' . PHP_EOL;
        $script .= '            return event.result;' . PHP_EOL;
        $script .= '        }' . PHP_EOL;
        $script .= '    });' . PHP_EOL;
        $script .= '});' . PHP_EOL;

        return $script;
    }
}