<?php
namespace FormHandler\Formatter;

/**
 * Formatter class.
 *
 * This class is responsible for they way how elements are rendered,
 * and in specific their "extra" display items, like <label> tags, error
 * messages, etc.
 *
 * Each Element has it's own render() method, which will generate the
 * html for that specific element. However, extra tags needed for the display
 * are not included by default, because this would force a specific
 * way the html is rendered.
 *
 * For example, a checkbox will render only:
 * <input type="checkbox" name="foo" value="bar" />
 *
 * There is no <label> tag rendered. Also, when this field would be invalid, there is
 * no error message rendered. All these extra layout "add-ons" are done in this class,
 * so you can influence the way how your elements are rendered.
 *
 * For each element the format() method is requested. It's a good practice to make
 * a difference how form fields are rendered (error checking) and non-form fields.
 *
 * You can distinguish these by checking if an element is an instance of the
 * AbstractFormField class. If so, it's a form field.
 *
 * See the {@see PlainFormatter} class for a basic example of a formatter.
 */
abstract class AbstractFormatter
{

    /**
     * The form which needs to be formatted
     *
     * @var Form
     */
    protected $form;

    /**
     * Set the form where this formatter is working on
     */
    public function setForm(Form $form)
    {
        $this->form = $form;
    }

    /**
     * Get the form where this formatter is working on
     */
    public function getForm()
    {
        return $form;
    }

    /**
     * Format this specific element
     *
     * @param Element $element
     * @return string The HTML of the element
     */
    public abstract function format(Element $element);

    /**
     * Return the needed javascript.
     *
     * This method will walk all fields and retrieve the javascript
     * needed for the client side validation of the fields. It will
     * return null if no javascript is needed.
     *
     * @return string
     */
    public function getJavascript()
    {
        if (! $this->form) {
            return null;
        }

        $javascript = null;

        // try to add javascript validation for certain elements
        $fields = $this->form->getFields();
        foreach ($fields as $field)
            if ($field instanceof AbstractFormField) {
                $validators = $field->getValidators();
                if ($validators) {
                    foreach ($validators as $validator)
                        if ($validator instanceof AbstractValidator) {
                            $javascript .= $validator->addJavascriptValidation($field);
                        }
                }
            }

        $this->form->setAttribute('onsubmit', '');
        if ($javascript) {
            $javascript = '<script type="text/javascript">' . PHP_EOL . '//<![CDATA[' . PHP_EOL . $javascript . '$(document).ready( function() {' . PHP_EOL . '    $("#' . $this->form->getId() . '").submit(function(){' . PHP_EOL . '        var event = jQuery.Event("validate");' . PHP_EOL . '        $(this).trigger(event);' . PHP_EOL . '        //console.debug(event.result);' . PHP_EOL . '        return !( event.result === false );' . PHP_EOL . '    });' . PHP_EOL . '});' . PHP_EOL . '//]]>' . PHP_EOL . '</script>' . PHP_EOL;
        }

        return $javascript;
    }
}