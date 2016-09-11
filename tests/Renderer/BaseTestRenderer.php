<?php

namespace FormHandler\Tests\Renderer;

use PHPUnit\Framework\TestCase;

class BaseTestRenderer extends TestCase
{
    protected function expectAttribute($html, $name, $value)
    {
        $this->assertContains($name . '="' . $value . '"', $html, 'Tag should contain attribute ' . $name);
    }
}
