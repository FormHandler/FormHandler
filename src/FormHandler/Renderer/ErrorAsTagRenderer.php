<?php

namespace FormHandler\Renderer;

use FormHandler\Field\Element;

class ErrorAsTagRenderer extends XhtmlRenderer
{
    public function render(Element $element)
    {
        $html =  parent::render($element);

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
}
