<?php
namespace FormHandler\Tests\Field;

use FormHandler\Form;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 29-08-16
 * Time: 15:01
 */
class ElementTest extends \PHPUnit_Framework_TestCase
{
    public function testElement()
    {
        $form = new Form();
        $field = $form->textField('name');

        $this->assertNull($field->getId());
        $field->setId("nameFld");
        $this->assertEquals('nameFld', $field->getId());

        $this->assertNull($field->getTitle());
        $field->setTitle("Enter your name");
        $this->assertEquals('Enter your name', $field->getTitle());

        $this->assertNull($field->getTabindex());
        $field->setTabindex(5);
        $this->assertEquals(5, $field->getTabindex());

        $this->assertNull($field->getAccesskey());
        $field->setAccesskey('n');
        $this->assertEquals('n', $field->getAccesskey());

        $this->assertNull($field->getClass());
        $field->addClass('bold');
        $this->assertEquals('bold', $field->getClass());
        $field->addClass('underline');
        $this->assertEquals('bold underline', $field->getClass());

        $this->assertNull($field->getStyle());
        $field->addStyle('color:red');
        $this->assertEquals('color:red', $field->getStyle());
        $field->addStyle(';');
        $this->assertEquals('color:red;', $field->getStyle());
    }
}
