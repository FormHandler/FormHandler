<?php
namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Validator\RadioButtonCheckedValidator;

class RadioButtonCheckedValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testRadioButton()
    {
        $form = new Form('', false);

        $male = $form->radioButton('gender', 'm')->setId('genderM');
        $female = $form->radioButton('gender', 'f')->setId('genderF');

        $validator = new RadioButtonCheckedValidator();
        $male->addValidator($validator);

        $this->assertFalse(
            $male->isValid(),
            'Field should be invalid as its not checked, nor is any radio button with the same name'
        );

        $female->setChecked(true);
        $male->clearCache();
        $this->assertTrue(
            $male->isValid(),
            'Field should be valid as a radio button with the same name is now checked'
        );

        $errormsg = 'You should select one';
        $validator->setErrorMessage($errormsg);

        $male->setValidator($validator);
        $female->setChecked(false);
        $male->clearCache();

        $this->assertFalse(
            $male->isValid(),
            'Field should be invalid as its not checked, nor is any radio button with the same name'
        );

        $this->assertContains($errormsg, $male->getErrorMessages());
    }

    /**
     * Test incorrect fields
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /only works on radio buttons/
     */
    public function testIncorrectField()
    {
        $form = new Form('', false);
        $form->textField('test')
            ->addValidator(new RadioButtonCheckedValidator());
    }
}
