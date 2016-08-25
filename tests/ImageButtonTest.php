<?php
namespace FormHandler\Tests;

use FormHandler\Form;
use PHPUnit\Framework\TestCase;

/**
 * Test Image Button.
 * User: teye
 * Date: 23-08-16
 * Time: 16:23
 */
class ImageButtonTest extends TestCase
{
    public function testImageButton()
    {
        $form = new Form();
        $btn = $form -> imageButton('submit', 'images/button.png');

        $this -> assertEquals('submit', $btn -> getName());
        $this -> assertEquals('images/button.png', $btn -> getSrc());

        $this -> assertEquals($form, $btn -> getForm());
        $this -> assertEquals(false, $btn -> isDisabled());
        $btn -> setDisabled(true);
        $this -> assertEquals(true, $btn -> isDisabled());

        $btn -> setSize(20);
        $this -> assertEquals(20, $btn -> getSize());
        $btn -> setAlt('alt');
        $this -> assertEquals('alt', $btn -> getAlt());


        $this->expectOutputRegex(
            "/<input type=\"image\" name=\"(.*?)\" ".
            "src=\"(.*?)\" alt=\"(.*?)\" size=\"(\d+)\" disabled=\"disabled\" \/>/i",
            'Check html tag'
        );
        echo $btn;
    }
}
