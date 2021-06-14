<?php

namespace FormHandler\Tests\Field;

use FormHandler\Form;
use FormHandler\Field\Option;
use FormHandler\Field\Optgroup;
use FormHandler\Tests\TestCase;
use FormHandler\Field\SelectField;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 22-08-16
 * Time: 09:36
 */
class SelectFieldTest extends TestCase
{
    public function testGetOptionByValue()
    {
        $form  = new Form();
        $field = $form->selectField('options');

        $dragon = new Option('dragon', 'Dragon');

        $field->addOption(new Option('cat', 'Cat'));
        $field->addOption(new Option('dog', 'Dog'));
        $field->addOption($dragon);

        $option = $field->getOptionByValue('dragon');

        $this->assertEquals($dragon, $option);
    }

    public function testSelectField()
    {
        // we begin simple by creating a field
        $form  = new Form();
        $field = $form->selectField('kids');

        // is the name correct?
        $this->assertEquals('kids', $field->getName());
        $this->assertEquals([], $field->getOptions());

        // lets set some options and check if they are still there
        $field->setOptionsAsArray([1, 2, 3], false);
        $expect = [
            new Option(1, 1),
            new Option(2, 2),
            new Option(3, 3),
        ];
        $this->assertEquals($expect, $field->getOptions());

        // add some more options (as array) without key as value
        $expect[] = new Option(4, 4);
        $expect[] = new Option(5, 5);
        $field->addOptionsAsArray([4, 5], false);
        $this->assertEquals($expect, $field->getOptions());

        // Set the options (overwrite current one)
        $options = [
            new Option(1, 'One'),
            new Option(2, 'Two'),
            new Option(3, 'Three'),
        ];

        $field->setOptions($options);
        $this->assertEquals($field->getOptions(), $options);

        // add single option ass object
        $field->addOption(new Option(4, 'Four'));

        // add options as array, now with maintaining key value
        $add = [0 => 'None'];
        $field->addOptionsAsArray($add, true);

        // lets add some options to our expected result
        $options[] = new Option(4, 'Four');
        $options[] = new Option(0, 'None');

        // get the options in the field.
        $infield = $field->getOptions();

        /** @var Option $option */
        $option = $infield[4];

        // make sure that the "None" field is not automatically selected because it's value is "0"
        // and no value ("null") is not the same as zero!
        $this->assertInstanceOf(Option::class, $option);
        $this->assertEquals('None', $option->getLabel());
        $this->assertEquals(false, $option->isSelected());

        // check if all options are still okay
        $this->assertEquals($options, $field->getOptions());

        // here we empty the options and add some new ones.
        $field->setOptions([]);
        $field->addOptions($options);
        $this->assertEquals($options, $field->getOptions());

        // test size field
        $field->setSize(10);
        $this->assertEquals(10, $field->getSize());

        // now set the value to "0" and make sure its now selected
        $field->setValue(0);

        // only the 4th field ("None") should be selected
        $infield = $field->getOptions();
        /** @var Option $option0 */
        $option0 = $infield[0];
        $this->assertFalse($option0->isSelected());

        /** @var Option $option1 */
        $option1 = $infield[1];
        $this->assertFalse($option1->isSelected());

        /** @var Option $option2 */
        $option2 = $infield[2];
        $this->assertFalse($option2->isSelected());

        /** @var Option $option3 */
        $option3 = $infield[3];
        $this->assertFalse($option3->isSelected());

        /** @var Option $option4 */
        $option4 = $infield[4];
        $this->assertTrue($option4->isSelected());
        $this->assertEquals('None', $option4->getLabel());

        // multiple should be false (by default)
        $this->assertEquals(false, $field->isMultiple());

        // set that this field can have multiple values
        $field->setMultiple(true);

        // also add an optgroup
        $optgroup = new Optgroup('Test');
        $optgroup->addOption(new Option(5, 'Five'));
        $field->addOptgroup($optgroup);

        // set some fields as selected
        $setValue = [0, 2, 5];
        $field->setValue($setValue);

        // get the options in the field.
        $infield = $field->getOptions();

        /** @var Option $option0 */
        $option0 = $infield[0];

        /** @var Option $option1 */
        $option1 = $infield[1];

        /** @var Option $option2 */
        $option2 = $infield[2];

        /** @var Option $option3 */
        $option3 = $infield[3];

        /** @var Option $option4 */
        $option4 = $infield[4];

        $this->assertFalse($option0->isSelected());
        $this->assertTrue($option1->isSelected());
        $this->assertFalse($option2->isSelected());
        $this->assertFalse($option3->isSelected());
        $this->assertTrue($option4->isSelected());

        // 5th element should be an Optgroup
        $this->assertInstanceOf(Optgroup::class, $infield[5]);

        // this optgroup should have 1 Option and it should be selected
        /** @var Optgroup $optgroup */
        $optgroup = $infield[5];
        $this->assertEquals(1, sizeof($optgroup->getOptions()));
        $this->assertInstanceOf(Option::class, $optgroup->getOptions()[0]);
        $this->assertTrue($optgroup->getOptions()[0]->isSelected());

        // sort the results, because it could be different
        $value = (array)$field->getValue();
        $this->assertEquals(sort($setValue), sort($value));

        // now set the multiple value to false. We should only expect 1 result, the last one, which is 5
        $field->setMultiple(false);
        $value = $field->getValue();
        $this->assertEquals(5, $value);

        // set a value which is not in the select field
        $field->setValue(16);

        // we now expect an empty string as value
        $this->assertEquals("", $field->getValue());

        // set to multiple, we should now expect an empty string
        $field->setMultiple(true);
        $this->assertEquals([], $field->getValue());

        /** @var Option $option */
        $option = $field->getOptionByValue('5');
        $this->assertInstanceOf(Option::class, $option);
        $this->assertEquals('Five', $option->getLabel());

        $this->assertEquals(null, $field->getOptionByValue('92'));

        // Remove the 'None' option
        $this->assertInstanceOf(SelectField::class, $field->removeOptionByValue(0));

        $infield = $field->getOptions();

        // we should now have 5 options left.
        $this->assertEquals(5, sizeof($infield));
        foreach ($infield as $option) {
            if ($option instanceof Option) {
                $this->assertNotEquals(0, $option->getValue());
                $this->assertNotEquals('None', $option->getLabel());
            }
        }

        // now remove the option in the optgroup, which should also remove the optgroup itsself
        $field->removeOptionByValue(5);

        // check the disabled field
        $this->assertFalse($field->isDisabled());
        $field->setDisabled(true);
        $this->assertTrue($field->isDisabled());

        $infield = $field->getOptions();

        // we should now have 4 options left.
        $this->assertEquals(4, sizeof($infield));
        $this->assertContainsOnly(Option::class, $infield);

        // add it again
        $optgroup->addOption(new Option(99, 'A lot'));
        $field->addOptgroup($optgroup);
    }
}
