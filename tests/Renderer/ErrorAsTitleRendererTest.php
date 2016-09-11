<?php

namespace FormHandler\Tests\Renderer;

use FormHandler\Form;
use FormHandler\Renderer\ErrorAsTitleRenderer;

class ErrorAsTitleRendererTest extends BaseTestRenderer
{
    public function testErrorAsTitle()
    {
        $form = new Form(null, false);
        $form->setRenderer(new ErrorAsTitleRenderer());

        $field = $form->textField('name');
        $field->addErrorMessage('Error, invalid name given!');

        $html = $field->render();

        $this->expectAttribute($html, 'title', 'Error, invalid name given!');
    }
}
