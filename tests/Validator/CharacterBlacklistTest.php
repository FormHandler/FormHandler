<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\CharacterBlacklistValidator;

class CharacterBlacklistTest extends \PHPUnit_Framework_TestCase
{
    public function testBlacklistValidator()
    {
        // create a form and the field
        $form = new Form('', false);
        $field = $form->textField('message');

        // create a required validator and add it.
        $validator = new CharacterBlacklistValidator('<>()-:PX', true, 'Smilies are not allowed!');
        $field->addValidator($validator);
        $this->assertCount(1, $field->getValidators());

        $this->assertFalse($field->isValid(), 'Field should be invalid as it is empty and validator says its required');

        // set a new validator which says its not required.
        $validator->setRequired(false);
        $field->setValidator($validator);
        $this->assertTrue($field->isValid(), 'Empty field should be valid when its not required.');

        // field should be invalid because it contains other characters
        $field->setValue(':-)');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because it contains blacklisted characters'
        );

        // now it should be valid
        $field->setValue('hi, I am happy but I cannot express it with an emoji');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because it contains only non-blacklisted characters'
        );

        // set blacklist as array, should be valid
        $whitelist = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $validator->setBlacklist($whitelist);
        $validator->setErrorMessage('Numbers not allowed');
        $field->setValidator($validator);

        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because it contains no blacklisted characters'
        );

        // array-object as whitelist, should be valid
        $whitelistObj = new \ArrayObject($whitelist);
        $whitelistObj->append('a');
        $whitelistObj->append('e');
        $whitelistObj->append('o');
        $whitelistObj->append('u');
        $whitelistObj->append('i');

        $errormsg = 'Numbers or vowels are not allowed. Good luck';
        $validator->setBlacklist($whitelistObj);
        $validator->setErrorMessage($errormsg);
        $field->setValidator($validator);


        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because it contains blacklisted characters'
        );
        $this->assertContains($errormsg, $field->getErrorMessages());

        // incorrect type as whitelist, expect an exception
        $this->expectException('\Exception');
        $this->expectExceptionMessageRegExp('/Incorrect blacklist given/');
        $validator->setBlacklist(new \stdClass());
    }

    /**
     * Test non-scalar values in a field for the blacklist validator.
     */
    public function testBlacklistValidatorNonScalar()
    {
        // create a form and the field
        $form = new Form('', false);

        // create a required validator and add it.
        $validator = new CharacterBlacklistValidator('0123456789abcdef', true);

        // test a non-scalar value in a field, expect an exception
        $field = $form->selectField('options[]')
            ->addOptionsAsArray([1, 2, 4, 5, 6, 7, 8, 9])
            ->setMultiple(true)
            ->setValue([1, 5, 6, 9]);

        $field->addValidator($validator);

        $this->expectException('\Exception');
        $this->expectExceptionMessageRegExp('/scalar types/');
        $field->isValid();
    }
}
