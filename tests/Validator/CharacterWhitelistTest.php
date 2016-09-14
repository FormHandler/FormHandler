<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\CharacterWhitelistValidator;

class CharacterWhitelistTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the Whitelist Validator
     */
    public function testWhitelistValidator()
    {
        // create a form and the field
        $form = new Form('', false);
        $field = $form->textField('hex');

        // create a required validator and add it.
        $validator = new CharacterWhitelistValidator('0123456789abcdef', true);

        $this->assertEquals([
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'

        ], $validator->getWhitelist());

        $field->addValidator($validator);
        $this->assertCount(1, $field->getValidators());

        $this->assertFalse($field->isValid(), 'Field should be invalid as it is empty and validator says its required');

        // set a new validator which says its not required.

        $validator->setRequired(false);
        $field->setValidator($validator);
        $this->assertTrue($field->isValid(), 'Empty field should be valid when its not required.');

        // field should be invalid because it contains other characters
        $field->setValue('g');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because it contains non-whitelisted characters'
        );

        // now it should be valid
        $field->setValue('fedcba9876543210ffee91');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because it contains only whitelisted characters'
        );

        // set whitelist as array, should be invalid
        $whitelist = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $validator->setWhitelist($whitelist);
        $field->setValidator($validator);

        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because it contains non-whitelisted characters'
        );

        // array-object as whitelist, should be valid
        $whitelist = new \ArrayObject($whitelist);
        $whitelist->append('a');
        $whitelist->append('b');
        $whitelist->append('c');
        $whitelist->append('d');
        $whitelist->append('e');
        $whitelist->append('f');

        $validator->setWhitelist($whitelist);
        $field->setValidator($validator);
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because it contains only whitelisted characters'
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Incorrect whitelist given/
     */
    public function testIncorrectType()
    {
        $validator = new CharacterWhitelistValidator('0123456789abcdef', true);
        // incorrect type as whitelist, expect an exception
        $validator->setWhitelist(new \stdClass());
    }

    /**
     * Test non-scalar values in a field for the whitelist validator.
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /scalar types/
     */
    public function testWhitelistValidatorNonScalar()
    {
        // create a form and the field
        $form = new Form('', false);

        // create a required validator and add it.
        $validator = new CharacterWhitelistValidator('0123456789abcdef', true);

        // test a non-scalar value in a field, expect an exception
        $field = $form->selectField('options[]')
            ->addOptionsAsArray([1, 2, 4, 5, 6, 7, 8, 9])
            ->setMultiple(true)
            ->setValue([1, 5, 6, 9]);

        $field->addValidator($validator);
        $field->isValid();
    }
}
