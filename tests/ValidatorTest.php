<?php
namespace FormHandler\Tests;

use FormHandler\Form;
use FormHandler\Validator\DutchBankNumberValidator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    function testDutchBankNumberValidator()
    {


        // create a form and the field
        $form = new Form('', false);
        $field = $form->textField('banknumber');

        // create a required validator and add it.
        $validator = new DutchBankNumberValidator(true);
        $field->addValidator($validator);
        $this->assertCount(1, $field->getValidators());

        $this->assertFalse($field->isValid(), 'Field should be invalid as it is empty and validator says its required');

        // set a new validator which says its not required.
        $field->clearValidators();
        $validator->setRequired(false);
        $field->addValidator($validator);
        $this->assertTrue($field->isValid(), 'Empty field should be valid when its not required.');

        // field should be invalid because it contains non numeric characters
        $field->clearCache();
        $field->setValue('abc');
        $this->assertFalse($field->isValid());
    }
}
