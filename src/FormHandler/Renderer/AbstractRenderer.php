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
    protected function parseTag(Tag $tag, Element $element)
    {
        $list = [
            'disabled' => 'isDisabled',
            'readonly' => 'isReadonly',
            'required' => 'isRequired',
            'placeholder' => 'getPlaceholder',
            'id' => 'getId',
            'title' => 'getTitle',
            'style' => 'getStyle',
            'class' => 'getClass',
            'tabindex' => 'getTabindex',
            'accesskey' => 'getAccessKey',
            'size' => 'getSize'
        ];

        foreach ($list as $attribute => $method) {
            if (method_exists($element, $method)) {
                $value = $element->$method();

                if (substr($method, 0, 2) == 'is') {
                    if ($value) {
                        $tag->setAttribute($attribute, $attribute);
                    }
                } else {
                    $tag->setAttribute($attribute, $value);
                }
            }
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

        foreach ($element->getAttributes() as $name => $value) {
            $tag->setAttribute($name, $value);
        }

        return $tag->render();
    }
}
