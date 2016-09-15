<?php

namespace FormHandler\Renderer;

use FormHandler\Field\AbstractFormField;
use FormHandler\Field\Element;

class Bootstrap3Renderer extends XhtmlRenderer
{
    public function render(Element $element)
    {
        if ($element instanceof AbstractFormField) {
            if (!$element->getId()) {
                $element->setId('field-' . uniqid());
            }

            $label = new Tag('label');
            $label->setAttribute('for', $element->getId());
            $label->setInnerHtml($element->getTitle());

            $element->addClass('form-control');
            $field = parent::render($element);

            $tag = new Tag('div');
            $tag->setAttribute('class', 'form-group');
            $tag->setInnerHtml($label->render() . PHP_EOL . $field);

            return $tag->render();
        } else {
            return parent::render($element);
        }
    }
}
