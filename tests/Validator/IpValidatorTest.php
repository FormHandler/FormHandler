<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\IpValidator;

class IpValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testIpv4()
    {
        $form = new Form('', false);
        $field = $form->textField('ip');

        $errormsg = 'Invalid ipv4';
        $validator = new IpValidator(true);
        $field->setValidator($validator);

        $this->assertFalse(
            $field->isValid(),
            'Should be false, field is empty and validator is set as required'
        );

        $field->setValue('19.16.172.241');
        $this->assertTrue(
            $field->isValid(),
            'This field should now be valid'
        );

        $field->setValue('');
        $validator->setRequired(false);
        $field->setValidator($validator);

        $this->assertTrue(
            $field->isValid(),
            'This field should be valid, because its empty and not required'
        );

        $validator->setErrorMessage($errormsg);

        $field->setValidator($validator);

        // should be invalid, not valid ipv4 address
        $field->setValue('127.0.0.256');
        $this->assertFalse(
            $field->isValid(),
            'Should be false, invalid ip address'
        );

        $this->assertContains(
            $errormsg,
            $field->getErrorMessages(),
            'Error message should now be set'
        );
    }
}
