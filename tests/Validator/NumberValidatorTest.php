<?php
namespace FormHandler\Tests\Validator;

use Exception;
use FormHandler\Form;
use FormHandler\Tests\TestCase;
use FormHandler\Validator\NumberValidator;

class NumberValidatorTest extends TestCase
{
    public function testNumberValidatorRequired()
    {
        $form  = new Form();
        $field = $form->textField('age');

        $validator = new NumberValidator(1, 99, true);
        $field->addValidator($validator);

        $this->assertFalse(
            $field->isValid(),
            'Value should be invalid because validator requires field to be not empty'
        );

        $validator->setRequired(false);
        $field->setValidator($validator);

        $this->assertTrue(
            $field->isValid(),
            'Value should be valid because validator does not requires field.'
        );
    }

    public function testFloadValidatorValue()
    {
        $form  = new Form();
        $field = $form->textField('age');

        $validator = new NumberValidator(1, 99, true);
        $this->assertEquals(1, $validator->getMin());
        $this->assertEquals(99, $validator->getMax());

        $field->setValue(31);
        $field->addValidator($validator);

        $this->assertTrue(
            $field->isValid(),
            'Value should be valid because its between the given range'
        );

        $field->setValue(131);

        $this->assertFalse(
            $field->isValid(),
            'Value should be invalid because its not between the given range'
        );

        $field->setValue(-2);

        $this->assertFalse(
            $field->isValid(),
            'Value should be invalid because its not between the given range'
        );

        $field->setValue('test');

        $this->assertFalse(
            $field->isValid(),
            'Value should be invalid because its contains an invalid number value'
        );
    }

    /**
     * Test non-scalar values in a field for the whitelist validator.
     */
    public function testNumberValidatorNonScalar()
    {
        $this->expectException(Exception::class);
        $this->expectErrorMessageMatches('/scalar types/');

        // create a form and the field
        $form = new Form('', false);

        // test a non-scalar value in a field, expect an exception
        $field = $form->selectField('options[]')
            ->addOptionsAsArray([1, 2, 4, 5, 6, 7, 8, 9])
            ->setMultiple(true)
            ->setValue([1, 5, 6, 9])
            ->addValidator(new NumberValidator(1, 99, true));
        $field->isValid();
    }
}
