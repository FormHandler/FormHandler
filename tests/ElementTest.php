<?php
namespace FormHandler\Tests;

use FormHandler\Form;
use PHPUnit\Framework\TestCase;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 29-08-16
 * Time: 15:01
 */
class ElementTest extends TestCase
{
    public function testElement()
    {
        $form = new Form();
        $field = $form->textField('name');

        $this->assertNull($field->getId());
        $field->setId("nameFld");
        $this->assertEquals('nameFld', $field->getId());

        $this->assertNull($field->getTitle());
        $field->setTitle("Enter your name");
        $this->assertEquals('Enter your name', $field->getTitle());

        $this->assertNull($field->getTabindex());
        $field->setTabindex(5);
        $this->assertEquals(5, $field->getTabindex());

        $this->assertNull($field->getAccesskey());
        $field->setAccesskey('n');
        $this->assertEquals('n', $field->getAccesskey());

        $this->assertNull($field->getClass());
        $field->addClass('bold');
        $this->assertEquals('bold', $field->getClass());
        $field->addClass('underline');
        $this->assertEquals('bold underline', $field->getClass());

        $this->assertNull($field->getStyle());
        $field->addStyle('color:red');
        $this->assertEquals('color:red', $field->getStyle());
        $field->addStyle(';');
        $this->assertEquals('color:red;', $field->getStyle());

        $this->expectOutputRegex(
            "/id=\"(.*?)\" title=\"(.*?)\" " .
            "style=\"(.*?)\" class=\"(.*?)\" tabindex=\"(\d+)\" accesskey=\"(.*?)\"/i",
            'Check html tag'
        );
        echo $field;
    }
}
