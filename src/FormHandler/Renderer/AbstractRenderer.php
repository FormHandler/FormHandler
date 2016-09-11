<?php
namespace FormHandler\Renderer;

use FormHandler\Field\AbstractFormField;
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
     * Render this specific element
     *
     * @param Element $element
     * @return string The HTML of the element
     */
    abstract public function render(Element $element);

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
    protected function getMethodNameForClass(Element $element)
    {
        // if a method exists for this element, then use that one
        $className = get_class($element);

        // strip namespaces;
        $className = substr($className, strrpos($className, '\\') + 1);

        // make first char lower case
        return strtolower(substr($className, 0, 1)) . substr($className, 1);
    }

    /**
     * Parse the given element and put it's attributes in the given tag.
     * Then render the HTML tag and return its HTML body.
     *
     * @param Tag $tag
     * @param Element $element
     * @return string
     */
    protected function parseTag(Tag &$tag, Element $element)
    {
        if (method_exists($element, 'isDisabled') && $element->isDisabled()) {
            $tag->setAttribute('disabled', 'disabled');
        }

        if (method_exists($element, 'isReadonly') && $element->isReadonly()) {
            $tag->setAttribute('readonly', 'readonly');
        }

        if (method_exists($element, 'getPlaceholder')) {
            $tag->setAttribute('placeholder', $element->getPlaceholder());
        }

        if (method_exists($element, 'getName') && $element->getName()) {
            $name = $element->getName();
            $suffix = '';
            if (method_exists($element, 'isMultiple') && $element->isMultiple() && substr($name, -1) != ']') {
                $suffix = '[]';
            }

            $tag->setAttribute('name', $name . $suffix);
        }

        if (method_exists($element, 'getValue') &&
            $element->getValue() !== null &&
            $tag->getName() != 'textarea' &&
            is_scalar($element->getValue())
        ) {
            $tag->setAttribute('value', htmlentities($element->getValue(), ENT_QUOTES, 'UTF-8'));
        }

        if (method_exists($element, 'getSize') && $element->getSize()) {
            $tag->setAttribute('size', $element->getSize());
        }

        $tag->setAttribute('id', $element->getId());
        $tag->setAttribute('title', $element->getTitle());
        $tag->setAttribute('style', $element->getStyle());
        $tag->setAttribute('class', $element->getClass());
        $tag->setAttribute('tabindex', $element->getTabindex());
        $tag->setAttribute('accesskey', $element->getAccesskey());

        if ($element instanceof AbstractFormField && $element->isRequired()) {
            $tag->setAttribute('required', 'required');
        }

        foreach ($element->getAttributes() as $name => $value) {
            $tag->setAttribute($name, $value);
        }

        return $tag->render();
    }
}
