<?php

namespace FormHandler\Renderer;

use FormHandler\Field\AbstractFormButton;
use FormHandler\Field\AbstractFormField;
use FormHandler\Field\CheckBox;
use FormHandler\Field\Element;
use FormHandler\Field\RadioButton;
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

    /**
     * This method is executed when an Element needs to be rendered.
     * Here we add some logic to add the label, an optional help block and the form group.
     * @param Element $element
     * @return string
     */
    public function render(Element $element)
    {
        // For all non-checkbox fields...
        if ($element instanceof AbstractFormField && !$element instanceof CheckBox) {
            if (!$element->getId()) {
                $element->setId('field-' . uniqid());
            }

            // Render our label
            $label = new Tag('label');
            $label->setAttribute('for', $element->getId());
            $label->setInnerHtml($element->getTitle());

            // If no labels are wanted, we set it to "Screen Reader" only,
            // @see http://getbootstrap.com/css/#callout-inline-form-labels
            if ($this->mode == self::MODE_INLINE_NO_LABELS) {
                $label->setAttribute('class', 'sr-only');
            }

            // Render our field, but with an css class "form-control"
            $element->addClass('form-control');
            $field = parent::render($element);

            // Render a help block if an help-text is set.
            $helpBlock = '';
            if ($element->getHelpText()) {
                $helpTag = new Tag('p');
                $helpTag->setAttribute('class', 'help-block');
                $helpTag->setInnerHtml($element->getHelpText());
                $helpBlock = $helpTag->render();
            }

            // Now render our container div, which will contain all of the above
            $tag = new Tag('div');
            $cssClass = 'form-group';

            if ($element->getForm()->isSubmitted()) {
                if (!$element->isValid()) {
                    $cssClass .= ' has-error has-feedback';
                } else {
                    $cssClass .= ' has-success has-feedback';
                }
            }

            $tag->setAttribute('class', $cssClass);

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
     * Render a RadioButton
     *
     * @param RadioButton $radioButton
     * @return string
     */
    public function radioButton(RadioButton $radioButton)
    {
        $label = new Tag('label');

        $tag = new Tag('input');
        $tag->setAttribute('type', 'checkbox');

        if ($radioButton->isChecked()) {
            $tag->setAttribute('checked', 'checked');
        }

        $tagHtml = $this->parseTag($tag, $radioButton);

        $label->setInnerHtml(
            $tagHtml . ' ' . ($radioButton->getLabel() ?: $radioButton->getTitle())
        );

        $div = new Tag('div');

        $cssClass = 'radio';
        if( $radioButton->isDisabled() ) {
            $cssClass .= ' disabled';
        }

        if ($radioButton->getForm()->isSubmitted()) {
            if (!$radioButton->isValid()) {
                $cssClass .= ' has-error has-feedback';
            } else {
                $cssClass .= ' has-success has-feedback';
            }
        }

        $div->setAttribute('class', $cssClass);
        $div->setInnerHtml($label->render());
        $html = $div->render();

        // if it's an horizontal form, then add some more classes
        if ($this->mode == self::MODE_HORIZONTAL) {
            $innerDiv = new Tag('div');
            $innerDiv->setAttribute('class', 'col-sm-offset-2 col-sm-10');
            $innerDiv->setInnerHtml($html);

            $outerDiv = new Tag('div');
            $outerDiv->setAttribute('class', 'form-group');
            $outerDiv->setInnerHtml($innerDiv->render());

            $html = $outerDiv->render();
        }

        return $html;
    }

    /**
     * Render a CheckBox
     *
     * @param CheckBox $checkbox
     * @return string
     */
    protected function checkBox(CheckBox $checkbox)
    {
        $label = new Tag('label');

        $tag = new Tag('input');
        $tag->setAttribute('type', 'checkbox');

        if ($checkbox->isChecked()) {
            $tag->setAttribute('checked', 'checked');
        }

        $tagHtml = $this->parseTag($tag, $checkbox);

        $label->setInnerHtml(
            $tagHtml . ' ' . ($checkbox->getLabel() ?: $checkbox->getTitle())
        );

        $div = new Tag('div');

        $cssClass = 'checkbox';
        if( $checkbox->isDisabled() ) {
            $cssClass .= ' disabled';
        }

        if ($checkbox->getForm()->isSubmitted()) {
            if (!$checkbox->isValid()) {
                $cssClass .= ' has-error has-feedback';
            } else {
                $cssClass .= ' has-success has-feedback';
            }
        }

        $div->setAttribute('class', $cssClass);

        $div->setInnerHtml($label->render());
        $html = $div->render();

        // if it's an horizontal form, then add some more classes
        if ($this->mode == self::MODE_HORIZONTAL) {
            $innerDiv = new Tag('div');
            $innerDiv->setAttribute('class', 'col-sm-offset-2 col-sm-10');
            $innerDiv->setInnerHtml($html);

            $outerDiv = new Tag('div');
            $outerDiv->setAttribute('class', 'form-group');
            $outerDiv->setInnerHtml($innerDiv->render());

            $html = $outerDiv->render();
        }

        return $html;
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

        return parent::form($form);
    }
}
