<?php
namespace FormHandler\Tests\Field;

use FormHandler\Form;
use FormHandler\Validator\RegexValidator;
use FormHandler\Validator\StringValidator;

class AbstractFormFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testErrorMessage()
    {
        $form = new Form();
        $field = $form->textField('name');
        $field->addErrorMessage('Test error message', true);

        $this->assertContains('Test error message', $field->getErrorMessages());
        $this->assertFalse($field->isValid());
    }

    public function testHelpText()
    {
        $form = new Form();
        $field = $form->textField('name');
        $field->setHelpText('Enter your name');

        $this->assertEquals('Enter your name', $field->getHelpText());
    }

    public function testSetName()
    {
        $form = new Form();
        $field = $form->textField('name');
        $this->assertEquals('name', $field->getName());

        $field->setName('email');
        $this->assertEquals('email', $field->getName());
    }

    public function testClearValidators()
    {
        $form = new Form();
        $field = $form->textField('name');
        $this->assertEquals([], $field->getValidators());

        $field->addValidator(new StringValidator());
        $field->addValidator(new RegexValidator('/a/'));

        $this->assertCount(2, $field->getValidators());
        $field->clearValidators();
        $this->assertEquals([], $field->getValidators());
    }
}
