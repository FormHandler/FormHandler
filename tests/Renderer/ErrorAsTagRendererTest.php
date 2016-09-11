<?php

namespace FormHandler\Tests\Renderer;

use FormHandler\Form;
use FormHandler\Renderer\ErrorAsTagRenderer;
use FormHandler\Renderer\Tag;

class ErrorAsTagRendererTest extends BaseTestRenderer
{
    public function testErrorAsCustomTag()
    {
        $form = new Form(null, false);
        $form->setRenderer(new ErrorAsTagRenderer(new Tag('label')));

        $field = $form->textField('name');
        $field->addErrorMessage('Error, invalid name given!');

        $html = $field->render();

        $this->assertContains('Error, invalid name given!', $html);
        $this->assertContains('<label', $html);
    }

    public function testErrorAsDefaultTag()
    {
        $form = new Form(null, false);
        $form->setRenderer(new ErrorAsTagRenderer());

        $field = $form->textField('name');
        $field->addErrorMessage('Error, invalid name given!');

        $html = $field->render();

        $this->assertContains('Error, invalid name given!', $html);
        $this->assertContains('<tt', $html);
    }
}
