<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\EmailValidator;

class EmailValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testRequiredEmailValidator()
    {
        $form = new Form('', false);

        $field = $form->textField('email');

        $validator = new EmailValidator(true);

        $field->setValidator($validator);
        $this->assertFalse(
            $field->isValid(),
            'Empty field with required validator should be invalid'
        );

        $validator = new EmailValidator(false);

        $field->setValidator($validator);
        $this->assertTrue(
            $field->isValid(),
            'Empty field with non-required validator should be valid'
        );
    }

    public function testValidEmailAddress()
    {
        $form = new Form('', false);

        $field = $form->textField('email');

        $field->addValidator(new EmailValidator(true));


        $valid = [
            'email@example.com',
            'firstname.lastname@example.com',
            'email@subdomain.example.com',
            'firstname+lastname@example.com',
            '1234567890@example.com',
            'email@example-one.com',
            '_______@example.com',
            'email@[123.123.123.123]',
            'email@example.name',
            'email@example.museum',
            'email@example.co.jp',
            'email@example.web',
            'firstname-lastname@example.com'
        ];

        foreach ($valid as $value) {
            $field->setValue($value);
            $this->assertTrue(
                $field->isValid(),
                'Field should be valid when email address is given: ' . $value
            );
        }

        $invalid = [
            'plainaddress',
            '#@%^%#$@#$@#.com',
            '@example.com',
            'Joe Smith <email@example.com>',
            'email.example.com',
            'email@123.123.123.123',
            '“email”@example.com',
            'email@example@example.com',
            '.email@example.com',
            'email.@example.com',
            'email..email@example.com',
            'あいうえお@example.com',
            'email@example.com (Joe Smith)',
            'email@example',
            'email@-example.com',
            'email@111.222.333.44444',
            'email@example..com',
            'Abc..123@example.com'
        ];

        foreach ($invalid as $value) {
            $field->setValue($value);
            $this->assertFalse(
                $field->isValid(),
                'Field should be invalid when email address is given: ' . $value
            );
        }
    }

    public function testWithoutDomainCheck()
    {
        $form = new Form('', false);

        // first skip domain check
        $validator = new EmailValidator(true);

        $field = $form->textField('email');
        $field->setValidator($validator);

        $field->setValue('john@iamabsolutlysurethatthisdomainwillneverexistsbecauseits.bogus');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because email address has correct format (no host checking)'
        );
    }

    public function testDomainCheckViaGetHostByName()
    {
        $GLOBALS['mock_function_not_exists'] = 'getmxrr';

        $form = new Form('', false);

        // first skip domain check
        $validator = new EmailValidator(true);
        $validator->setCheckIfDomainExist(true);

        $field = $form->textField('email');
        $field->setValidator($validator);

        $field->setValue('john@iamabsolutlysurethatthisdomainwillneverexistsbecauseits.bogus');

        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because host of email address does not exists (getmxrr disabled)'
        );

        $field->setValue('test@gmail.com');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because host of email address does exists (getmxrr disabled)'
        );

        unset($GLOBALS['mock_function_not_exists']);
    }

    public function testDomainCheckWithGetMxrr()
    {
        $GLOBALS['mock_function_exists'] = 'getmxrr';

        $form = new Form('', false);

        // first skip domain check
        $validator = new EmailValidator(true);
        $validator->setCheckIfDomainExist(true);

        $field = $form->textField('email');
        $field->setValidator($validator);

        $GLOBALS['mock_mxrr_response'] = false;
        $GLOBALS['mock_dnsrr_response'] = false;

        $field->setValue('john@iamabsolutlysurethatthisdomainwillneverexistsbecauseits.bogus');

        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because host of email address does not exists (getmxrr enabled)'
        );

        $GLOBALS['mock_mxrr_response'] = true;

        $field->setValue('test@gmail.com');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because host of email address does exists (getmxrr enabled)'
        );

        unset($GLOBALS['mock_mxrr_response']);
        unset($GLOBALS['mock_dnsrr_response']);
        unset($GLOBALS['mock_function_exists']);
    }

    public function testDomainCheckWithCheckDnsrr()
    {
        $form = new Form('', false);

        // first skip domain check
        $validator = new EmailValidator(true);
        $validator->setCheckIfDomainExist(true);

        $field = $form->textField('email');
        $field->setValidator($validator);

        $GLOBALS['mock_mxrr_response'] = false;
        $GLOBALS['mock_dnsrr_response'] = true;

        $field->setValue('john@iamabsolutlysurethatthisdomainwillneverexistsbecauseits.bogus');
        $this->assertTrue(
            $field->isValid(),
            'Field should be valid because we getmxrr respond with true'
        );

        $GLOBALS['mock_dnsrr_response'] = false;

        $field->clearCache();
        $this->assertFalse(
            $field->isValid(),
            'Field should be invalid because we getmxrr respond with false'
        );
    }

    /**
     * Test non-scalar values in a field for the whitelist validator.
     */
    public function testEmailValidatorNonScalar()
    {
        // create a form and the field
        $form = new Form('', false);

        // create a required validator and add it.
        $validator = new EmailValidator(true);

        // test a non-scalar value in a field, expect an exception
        $field = $form->selectField('options[]')
            ->addOptionsAsArray([1, 2, 4, 5, 6, 7, 8, 9])
            ->setMultiple(true)
            ->setValue([1, 5, 6, 9]);

        // this would be strange, but hey... the world is strange!
        $field->addValidator($validator);

        $this->expectException('\Exception');
        $this->expectExceptionMessageRegExp('/scalar types/');
        $field->isValid();
    }
}
