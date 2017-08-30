<?php

namespace FormHandler\Renderer;

use FormHandler\Field\AbstractFormButton;
use FormHandler\Field\AbstractFormField;
use FormHandler\Field\Element;
use FormHandler\Form;

class Bootstrap3Renderer extends XhtmlRenderer
{
    const MODE_NORMAL = 1;
    const MODE_INLINE = 2;
    const MODE_INLINE_NO_LABELS = 3;
    const MODE_HORIZONTAL = 4;

    protected $mode = self::MODE_NORMAL;

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

            // if no labels are
            if ($this->mode == self::MODE_INLINE_NO_LABELS) {
                $label->setAttribute('class', 'sr-only');
            }

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
                $element->addClass('btn-primary');
            }

            return parent::render($element);
        }
        return parent::render($element);
    }

    /**
     * Render a Form,
     *
     * @param Form $form
     * @return string
     */
    public function form(Form $form)
    {
        if ($this->mode == self::MODE_INLINE || $this->mode == self::MODE_INLINE_NO_LABELS) {
            $form->addClass('form-inline');
        } elseif ($this->mode == self::MODE_HORIZONTAL) {
            $form->addClass('form-horizontal');
        }

        return parent::form( $form );
    }
}
