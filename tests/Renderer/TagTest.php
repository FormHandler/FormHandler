<?php

namespace FormHandler\Tests\Renderer;

use FormHandler\Renderer\Tag;

class TagTest extends \PHPUnit_Framework_TestCase
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
