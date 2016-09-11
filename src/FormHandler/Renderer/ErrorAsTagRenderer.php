<?php

namespace FormHandler\Renderer;

use FormHandler\Field\AbstractFormField;
use FormHandler\Field\Element;

class ErrorAsTagRenderer extends XhtmlRenderer
{
    /**
     * The HTML tag which is used to render the error message
     * @var Tag
     */
    protected $tag;

    /**
     * Create a new Error As HTML Tag Renderer.
     * You can supply the HTML tag which is used to render the error message. If you don't supply it, we will use "tt".
     * ErrorAsTagRenderer constructor.
     * @param Tag $tag
     */
    public function __construct(Tag $tag = null)
    {
        if ($tag == null) {
            $tag = new Tag('tt');
        }
        $this->tag = $tag;
    }

    public function render(Element $element)
    {
        $html = parent::render($element);

        // if the element is a form field, also render the errors
        if ($element instanceof AbstractFormField) {
            // @todo: render help text
//            if ($element->getHelpText()) {
//                $html .= '<dfn>' . $element->getHelpText() . '</dfn>' . PHP_EOL;
//            }

            if (sizeof($element->getErrorMessages()) > 0) {
                $this->tag->setInnerHtml(implode('<br />' . PHP_EOL, $element->getErrorMessages()));
            }

            $html .= $this->tag->render();
        }

        return $html;
    }
}
