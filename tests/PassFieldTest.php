<?php
namespace FormHandler\Tests;

use FormHandler\Field\TextField;
use FormHandler\Form;
use PHPUnit\Framework\TestCase;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 23-08-16
 * Time: 16:23
 */
class PassFieldTest extends TestCase
{
    public function testTextField()
    {
        $form = new Form();
        $field = $form -> passField('password');

        $this -> assertEquals('password', $field -> getName());

        $field -> setPlaceholder('Enter your password');
        $this -> assertEquals('Enter your password', $field -> getPlaceholder());

        $field -> setTitle('Set your pwd');
        $this -> assertEquals('Set your pwd', $field -> getTitle());

        $field -> setSize(2);
        $this -> assertEquals(2, $field -> getSize());

        $this -> assertEquals(false, $field -> isDisabled());
        $this -> assertEquals(false, $field -> isReadonly());

        $field -> setDisabled(true);
        $field -> setReadonly(true);

        $this -> assertEquals(true, $field -> isDisabled());
        $this -> assertEquals(true, $field -> isReadonly());

        $field -> setValue('Piet');
        $this -> assertEquals('Piet', $field -> getValue());

        $field -> setMaxlength(10);
        $this -> assertEquals(10, $field -> getMaxlength());

        $this->expectOutputRegex(
            "/<input type=\"password\" name=\"(.*?)\" size=\"(\d+)\" ".
            "disabled=\"disabled\" maxlength=\"(\d+)\" readonly=\"readonly\" ".
            "placeholder=\"(.*?)\" title=\"(.*?)\" \/>/i",
            'Check html tag'
        );
        echo $field;
    }
}
