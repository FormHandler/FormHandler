<?php

namespace FormHandler\Renderer;

use FormHandler\Field\Element;

class CowSayRenderer extends XhtmlRenderer
{
    public function render(Element $element)
    {
        $html = parent::render($element);

        if ($element instanceof AbstractFormField) {
            return $this->cowSay($html);
        }

        return $html;
    }

    /**
     * Cow say formatter
     * @param $html
     * @return string
     */
    public function cowSay($html)
    {
        return
            '<div id="bubble" >' . $html . '</div>
	    <pre id="cow">
           \   ^__^
            \  (oo)\_______
               (__)\       )\/\
                   ||----w |
                   ||     ||
	    </pre>';
    }
}
