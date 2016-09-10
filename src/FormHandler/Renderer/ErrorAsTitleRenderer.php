<?php

namespace FormHandler\Renderer;

use FormHandler\Field\Element;

class ErrorAsTitleRenderer extends XhtmlRenderer
{
    public function render(Element $element)
    {
        // if the element is a form field, add the errors in the title tag
        if ($element instanceof AbstractFormField && $element->getForm()->isSubmitted() && !$element->isValid()) {
            $errors = $element->getErrorMessages();
            // if there are any errors to show...
            if ($errors) {
                $element->setTitle(implode("<br />\n", $errors));
            }
        }

        return parent::render($element);
    }
}
