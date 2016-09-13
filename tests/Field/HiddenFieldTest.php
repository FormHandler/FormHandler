<?php
namespace FormHandler\Tests\Field;

use FormHandler\Form;

class HiddenFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testHiddenField()
    {
        $form = new Form();
        $field = $form->hiddenField('bid');

        $field->setValue('17,00');

        $this->assertEquals('17,00', $field->getValue());

        $field->setDisabled(true);
        $this->assertTrue($field->isDisabled());
    }
}
