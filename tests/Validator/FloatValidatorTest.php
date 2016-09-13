<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\FloatValidator;

class FloatValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testFloatValidatorRequired()
    {
        $form = new Form();
        $field = $form->textField('amount');

        $validator = new FloatValidator(0.0, 50.0, true);
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

    /**
     * Test non-scalar values in a field for the float validator.
     */
    public function testFloatValidatorNonScalar()
    {
        // create a form and the field
        $form = new Form('', false);

        // test a non-scalar value in a field, expect an exception
        $field = $form->selectField('options[]')
            ->addOptionsAsArray([1, 2, 4, 5, 6, 7, 8, 9])
            ->setMultiple(true)
            ->setValue([1, 5, 6, 9])
            ->addValidator(new FloatValidator(0.0, 1.0, true));

        $this->expectException('\Exception');
        $this->expectExceptionMessageRegExp('/scalar types/');
        $field->isValid();
    }

    public function testFloadValidatorValue()
    {
        $form = new Form();
        $field = $form->textField('amount');

        $validator = new FloatValidator(0.0, 50.0, true);
        $this->assertEquals(0.0, $validator->getMin());
        $this->assertEquals(50.0, $validator->getMax());

        $field->setValue(10.31);
        $field->addValidator($validator);

        $this->assertTrue(
            $field->isValid(),
            'Value should be valid because its between the given range'
        );

        $field->setValue(52.1);

        $this->assertFalse(
            $field->isValid(),
            'Value should be invalid because its not between the given range'
        );

        $field->setValue(-2.8);

        $this->assertFalse(
            $field->isValid(),
            'Value should be invalid because its not between the given range'
        );

        $field->setValue('-2.4.2.2.8');

        $this->assertFalse(
            $field->isValid(),
            'Value should be invalid because its contains an invalid float value'
        );
    }

    public function testFloatDecimalSigns()
    {
        $form = new Form();
        $field = $form->textField('amount');

        $errormsg = 'Enter correct amount';
        foreach ([FloatValidator::DECIMAL_COMMA, ','] as $type) {
            $validator = new FloatValidator(0, 5, true, $errormsg, FloatValidator::DECIMAL_COMMA);

            $field->addValidator($validator);

            $field->setValue('1.0');
            $this->assertFalse(
                $field->isValid(),
                'Value should be invalid because it uses the wrong decimal sign'
            );
            $this->assertContains($errormsg, $field->getErrorMessages());

            $field->setValue('1,0');
            $this->assertTrue(
                $field->isValid(),
                'Value should be valid because it uses the correct decimal sign'
            );
        }

        foreach ([FloatValidator::DECIMAL_POINT, '.'] as $type) {
            $validator = new FloatValidator(0, 5, true, $errormsg, $type);

            $field->setValidator($validator);

            $field->setValue('1,0');
            $this->assertFalse(
                $field->isValid(),
                'Value should be invalid because it uses the wrong decimal sign'
            );

            $field->setValue('1.0');
            $this->assertTrue(
                $field->isValid(),
                'Value should be valid because it uses the correct decimal sign'
            );
        }

        foreach ([FloatValidator::DECIMAL_POINT_OR_COMMA, '.,', ',.'] as $type) {
            $validator = new FloatValidator(0, 5, true, $errormsg, $type);

            $field->setValidator($validator);

            $field->setValue('1,0');
            $this->assertTrue(
                $field->isValid(),
                'Value should be valid because it uses the correct decimal sign'
            );

            $field->setValue('1.0');
            $this->assertTrue(
                $field->isValid(),
                'Value should be valid because it uses the correct decimal sign'
            );
        }
    }
}
