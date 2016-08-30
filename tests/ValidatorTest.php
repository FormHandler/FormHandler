<?php
namespace FormHandler\Tests;

use FormHandler\Form;
use FormHandler\Validator\CharacterWhitelistValidator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * Test the Whitelist Validator
     */
    function testWhitelistValidator()
    {
        // create a form and the field
        $form = new Form('', false);
        $field = $form->textField('hex');

        // create a required validator and add it.
        $validator = new CharacterWhitelistValidator('0123456789abcdef'. true);
        $field->addValidator($validator);
        $this->assertCount(1, $field->getValidators());

        $this->assertFalse($field->isValid(), 'Field should be invalid as it is empty and validator says its required');

        // set a new validator which says its not required.
        $field->clearValidators();
        $validator->setRequired(false);
        $field->addValidator($validator);
        $this->assertTrue($field->isValid(), 'Empty field should be valid when its not required.');

        // field should be invalid because it contains other characters
        $field->clearCache();
        $field->setValue('g');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because it contains non-whitelisted characters'
        );

        $field -> setValue('fedcba9876543210ffee91');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because it contains only whitelisted characters'
        );


    }
}
