<?php

namespace FormHandler\Tests\Renderer;

class BaseTestRenderer extends \PHPUnit_Framework_TestCase
{
    protected function expectAttribute($html, $name, $value)
    {
        $this->assertContains($name . '="' . $value . '"', $html, 'Tag should contain attribute ' . $name);
    }
}
