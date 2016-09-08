<?php

namespace FormHandler\Renderer;

use FormHandler\Field\CheckBox;
use FormHandler\Field\Element;
use FormHandler\Field\HiddenField;
use FormHandler\Field\ImageButton;
use FormHandler\Field\Optgroup;
use FormHandler\Field\Option;
use FormHandler\Field\PassField;
use FormHandler\Field\RadioButton;
use FormHandler\Field\SubmitButton;

class XhtmlRenderer extends AbstractRenderer
{

    /**
     * Render this specific element
     *
     * @param Element $element
     * @return string The HTML of the element
     */
    public function render(Element $element)
    {
        $method = $this->getMethodNameForClass($element);

        if (method_exists($this, $method)) {
            $html = $this->$method($element);
        } // if form field
        elseif ($element instanceof AbstractFormField) {
            $html = $this->formField($element);
        } // in case that the form class was overwritten...
        elseif ($element instanceof Form && method_exists($this, 'form')) {
            $html = $this->form($element);
        } // a "normal" element, like a submitbutton or such
        else {
            $html = $element->render();
        }

        // if the element is a form field, also render the errors
        if ($element instanceof AbstractFormField) {
            if ($element->getHelpText()) {
                $html .= '<dfn>' . $element->getHelpText() . '</dfn>' . PHP_EOL;
            }
            if ($element->getForm()->isSubmitted() && !$element->isValid()) {
                $errors = $element->getErrorMessages();
                // if there are any errors to show...
                if ($errors) {
                    $html .= '<tt>' . implode('<br />' . PHP_EOL, $errors) . '</tt>';
                }
            }
        }

        return $html;
    }

    protected function checkBox(CheckBox $checkbox)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'checkbox');

        if ($checkbox->isChecked()) {
            $tag->addAttribute('checked', 'checked');
        }

        $this->parseDefaultElement($tag, $checkbox);

        return $tag->render();
    }

    protected function submitButton(SubmitButton $button)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'submit');

        $this->parseDefaultElement($tag, $button);

        return $tag->render();
    }

    public function hiddenField(HiddenField $field)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'hidden');

        $this->parseDefaultElement($tag, $field);

        return $tag->render();
    }

    public function imageButton(ImageButton $button)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'image');
        $tag->addAttribute('src', $button->getSrc());
        $tag->addAttribute('alt', $button->getAlt());

        $this->parseDefaultElement($tag, $button);

        return $tag->render();
    }

    public function optgroup(Optgroup $optgroup)
    {
        $innerHtml = '';
        foreach ($optgroup->getOptions() as $option) {
            $innerHtml .= $option->render() . PHP_EOL;
        }


        $tag = new Tag('optgroup', $innerHtml);
        $tag->addAttribute('label', $optgroup->getLabel());

        $this->parseDefaultElement($tag, $optgroup);

        return $tag->render();
    }

    public function option(Option $option)
    {
        $value = $option->getLabel() ?: htmlentities($option->getValue(), ENT_QUOTES, 'UTF-8');
        $tag = new Tag('option', $value);

        if ($option->isSelected()) {
            $tag->addAttribute('selected', 'selected');
        }

        $this->parseDefaultElement($tag, $option);

        return $tag->render();
    }

    public function passField(PassField $passField)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'password');
        $tag->addAttribute('maxlength', $passField->getMaxlength());

        $this->parseDefaultElement($tag, $passField);

        return $tag->render();
    }

    public function radioButton(RadioButton $radioButton)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'radio');

        if ($radioButton->isChecked()) {
            $tag->addAttribute('checked', 'checked');
        }

        $this->parseDefaultElement($tag, $radioButton);

        return $tag->render();
    }

    protected function parseDefaultElement(Tag &$tag, Element $element)
    {
        if (method_exists($element, 'isDisabled') && $element->isDisabled()) {
            $tag->addAttribute('disabled', 'disabled');
        }

        if (method_exists($element, 'isReadonly') && $element->isReadonly()) {
            $tag->addAttribute('readonly', 'readonly');
        }

        if (method_exists($element, 'getPlaceholder')) {
            $tag->addAttribute('placeholder', $element->getPlaceholder());
        }

        if (method_exists($element, 'getName') && $element->getName()) {
            $tag->addAttribute('name', $element->getName());
        }

        if (method_exists($element, 'getValue') && $element->getValue() !== null) {
            $tag->addAttribute('value', htmlentities($element->getValue(), ENT_QUOTES, 'UTF-8'));
        }

        if (method_exists($element, 'getSize') && $element->getSize()) {
            $tag->addAttribute('size', $element->getSize());
        }

        $tag->addAttribute('id', $element->getId());
        $tag->addAttribute('title', $element->getTitle());
        $tag->addAttribute('style', $element->getStyle());
        $tag->addAttribute('class', $element->getClass());
        $tag->addAttribute('tabindex', $element->getTabindex());
        $tag->addAttribute('accesskey', $element->getAccesskey());

        foreach ($element->getAttributes() as $name => $value) {
            $tag->addAttribute($name, $value);
        }
    }
}
