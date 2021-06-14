<?php

namespace FormHandler\Tests\Field;

use FormHandler\Form;
use FormHandler\Tests\TestCase;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 29-08-16
 * Time: 09:36
 */
class RadioButtonTest extends TestCase
{
    public function testRadioButton()
    {
        $form = new Form();

        $form->hiddenField('hidden')->setValue('1');
        $form->radioButton('gender', 'male')->setId('male');
        $form->radioButton('gender', 'female')->setId('female');
        $form->radioButton('gender', 'alien')->setId('alien');

        /** @var \FormHandler\Field\RadioButton $male */
        $male = $form->getFieldById('male');
        /** @var \FormHandler\Field\RadioButton $female */
        $female = $form->getFieldById('female');
        /** @var \FormHandler\Field\RadioButton $alien */
        $alien = $form->getFieldById('alien');

        $male->setLabel('Male');
        $female->setLabel('Female');
        $alien->setLabel('Something from outer space');

        $this->assertEquals('Male', $male->getLabel());
        $this->assertEquals('Female', $female->getLabel());
        $this->assertEquals('Something from outer space', $alien->getLabel());

        $this->assertFalse($male->isChecked());
        $this->assertFalse($female->isChecked());
        $this->assertFalse($alien->isChecked());

        $this->assertFalse($male->isDisabled());
        $this->assertFalse($female->isDisabled());
        $this->assertFalse($alien->isDisabled());

        // set the value to disabled. It should now be true

        $male->setDisabled(true);
        $female->setDisabled(true);
        $alien->setDisabled(true);

        $this->assertTrue($male->isDisabled());
        $this->assertTrue($female->isDisabled());
        $this->assertTrue($alien->isDisabled());
    }

    public function testRadioButtonInSubmittedForm()
    {
        // we fake a form submit
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST                     = [];
        $_POST['gender']           = 'male';
        $_POST['hidden']           = '1';

        $form = new Form();

        $form->hiddenField('hidden')->setValue('1');
        $form->radioButton('gender', 'male')->setId('male');
        $form->radioButton('gender', 'female')->setId('female');
        $form->radioButton('gender', 'alien')->setId('alien');

        /** @var \FormHandler\Field\RadioButton $male */
        $male = $form->getFieldById('male');
        /** @var \FormHandler\Field\RadioButton $female */
        $female = $form->getFieldById('female');
        /** @var \FormHandler\Field\RadioButton $alien */
        $alien = $form->getFieldById('alien');

        $this->assertTrue($male->isChecked());
        $this->assertFalse($female->isChecked());
        $this->assertFalse($alien->isChecked());
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
