<?php

namespace FormHandler\Tests\Renderer;

use FormHandler\Renderer\Tag;
use FormHandler\Tests\TestCase;

class TagTest extends TestCase
{
    public function testTag()
    {
        $tag = new Tag('label');
        $tag->setAttribute('for', 'test');
        $tag->setInnerHtml('Name');

        $this->assertEquals('Name', $tag->getInnerHtml());
        $this->assertEquals('<label for="test">Name</label>', $tag->render());
    }
}
