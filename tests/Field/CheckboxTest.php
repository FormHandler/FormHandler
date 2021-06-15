<?php

namespace FormHandler\Tests\Field;

use FormHandler\Form;
use FormHandler\Tests\TestCase;

class CheckboxTest extends TestCase
{
    /**
     * Test the checkbox
     */
    public function testCheckbox()
    {
        $form = new Form();

        $obj = $form->checkBox('test');
        $obj->setId('test2');
        $obj->setAccesskey('a');
        $obj->setLabel('Set your name');

        $this->assertClassHasAttribute('checked', '\FormHandler\Field\CheckBox');

        $this->assertEquals('test', $obj->getName());
        $this->assertEquals('test2', $obj->getId());
        $this->assertEquals('a', $obj->getAccesskey());
        $this->assertEquals('Set your name', $obj->getLabel());
        $this->assertFalse($obj->isChecked()); // should not be checked.

        // checked should still be false (form is not submitted!)
        $obj->setValue('on');
        $this->assertFalse($obj->isChecked(), 'Check if checkbox is checked (form not submitted).');
    }

    /**
     * Test the checkbox in a submitted form
     *
     * @throws \Exception
     */
    public function testSubmittedCheckbox()
    {
        // fake a post request;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['submitted']        = '1';
        $_POST['hidden']           = '1';

        $form = new Form('');
        $form->setMethod(Form::METHOD_POST);
        $form->hiddenField('hidden')->setValue('1');

        $obj = $form->checkBox('submitted');

        $this->assertEquals('submitted', $obj->getName());
        $this->assertTrue($obj->isChecked(), 'Check if checkbox is checked (form submitted).');
    }

    /**
     * @throws \Exception
     */
    public function testArrayOfCheckboxes()
    {
        // fake a post request;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET                      = [];
        $_GET['arr']['0']          = '1';
        $_GET['arr']['1']          = '1';
        $_GET['hidden']            = '1';

        $form = new Form('');
        $form->setMethod(Form::METHOD_GET);
        $form->hiddenField('hidden')->setValue('1');

        $obj1 = $form->checkBox('arr[]');

        $this->assertTrue($obj1->isChecked(), 'Obj1 should be checked');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $_POST = [];
    }
}
