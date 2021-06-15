<?php

use FormHandler\Form;
use FormHandler\Field\Element;
use FormHandler\Renderer\XhtmlRenderer;
use FormHandler\Field\AbstractFormField;
use FormHandler\Validator\StringValidator;

require dirname(__DIR__) . '/vendor/autoload.php';

class RequiredRenderer extends XhtmlRenderer
{
    /**
     * Extend the default rendering: Also render
     *
     * @param Element $element
     *
     * @return string
     * @throws \Exception
     */
    public function render(Element $element): string
    {
        // our HTML container
        $html = '';

        // If this is a field, also render our title
        if ($element instanceof AbstractFormField) {
            $html .= '<label>';
            $html .= $element->getTitle();

            // If the field is required, render a red asterisk
            if ($element->isRequired()) {
                $html .= ' <span style="color:red">*</span>';
            }

            $html .= ':</label> ';
        }

        // Now add the default rendering logic to it.
        // This will take care of rendering the rest
        $html .= parent::render($element);

        return $html;
    }
}

$form = new Form();
$form->setRenderer(new RequiredRenderer());

$form->textField('name')
    ->setTitle('Enter your name')
    ->addValidator(new StringValidator(3, 30, true));

$form->submitButton('btn', 'Submit');

if ($form->isSubmitted($reason)) {
    if ($form->isValid()) {
        printf("Hello <b>%s</b><br />",
            htmlentities(
                $form('name')->getValue() ?: 'John Doe'
            )
        );
    } else {
        print_r($form->getValidationErrors());
    }
}

//var_dump( $reason, $form -> isSubmitted(), $form -> isValid() );

echo $form;
echo $form('name');
echo $form('btn');