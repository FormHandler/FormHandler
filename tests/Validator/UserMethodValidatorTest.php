<?php

namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Tests\TestCase;
use FormHandler\Field\AbstractFormField;

class UserMethodValidatorTest extends TestCase
{
    public function testUserMethod()
    {
        $form = new Form('', false);

        $field = $form->textField('name');
        $field->addValidator([$this, 'validateNameJohn']);

        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because its empty'
        );

        $this->assertTrue(in_array(
            "You have to supply a value",
            $field->getErrorMessages()
        ));

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

        $field->setValidator(function (AbstractFormField $field) {
            return $field->getValue() == "OK";
        });

        $field->setValue('NOK');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because its not OK'
        );

        $field->setValue('OK');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because its OK'
        );
    }

    /**
     * This is our validator function which we use to test
     *
     * @param AbstractFormField $field
     *
     * @return string|bool
     */
    public static function validateNameJohn(AbstractFormField $field)
    {
        if ($field->getValue() == 'John') {
            return true;
        } elseif ($field->getValue() == "") {
            return "You have to supply a value";
        }

        return "Nope, you are not allowed!";
    }
}
