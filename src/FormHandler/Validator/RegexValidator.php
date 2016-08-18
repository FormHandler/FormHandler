<?php
namespace FormHandler\Validator;

/**
 */
class RegexValidator extends AbstractValidator
{

    protected $required = true;

    protected $regex;

    protected $not;

    /**
     * Create a new regular expression validator
     *
     * @param string $regex
     *            The regular expressen where to test on
     * @param boolean $required
     *            Is the field required?
     * @param string $message
     *            The message which should be displayed if the value was incorrect
     * @param boolean $not
     *            If set to true, the value should NOT match the regex. If it does, the field will be set as incorrect.
     */
    public function __construct($regex, $required = true, $message = null, $not = false)
    {
        if ($message === null) {
            $message = dgettext('d2frame', 'This value is incorrect.');
        }

        $this->setRegularExpression($regex);
        $this->setErrorMessage($message);
        $this->setRequired($required);
        $this->setNot($not);
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
            $script .= 'function d2RegexValidator( field, regex ) {' . PHP_EOL;
            $script .= '    if( !$(field).hasClass("required")) {' . PHP_EOL;
            $script .= '        // the field is not required. Skip the validation if the field is empty.' . PHP_EOL;
            $script .= '        if( $.trim($(field).val()) == "" ) { ' . PHP_EOL;
            $script .= '            $(field).removeClass("invalid");' . PHP_EOL;
            $script .= '            return true;' . PHP_EOL;
            $script .= '        }' . PHP_EOL;
            $script .= '    }' . PHP_EOL;
            $script .= '    if( regex.test( $(field).val() )) {' . PHP_EOL;
            $script .= '        $(field).removeClass("invalid");' . PHP_EOL;
            $script .= '        return true;' . PHP_EOL;
            $script .= '    } else {' . PHP_EOL;
            $script .= '        $(field).addClass("invalid");' . PHP_EOL;
            $script .= '        return false;' . PHP_EOL;
            $script .= '    } ' . PHP_EOL;
            $script .= '}' . PHP_EOL;

            $addedJavascriptFunction = true;
        }

        if ($this->required) {
            $field->addClass('required');
        }

        $form = $field->getForm();
        if (! $form->getId()) {
            $form->setId(uniqid(get_class($form)));
        }

        if (! $field->getId()) {
            $field->setId(uniqid(get_class($field)));
        }

        // change delimitter for JS
        $jsregex = $this->regex;
        $delimiter = substr($this->regex, 0, 1);

        // we only need to replace the regex when delimiter is different than /
        if ($delimiter != '/') {
            $after = substr($this->regex, strrpos($this->regex, $delimiter) + 1);
            $inner = substr($this->regex, 1, strrpos($this->regex, $delimiter) - 1);

            // remove escaping from old delimiter
            $inner = str_replace('\\' . $delimiter, $delimiter, $inner);

            // strip any escaping of new delimiter (to prevent double escaping)
            $inner = str_replace('\/', '/', $inner);

            // add escaping to new delimiter
            $inner = str_replace('/', '\/', $inner);

            // rebuild regex
            $jsregex = '/' . $inner . '/' . $after;
        }

        $script .= '$(document).ready( function() {' . PHP_EOL;
        if (! ($field instanceof HiddenField)) {
            $script .= '    $("#' . $field->getId() . '").blur(function() {' . PHP_EOL;
            $script .= '       d2RegexValidator( $("#' . $field->getId() . '"), ' . $jsregex . ' );' . PHP_EOL;
            $script .= '    });' . PHP_EOL;
        }
        $script .= '    $("form#' . $form->getId() . '").bind( "validate", function( event ) {' . PHP_EOL;
        $script .= '        if( !d2RegexValidator( $("#' . $field->getId() . '"), ' . $jsregex . ' )) {' . PHP_EOL;
        $script .= '            return false;' . PHP_EOL;
        $script .= '        } else {' . PHP_EOL;
        $script .= '            return event.result;' . PHP_EOL;
        $script .= '        }' . PHP_EOL;
        $script .= '    });' . PHP_EOL;
        $script .= '});' . PHP_EOL;

        return $script;
    }

    /**
     * Set the "NOT" value.
     * If set to true, the field's value will be set as "correct" if the Regex DOES NOT match.
     * If set to false (default), the field will be "correct" if the regex DOES match.
     *
     * @param boolean $not
     */
    public function setNot($not = false)
    {
        $this->not = $not;
    }

    /**
     * Get the "NOT" value.
     * If set to true, the field's value will be set as "correct" if the Regex DOES NOT match.
     * If set to false (default), the field will be "correct" if the regex DOES match.
     *
     * @param boolean $not
     */
    public function getNot()
    {
        return $this->not;
    }

    /**
     * Set the regular expression to test the value with
     *
     * @param string $regex
     */
    public function setRegularExpression($regex)
    {
        $this->regex = $regex;
    }

    /**
     * Gt the regular expression
     */
    public function getRegularExpression()
    {
        return $this->regex;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        $value = $this->field->getValue();

        if (is_array($value) || is_object($value)) {
            throw new Exception("This validator only works on scalar types!");
        }

        // required but not given
        if ($this->required && $value == null) {
            return false;
        }  // if the field is not required and the value is empty, then it's also valid
else
            if (! $this->required && $value == "") {
                return true;
            }

        $match = preg_match($this->regex, $value);

        return $this->not ? ! $match : $match;
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