<?php
namespace FormHandler\Formatter;

use FormHandler\Field\Element;
use FormHandler\Form;

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
 * a difference how form fields are rendered (error checking) and non-form fields, like buttons.
 *
 * You can distinguish these by checking if an element is an instance of the
 * AbstractFormField class. If so, it's a form field.
 *
 * See the {@see PlainFormatter} class for a basic example of a formatter.
 */
abstract class AbstractFormatter
{
    /**
     * Shorthand to format an element
     * @param Element $element
     * @return string
     */
    public function __invoke(Element $element)
    {
        return $this -> format($element);
    }

    /**
     * Format this specific element
     *
     * @param Element $element
     * @return string The HTML of the element
     */
    abstract public function format(Element $element);
}
