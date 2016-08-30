<?php
namespace FormHandler\Tests;

use FormHandler\Form;
use FormHandler\Formatter\PlainFormatter;
use FormHandler\Validator\StringValidator;
use PHPUnit\Framework\TestCase;

class FormatterTest extends TestCase
{
    public function testFormatter()
    {
        $formatter = new PlainFormatter();

        $form = new Form();
        $form -> setFormatter( $formatter );

        $form -> radioButton('test1');

        $form -> checkBox('test2');

        $form -> textField('name') -> addValidator( new StringValidator(1, 50, true));


        echo "\n";
        echo $form('test1') . "\n";
        echo $form('test2') . "\n";
        echo $form('name') . "\n";
    }
}