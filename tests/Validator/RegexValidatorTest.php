<?php

namespace FormHandler\Tests\Validator;

use Exception;
use FormHandler\Form;
use FormHandler\Tests\TestCase;
use FormHandler\Validator\RegexValidator;

class RegexValidatorTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testRequired()
    {
        $form = new Form('', false);

        $field = $form->textField('name');

        $validator = new RegexValidator('/^[a-z]{2,50}$/i', false);

        $this->assertEquals('/^[a-z]{2,50}$/i', $validator->getRegularExpression());

        $field->setValidator($validator);
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid as its empty and not required'
        );

        $errormsg = 'Invalid field!';
        $validator->setRequired(true);
        $validator->setErrorMessage($errormsg);
        $field->setValidator($validator);

        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid as its empty and required'
        );

        $this->assertTrue(in_array(
            $errormsg,
            $field->getErrorMessages()
        ));

        // test a invalid value
        $field->setValue('Piet06');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid as it contains an invalid value'
        );

        // test a valid value
        $field->setValue('Pieter');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid as it contains an valid value'
        );
        $this->assertFalse($validator->isNot());

        // test the NOT logic
        $validator->setNot(true);
        $this->assertTrue($validator->isNot());
        $field->setValidator($validator);

        // test a valid value, but now with NOT. thus this should be invalid
        $field->setValue('Pieter');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid as it contains an valid value, but we now use NOT'
        );

        // test a invalid value, but now with NOT. thus this should be valid
        $field->setValue('Piet06');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid as it contains an invalid value, but we now use NOT'
        );
    }

    /**
     * Test non-scalar values in a field for the regex validator.
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
            ->addValidator(new RegexValidator('/^[a-z]*$/i', true));

        $field->isValid();
    }
}
