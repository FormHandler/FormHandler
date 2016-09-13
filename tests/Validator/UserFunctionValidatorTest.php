<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Field\AbstractFormField;
use FormHandler\Form;

/**
 * This is our validator function which we use to test
 * @param AbstractFormField $field
 * @return string|bool
 */
function validateNameJohn(AbstractFormField $field)
{
    if ($field->getValue() == 'John') {
        return true;
    } elseif ($field->getValue() == "") {
        return "You have to supply a value";
    }

    return "Nope, you are not allowed!";
}

class UserFunctionValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testUserFunctionValidator()
    {
        $form = new Form('', false);

        $field = $form->textField('name');
        $field->addValidator('FormHandler\Tests\Validator\validateNameJohn');

        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because its empty'
        );

        $this->assertContains(
            "You have to supply a value",
            $field->getErrorMessages()
        );

        $field->setValue('Jane');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because its not John'
        );

        $field->setValue('John');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because its John'
        );
    }

    public function testNonExistingFunction()
    {
        $this->expectException('\Exception');
        $this->expectExceptionMessageRegExp('/does not exists/');

        $form = new Form('', false);

        $field = $form->textField('name');
        $field->addValidator('ThisFunctionDoesNotExist');
    }
}
