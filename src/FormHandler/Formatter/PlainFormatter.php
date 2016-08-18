<?php
namespace FormHandler\Formatter;

/**
 * PlainFormatter class.
 *
 * This class renders all the fields and elements.
 *
 * For checkboxes and radio buttons, it renders the <label> tags
 * directly after the field.
 *
 * All form fields will be checked if they are invalid. If so,
 * the error messages will be added behind the <field> tag surrounded
 * by the <tt> tag. Error messages are seperated by a <br />.
 *
 * Hidden fields are automatically placed directly after the <form> tag,
 * surrounded by <ins> tag to make sure the html is valid.
 */
class PlainFormatter extends AbstractFormatter
{

    /**
     * Format the element and return it's new layout
     *
     * @param Element $element
     */
    public function format(Element $element)
    {
        // if a method exists for this element, then use that one
        $className = get_class($element);
        $className = strtolower(substr($className, 0, 1)) . substr($className, 1);

        if (method_exists($this, $className)) {
            $html = $this->$className($element);
        }  // if form field
else
            if ($element instanceof AbstractFormField) {
                $html = $this->formField($element);
            }  // in case that the form class was overwritten...
else
                if ($element instanceof Form && method_exists($this, 'form')) {
                    $html = $this->form($element);
                }  // a "normal" element, like a submitbutton or such
else {
                    $html = $element->render();
                }

        // if the element is a form field, also render the errors
        if ($element instanceof AbstractFormField) {
            if ($element->getHelpText()) {
                $html .= '<dfn>' . $element->getHelpText() . '</dfn>' . PHP_EOL;
            }
            if ($element->getForm()->isSubmitted() && ! $element->isValid()) {
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
     * Render een radio button
     *
     * @param RadioButton $field
     * @return string
     */
    public function radioButton(RadioButton $field)
    {
        return $this->checkBox($field);
    }

    /**
     * Render a checkbox
     *
     * @param CheckBox|RadioButton $field
     * @return string
     */
    public function checkBox($element)
    {
        // if the form is submitted
        if ($element->getForm()->isSubmitted()) {
            // if this field was not valid
            if (! $element->isValid()) {
                // add css class
                $element->addClass('invalid');
            }
        }

        $label = $element->getLabel();
        if (! empty($label) && $element->getId() == "") {
            $element->setId("field-" . uniqid(get_class($element)));
        }

        $html = $element->render();
        if (! empty($label)) {
            $html .= '<label for="' . $element->getId() . '">' . $label . '</label>' . PHP_EOL;
        }

        return $html;
    }

    /**
     * Render a "general" form field
     *
     * @param AbstractFormField $element
     * @return string
     */
    public function formField(AbstractFormField $element)
    {
        // if the form is submitted
        if ($element->getForm()->isSubmitted()) {
            // if this field was not valid
            if (! $element->isValid()) {
                // add css class
                $element->addClass('invalid');
            }
        }

        return $element->render();
    }

    /**
     * Render a form element
     *
     * @param Form $form
     * @return string
     */
    public function form(Form $form)
    {
        $html = $form->render();
        $fields = $form->getFields();

        $maxFilesize = null;

        $hiddenHtml = "";
        foreach ($fields as $field) {
            if ($field instanceof HiddenField) {
                $hiddenHtml .= $field->render() . PHP_EOL;
            }

            if ($field instanceof UploadField && ! $form->getFieldByName('MAX_FILE_SIZE')) {
                $validators = $field->getValidators();
                if ($validators) {
                    foreach ($validators as $validator)
                        if ($validator instanceof UploadValidator) {
                            if ($validator->getMaxFilesize()) {
                                if ($validator->getMaxFilesize() > $maxFilesize) {
                                    $maxFilesize = $validator->getMaxFilesize();
                                }
                            }
                        }
                }
            }
        }

        if ($maxFilesize) {
            $hiddenHtml .= $form->hiddenField('MAX_FILE_SIZE')
                ->setValue($maxFilesize)
                ->render() . PHP_EOL;
        }

        if (! empty($hiddenHtml)) {
            $html .= "<ins>" . PHP_EOL . $hiddenHtml . "</ins>" . PHP_EOL;
        }

        return $html;
    }

    /**
     * Render a hidden field
     *
     * @param HiddenField $field
     * @return string
     */
    public function hiddenField(HiddenField $field)
    {
        // skip the hidden fields, they are parsed by the <form> tag
        return '';
    }
}