<?php
namespace FormHandler\Tests;

use FormHandler\Encoding\Utf8EncodingFilter;
use FormHandler\Form;
use FormHandler\Renderer\XhtmlRenderer;
use FormHandler\Validator\CsrfValidator;
use FormHandler\Validator\StringValidator;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @todo: also make sure that the formatter is applied
     */
    public function testDefaultRenderer()
    {
        // set a formatter and check if it's still defined
        $this->assertNull(Form::getDefaultRenderer());
        Form::setDefaultRenderer(new XhtmlRenderer());
        $this->assertInstanceOf('\FormHandler\Renderer\XhtmlRenderer', Form::getDefaultRenderer());
    }


    public function testDisabledFieldsInSubmit()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'name' => 'Piet'
        ];

        $form = new Form('', false);
        $form->textField('name');
        $form->textField('age')->setDisabled(true);

        $this->assertTrue(
            $form->isSubmitted(),
            'Form should be submitted. Age field is not in post but is disabled.'
        );
    }

    public function testDisabledButtonsInSubmit()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'name' => 'Piet'
        ];

        $form = new Form('', false);
        $form->textField('name');
        $form->submitButton('submit')->setDisabled(true);

        $this->assertTrue(
            $form->isSubmitted(),
            'Form should be submitted. Submit button is not in post but is disabled.'
        );
    }

    /**
     * Test if our default encoding filter is stored correctly
     */
    public function testDefaultEncodingFilter()
    {
        $this->assertNull(Form::getDefaultEncodingFilter());

        // our UTF8 encoding filter is the default
        $form = new Form();
        $this->assertInstanceOf('FormHandler\Encoding\Utf8EncodingFilter', $form->getEncodingFilter());

        Form::setDefaultEncodingFilter(new Utf8EncodingFilter());
        $this->assertInstanceOf('FormHandler\Encoding\Utf8EncodingFilter', Form::getDefaultEncodingFilter());
    }

    /**
     * Test the form action
     */
    public function testFormAction()
    {
        $form = new Form('');
        $this->assertEquals('', $form->getAction());

        $form->setAction("/form/test");
        $this->assertEquals('/form/test', $form->getAction());
    }

    /**
     * Test the form target
     */
    public function testFormTarget()
    {
        $form = new Form();
        $this->assertEmpty($form->getTarget());

        $this->assertInstanceOf('\FormHandler\Form', $form->setTarget('_blank'));
        $this->assertEquals('_blank', $form->getTarget());
    }

    /**
     * Test the form's encoding type.
     * @expectedException \Exception
     */
    public function testFormEnctype()
    {
        $form = new Form();

        // URLENCODED is default
        $this->assertEquals(Form::ENCTYPE_URLENCODED, $form->getEnctype());

        $this->assertInstanceOf('\FormHandler\Form', $form->setEnctype(Form::ENCTYPE_MULTIPART));
        $this->assertEquals(Form::ENCTYPE_MULTIPART, $form->getEnctype());

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
        $this->assertInstanceOf('\FormHandler\Form', $form->setAccept($str));
        $this->assertEquals($str, $form->getAccept());
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
        $this->assertInstanceOf('\FormHandler\Form', $form->clearCache());
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

        $this->assertInstanceOf('\FormHandler\Form', $form->setName('myForm'));
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
        $this->assertInstanceOf('FormHandler\Field\TextField', $form->textField('name')->setId('name'));
        $this->assertContainsOnlyInstancesOf('FormHandler\Field\TextField', $form->getFields());
        $this->assertCount(1, $form->getFields());

        // test invoke
        $field = $form('name');
        $this->assertInstanceOf('FormHandler\Field\TextField', $field);
        $this->assertEquals('name', $field->getName());

        // add some more
        $this->assertInstanceOf('FormHandler\Field\RadioButton', $form->radioButton('gender', 'm'));
        $this->assertInstanceOf('FormHandler\Field\RadioButton', $form->radioButton('gender', 'f'));
        $this->assertInstanceOf('FormHandler\Field\RadioButton', $form->radioButton('gender', 'u'));
        $this->assertContainsOnlyInstancesOf('FormHandler\Field\AbstractFormField', $form->getFields());
        $this->assertCount(4, $form->getFields());

        $gender = $form->getFieldsByName('gender');
        $this->assertCount(3, $gender);
        $this->assertContainsOnlyInstancesOf('FormHandler\Field\RadioButton', $gender);

        // now remove 1 gender field
        $this->assertInstanceOf('\FormHandler\Form', $form->removeFieldByName('gender'));
        $this->assertCount(3, $form->getFields());

        // now all gender fields should be removed
        $this->assertInstanceOf('\FormHandler\Form', $form->removeAllFieldsByName('gender'));
        $this->assertCount(1, $form->getFields());

        // delete by id
        $this->assertInstanceOf('\FormHandler\Form', $form->removeFieldById('name'));

        $this->assertNull($form->getFieldById('IDontExists'));

        // should be empty
        $this->assertEquals([], $form->getFields());
        $this->assertCount(0, $form->getFields());

        $this->assertInstanceOf('FormHandler\Field\TextField', $form->textField('age')->setId('age'));
        $this->assertContainsOnlyInstancesOf('FormHandler\Field\TextField', $form->getFields());
        $this->assertCount(1, $form->getFields());

        $form->removeField($form->getFieldById('age'));

        // should be empty
        $this->assertEquals([], $form->getFields());
        $this->assertCount(0, $form->getFields());
    }

    /**
     * Test the form method
     * @expectedException \Exception
     */
    public function testFormMethod()
    {
        $form = new Form();

        // default
        $this->assertEquals(Form::METHOD_POST, $form->getMethod());

        $this->assertInstanceOf('\FormHandler\Form', $form->setMethod(Form::METHOD_GET));
        $this->assertEquals(Form::METHOD_GET, $form->getMethod());

        $this->assertInstanceOf('\FormHandler\Form', $form->setMethod(Form::METHOD_POST));
        $this->assertEquals(Form::METHOD_POST, $form->getMethod());

        $form->setMethod('put');
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

        // new test
        $form = new Form('', false);
        $form->textField('name')->setValue('Piet');
        $form->checkBox('agree', 'ok')->setChecked(true);
        $form -> radioButton('gender', 'm') -> setChecked( false );
        $form->submitButton('submit', 'Submit');

        $this->assertEquals(['name' => 'Piet', 'agree' => 'ok', 'gender' => ''], $form->getDataAsArray());
    }

    public function testClose()
    {
        $form = new Form();
        $this -> assertEquals('</form>', $form -> close());
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
        $form->fill($values);

        $this->assertEquals('John', $form->getFieldByName('name')->getValue());
        $this->assertTrue($form->getFieldById('genderM')->isChecked());
        $this->assertFalse($form->getFieldById('genderF')->isChecked());
        $this->assertTrue($form->getFieldByName('agree')->isChecked());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /composite types/
     */
    public function testIncorrectFill()
    {
        $form = new Form();
        $form->fill('wrong');
    }

    public function testValidationErrors()
    {
        $form = new Form('', false);
        $this->assertEquals([], $form->getValidationErrors());

        $form->textField('test')->addValidator(new StringValidator(2, 50, true, 'Enter your name'));
        $this->assertEquals(['Enter your name'], $form->getValidationErrors());

        $this->assertFalse($form->isValid());
    }


    protected function setUp()
    {
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_SESSION = [];
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
        $_SESSION = [];
    }
}
