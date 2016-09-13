<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\UrlValidator;

class UrlValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testUrlValidator()
    {
        $form = new Form('', false);
        $field = $form->textField('url');

        $validator = new UrlValidator(true, null, 20);

        $field->addValidator($validator);

        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid as its empty but required'
        );

        $field->setValue('http://www.thisisaverylongandnonexistingdomainwhichistoolong.com');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid as its too long'
        );

        $validator->setMaxLength(null);
        $field->setValidator($validator);

        $field->setValue('http://www.google.com');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid'
        );

        $field->setValue('ftp://www.google.com');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid its scheme is not whitelisted'
        );

        $validator->setAllowedSchemes(['ftp']);
        $field->setValidator($validator);
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid its scheme is now whitelisted'
        );

        $field->setValue('');
        $validator->setRequired(false);
        $field->setValidator($validator);
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid its empty and not required'
        );


        $field->setValue('http://192.168.1.1');
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid this is not a valid host url (TLD check)'
        );

        $validator->setSkipTldCheck(true);
        $validator->setAllowedSchemes(['http']);
        $field->setValidator($validator);
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid, we skipped the TLD check'
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /not an array/
     */
    public function testIncorrectSchemesType()
    {
        $validator = new UrlValidator();
        $validator->setAllowedSchemes('ftp');
    }
}
