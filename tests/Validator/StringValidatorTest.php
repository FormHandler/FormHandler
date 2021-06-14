<?php

namespace FormHandler\Tests\Validator;

use Exception;
use FormHandler\Form;
use FormHandler\Tests\TestCase;
use FormHandler\Validator\StringValidator;

class StringValidatorTest extends TestCase
{
    /**
     */
    public function testInvalidValidator()
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessageMatches('/AbstractValidator/');
        $form = new Form('', false);

        $field = $form->textField('name');
        $field->addValidator(new \stdClass);
    }

    public function testRequired()
    {
        $form = new Form('', false);

        $field = $form->textField('name');

        $validator = new StringValidator(2, 10, true);
        $field->addValidator($validator);

        $this->assertTrue($validator->isRequired());
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid as its empty but required'
        );

        $validator->setRequired(false);
        $this->assertFalse($validator->isRequired());
        $field->setValidator($validator);

        $this->assertTrue(
            $field->isValid(),
            'Field should be valid as its empty and not required'
        );

        $field->setValue('a');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid as too short'
        );

        $field->setValue('123456789011111');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid as its too long'
        );
    }

    /**
     * Test non-scalar values in a field for the string validator.
     */
    public function testRegexValidatorNonScalar()
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
            ->addValidator(new StringValidator(2, 50, true));

        $field->isValid();
    }
}
