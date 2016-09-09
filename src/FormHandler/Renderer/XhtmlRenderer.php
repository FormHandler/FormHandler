<?php

namespace FormHandler\Renderer;

use FormHandler\Field\AbstractFormField;
use FormHandler\Field\CheckBox;
use FormHandler\Field\Element;
use FormHandler\Field\ImageButton;
use FormHandler\Field\Optgroup;
use FormHandler\Field\Option;
use FormHandler\Field\PassField;
use FormHandler\Field\RadioButton;
use FormHandler\Field\SelectField;
use FormHandler\Field\SubmitButton;
use FormHandler\Field\TextArea;
use FormHandler\Field\TextField;
use FormHandler\Field\UploadField;
use FormHandler\Form;

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

        $html = '';
        if (method_exists($this, $method)) {
            $html = $this->$method($element);
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

    /**
     * By default we do not render hidden fields, as they are rendered together
     * with the <form> tag.
     * @return string
     */
    public function hiddenField()
    {
        return '';
    }

    public function imageButton(ImageButton $button)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'image');
        $tag->addAttribute('src', $button->getSrc());
        $tag->addAttribute('alt', $button->getAlt());

        return $this->parseTag($tag, $button);
    }

    public function optgroup(Optgroup $optgroup)
    {
        $innerHtml = '';
        foreach ($optgroup->getOptions() as $option) {
            $innerHtml .= $option->render() . PHP_EOL;
        }


        $tag = new Tag('optgroup', $innerHtml);
        $tag->addAttribute('label', $optgroup->getLabel());

        return $this->parseTag($tag, $optgroup);
    }

    public function option(Option $option)
    {
        $value = $option->getLabel() ?: htmlentities($option->getValue(), ENT_QUOTES, 'UTF-8');
        $tag = new Tag('option', $value);

        if ($option->isSelected()) {
            $tag->addAttribute('selected', 'selected');
        }

        return $this->parseTag($tag, $option);
    }

    public function passField(PassField $passField)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'password');
        $tag->addAttribute('maxlength', $passField->getMaxlength());


        return $this->parseTag($tag, $passField);
    }

    public function radioButton(RadioButton $radioButton)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'radio');

        if (!$radioButton->getId() && $radioButton->getLabel()) {
            $radioButton->setId('field-' . uniqid('radiobutton'));
        }

        if ($radioButton->isChecked()) {
            $tag->addAttribute('checked', 'checked');
        }

        $html = $this->parseTag($tag, $radioButton);

        if ($radioButton->getLabel()) {
            $label = new Tag('label', $radioButton->getLabel());
            $label->addAttribute('for', $radioButton->getId());
            $html .= $label->render();
        }

        return $html;
    }

    public function selectField(SelectField $selectField)
    {
        $value = $selectField->getValue();
        $values = is_array($value) ? $value : array((string)$value);

        $innerHtml = '';

        // walk all options
        foreach ($selectField->getOptions() as $option) {
            // set selected if the value matches
            if ($option instanceof Option) {
                $option->setSelected(in_array((string)$option->getValue(), $values));
            } elseif ($option instanceof Optgroup) {
                foreach ($option->getOptions() as $option2) {
                    $option2->setSelected(in_array((string)$option2->getValue(), $values));
                }
            }

            $innerHtml .= $option->render();
        }

        $tag = new Tag('select', $innerHtml);

        if ($selectField->isMultiple()) {
            $tag->addAttribute('multiple', 'multiple');
        }

        return $this->parseTag($tag, $selectField);
    }

    public function textArea(TextArea $textArea)
    {
        $value = htmlentities($textArea->getValue(), ENT_QUOTES, 'UTF-8');

        $tag = new Tag('textarea', $value);
        $tag->addAttribute('cols', $textArea->getCols());
        $tag->addAttribute('rows', $textArea->getRows());

        return $this->parseTag($tag, $textArea);
    }

    public function textField(TextField $textField)
    {
        $tag = new Tag('input');
        $tag->setAttribute('type', $textField->getType());

        return $this->parseTag($tag, $textField);
    }

    public function uploadField(UploadField $uploadField)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'file');
        $tag->addAttribute('accept', $uploadField->getAccept());

        return $this->parseTag($tag, $uploadField);
    }

    public function form(Form $form)
    {
        $tag = new Tag('form');
        $tag->addAttribute('action', $form->getAction());
        $tag->addAttribute('accept', $form->getAccept());
        $tag->addAttribute('accept-charset', $form->getAcceptCharset());
        $tag->addAttribute('enctype', $form->getEnctype());
        $tag->addAttribute('method', $form->getMethod());
        $tag->addAttribute('target', $form->getTarget());

        $html = $this->parseTag($tag, $form);

        $fields = $form->getFieldsByClass('HiddenField');

        if (sizeof($fields) > 0) {
            $html .= '<ins>' . PHP_EOL;

            foreach ($fields as $field) {
                // hidden fields
                $tag = new Tag('input');
                $tag->addAttribute('type', 'hidden');

                $html .= $this->parseTag($tag, $field) . PHP_EOL;
            }
            $html .= '</ins>' . PHP_EOL;
        }

        return $html;
    }

    protected function checkBox(CheckBox $checkbox)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'checkbox');

        if (!$checkbox->getId() && $checkbox->getLabel()) {
            $checkbox->setId('field-' . uniqid('checkbox'));
        }

        if ($checkbox->isChecked()) {
            $tag->addAttribute('checked', 'checked');
        }

        $html = $this->parseTag($tag, $checkbox);

        if ($checkbox->getLabel()) {
            $label = new Tag('label', $checkbox->getLabel());
            $label->addAttribute('for', $checkbox->getId());
            $html .= $label->render();
        }

        return $html;
    }

    protected function submitButton(SubmitButton $button)
    {
        $tag = new Tag('input');
        $tag->addAttribute('type', 'submit');

        return $this->parseTag($tag, $button);
    }
}
