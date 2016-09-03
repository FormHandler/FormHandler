<?php
namespace FormHandler\Tests\Utils;

use FormHandler\Field\HiddenField;
use FormHandler\Form;
use FormHandler\Utils\FormUtils;
use PHPUnit\Framework\TestCase;

class FormUtilsTest extends TestCase
{
    public function testAutoFill()
    {
        $form = new Form();

        $form->textField('name');
        $form->radioButton('gender', 'm')->setId('genderM');
        $form->radioButton('gender', 'f')->setId('genderF');
        $form->checkBox('agree');

        $this->assertEmpty($form->getFieldByName('name')->getValue());
        $this->assertFalse($form->getFieldById('genderM')->isChecked());
        $this->assertFalse($form->getFieldById('genderF')->isChecked());
        $this->assertFalse($form->getFieldByName('agree')->isChecked());

        $values = [
            'name' => 'John',
            'agree' => 1,
            'gender' => 'm'
        ];
        FormUtils::autoFill($form, $values);

        $this->assertEquals('John', $form->getFieldByName('name')->getValue());
        $this->assertTrue($form->getFieldById('genderM')->isChecked());
        $this->assertFalse($form->getFieldById('genderF')->isChecked());
        $this->assertTrue($form->getFieldByName('agree')->isChecked());
    }

    public function testIncorrectAutofill()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/composite types/');

        $form = new Form();
        FormUtils::autoFill($form, 'wrong');
    }

    public function testGetDataAsArray()
    {
        $_POST['name'] = 'John';
        $_POST['age'] = 16;
        $_POST['gender'] = 'm';
        $_POST['pet'] = ['dog', 'dragon', 'other'];
        $_POST['partner'] = 'y';

        $_FILES = array(
            'cv' => array(
                'name' => 'test.pdf',
                'type' => 'application/pdf',
                'size' => 542,
                'tmp_name' => __DIR__ . '/_tmp/test.pdf',
                'error' => 0
            )
        );

        $form = new Form('', false);

        $this->assertEquals([], $form->getDataAsArray($form));

        $form->textField('name');
        $form->textField('age');
        $form->checkBox('agree', 'agree');
        $form->checkBox('terms')->setDisabled(true);
        $form->radioButton('gender', 'm')->setId('genderM');
        $form->radioButton('gender', 'f')->setId('genderF');

        $form->radioButton('partner', 'y')->setId('partnerY')->setDisabled(true);
        $form->radioButton('partner', 'n')->setId('partnerN');

        $form->uploadField('cv');
        $form->uploadField('avatar');
        $form->selectField('pet')->addOptionsAsArray(['dog', 'cat', 'dragon'], false)->setMultiple(true);
        $form->selectField('instrument')->addOptionsAsArray(['guitar', 'piano']);

        $expected = [
            'name' => 'John',
            'age' => 16,
            'agree' => '', // not checked, thus empty
            'gender' => 'm',
            'partner' => '', // disabled, thus should be empty
            'cv' => 'test.pdf', // the name of the uploaded file
            'pet' => ['dog', 'dragon'], // "other" was not an option thus should not be there
            'instrument' => '' // not in post thus should be empty
        ];

        $this->assertEquals($expected, $form->getDataAsArray($form));
    }

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
        $_POST = [];
        $_FILES = [];
    }

}