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
class TextAreaTest extends TestCase
{
    public function testTextAreaTest()
    {
        $form = new Form();
        $field = $form -> textArea('msg');

        $field -> setPlaceholder('Enter a message');
        $this -> assertEquals('Enter a message', $field -> getPlaceholder());
        $this -> assertEquals('msg', $field -> getName());

        $field -> setCols(10);
        $field -> setRows(10);
        $this -> assertEquals([10, 10], [$field -> getRows(), $field -> getCols()]);

        $this -> assertEquals(false, $field -> isDisabled());
        $this -> assertEquals(false, $field -> isReadonly());

        $field -> setDisabled(true);
        $field -> setReadonly(true);

        $this -> assertEquals(true, $field -> isDisabled());
        $this -> assertEquals(true, $field -> isReadonly());

        $field -> setValue('Piet');
        $this -> assertEquals('Piet', $field -> getValue());

        $this->expectOutputRegex(
            "/<textarea cols=\"(\d+)\" rows=\"(\d+)\" name=\"(.*?)\" ".
            "disabled=\"disabled\" readonly=\"readonly\" placeholder=\"(.*?)\">(.*?)<\/textarea>/i",
            'Check html tag'
        );
        echo $field;
    }
}
