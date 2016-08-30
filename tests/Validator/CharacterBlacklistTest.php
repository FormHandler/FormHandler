<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\CharacterBlacklistValidator;
use PHPUnit\Framework\TestCase;

class CharacterBlacklistTest extends TestCase
{
    /**
     * Test the blacklist validator
     */
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
        $field->clearCache();
        $field->setValue(':-)');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because it contains blacklisted characters'
        );

        // now it should be valid
        $field->clearCache();
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
        $field->clearCache();

        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because it contains no blacklisted characters'
        );

        // array-object as whitelist, should be valid
        $whitelist = new \ArrayObject($whitelist);
        $whitelist->append('a');
        $whitelist->append('e');
        $whitelist->append('o');
        $whitelist->append('u');
        $whitelist->append('i');

        $errormsg = 'Numbers or vowels are not allowed. Good luck';
        $validator->setBlacklist($whitelist);
        $validator->setErrorMessage($errormsg);
        $field->setValidator($validator);


        $field->clearCache();
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because it contains blacklisted characters'
        );
        $this->assertContains($errormsg, $field->getErrorMessages());

        // incorrect type as whitelist, expect an exception
        $this->expectException(\Exception::class);
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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/scalar types/');
        $field->isValid();
    }
}
