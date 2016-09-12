<?php

namespace FormHandler\Tests\Renderer;

use FormHandler\Form;
use FormHandler\Renderer\CowSayRenderer;
use FormHandler\Renderer\ErrorAsTagRenderer;
use FormHandler\Renderer\Tag;

class CowSayRendererTest extends BaseTestRenderer
{
    public function testCowSay()
    {
        $form = new Form(null, false);
        $form->setRenderer(new CowSayRenderer() );

        $field = $form->textField('name');

        $html = $field->render();

        $this->assertContains('<pre>', $html);
        $this->assertContains('</pre>', $html);
    }

    public function testNonCowSay()
    {
        $form = new Form(null, false);
        $form->setRenderer(new CowSayRenderer() );

        $btn = $form->submitButton('btn');

        $html = $btn->render();

        $this->assertNotContains('<pre>', $html);
        $this->assertNotContains('</pre>', $html);
    }
}
