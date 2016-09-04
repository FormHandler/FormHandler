<?php
namespace FormHandler\Formatter;

use FormHandler\Field\AbstractFormField;
use FormHandler\Field\CheckBox;
use FormHandler\Field\Element;
use FormHandler\Field\HiddenField;
use FormHandler\Field\RadioButton;
use FormHandler\Field\UploadField;
use FormHandler\Form;
use FormHandler\Validator\UploadValidator;

/**
 * PlainFormatter class.
 *
 * This class renders all the fields and elements.
 *
 * For checkboxes and radio buttons, it renders the <label> tags
 * directly after the field.
 *
 * Invalid fields will receive the CSS class invalid.
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
     * @return string
     */
    public function format(Element $element)
    {
        // if a method exists for this element, then use that one
        $className = get_class($element);

        // strip namespaces;
        $className = substr($className, strrpos($className, '\\') + 1);

        // make first char lower case
        $className = strtolower(substr($className, 0, 1)) . substr($className, 1);

        if (method_exists($this, $className)) {
            $html = $this->$className($element);
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
     * @param AbstractFormField $field
     * @return string
     */
    public function radioButton(AbstractFormField $field)
    {
        return $this->checkBox($field);
    }

    /**
     * Render a checkbox
     *
     * @param AbstractFormField $element
     * @return string
     */
    public function checkBox(AbstractFormField $element)
    {
        // if the form is submitted
        if ($element->getForm()->isSubmitted()) {
            // if this field was not valid
            if (! $element->isValid()) {
                // add css class
                $element->addClass('invalid');
            }
        }

        $label = "";
        if ($element instanceof CheckBox || $element instanceof RadioButton) {
            $label = $element->getLabel();
        }
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
                    foreach ($validators as $validator) {
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
     * @return string
     */
    public function hiddenField()
    {
        // skip the hidden fields, they are parsed by the <form> tag
        return '';
    }
}
