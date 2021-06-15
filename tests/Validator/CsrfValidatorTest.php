<?php

namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Tests\TestCase;
use FormHandler\Field\HiddenField;
use FormHandler\Validator\CsrfValidator;
use FormHandler\Validator\StringValidator;

class CsrfValidatorTest extends TestCase
{
    /**
     * Test if our default csrf setting is stored correctly
     */
    public function testDefaultCsrfLogic()
    {
        // make sure the default CSRF logic works like expected
        $this->assertTrue(Form::isDefaultCsrfProtectionEnabled());

        Form::setDefaultCsrfProtectionEnabled(false);
        $this->assertFalse(Form::isDefaultCsrfProtectionEnabled());

        $form = new Form();
        $this->assertFalse($form->isCsrfProtectionEnabled());

        $form = new Form('', true);
        $this->assertTrue($form->isCsrfProtectionEnabled());

        Form::setDefaultCsrfProtectionEnabled(true);
        $this->assertTrue(Form::isDefaultCsrfProtectionEnabled());

        $form = new Form('', false);
        $this->assertFalse($form->isCsrfProtectionEnabled());
        $this->assertNull($form->getFieldByName('csrftoken')); // should not exists

        $form = new Form();
        $this->assertTrue($form->isCsrfProtectionEnabled());
        $this->assertInstanceOf('\FormHandler\Field\HiddenField', $form->getFieldByName('csrftoken'));
    }

    /**
     * Test CSRF protection
     *
     * @throws \Exception
     */
    public function testCsrf()
    {
        // first, create a Form which is "not" submitted.
        $form = new Form('', true);
        $form->textField('name');
        $this->assertTrue($form->isCsrfProtectionEnabled());

        $form->setCsrfProtection(false);
        $this->assertFalse($form->isCsrfProtectionEnabled());

        $form->setCsrfProtection(true);
        $this->assertTrue($form->isCsrfProtectionEnabled());

        // this should exists
        /** @var \FormHandler\Field\HiddenField $field */
        $field = $form->getFieldByName('csrftoken');
        $this->assertInstanceOf(HiddenField::class, $field, 'csrf field should exists');

        // this should contain a token
        $this->assertNotEmpty(
            $field->getValue(),
            'csrftoken field should contain a value, as a token should be generated'
        );

        $this->assertFalse($form->isSubmitted(), 'the field should not be submitted');
    }

    /**
     * @throws \Exception
     */
    public function testCsrfWithoutTokenPosted()
    {
        // Now fake a "wrong" submit
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST                     = [
            'name' => 'John',
        ];

        // create a similar form.
        $form = new Form('', false);
        $this->assertTrue($form->isSubmitted(), 'Form should be submitted');

        $form->clearCache(); // clear static cache
        $form->textField('name');

        $this->assertTrue($form->isSubmitted(), 'Form should be submitted, name field exists');

        // enable csrf protection
        $form->setCsrfProtection(true);
        $this->assertTrue($form->isCsrfProtectionEnabled(), 'csrf protection should be enabled');
        $form->clearCache(); // clear static cache

        $this->assertFalse($form->isSubmitted(), 'Form should be not submitted, csrf token not in POST field');

        // after checking it should exists, but if should be invalid.
        /** @var HiddenField $field */
        $field = $form->getFieldByName('csrftoken');
        $this->assertInstanceOf(HiddenField::class, $field);

        $this->assertEmpty($field->getValue(), 'csrf token should be empty');
    }

    /**
     * Test form with invalid token
     *
     * @throws \Exception
     */
    public function testCsrfWithWrongTokenPosted()
    {
        // Now fake a "wrong" submit
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST                     = [
            'name'      => 'John',
            'csrftoken' => 'wrong.value',
        ];

        // create a similar form.
        $form = new Form('', false);
        $this->assertTrue($form->isSubmitted(), 'Form should be submitted');

        $form->clearCache(); // clear static cache
        $form->textField('name');

        $this->assertTrue($form->isSubmitted(), 'Form should be submitted, name field exists');

        // enable csrf protection
        $form->setCsrfProtection(true);
        $this->assertTrue($form->isCsrfProtectionEnabled(), 'csrf protection should be enabled');

        $form->clearCache(); // clear static cache

        $reason = '';
        $this->assertTrue($form->isSubmitted($reason), 'Form should be submitted, csrf token is in POST field');

        $this->assertFalse($form->isValid(), 'Form should be invalid, csrf token is not correct');

        // after checking it should exists, but if should be invalid.
        /** @var HiddenField $field */
        $field = $form->getFieldByName('csrftoken');
        $this->assertInstanceOf(HiddenField::class, $field);

        $this->assertEquals('wrong.value', $field->getValue(), 'csrf token should be wrong.value');
    }

    /**
     * Test token session cleanup
     */
    public function testTokenCleanup()
    {
        $_SESSION['csrftokens'] = ''; // test incorrect type;

        new CsrfValidator();
        $this->assertTrue(
            is_array($_SESSION['csrftokens']),
            'Session csrftokens should now be an array'
        );

        $expired = (time() - 86400) . '.invalid';
        // add some wrong tokens. They should be removed afterwards
        $_SESSION['csrftokens'] = ['wrong.token', $expired];

        new CsrfValidator();

        $this->assertFalse(
            in_array('wrong.token', (array)$_SESSION['csrftokens']),
            'CSRF session should not contain "wrong.token" anymore as its not a timestamp'
        );
        $this->assertFalse(
            in_array($expired, (array)$_SESSION['csrftokens']),
            'CSRF token should be removed because its timestamp is expired'
        );

        $expired    = (time() - 86400) . '.expired';
        $notExpired = (time() - 6200) . '.not-expired';

        $_SESSION['csrftokens'] = [$expired, $notExpired];
        define('CSRFTOKEN_EXPIRE', 6600);

        new CsrfValidator();

        $this->assertFalse(
            in_array($expired, $_SESSION['csrftokens']),
            'CSRF session should not contain the expired token'
        );

        $this->assertTrue(
            in_array($notExpired, $_SESSION['csrftokens']),
            'CSRF session should contain ' . $notExpired . ' because its not expired yet'
        );
    }

    /**
     * Test valid CSRF token
     */
    public function testValidCsrfFlow()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // form should not be submitted.
        $form = new Form(null, true);
        $form->textField('name');

        $this->assertFalse($form->isSubmitted(), 'Form should not be submitted');

        /** @var HiddenField $field */
        $field = $form('csrftoken');

        $token = $field->getValue();

        $this->assertTrue(is_array($_SESSION['csrftokens']));

        // now fake a post and retry
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST                     = [
            'name'      => 'Piet',
            'csrftoken' => $token,
        ];

        // form should be submitted.
        $form = new Form(null, true);
        $form->textField('name');

        $this->assertTrue($form->isSubmitted(), 'Form should be submitted');

        $valid = $form->isValid();
        $this->assertTrue($valid, 'Form should be valid, token is in the POST');
        $this->assertTrue($form->isCsrfValid());
    }

    public function testCsrfDisabled()
    {
        // now fake a post and retry
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST                     = [
            'name' => 'Piet',
        ];

        $form = new Form('', false);
        $form->textField('name');

        $this->assertTrue($form->isCsrfValid(), 'CSRF should be valid as it is disabled');
    }

    public function testCsrfValidOnNonSubmittedForm()
    {
        // now fake a post and retry
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $form = new Form('', true);
        $form->textField('name');

        $this->assertFalse($form->isSubmitted(), 'Form is not submitted');
        $this->assertTrue($form->isCsrfValid(), 'CSRF should be valid as the form is not submitted');
    }

    /**
     * @throws \Exception
     */
    public function testInvalidField()
    {
        // now fake a post and retry
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST                     = [
            'name' => 'Pi',
        ];

        $form  = new Form('', false);
        $field = $form->textField('name');
        $field->addValidator(new StringValidator(3));

        $this->assertFalse($form->isValid(), 'Form should not be valid');
        $this->assertTrue($form->isCsrfValid(), 'CSRF should be valid as the form is invalid');
    }

    /**
     * Test valid CSRF token
     */
    public function testInvalidCsrfFlow()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // form should not be submitted.
        $form = new Form(null, true);
        $form->textField('name');

        $this->assertFalse($form->isSubmitted(), 'Form should not be submitted');

        /** @var HiddenField $field */
        $field = $form('csrftoken');
        $token = $field->getValue();

        $this->assertTrue(is_array($_SESSION['csrftokens']));

        // now fake a post and retry
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST                     = [
            'name'      => 'Piet',
            'csrftoken' => $token . 'wrong',
        ];

        // form should be submitted.
        $form = new Form(null, true);
        $form->textField('name');

        $this->assertTrue($form->isSubmitted(), 'Form should be submitted');

        $valid = $form->isValid();
        $this->assertFalse($valid, 'Form should be valid, token is in the POST');

        $this->assertFalse($form->isCsrfValid());
    }

    protected function setUp(): void
    {
        $_GET     = [];
        $_POST    = [];
        $_FILES   = [];
        $_SESSION = [];
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $_GET     = [];
        $_POST    = [];
        $_FILES   = [];
        $_SESSION = [];
    }
}
