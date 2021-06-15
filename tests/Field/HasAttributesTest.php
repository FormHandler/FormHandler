<?php

namespace FormHandler\Tests\Field;

use FormHandler\Field\Option;
use FormHandler\Tests\TestCase;

class HasAttributesTest extends TestCase
{
    public function testAttributes()
    {
        $option = new Option();
        $option->addAttribute('data-label', 'test');
        $this->assertEquals('test', $option->getAttribute('data-label'));

        $option->addAttribute('data-label', 'name');
        $this->assertEquals('testname', $option->getAttribute('data-label'));

        $option->setAttribute('data-label', 'test2');
        $this->assertEquals('test2', $option->getAttribute('data-label'));
    }
}
