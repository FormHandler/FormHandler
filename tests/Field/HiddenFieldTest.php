<?php
namespace FormHandler\Tests\Field;

use FormHandler\Form;
use PHPUnit\Framework\TestCase;

class HiddenFieldTest extends TestCase
{
    public function testHiddenField()
    {
        $form = new Form();
        $field = $form->hiddenField('bid')->setValue('17,00')->setDisabled(true);


        $this->expectOutputRegex(
            "/<input type=\"hidden\" name=\"(.*?)\" value=\"(.*?)\" " .
            "disabled=\"disabled\" \/>/i",
            'Check html tag'
        );
        echo $field;
    }

}