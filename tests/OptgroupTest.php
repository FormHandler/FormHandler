<?php
namespace FormHandler\Tests;

use PHPUnit\Framework\TestCase;
use FormHandler\Field\Optgroup;
use FormHandler\Field\Option;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 22-08-16
 * Time: 09:36
 */
class OptgroupTest extends TestCase
{
    public function testOptgroup()
    {
        $title = 'How many kids do you have?';

        $optgroup = new Optgroup($title);

        $this->assertEquals($title, $optgroup->getLabel());

        $options = [
            new Option('1', 'One'),
            new Option('2', 'Two'),
            new Option('3', 'Three')
        ];

        $alloptions = [
            new Option('1', 'One'),
            new Option('2', 'Two'),
            new Option('3', 'Three'),
            new Option('4', 'Four'),
            new Option('0', 'None')
        ];

        $optgroup->setOptions($options);
        $this->assertEquals($options, $optgroup->getOptions());
        $this->assertCount(3, $optgroup->getOptions());

        $option = new Option('4', 'Four');
        $optgroup->addOption($option);
        $this->assertContainsOnlyInstancesOf(Option::class, $optgroup->getOptions());
        $this->assertCount(4, $optgroup->getOptions());

        $newoptions = [new Option('0', 'None')];
        $optgroup->addOptions($newoptions);
        $this->assertContainsOnlyInstancesOf(Option::class, $optgroup->getOptions());
        $this->assertCount(5, $optgroup->getOptions());
        $this->assertEquals($alloptions, $optgroup->getOptions());

        $arr = ['1' => 'One', '2' => 'Two', '3' => 'Three'];
        $optgroup->setOptionsAsArray($arr);
        $this->assertCount(3, $optgroup->getOptions());
        $this->assertEquals($options, $optgroup->getOptions());


        $arr2 = ['4' => 'Four', '0' => 'None'];
        $optgroup->addOptionsAsArray($arr2);
        $this->assertCount(5, $optgroup->getOptions());
        $this->assertEquals($alloptions, $optgroup->getOptions());

        $optgroup = new Optgroup($title);
        $optgroup->addOption($option);
        $this->assertCount(1, $optgroup->getOptions());
        $this->assertEquals([$option], $optgroup->getOptions());

        $this->assertEquals(false, $optgroup->isDisabled());
        $optgroup->setDisabled(true);
        $this->assertEquals(true, $optgroup->isDisabled());

        $optgroup -> setId('kids');
        $optgroup -> setClass("className");
        $optgroup -> setStyle('color: black');

        $this->expectOutputRegex(
            "/<optgroup label=\"(.*?)\"(.*?)(disabled=\"disabled\")?(id=\"(.*?)\")?(.*?)>".
            "(<option value=\"(.*?)\">(.*?)<\/option>)*<\/optgroup>/i",
            'Check input html tag'
        );
        echo $optgroup;
        //echo $obj;
    }
}
