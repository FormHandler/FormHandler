<?php
namespace FormHandler\Validator;

/**
 * This validator will check if the value of the submitted select field
 * do exists in the options of that field
 */
class IsOptionValidator extends AbstractValidator
{

    protected $required = true;

    /**
     * Create a IsExistingOptionValidator
     *
     * @param boolean $required
     *            (optional)
     * @param string $message
     *            (optional)
     */
    public function __construct($required = true, $message = null)
    {
        if ($message === null) {
            $message = dgettext('d2frame', 'This value is incorrect.');
        }

        $this->setRequired($required);
        $this->setErrorMessage($message);
    }

    /**
     * Set the field which should be validated.
     *
     * @param AbstractFormField $field
     */
    public function setField(AbstractFormField $field)
    {
        if (! ($field instanceof SelectField)) {
            throw new Exception('The validator "' . get_class($this) . '" only works on select fields!');
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
        // get the submitted value
        $value = $this->field->getForm()->getFieldValue($this->field->getName());

        // this would be strange
        if (is_object($value)) {
            return false;
        }

        // required but not given
        if ($this->required && $value == null) {
            return false;
        }  // if the field is not required and the value is empty, then it's also valid
else
            if (! $this->required && $value == "") {
                return true;
            }

        // check if multiple values are returned and if this is allowed.
        if (is_array($value) && ! $this->field->isMultiple()) {
            return false;
        }

        // check if the submitted value(s) are in the options value's
        $options = $this->field->getOptions();

        if (! is_array($value)) {
            $value = array(
                $value
            );
        }

        foreach ($value as $selected) {
            $found = false;

            // walk all options
            foreach ($options as $option) {
                if ($option instanceof Option) {
                    if ($option->getValue() == $selected) {
                        $found = true;
                        break;
                    }
                } else
                    if ($option instanceof Optgroup) {
                        $options2 = $option->getOptions();
                        foreach ($options2 as $option) {
                            if ($option->getValue() == $selected) {
                                $found = true;
                                break 2;
                            }
                        }
                    }
            }

            // if not found, then error!
            if (! $found) {
                // troubles? Then you want to probably log something here
                return false;
            }
        }

        return true;
    }

    /**
     * Set if this field is required or not.
     *
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;
    }

    /**
     * Get if this field is required or not.
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }
}