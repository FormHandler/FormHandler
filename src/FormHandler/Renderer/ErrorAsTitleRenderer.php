<?php

namespace FormHandler\Renderer;

use FormHandler\Field\AbstractFormField;
use FormHandler\Field\Element;

class ErrorAsTitleRenderer extends XhtmlRenderer
{
    public function render(Element $element)
    {
        // if the element is a form field, add the errors in the title tag
        if ($element instanceof AbstractFormField && sizeof($element->getErrorMessages()) > 0) {
            $element->setTitle(implode("<br />\n", $element->getErrorMessages()));
        }
        //var_dump( $element );

        return parent::render($element);
    }
}
