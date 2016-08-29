<?php
use FormHandler\Field\HiddenField;
use FormHandler\Formatter\PlainFormatter;
use PHPUnit\Framework\TestCase;

use FormHandler\Form;

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

    public function testDefaultCsrf()
    {
        // make sure the default CSRF logic works like expected
        $this->assertNull(Form::isDefaultCsrfProtectionEnabled());

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
     * Test CSRF protection
     */
    public function testCsrf()
    {
        // first, create a Form which is "not" submitted.
        $form = new Form('', true);
        $form->textField('name');
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
}
