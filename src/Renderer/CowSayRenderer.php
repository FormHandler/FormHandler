<?php

namespace FormHandler\Renderer;

use FormHandler\Field\Element;
use FormHandler\Field\AbstractFormField;

class CowSayRenderer extends XhtmlRenderer
{
    public function render(Element $element): string
    {
        $html = parent::render($element);

        if ($element instanceof AbstractFormField) {
            return $this->cowSay($html);
        }

        return $html;
    }

    /**
     * Cow say formatter
     *
     * @param string $html
     *
     * @return string
     */
    public function cowSay(string $html): string
    {
        return
            '<div>' . $html . '</div>
	    <pre>
           \   ^__^
            \  (oo)\_______
               (__)\       )\/\
                   ||----w |
                   ||     ||
	    </pre>';
    }
}
