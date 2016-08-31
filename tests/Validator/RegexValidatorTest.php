<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\RegexValidator;
use PHPUnit\Framework\TestCase;

class RegexValidatorTest extends TestCase
{
    public function testRequired()
    {
        $form = new Form('', false);

        $field = $form -> textField('name');

        $validator = new RegexValidator('/^[a-z]{2,50}$/i', false);

        $field -> setValidator($validator);
        $this -> assertTrue(
            $field -> isValid(),
            'Field should be valid as its empty and not required'
        );

        $errormsg = 'Invalid field!';
        $validator -> setRequired(true);
        $validator -> setErrorMessage($errormsg);
        $field -> setValidator($validator);
        $this -> assertFalse(
            $field -> isValid(),
            'Field should be invalid as its empty and required'
        );

        $this -> assertContains(
            $errormsg,
            $field -> getErrorMessages()
        );

        // test a invalid value
        $field -> setValue('Piet06');
        $this -> assertFalse(
            $field -> isValid(),
            'Field should be invalid as it contains an invalid value'
        );

        // test a valid value
        $field -> setValue('Pieter');
        $this -> assertTrue(
            $field -> isValid(),
            'Field should be valid as it contains an valid value'
        );

        // test the NOT logic
        $validator -> setNot(true);
        $field -> setValidator($validator);

        // test a valid value, but now with NOT. thus this should be invalid
        $field -> setValue('Pieter');
        $this -> assertFalse(
            $field -> isValid(),
            'Field should be invalid as it contains an valid value, but we now use NOT'
        );

        // test a invalid value, but now with NOT. thus this should be valid
        $field -> setValue('Piet06');
        $this -> assertTrue(
            $field -> isValid(),
            'Field should be valid as it contains an invalid value, but we now use NOT'
        );
    }

    /**
     * Test non-scalar values in a field for the regex validator.
     */
    public function testRegexValidatorNonScalar()
    {
        // create a form and the field
        $form = new Form('', false);

        // test a non-scalar value in a field, expect an exception
        $field = $form->selectField('options[]')
            ->addOptionsAsArray([1, 2, 4, 5, 6, 7, 8, 9])
            ->setMultiple(true)
            ->setValue([1, 5, 6, 9])
            -> addValidator(new RegexValidator('/^[a-z]*$/i', true));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/scalar types/');
        $field->isValid();
    }
}
