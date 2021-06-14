<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Tests\TestCase;
use FormHandler\Validator\EqualsValidator;

class EqualsValidatorTest extends TestCase
{
    public function testEqualsValidatorRequired()
    {
        $form  = new Form(null, false);
        $field = $form->textField('agree');

        $validator = new EqualsValidator("OK", false);

        $this->assertEquals('OK', $validator->getCompareToValue());

        $field->setValidator($validator);

        $this->assertTrue(
            $field->isValid(),
            'Value should be valid because validator is not required and field is empty'
        );

        $validator->setRequired(true);

        $field->setValidator($validator);
        $this->assertFalse(
            $field->isValid(),
            'Value should be invalid because validator is required and field is empty'
        );
    }

    public function testEqualsNot()
    {
        $form = new Form(null, false);
        $field = $form->textField('agree');

        $validator = new EqualsValidator("OK", false);

        $this->assertFalse($validator->isNot());
        $validator->setNot(true);
        $this->assertTrue($validator->isNot());

        $field->setValidator($validator);
        $field->setValue('OK');

        $this->assertFalse(
            $field->isValid(),
            'Value should be invalid because it should NOT equals the value of the validator'
        );

        $field->setValue('NOK');
        $this->assertTrue(
            $field->isValid(),
            'Value should be valid because it does NOT equals the value of the validator'
        );
    }

    public function testEqualsRadioButtonAndCheckBox()
    {
        $form = new Form(null, false);

        $validator = new EqualsValidator('OK', false);

        $radiobutton = $form->radioButton('radiobutton', 'OK')->addValidator($validator);
        $checkbox = $form->checkBox('checkbox', 'OK')->addValidator($validator);

        $this->assertFalse(
            $checkbox->isValid(),
            'Checkbox should be invalid because its not checked, thus value does not equals'
        );

        $this->assertFalse(
            $radiobutton->isValid(),
            'Radiobutton should be invalid because its not checked, thus value does not equals'
        );

        $checkbox->setChecked(true);
        $radiobutton->setChecked(true);

        $this->assertTrue(
            $checkbox->isValid(),
            'Checkbox should be valid because its checked and thus value does not equals'
        );

        $this->assertTrue(
            $radiobutton->isValid(),
            'Radiobutton should be valid because its checked and thus value does not equals'
        );
    }
}
