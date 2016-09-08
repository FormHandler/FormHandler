<?php
namespace FormHandler\Renderer;

use FormHandler\Field\Element;

/**
 * Class AbstractRenderer
 * @package FormHandler\Renderer
 *
 * This class is responsible for rendering our elements in the Form.
 *
 * This class has 1 method, render, which will analyze the given element and makes sure that the
 * correct output is returned. It has to take into account if the field is valid or not and render
 * possible error messages also.
 * Labels should/could also be rendered for CheckBox and RadioButton fields.
 */
abstract class AbstractRenderer
{
    /**
     * Shorthand to render an element
     * @param Element $element
     * @return string
     */
    public function __invoke(Element $element)
    {
        return $this->render($element);
    }

    /**
     * Return the name of the method as we would expect it for this class.
     *
     * It creates method names like this:
     *
     * ```FormHandler\Fields\TextField => textField```
     * ```FormHandler\Fields\RadioButton => radioButton```
     *
     * @param Element $element
     * @return string
     */
    protected function getMethodNameForClass( Element $element )
    {
        // if a method exists for this element, then use that one
        $className = get_class($element);

        // strip namespaces;
        $className = substr($className, strrpos($className, '\\') + 1);

        // make first char lower case
        return strtolower(substr($className, 0, 1)) . substr($className, 1);
    }

    /**
     * Render this specific element
     *
     * @param Element $element
     * @return string The HTML of the element
     */
    abstract public function render(Element $element);
}