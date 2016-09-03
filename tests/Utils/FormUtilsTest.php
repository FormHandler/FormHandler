<?php
namespace FormHandler\Tests\Utils;

use FormHandler\Field\HiddenField;
use FormHandler\Form;
use FormHandler\Utils\FormUtils;
use PHPUnit\Framework\TestCase;

class FormUtilsTest extends TestCase
{
    public function testQueryStringToFormWithWhitelist()
    {
        $_GET['name'] = 'John';
        $_GET['age'] = 16;
        $_GET['gender'] = 'm';

        $form = new Form();

        $whitelist = ['name', 'gender'];
        $blacklist = null;
        FormUtils::queryStringToForm($form, $whitelist, $blacklist);

        $this->assertInstanceOf(HiddenField::class, $form->getFieldByName('name'));
        $this->assertEquals('John', $form->getFieldByName('name')->getValue());

        $this->assertInstanceOf(HiddenField::class, $form->getFieldByName('gender'));
        $this->assertEquals('m', $form->getFieldByName('gender')->getValue());

        $this->assertNull($form->getFieldByName('age'));
    }

    public function testQueryStringToFormWithBlacklist()
    {
        $_GET['name'] = 'John';
        $_GET['age'] = 16;
        $_GET['gender'] = 'm';

        $form = new Form();

        $whitelist = null;
        $blacklist = ['age'];
        FormUtils::queryStringToForm($form, $whitelist, $blacklist);

        $this->assertInstanceOf(HiddenField::class, $form->getFieldByName('name'));
        $this->assertEquals('John', $form->getFieldByName('name')->getValue());

        $this->assertInstanceOf(HiddenField::class, $form->getFieldByName('gender'));
        $this->assertEquals('m', $form->getFieldByName('gender')->getValue());

        $this->assertNull($form->getFieldByName('age'));
    }

    protected function tearDown()
    {
        $_GET = [];
    }
}
