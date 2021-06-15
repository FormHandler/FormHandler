<?php

namespace FormHandler\Tests\Renderer;

use FormHandler\Tests\TestCase;

class BaseTestRenderer extends TestCase
{
    /**
     * @param string $html
     * @param string $name
     * @param mixed  $value
     */
    protected function expectAttribute(string $html, string $name, $value)
    {
        $this->assertStringContainsString(
            $name . '="' . $value . '"',
            $html,
            'Tag should contain attribute ' . $name
        );
    }
}
