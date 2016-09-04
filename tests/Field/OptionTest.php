<?php
namespace FormHandler\Tests\Field;

use FormHandler\Field\Option;
use PHPUnit\Framework\TestCase;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 29-08-16
 * Time: 09:02
 */
class OptionTest extends TestCase
{
    public function testOption()
    {
        $option = new Option('m', 'Male');

        $this->assertEquals('Male', $option->getLabel());
        $this->assertEquals('m', $option->getValue());
        $this->assertFalse($option->isDisabled());

        $option->setDisabled(true);
        $this->assertTrue($option->isDisabled());

        $this->assertEmpty($option->getStyle());
        $option->setStyle("color:red");
        $this->assertEquals("color:red", $option->getStyle());

        $this->assertEmpty($option->getClass());

        $option->addClass("white");
        $this->assertEquals("white", $option->getClass());

        $option->setClass("gray");
        $this->assertEquals("gray", $option->getClass());

        $option->addClass("bold");
        $this->assertEquals("gray bold", $option->getClass());

        $this->assertEmpty($option->getTitle());
        $option->setTitle("Select this option if you are male");
        $this->assertEquals("Select this option if you are male", $option->getTitle());

        $this->assertEmpty($option->getId());
        $option->setId("m");
        $this->assertEquals("m", $option->getId());

        $this->assertFalse($option->isSelected());
        $option->setSelected(true);
        $this->assertTrue($option->isSelected());

        $option -> addAttribute('data-full-name', 'male');

        $this->expectOutputRegex(
            "/<option value=\"(.*?)\" disabled=\"disabled\" id=\"(.*?)\" title=\"(.*?)\" " .
            "style=\"(.*?)\" class=\"(.*?)\" data-full-name=\"male\">(.*?)" .
            "<\/option>/i",
            'Check input html tag'
        );
        echo $option;
    }
}
