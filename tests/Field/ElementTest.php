<?php

namespace FormHandler\Tests\Field;

use FormHandler\Form;
use FormHandler\Tests\TestCase;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 29-08-16
 * Time: 15:01
 */
class ElementTest extends TestCase
{
    public function testElement()
    {
        $form  = new Form();
        $field = $form->textField('name');

        $this->assertEquals('', $field->getId());
        $field->setId("nameFld");
        $this->assertEquals('nameFld', $field->getId());

        $this->assertEquals('', $field->getTitle());
        $field->setTitle("Enter your name");
        $this->assertEquals('Enter your name', $field->getTitle());

        $this->assertNull($field->getTabindex());
        $field->setTabindex(5);
        $this->assertEquals(5, $field->getTabindex());

        $this->assertEquals('', $field->getAccesskey());
        $field->setAccesskey('n');
        $this->assertEquals('n', $field->getAccesskey());

        $this->assertEquals('', $field->getClass());
        $field->addClass('bold');
        $this->assertEquals('bold', $field->getClass());
        $field->addClass('underline');
        $this->assertEquals('bold underline', $field->getClass());

        $this->assertEquals('', $field->getStyle());
        $field->addStyle('color:red');
        $this->assertEquals('color:red', $field->getStyle());
        $field->addStyle(';');
        $this->assertEquals('color:red;', $field->getStyle());
    }
}
