<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\FloatValidator;
use PHPUnit\Framework\TestCase;

class FloatValidatorTest extends TestCase
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
        $field->setValidator($validator)->clearCache();

        $this->assertTrue(
            $field->isValid(),
            'Value should be valid because validator does not requires field.'
        );
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

        $field->setValue(52.1)->clearCache();

        $this->assertFalse(
            $field->isValid(),
            'Value should be invalid because its not between the given range'
        );

        $field->setValue(-2.8)->clearCache();

        $this->assertFalse(
            $field->isValid(),
            'Value should be invalid because its not between the given range'
        );

        $field->setValue('-2.4.2.2.8')->clearCache();

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
