<?php
namespace FormHandler\Tests;

use FormHandler\Encoding\Utf8EncodingFilter;
use FormHandler\Field\AbstractFormField;
use FormHandler\Field\HiddenField;
use FormHandler\Field\RadioButton;
use FormHandler\Field\TextField;
use FormHandler\Form;
use FormHandler\Formatter\PlainFormatter;
use FormHandler\Validator\StringValidator;
use PHPUnit\Framework\TestCase;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 29-08-16
 * Time: 15:16
 */
class FormTest extends TestCase
{
    /**
     * @todo: also make sure that the formatter is applied
     */
    public function testDefaultFormatter()
    {
        // set a formatter and check if it's still defined
        $this->assertNull(Form::getDefaultFormatter());
        Form::setDefaultFormatter(new PlainFormatter());
        $this->assertInstanceOf(PlainFormatter::class, Form::getDefaultFormatter());
    }

    /**
     * Test if our default csrf setting is stored correctly
     */
    public function testDefaultCsrf()
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
        $this->assertInstanceOf(HiddenField::class, $form->getFieldByName('csrftoken'));
    }

    /**
     * Test if our default encoding filter is stored correctly
     */
    public function testDefaultEncodingFilter()
    {
        $this->assertNull(Form::getDefaultEncodingFilter());

        // our UTF8 encoding filter is the default
        $form = new Form();
        $this->assertInstanceOf(Utf8EncodingFilter::class, $form->getEncodingFilter());

        Form::setDefaultEncodingFilter(new Utf8EncodingFilter());
        $this->assertInstanceOf(Utf8EncodingFilter::class, Form::getDefaultEncodingFilter());
    }

    /**
     * Test the form action
     */
    public function testFormAction()
    {
        $form = new Form('');
        $this->assertEquals('', $form->getAction());

        $form->setAction('/form/test');
        $this->assertEquals('/form/test', $form->getAction());
    }

    /**
     * Test the form target
     */
    public function testFormTarget()
    {
        $form = new Form();
        $this->assertEmpty($form->getTarget());

        $this->assertInstanceOf(Form::class, $form->setTarget('_blank'));
        $this->assertEquals('_blank', $form->getTarget());
    }

    /**
     * Test the form's encoding type.
     */
    public function testFormEnctype()
    {
        $form = new Form();

        // URLENCODED is default
        $this->assertEquals(Form::ENCTYPE_URLENCODED, $form->getEnctype());

        $this->assertInstanceOf(Form::class, $form->setEnctype(Form::ENCTYPE_MULTIPART));
        $this->assertEquals(Form::ENCTYPE_MULTIPART, $form->getEnctype());

        $this->expectException(\Exception::class);
        $form->setEnctype('wrong');
    }

    /**
     * Test if the accept parameter works as expected
     */
    public function testAccept()
    {
        $form = new Form();
        $this->assertEmpty($form->getAccept());

        $str = 'image/jpeg image/jpg';
        $this->assertInstanceOf(Form::class, $form->setAccept($str));
        $this->assertEquals($str, $form->getAccept());
    }

    /**
     * Test the HTML form tags of the form
     */
    public function testFormTags()
    {
        $form = new Form(null, false);
        $form->setName('myForm');
        $form->setAccept('text/plain');
        $form->setTarget('_self');

        $this->assertEquals('</form>', $form->close());

        $this->expectOutputRegex(
            '/^<form action="" name="myForm" accept="text\/plain" accept-charset="utf-8" '.
            'enctype="application\/x-www-form-urlencoded" method="post" target="_self">$/i',
            'Check html tag'
        );
        echo $form;
    }

    /**
     * Thest the isvalid method
     */
    public function testIsValidAndErrorMessages()
    {
        // create a new form without csrf protection
        $form = new Form('', false);

        // add a field
        $form->textField('name')
            ->addValidator(new StringValidator(2, 50, true));

        $this->assertFalse($form->isValid(), 'Form should not be valid as "name" is invalid');

        $this->assertCount(1, $form->getErrorMessages());
        $this->assertInternalType('array', $form->getErrorMessages());

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'John';

        // create a new form without csrf protection
        $form = new Form('', false);

        // add a field
        $form->textField('name')
            ->addValidator(new StringValidator(2, 50, true));

        $this->assertTrue($form->isValid(), 'Form should now be valid, as all fields are valid');

        $this->assertCount(0, $form->getErrorMessages());
        $this->assertInternalType('array', $form->getErrorMessages());
    }

    /**
     * Test form submition
     */
    public function testSubmitted()
    {
        $reason = '';
        $_POST['name'] = 'test';
        $_GET['name'] = 'test';

        $form = new Form('', false);
        $form->textField('name');
        $form->checkBox('agree', 'true');
        $form->checkBox('option', 'conditions');

        unset($_SERVER['REQUEST_METHOD']);
        $this->assertFalse($form->isSubmitted());

        // invalid request method
        $this->assertInstanceOf(Form::class, $form->clearCache());
        $form->setMethod(Form::METHOD_POST);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertFalse($form->isSubmitted());

        // invalid request method
        $form->clearCache();
        $form->setMethod(Form::METHOD_GET);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertFalse($form->isSubmitted());

        // set it to post for the rest of the tests.
        $form->setMethod(Form::METHOD_POST);
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Should be valid, even without an "option" $_POST value.
        $form->clearCache();
        $this->assertTrue(
            $form->isSubmitted(),
            'Form should be submitted, even without the "option" field missing in the $_POST'
        );

        // now add an textField. It should now be invalid because hobbies is missing from the $_POST
        $form->textField('hobbies[]');
        $form->clearCache();
        $this->assertFalse(
            $form->isSubmitted(),
            'Form should not be submitted because hobbies is missing from $_POST'
        );

        // now add it to the post field so that we don't influence the tests below.
        $_POST['hobbies'] = ['sleeping'];


        // test an uploadfield
        $form->uploadField('avatar');

        $form->clearCache();
        $this->assertFalse(
            $form->isSubmitted($reason),
            'Form should not be submitted because "avatar" is not known in $_FILES'
        );

        // now add it to the $_FILES array
        $_FILES['avatar'] = [];

        $form->clearCache();
        $this->assertTrue(
            $form->isSubmitted($reason),
            'Form should be submitted because "avatar" is now known in $_FILES'
        );


        $pets = $form->selectField('pets');
        $form->clearCache();

        $this->assertTrue(
            $form->isSubmitted($reason),
            'Form should be submitted, even without the "pets" selectfield because it contains no options.'
        );

        $pets->addOptionsAsArray(['dog', 'cat', 'fish', 'tiger'], false);

        $form->clearCache();
        $this->assertFalse(
            $form->isSubmitted(),
            'Form should not be submitted because the selectfield "pets" is missing from the $_POST (now with options)'
        );

        $form->getFieldByName('pets')->setMultiple(true);
        $form->clearCache();
        $this->assertTrue(
            $form->isSubmitted($reason),
            'Form should be submitted, even without the "pets" field because its now an multiple select'
        );

        // test the image button
        $form->imageButton('cancel', 'images/cancel.png');
        $form->clearCache();
        $this->assertFalse(
            $form->isSubmitted($reason),
            'Form should not be submitted because imagebuttons x and y value are not present'
        );

        $_POST['cancel_x'] = 16;
        $_POST['cancel_y'] = 12;
        $form->clearCache();
        $this->assertTrue(
            $form->isSubmitted($reason),
            'Form should be submitted because imagebuttons x and y value are now present'
        );

        // remove our tests
        unset($_POST['cancel_x']);
        unset($_POST['cancel_y']);
        $form->removeFieldByName('cancel');

        // only 1 submit button in the form. We expect it to be there
        $form->submitButton('submit', 'Submit');
        $form->clearCache();
        $this->assertFalse(
            $form->isSubmitted($reason),
            'Form should not be submitted, because the submit buttons name => value are not present in $_POST'
        );

        // now its there, thus this should be submitted
        $_POST['submit'] = 'Submit';
        $form->clearCache();
        $this->assertTrue(
            $form->isSubmitted($reason),
            'Form should be submitted because the submit buttons name => value are now present in $_POST'
        );

        // now add a second button.
        $form->submitButton('cancel', 'Cancel');
        $this->assertTrue(
            $form->isSubmitted($reason),
            'The form should be present, even with the cancel button not being present, because at least 1 button is.'
        );

        unset($_POST['submit']);
        $form->clearCache();
        $this->assertFalse(
            $form->isSubmitted($reason),
            'Form should not be submitted, because the submit buttons are not present (neither one)'
        );

        $form->setSubmitted(true);
        $this->assertTrue(
            $form->isSubmitted($reason),
            'Form should be submitted because its explicitly set to true'
        );
    }

    /**
     * Test the form name
     */
    public function testFormName()
    {
        $form = new Form();
        $this->assertEmpty($form->getName());

        $this->assertInstanceOf(Form::class, $form->setName('myForm'));
        $this->assertEquals('myForm', $form->getName());
    }

    /**
     * Test if adding and removing fields works correctly
     */
    public function testFields()
    {
        // disable csrf because this will add an extra hidden field to our fields list
        $form = new Form(null, false);

        // should be empty
        $this->assertEquals([], $form->getFields());

        // after adding 1 it should only contain 1
        $this->assertInstanceOf(TextField::class, $form->textField('name')->setId('name'));
        $this->assertContainsOnlyInstancesOf(TextField::class, $form->getFields());
        $this->assertCount(1, $form->getFields());

        // test invoke
        $field = $form('name');
        $this->assertInstanceOf(TextField::class, $field);
        $this->assertEquals('name', $field->getName());

        // add some more
        $this->assertInstanceOf(RadioButton::class, $form->radioButton('gender', 'm'));
        $this->assertInstanceOf(RadioButton::class, $form->radioButton('gender', 'f'));
        $this->assertInstanceOf(RadioButton::class, $form->radioButton('gender', 'u'));
        $this->assertContainsOnlyInstancesOf(AbstractFormField::class, $form->getFields());
        $this->assertCount(4, $form->getFields());

        $gender = $form->getFieldsByName('gender');
        $this->assertCount(3, $gender);
        $this->assertContainsOnlyInstancesOf(RadioButton::class, $gender);

        // now remove 1 gender field
        $this->assertInstanceOf(Form::class, $form->removeFieldByName('gender'));
        $this->assertCount(3, $form->getFields());

        // now all gender fields should be removed
        $this->assertInstanceOf(Form::class, $form->removeAllFieldsByName('gender'));
        $this->assertCount(1, $form->getFields());

        // delete by id
        $this->assertInstanceOf(Form::class, $form->removeFieldById('name'));

        $this->assertNull($form->getFieldById('IDontExists'));

        // should be empty
        $this->assertEquals([], $form->getFields());
        $this->assertCount(0, $form->getFields());

        $this->assertInstanceOf(TextField::class, $form->textField('age')->setId('age'));
        $this->assertContainsOnlyInstancesOf(TextField::class, $form->getFields());
        $this->assertCount(1, $form->getFields());

        $form->removeField($form->getFieldById('age'));

        // should be empty
        $this->assertEquals([], $form->getFields());
        $this->assertCount(0, $form->getFields());
    }

    /**
     * Test the form method
     */
    public function testFormMethod()
    {
        $form = new Form();

        // default
        $this->assertEquals(Form::METHOD_POST, $form->getMethod());

        $this->assertInstanceOf(Form::class, $form->setMethod(Form::METHOD_GET));
        $this->assertEquals(Form::METHOD_GET, $form->getMethod());

        $this->assertInstanceOf(Form::class, $form->setMethod(Form::METHOD_POST));
        $this->assertEquals(Form::METHOD_POST, $form->getMethod());

        $this->expectException(\Exception::class);
        $form->setMethod('put');
    }

    /**
     * Test CSRF protection
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
        $field = $form->getFieldByName('csrftoken');
        $this->assertInstanceOf(HiddenField::class, $field);

        $this->assertFalse($form->isSubmitted());

        // get the token
        $token = $field->getValue();
        echo "\nToken: $token\n";

        // Now fake a "wrong" submit
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'name' => 'John',
            'csrftoken' => 'wrong'
        ];

        // create a simular form.
        $form = new Form('', true);
        $form->clearCache(); // clear static cache
        $form->textField('name');
        $this->assertTrue($form->isCsrfProtectionEnabled());

        // after checking it should exists, but if should be invalid.
        $field = $form->getFieldByName('csrftoken');
        $this->assertInstanceOf(HiddenField::class, $field);

        // destroy the session. Now csrf should be disabled.
        session_destroy();
        $form = new Form('', true);
        $this->assertFalse($form->isCsrfProtectionEnabled());

        // @todo: fixme!
        // the form should be invalid because the csrf token is wrong.
//        $this->assertFalse($form->isValid(), "Validate that the form is invalid");
//        $this->assertFalse($form->isCsrfValid(), "Validate that the csrf is invalid");
//        $this->assertFalse($field->isValid(), "validate that the field is invalid");
//
//        // now also push the token in the $_POST and try again
//        $_POST['csrftoken'] = $field -> getValue();
//
//
//        $this->assertTrue($form->isCsrfValid());
//        $this->assertTrue($form->isValid());
    }

    public function testGetDataAsArray()
    {
        $_POST['name'] = 'John';
        $_POST['age'] = 16;
        $_POST['gender'] = 'm';
        $_POST['pet'] = ['dog', 'dragon', 'other'];
        $_POST['partner'] = 'y';

        $_FILES = array(
            'cv' => array(
                'name' => 'test.pdf',
                'type' => 'application/pdf',
                'size' => 542,
                'tmp_name' => __DIR__ . '/_tmp/test.pdf',
                'error' => 0
            )
        );

        $form = new Form('', false);

        $this->assertEquals([], $form->getDataAsArray($form));

        $form->textField('name');
        $form->textField('age');
        $form->checkBox('agree', 'agree');
        $form->checkBox('terms')->setDisabled(true);
        $form->radioButton('gender', 'm')->setId('genderM');
        $form->radioButton('gender', 'f')->setId('genderF');

        $form->radioButton('partner', 'y')->setId('partnerY')->setDisabled(true);
        $form->radioButton('partner', 'n')->setId('partnerN');

        $form->uploadField('cv');
        $form->uploadField('avatar');
        $form->selectField('pet')->addOptionsAsArray(['dog', 'cat', 'dragon'], false)->setMultiple(true);
        $form->selectField('instrument')->addOptionsAsArray(['guitar', 'piano']);

        $expected = [
            'name' => 'John',
            'age' => '16',
            'agree' => '', // not checked, thus empty
            'terms' => '', // disabled, thus empty
            'gender' => 'm',
            'partner' => '', // disabled, thus should be empty
            'cv' => 'test.pdf', // the name of the uploaded file
            'pet' => ['dog', 'dragon'], // "other" was not an option thus should not be there
            'instrument' => '' // not in post thus should be empty
        ];

        $this->assertEquals($expected, $form->getDataAsArray($form));
    }

    public function testFill()
    {
        $form = new Form();

        $form->textField('name');
        $form->radioButton('gender', 'm')->setId('genderM');
        $form->radioButton('gender', 'f')->setId('genderF');
        $form->checkBox('agree');

        $this->assertEmpty($form->getFieldByName('name')->getValue());
        $this->assertFalse($form->getFieldById('genderM')->isChecked());
        $this->assertFalse($form->getFieldById('genderF')->isChecked());
        $this->assertFalse($form->getFieldByName('agree')->isChecked());

        $values = [
            'name' => 'John',
            'agree' => 1,
            'gender' => 'm'
        ];
        $form -> fill($values);

        $this->assertEquals('John', $form->getFieldByName('name')->getValue());
        $this->assertTrue($form->getFieldById('genderM')->isChecked());
        $this->assertFalse($form->getFieldById('genderF')->isChecked());
        $this->assertTrue($form->getFieldByName('agree')->isChecked());
    }

    public function testIncorrectFill()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/composite types/');

        $form = new Form();
        $form -> fill('wrong');
    }

    public function testValidationErrors()
    {
        $form = new Form('', false);
        $this -> assertEquals([], $form -> getValidationErrors());

        $form -> textField('test') -> addValidator(new StringValidator(2, 50, true, 'Enter your name'));
        $this -> assertEquals(['Enter your name'], $form -> getValidationErrors());

        $this -> assertFalse($form -> isValid());
    }


    protected function setUp()
    {
        $_GET = [];
        $_POST = [];
        $_FILES = [];
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $_GET = [];
        $_POST = [];
        $_FILES = [];
    }
}
