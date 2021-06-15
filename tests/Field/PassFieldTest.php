<?php
namespace FormHandler\Tests\Field;

use FormHandler\Form;
use FormHandler\Tests\TestCase;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 23-08-16
 * Time: 16:23
 */
class PassFieldTest extends TestCase
{
    public function testTextField()
    {
        $form  = new Form();
        $field = $form->passField('password');

        $this->assertEquals('password', $field->getName());

        $field->setPlaceholder('Enter your password');
        $this->assertEquals('Enter your password', $field->getPlaceholder());

        $field->setTitle('Set your pwd');
        $this->assertEquals('Set your pwd', $field->getTitle());

        $field->setSize(2);
        $this->assertEquals(2, $field->getSize());

        $this->assertFalse($field->isDisabled());
        $this->assertFalse($field->isReadonly());

        $field->setDisabled(true);
        $field->setReadonly(true);

        $this->assertTrue($field->isDisabled());
        $this->assertTrue($field->isReadonly());

        $field->setValue('Piet');
        $this->assertEquals('Piet', $field->getValue());

        $field->setMaxlength(10);
        $this->assertEquals(10, $field->getMaxlength());
    }
}
