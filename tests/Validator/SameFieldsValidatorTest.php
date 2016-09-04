<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\SameFieldsValidator;
use PHPUnit\Framework\TestCase;

class SameFieldsValidatorTest extends TestCase
{
    public function testEqualsValidatorRequired()
    {
        $form = new Form(null, false);
        $field1 = $form->textField('email1');
        $field2 = $form->textField('email2');

        $validator = new SameFieldsValidator($field2, null, false);
        $field1 -> setValidator($validator);

        $this->assertTrue(
            $field1->isValid(),
            'Value should be valid because validator is not required and field is empty'
        );

        $errormsg = 'You should enter something';
        $validator->setRequired(true);
        $validator-> setErrorMessage($errormsg);

        $field1->setValidator($validator);
        $this->assertFalse(
            $field1->isValid(),
            'Value should be invalid because validator is required and field is empty'
        );
        $this-> assertContains($errormsg, $field1 -> getErrorMessages());
    }

    public function testFieldByName()
    {
        $form = new Form(null, false);
        $field1 = $form->textField('email1');
        $field2 = $form->textField('email2');

        $validator = new SameFieldsValidator('email2', null, false);

        $field1 -> setValidator($validator);

        $field1 -> setValue('abv');
        $field2 -> setValue('test');

        $this -> assertFalse(
            $field1 -> isValid(),
            'Field should be invalid as the values are not the same'
        );

        $field1 -> setValue('test');
        $this -> assertTrue(
            $field1 -> isValid(),
            'Field should be valid as the values are now the same'
        );
    }

    public function testInvalidField()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/has to be/');

        new SameFieldsValidator(1);
    }
}
