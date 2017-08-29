<?php

namespace FormHandler\Tests\Renderer;

use FormHandler\Field\Element;

class FakeElement extends Element
{

    /**
     * Render a field and return the HTML
     * @return string
     */
    public function render()
    {
        return '';
    }
}
