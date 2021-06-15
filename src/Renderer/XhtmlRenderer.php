<?php

namespace FormHandler\Renderer;

use Exception;
use FormHandler\Form;
use FormHandler\Field\Option;
use FormHandler\Field\Element;
use FormHandler\Field\CheckBox;
use FormHandler\Field\Optgroup;
use FormHandler\Field\TextArea;
use FormHandler\Field\PassField;
use FormHandler\Field\TextField;
use FormHandler\Field\ImageButton;
use FormHandler\Field\RadioButton;
use FormHandler\Field\SelectField;
use FormHandler\Field\UploadField;
use FormHandler\Field\SubmitButton;
use FormHandler\Field\AbstractFormField;

class XhtmlRenderer extends AbstractRenderer
{
    /**
     * Use this constant to make sure that errors are
     * set in the "title" attribute of the field. Any previous value of the title attribute will be overwritten.
     */
    const RENDER_AS_ATTRIBUTE = 1;
    /**
     * Use this constant to render error messages in a specific
     * HTML tag.
     */
    const RENDER_AS_TAG = 2;
    /**
     * Use this constant if you don't want us to render error messages. In that case you should do it yourself.
     */
    const RENDER_NONE = 3;

    /**
     * Set an HTML tag or attribute which should be used for rendering error messages.
     * If none is set we will use the `tt` tag.
     *
     * @var string
     */
    protected string $errorTagOrAttr = 'tt';

    /**
     * Set an HTML tag or attribute which should be used for rendering Help Text messages.
     * By default we will use the `dfn` tag.
     *
     * @var string
     */
    protected string $helpTagOrAttr = 'dfn';

    /**
     * Set the format how error messages should be rendered.
     *
     * @var int
     */
    protected int $errorFormat = self::RENDER_AS_TAG;

    /**
     * @var int
     */
    protected int $helpFormat = self::RENDER_AS_TAG;

    /**
     * Render a HiddenField.
     * By default we do not render hidden fields, as they are rendered together
     * with the <form> tag.
     *
     * @return string
     */
    public function hiddenField(): string
    {
        return '';
    }

    /**
     * Render an ImageButton
     *
     * @param ImageButton $button
     *
     * @return string
     */
    public function imageButton(ImageButton $button): string
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
     *
     * @return string
     * @throws \Exception
     */
    public function optgroup(Optgroup $optgroup): string
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
     * Render the given element.
     *
     * @param Element $element
     *
     * @return string The HTML of the element
     * @throws \Exception
     */
    public function render(Element $element): string
    {
        $method = $this->getMethodNameForClass($element);

        if (!method_exists($this, $method)) {
            throw new Exception('Error, render method "' . $method . '" was not found');
        }

        $errorHtml = $this->renderErrorMessages($element);

        $helpHtml = $this->renderHelpText($element);

        $html = $this->$method($element);

        return $html . $helpHtml . $errorHtml;
    }

    /**
     * If the given element is a form field, and it has error messages, then also render those and return them
     * in the expected format.
     *
     * @param Element $element
     *
     * @return string
     */
    protected function renderErrorMessages(Element $element): string
    {
        $html = '';

        // if we have error messages, then also render those
        if ($element instanceof AbstractFormField && sizeof($element->getErrorMessages()) > 0) {
            // render the error as title?
            if ($this->errorFormat == self::RENDER_AS_ATTRIBUTE) {
                // if the element is a form field, add the errors in the title tag
                $element->setAttribute($this->errorTagOrAttr, implode("\n", $element->getErrorMessages()));
            } elseif ($this->errorFormat == self::RENDER_AS_TAG) {
                $tag = new Tag($this->errorTagOrAttr);
                $tag->setInnerHtml(implode('<br />' . PHP_EOL, $element->getErrorMessages()));
                $html .= $tag->render();
            }
        }

        return $html;
    }

    public function renderHelpText(Element $element): string
    {
        $html = '';

        // if we have error messages, then also render those
        if ($element instanceof AbstractFormField && $element->getHelpText()) {
            // render the error as title?
            if ($this->helpFormat == self::RENDER_AS_ATTRIBUTE) {
                // if the element is a form field, add the errors in the title tag
                $element->setAttribute($this->helpTagOrAttr, $element->getHelpText());
            } elseif ($this->helpFormat == self::RENDER_AS_TAG) {
                $tag = new Tag($this->helpTagOrAttr);
                $tag->setInnerHtml($element->getHelpText());
                $html .= $tag->render();
            }
        }

        return $html;
    }

    /**
     * Render an Option
     *
     * @param Option $option
     *
     * @return string
     */
    public function option(Option $option): string
    {
        $value = $option->getLabel() ?: htmlentities($option->getValue(), ENT_QUOTES, 'UTF-8');
        $tag   = new Tag('option', $value);

        if ($option->isSelected()) {
            $tag->setAttribute('selected', 'selected');
        }

        return $this->parseTag($tag, $option);
    }

    /**
     * Render a PassField
     *
     * @param PassField $passField
     *
     * @return string
     */
    public function passField(PassField $passField): string
    {
        $tag = new Tag('input');
        $tag->setAttribute('type', 'password');
        if ($passField->getMaxlength()) {
            $tag->setAttribute('maxlength', (string)$passField->getMaxlength());
        }

        return $this->parseTag($tag, $passField);
    }

    /**
     * Render a RadioButton
     *
     * @param RadioButton $radioButton
     *
     * @return string
     */
    public function radioButton(RadioButton $radioButton): string
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
     * @param SelectField $selectField
     *
     * @return string
     * @throws \Exception
     */
    public function selectField(SelectField $selectField): string
    {
        $value  = $selectField->getValue();
        $values = is_array($value) ? $value : [(string)$value];

        $innerHtml = '';

        // walk all options
        foreach ($selectField->getOptions() as $option) {
            // set selected if the value matches
            if ($option instanceof Option) {
                $option->setSelected(in_array($option->getValue(), $values));
            } else {
                if ($option instanceof Optgroup) {
                    foreach ($option->getOptions() as $option2) {
                        $option2->setSelected(in_array($option2->getValue(), $values));
                    }
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
     *
     * @return string
     */
    public function textArea(TextArea $textArea): string
    {
        $value = htmlentities($textArea->getValue(), ENT_QUOTES, 'UTF-8');

        $tag = new Tag('textarea', $value);
        $tag->setAttribute('cols', (string)$textArea->getCols());
        $tag->setAttribute('rows', (string)$textArea->getRows());

        return $this->parseTag($tag, $textArea);
    }

    /**
     * Render a TextField
     *
     * @param TextField $textField
     *
     * @return string
     */
    public function textField(TextField $textField): string
    {
        $tag = new Tag('input');
        $tag->setAttribute('type', $textField->getType());

        return $this->parseTag($tag, $textField);
    }

    /**
     * Render an UploadField
     *
     * @param UploadField $uploadField
     *
     * @return string
     */
    public function uploadField(UploadField $uploadField): string
    {
        $tag = new Tag('input');
        $tag->setAttribute('type', 'file');
        if ($uploadField->getAccept()) {
            $tag->setAttribute('accept', (string)$uploadField->getAccept());
        }
        if ($uploadField->isMultiple()) {
            $tag->setAttribute('multiple', 'multiple');
        }

        return $this->parseTag($tag, $uploadField);
    }

    /**
     * Render a Form
     *
     * @param Form $form
     *
     * @return string
     */
    public function form(Form $form): string
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
     * @return string
     */
    public function getErrorTagOrAttr(): string
    {
        return $this->errorTagOrAttr;
    }

    /**
     * @param string $errorTagOrAttr
     *
     * @return XhtmlRenderer
     */
    public function setErrorTagOrAttr(string $errorTagOrAttr): self
    {
        $this->errorTagOrAttr = $errorTagOrAttr;

        return $this;
    }

    /**
     * @return string
     */
    public function getHelpTagOrAttr(): string
    {
        return $this->helpTagOrAttr;
    }

    /**
     * @param string $helpTagOrAttr
     *
     * @return XhtmlRenderer
     */
    public function setHelpTagOrAttr(string $helpTagOrAttr): self
    {
        $this->helpTagOrAttr = $helpTagOrAttr;

        return $this;
    }

    /**
     * @return int
     */
    public function getErrorFormat(): int
    {
        return $this->errorFormat;
    }

    /**
     * @param int $errorFormat
     *
     * @return XhtmlRenderer
     */
    public function setErrorFormat(int $errorFormat): self
    {
        $this->errorFormat = $errorFormat;

        return $this;
    }

    /**
     * @return int
     */
    public function getHelpFormat(): int
    {
        return $this->helpFormat;
    }

    /**
     * @param int $helpFormat
     *
     * @return XhtmlRenderer
     */
    public function setHelpFormat(int $helpFormat): self
    {
        $this->helpFormat = $helpFormat;

        return $this;
    }

    /**
     * Render a CheckBox
     *
     * @param CheckBox $checkbox
     *
     * @return string
     */
    protected function checkBox(CheckBox $checkbox): string
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
     *
     * @return string
     */
    protected function submitButton(SubmitButton $button): string
    {
        $tag = new Tag('input');
        $tag->setAttribute('type', 'submit');

        return $this->parseTag($tag, $button);
    }
}
