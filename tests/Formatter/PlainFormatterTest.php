<?php
namespace FormHandler\Tests;

use FormHandler\Form;
use FormHandler\Formatter\PlainFormatter;
use FormHandler\Validator\StringValidator;
use PHPUnit\Framework\TestCase;

class PlainFormatterTest extends TestCase
{
    public function testCheckbox()
    {
        $formatter = new PlainFormatter();

        $form = new Form();
        $form->setFormatter($formatter);
        $form -> setSubmitted(true);

        $form->radioButton('test1');

        $form->checkBox('test2')
            ->setLabel('Please check this')
            ->addErrorMessage('You should check this field', true);

        $form->textField('name')->addValidator(new StringValidator(1, 50, true));


        echo "\n";
        echo $form('test1') . "\n";
        echo $form('test2') . "\n";
        echo $form('name') . "\n";
    }
}
