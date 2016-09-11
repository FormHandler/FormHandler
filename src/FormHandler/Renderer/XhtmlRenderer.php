<?php

namespace FormHandler\Renderer;

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
use Herrera\Json\Exception\Exception;

class XhtmlRenderer extends AbstractRenderer
{
    /**
     * Render the given element.
     *
     * @param Element $element
     * @return string The HTML of the element
     * @throws Exception
     */
    public function render(Element $element)
    {
        $method = $this->getMethodNameForClass($element);

        if (!method_exists($this, $method)) {
            throw new Exception('Error, render method "' . $method . '" was not found');
        }

        return $this->$method($element);
    }

    /**
     * Render a HiddenField.
     * By default we do not render hidden fields, as they are rendered together
     * with the <form> tag.
     * @return string
     */
    public function hiddenField()
    {
        return '';
    }

    /**
     * Render an ImageButton
     *
     * @param ImageButton $button
     * @return string
     */
    public function imageButton(ImageButton $button)
    {
        $tag = new Tag('input');
        $tag->setAttribute('type', 'image');
        $tag->setAttribute('src', $button->getSrc());
        $tag->setAttribute('alt', $button->getAlt());

        return $this->parseTag($tag, $button);
    }

    /**
     * Render an Optgroup
     *
     * @param Optgroup $optgroup
     * @return string
     */
    public function optgroup(Optgroup $optgroup)
    {
        $innerHtml = '';
        foreach ($optgroup->getOptions() as $option) {
            $innerHtml .= $this->render($option) . PHP_EOL;
        }

        $tag = new Tag('optgroup', $innerHtml);
        $tag->setAttribute('label', $optgroup->getLabel());

        return $this->parseTag($tag, $optgroup);
    }

    /**
     * Render an Option
     *
     * @param Option $option
     * @return string
     */
    public function option(Option $option)
    {
        $value = $option->getLabel() ?: htmlentities($option->getValue(), ENT_QUOTES, 'UTF-8');
        $tag = new Tag('option', $value);

        if ($option->isSelected()) {
            $tag->setAttribute('selected', 'selected');
        }

        return $this->parseTag($tag, $option);
    }

    /**
     * Render a PassField
     *
     * @param PassField $passField
     * @return string
     */
    public function passField(PassField $passField)
    {
        $tag = new Tag('input');
        $tag->setAttribute('type', 'password');
        $tag->setAttribute('maxlength', $passField->getMaxlength());


        return $this->parseTag($tag, $passField);
    }

    /**
     * Render a RadioButton
     *
     * @param RadioButton $radioButton
     * @return string
     */
    public function radioButton(RadioButton $radioButton)
    {
        $tag = new Tag('input');
        $tag->setAttribute('type', 'radio');

        if (!$radioButton->getId() && $radioButton->getLabel()) {
            $radioButton->setId('field-' . uniqid('radiobutton'));
        }

        if ($radioButton->isChecked()) {
            $tag->setAttribute('checked', 'checked');
        }

        $html = $this->parseTag($tag, $radioButton);

        if ($radioButton->getLabel()) {
            $label = new Tag('label', $radioButton->getLabel());
            $label->setAttribute('for', $radioButton->getId());
            $html .= $label->render();
        }

        return $html;
    }

    /**
     * Render a selectField
     *
     * @codeCoverageIgnore - Ignore because "$option instanceof Optgroup" is marked as not covered while it is.
     * @param SelectField $selectField
     * @return string
     */
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

            $innerHtml .= $this->render($option);
        }

        $tag = new Tag('select', $innerHtml);

        if ($selectField->isMultiple()) {
            $tag->setAttribute('multiple', 'multiple');
        }

        return $this->parseTag($tag, $selectField);
    }

    /**
     * Render a TextArea
     *
     * @param TextArea $textArea
     * @return string
     */
    public function textArea(TextArea $textArea)
    {
        $value = htmlentities($textArea->getValue(), ENT_QUOTES, 'UTF-8');

        $tag = new Tag('textarea', $value);
        $tag->setAttribute('cols', $textArea->getCols());
        $tag->setAttribute('rows', $textArea->getRows());

        return $this->parseTag($tag, $textArea);
    }

    /**
     * Render a TextField
     *
     * @param TextField $textField
     * @return string
     */
    public function textField(TextField $textField)
    {
        $tag = new Tag('input');
        $tag->setAttribute('type', $textField->getType());

        return $this->parseTag($tag, $textField);
    }

    /**
     * Render an UploadField
     *
     * @param UploadField $uploadField
     * @return string
     */
    public function uploadField(UploadField $uploadField)
    {
        $tag = new Tag('input');
        $tag->setAttribute('type', 'file');
        $tag->setAttribute('accept', $uploadField->getAccept());

        return $this->parseTag($tag, $uploadField);
    }

    /**
     * Render a Form
     *
     * @param Form $form
     * @return string
     */
    public function form(Form $form)
    {
        $tag = new Tag('form');
        $tag->setAttribute('action', $form->getAction());
        $tag->setAttribute('accept', $form->getAccept());
        $tag->setAttribute('accept-charset', $form->getAcceptCharset());
        $tag->setAttribute('enctype', $form->getEnctype());
        $tag->setAttribute('method', $form->getMethod());
        $tag->setAttribute('target', $form->getTarget());

        $html = $this->parseTag($tag, $form);

        $fields = $form->getFieldsByClass('HiddenField');

        if (sizeof($fields) > 0) {
            $html .= '<ins>' . PHP_EOL;

            foreach ($fields as $field) {
                // hidden fields
                $tag = new Tag('input');
                $tag->setAttribute('type', 'hidden');

                $html .= $this->parseTag($tag, $field) . PHP_EOL;
            }
            $html .= '</ins>' . PHP_EOL;
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
        $tag = new Tag('input');
        $tag->setAttribute('type', 'checkbox');

        if (!$checkbox->getId() && $checkbox->getLabel()) {
            $checkbox->setId('field-' . uniqid('checkbox'));
        }

        if ($checkbox->isChecked()) {
            $tag->setAttribute('checked', 'checked');
        }

        $html = $this->parseTag($tag, $checkbox);

        if ($checkbox->getLabel()) {
            $label = new Tag('label', $checkbox->getLabel());
            $label->setAttribute('for', $checkbox->getId());
            $html .= $label->render();
        }

        return $html;
    }

    /**
     * Render a SubmitButton
     *
     * @param SubmitButton $button
     * @return string
     */
    protected function submitButton(SubmitButton $button)
    {
        $tag = new Tag('input');
        $tag->setAttribute('type', 'submit');

        return $this->parseTag($tag, $button);
    }
}
