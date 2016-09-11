<?php
namespace FormHandler\Tests\Field;

use FormHandler\Form;
use PHPUnit\Framework\TestCase;

/**
 * Test Submit Button.
 * User: teye
 * Date: 23-08-16
 * Time: 16:23
 */
class SubmitButtonTest extends TestCase
{
    public function testSubmitButton()
    {
        $form = new Form();
        $btn = $form->submitButton('submit', 'Submit Form');

        $this->assertEquals('submit', $btn->getName());
        $this->assertEquals('Submit Form', $btn->getValue());

        $btn->setValue('Submit');
        $this->assertEquals('Submit', $btn->getValue());


        $this->assertEquals($form, $btn->getForm());
        $this->assertFalse($btn->isDisabled());
        $btn->setDisabled(true);
        $this->assertTrue($btn->isDisabled());

        $btn->setSize(20);
        $this->assertEquals(20, $btn->getSize());
    }
}
