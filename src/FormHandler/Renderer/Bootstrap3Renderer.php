<?php

namespace FormHandler\Renderer;

use FormHandler\Field\AbstractFormButton;
use FormHandler\Field\AbstractFormField;
use FormHandler\Field\Element;

class Bootstrap3Renderer extends XhtmlRenderer
{
    public function __construct()
    {
        $this->setHelpFormat(self::RENDER_NONE);
    }

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

            $helpBlock = '';
            if ($element->getHelpText()) {
                $helpTag = new Tag('p');
                $helpTag->setAttribute('class', 'help-block');
                $helpTag->setInnerHtml($element->getHelpText());
                $helpBlock = $helpTag->render();
            }

            $tag = new Tag('div');
            $tag->setAttribute('class', 'form-group');
            $tag->setInnerHtml(
                $label->render() . PHP_EOL .
                $field . PHP_EOL .
                $helpBlock . PHP_EOL
            );

            return $tag->render();
        } elseif ($element instanceof AbstractFormButton) {
            $element->addClass('btn');

            $buttons = $element->getForm()->getFieldsByClass('\FormHandler\Field\AbstractFormButton');
            if (sizeof($buttons) == 1) {
                $element->addClass('btn-default');
            }

            return parent::render($element);
        } else {
            return parent::render($element);
        }
    }
}
