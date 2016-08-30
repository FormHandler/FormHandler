<?php
// @codingStandardsIgnoreStart
namespace FormHandler\Validator {

    /**
     * Check if a function exists.
     * We override this function so that we can mock that the "getmxrr" function does not exists, so that
     * we can test both cases.
     *
     * @param $func
     * @return bool
     */
    function function_exists($func)
    {
        static $count = 0;

        $exists = \function_exists($func);

        if ($func == 'getmxrr') {
            return ($count++ === 0 || !$exists) ? false : true;
        } else {
            return $exists;
        }
    }
}
// @codingStandardsIgnoreEnd

namespace FormHandler\Tests\Validator {

    use FormHandler\Form;
    use FormHandler\Validator\EmailValidator;
    use PHPUnit\Framework\TestCase;

    class EmailValidatorTest extends TestCase
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
            $field->clearCache();
            $this->assertTrue(
                $field->isValid(),
                'Empty field with non-required validator should be valid'
            );
        }

        public function testValidEmailAddress()
        {
            $form = new Form('', false);

            $field = $form->textField('email');

            $validator = new EmailValidator(true);

            $field->addValidator($validator);


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
                $field->clearCache();
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
                $field->clearCache();
                $field->setValue($value);
                $this->assertFalse(
                    $field->isValid(),
                    'Field should be invalid when email address is given: ' . $value
                );
            }
        }

        public function testDomainCheck()
        {
            $form = new Form('', false);

            // first skip domain check
            $validator = new EmailValidator(true);

            $field = $form->textField('email');
            $field->setValue('john@iamabsolutlysurethatthisdomainwillneverexistsbecauseits.bogus');
            $field->setValidator($validator);

            $this->assertTrue(
                $field->isValid(),
                'Field should be valid because email address has correct format (no host checking)'
            );

            $validator->setCheckIfDomainExist(true);
            $field->setValidator($validator);

            $field->clearCache();
            $this->assertFalse(
                $field->isValid(),
                'Field should be invalid because host of email address does not exists'
            );

            $field->setValue('test@gmail.com');
            $field->clearCache();
            $this->assertTrue(
                $field->isValid(),
                'Field should be valid because host of email address does exists'
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

            $this->expectException(\Exception::class);
            $this->expectExceptionMessageRegExp('/scalar types/');
            $field->isValid();
        }
    }
}
