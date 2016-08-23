<?php

namespace FormHandler\Tests;

use PHPUnit\Framework\TestCase;

class CheckboxTest extends TestCase
{
    public function testCheckbox()
    {

        $form = new \FormHandler\Form();

        $obj = $form -> checkBox('test');
        $obj -> setId('test2');
        $obj -> setAccesskey('a');
        $obj -> setLabel('Set your name');

        $this -> assertClassHasAttribute('checked', \FormHandler\Field\CheckBox::class);

        $this -> assertEquals('test', $obj -> getName());
        $this -> assertEquals('test2', $obj -> getId());
        $this -> assertEquals('a', $obj -> getAccesskey());
        $this -> assertEquals('Set your name', $obj -> getLabel());
        $this -> assertFalse($obj -> isChecked()); // should not be checked.

        // checked should still be false (form is not submitted!)
        $obj -> setValue('on');
        $this -> assertFalse($obj -> isChecked(), 'Check if checkbox is checked (form not submitted).');
    }

    public function testSubmittedCheckbox()
    {
        // fake a post request;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['submitted'] = '1';
        $_POST['hidden'] = '1';

        $form = new \FormHandler\Form('');
        $form -> setMethod(\FormHandler\Form::METHOD_POST);
        $form -> hiddenField('hidden') -> setValue('1');

        $obj = $form -> checkBox('submitted', '1');

        $this -> assertEquals('submitted', $obj -> getName());
        $this -> assertTrue($obj -> isChecked(), 'Check if checkbox is checked (form submitted).');
    }

    public function testArrayOfCheckboxes()
    {
        // fake a post request;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['arr']['0'] = '1';
        $_GET['arr']['1'] = '1';
        $_GET['hidden'] = '1';

        $form = new \FormHandler\Form('');
        $form -> setMethod(\FormHandler\Form::METHOD_GET);
        $form -> hiddenField('hidden') -> setValue('1');

        $obj1 = $form -> checkBox('arr[]', '1');

        $this -> assertTrue($obj1 -> isChecked(), 'Obj1 should be checked');
    }

    public function testRender()
    {
        $form = new \FormHandler\Form('');
        $obj = $form -> checkBox('test', '1');
        $obj -> setChecked(true);
        $obj -> setDisabled(true);


        $this->expectOutputRegex(
            "/<input type=\"checkbox\"(.*) checked=\"checked\" ".
            "disabled=\"disabled\" value=\"1\"(.*)\/>/i",
            'Check input html tag'
        );
        echo $obj;
    }
}
