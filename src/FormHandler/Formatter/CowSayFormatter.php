<?php
namespace FormHandler\Formatter;

use FormHandler\Field\AbstractFormField;
use FormHandler\Field\Element;

class CowSayFormatter extends PlainFormatter
{

    /**
     * Format the element and return it's new layout
     *
     * @param Element $element
     * @return string
     */
    public function format(Element $element)
    {
        $html = parent::format($element);

        if ($element instanceof AbstractFormField) {
            return $this -> cowSay($html);
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
        '<div id="bubble" >' . $html .'</div>
	    <pre id="cow">
           \   ^__^
            \  (oo)\_______
               (__)\       )\/\
                   ||----w |
                   ||     ||
	    </pre>';
    }
}
