<?php
namespace FormHandler\Tests\Renderer;

use FormHandler\Field\Optgroup;
use FormHandler\Field\Option;
use FormHandler\Form;
use FormHandler\Validator\UploadValidator;
use PHPUnit\Framework\TestCase;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 09-09-16
 * Time: 15:24
 */
class XhtmlRendererTest extends TestCase
{
    protected function expectAttribute($html, $name, $value)
    {
        $this -> assertContains($name .'="'. $value .'"', $html, 'Tag should contain attribute '. $name);
    }

    public function testUploadRender()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        $field->setMultiple(true);
        $field->setSize(20);
        $field->setAccept('image/jpg');
        $field->setDisabled(true);
        $field->addAttribute('data-descr', 'CV');

        $field->addValidator(new UploadValidator(true));

        ob_start();
        echo $field;
        $html = ob_get_clean();

        $this -> expectAttribute($html, 'type', 'file');
        $this -> expectAttribute($html, 'size', 20);
        $this -> expectAttribute($html, 'accept', 'image/jpg');
        $this -> expectAttribute($html, 'disabled', 'disabled');
        $this -> expectAttribute($html, 'name', 'cv[]');
        $this -> expectAttribute($html, 'required', 'required');
        $this -> expectAttribute($html, 'data-descr', 'CV');
    }

    public function testOptGroup()
    {
        $optgroup = new Optgroup('Favorite Benelux Country');
        $optgroup -> addOption(new Option('nl', 'Netherlands'));
        $optgroup -> addOption(new Option('be', 'Belgium'));
        $optgroup -> addOption(new Option('lu', 'Luxemburg'));

        $html = $optgroup -> render();
        $this -> assertEquals('', $html, 'Response should be empty as no renderer is known');

        $form = new Form();
        $optgroup -> setForm($form);
        $this -> assertEquals($form, $optgroup -> getForm());

        ob_start();
        echo $optgroup;
        $html = ob_get_clean();

        //$this -> expectAttribute( $html, 'label', 'Favorite Benelux Country');

//        $this->expectOutputRegex(
//            "/<optgroup label=\"(.*?)\" disabled=\"disabled\" id=\"(.*?)\" title=\"(.*?)\" ".
//            "style=\"(.*?)\" class=\"(.*?)\" data-evil=\"(.*?)\">".
//            "(<option value=\"(.*?)\">(.*?)<\/option>)*<\/optgroup>/i",
//            'Check input html tag'
//        );
//        echo $optgroup;
    }

    public function testOption()
    {
//        $this->expectOutputRegex(
//            "/<option value=\"(.*?)\" disabled=\"disabled\" id=\"(.*?)\" title=\"(.*?)\" " .
//            "style=\"(.*?)\" class=\"(.*?)\" data-full-name=\"male\">(.*?)" .
//            "<\/option>/i",
//            'Check input html tag'
//        );
//        echo $option;
    }

    public function testCheckbox()
    {
//        $form = new Form('');
//        $obj = $form -> checkBox('test', '1');
//        $obj -> setChecked(true);
//        $obj -> setDisabled(true);
//
//
//        $this->expectOutputRegex(
//            "/<input type=\"checkbox\"(.*) checked=\"checked\" ".
//            "disabled=\"disabled\" value=\"1\"(.*)\/>/i",
//            'Check input html tag'
//        );
//        echo $obj;
    }

    public function testElement()
    {
//        $this->expectOutputRegex(
//            "/id=\"(.*?)\" title=\"(.*?)\" " .
//            "style=\"(.*?)\" class=\"(.*?)\" tabindex=\"(\d+)\" accesskey=\"(.*?)\"/i",
//            'Check html tag'
//        );
//        echo $field;
    }

    public function testHiddenField()
    {
//        $this->expectOutputRegex(
//            "/<input type=\"hidden\" name=\"(.*?)\" value=\"(.*?)\" " .
//            "disabled=\"disabled\" \/>/i",
//            'Check html tag'
//        );
//
//        // Note, we use render because our formatter will only output the hidden fields
//        // at the <form> tag.
//        echo $field -> render();
    }

    public function testImageButton()
    {
//        $this->expectOutputRegex(
//            "/<input type=\"image\" name=\"(.*?)\" ".
//            "src=\"(.*?)\" alt=\"(.*?)\" size=\"(\d+)\" disabled=\"disabled\" \/>/i",
//            'Check html tag'
//        );
//        echo $btn;
    }

    public function testPassField()
    {
//        $this->expectOutputRegex(
//            "/<input type=\"password\" name=\"(.*?)\" size=\"(\d+)\" ".
//            "disabled=\"disabled\" maxlength=\"(\d+)\" readonly=\"readonly\" ".
//            "placeholder=\"(.*?)\" title=\"(.*?)\" \/>/i",
//            'Check html tag'
//        );
//        echo $field;
    }

    public function testTextArea()
    {
//        $this->expectOutputRegex(
//            "/<textarea cols=\"(\d+)\" rows=\"(\d+)\" name=\"(.*?)\" ".
//            "disabled=\"disabled\" maxlength=\"(\d+)\" readonly=\"readonly\" ".
//            "placeholder=\"(.*?)\">(.*?)<\/textarea>/i",
//            'Check html tag'
//        );
//        echo $field;
    }

    public function testTextField()
    {
//        $this->expectOutputRegex(
//            "/<input type=\"(.*?)\" name=\"(.*?)\" value=\"(.*?)\" size=\"(\d+)\" ".
//            "disabled=\"disabled\" maxlength=\"(\d+)\" readonly=\"readonly\" placeholder=\"(.*?)\" \/>/i",
//            'Check html tag'
//        );
//        echo $field;
    }

    /**
     * Test the HTML form tags of the form
     */
    public function testFormTags()
    {
//        $form = new Form(null, false);
//        $form->setName('myForm');
//        $form->setAccept('text/plain');
//        $form->setTarget('_self');
//
//        $this->assertEquals('</form>', $form->close());
//
//        $this->expectOutputRegex(
//            '/^<form action="" name="myForm" accept="text\/plain" accept-charset="utf-8" ' .
//            'enctype="application\/x-www-form-urlencoded" method="post" target="_self">$/i',
//            'Check html tag'
//        );
//        echo $form;
    }

    public function testSubmitButton()
    {
//        $this->expectOutputRegex(
//            "/<input type=\"submit\" name=\"(.*?)\" ".
//            "size=\"(\d+)\" disabled=\"disabled\" \/>/i",
//            'Check html tag'
//        );
//        echo $btn;
    }

    public function testSelectField()
    {
//        $this->expectOutputRegex(
//            "/<select name=\"(.*?)\" multiple=\"multiple\" ".
//            "size=\"(\d+)\" disabled=\"disabled\">".
//            "(<option value=\"(\d+)\">(.*?)<\/option>)+" .
//            "(<optgroup label=\"(.*?)\">(<option value=\"(\d+)\">(.*?)<\/option>)*<\/optgroup>)*" .
//            "<\/select>/i",
//            'Check input html tag'
//        );
//        echo $field;
    }

    public function testRadioButton()
    {
//        $this->expectOutputRegex(
//            "/<input type=\"radio\" name=\"(.*?)\" checked=\"checked\" ".
//            "disabled=\"disabled\" value=\"(.*?)\" id=\"(.*?)\" \/>/i",
//            'Check input html tag'
//        );
//        echo $male;
    }
}
