<?php

namespace FormHandler\Tests\Renderer;

use FormHandler\Form;
use FormHandler\Renderer\CowSayRenderer;

class CowSayRendererTest extends BaseTestRenderer
{
    public function testCowSay()
    {
        $form = new Form(null, false);
        $form->setRenderer(new CowSayRenderer());

        $field = $form->textField('name');

        $html = $field->render();

        $this->assertStringContainsString('<pre>', $html);
        $this->assertStringContainsString('</pre>', $html);
    }

    public function testNonCowSay()
    {
        $form = new Form(null, false);
        $form->setRenderer(new CowSayRenderer());

        $btn = $form->submitButton('btn');

        $html = $btn->render();

        $this->assertStringNotContainsString('<pre>', $html);
        $this->assertStringNotContainsString('</pre>', $html);
    }
}
