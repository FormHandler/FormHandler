<?php
namespace FormHandler\Tests\Renderer;

use FormHandler\Field\Optgroup;
use FormHandler\Field\Option;
use FormHandler\Field\TextField;
use FormHandler\Form;
use FormHandler\Renderer\XhtmlRenderer;
use FormHandler\Validator\UploadValidator;

/**
 * Created by PhpStorm.
 * User: teye
 * Date: 09-09-16
 * Time: 15:24
 */
class XhtmlRendererTest extends BaseTestRenderer
{
    public function testRenderInvalidClass()
    {
        $this->expectException(\Exception::class);

        $renderer = new XhtmlRenderer();
        $renderer->render(new FakeElement());
    }

    public function testHelpTextAsAttribute()
    {
        $form = new Form();
        $field = $form->textField('name');
        $field->setHelpText('Enter your name');

        $renderer = new XhtmlRenderer();
        $renderer->setHelpFormat(XhtmlRenderer::RENDER_AS_ATTRIBUTE);
        $renderer->setHelpTagOrAttr('data-help');
        $form->setRenderer($renderer);

        $html = $field->render();

        $this->assertEquals(XhtmlRenderer::RENDER_AS_ATTRIBUTE, $renderer->getHelpFormat());
        $this->assertEquals('data-help', $renderer->getHelpTagOrAttr());
        $this->expectAttribute($html, 'data-help', 'Enter your name');

        $field->setTitle('test');
        $renderer->setHelpTagOrAttr('title');

        // title tag should be overwritten.
        $html = $field->render();
        $this->expectAttribute($html, 'title', 'Enter your name');
    }

    public function testHelpTextAsTag()
    {
        $form = new Form();
        $field = $form->textField('name');
        $field->setHelpText('Enter your name');

        $renderer = new XhtmlRenderer();
        $renderer->setHelpFormat(XhtmlRenderer::RENDER_AS_TAG);
        $renderer->setHelpTagOrAttr('dfn');
        $form->setRenderer($renderer);

        $html = $field->render();

        $this->assertEquals(XhtmlRenderer::RENDER_AS_TAG, $renderer->getHelpFormat());
        $this->assertEquals('dfn', $renderer->getHelpTagOrAttr());
        $this->assertContains('<dfn>Enter your name</dfn>', $html);
    }

    public function testErrorAsTag()
    {
        $form = new Form();
        $field = $form->textField('name');
        $field->addErrorMessage('Your name is too long.');

        $renderer = new XhtmlRenderer();
        $renderer->setErrorFormat(XhtmlRenderer::RENDER_AS_TAG);
        $renderer->setErrorTagOrAttr('label');
        $form->setRenderer($renderer);

        $html = $field->render();

        $this->assertEquals('label', $renderer->getErrorTagOrAttr());
        $this->assertEquals(XhtmlRenderer::RENDER_AS_TAG, $renderer->getErrorFormat());
        $this->assertContains('<label>Your name is too long.</label>', $html);
    }

    public function testErrorAsAttribute()
    {
        $form = new Form();
        $field = $form->textField('name');
        $field->addErrorMessage('Your name is too long.');

        $renderer = new XhtmlRenderer();
        $renderer->setErrorFormat(XhtmlRenderer::RENDER_AS_ATTRIBUTE);
        $renderer->setErrorTagOrAttr('data-error');
        $form->setRenderer($renderer);

        $html = $field->render();

        $this->assertEquals('data-error', $renderer->getErrorTagOrAttr());
        $this->assertEquals(XhtmlRenderer::RENDER_AS_ATTRIBUTE, $renderer->getErrorFormat());
        $this->expectAttribute($html, 'data-error', 'Your name is too long.');
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
        $renderer = $form->getRenderer();
        echo $renderer($field);
        $html = ob_get_clean();

        $this->expectAttribute($html, 'type', 'file');
        $this->expectAttribute($html, 'size', 20);
        $this->expectAttribute($html, 'accept', 'image/jpg');
        $this->expectAttribute($html, 'disabled', 'disabled');
        $this->expectAttribute($html, 'name', 'cv[]');
        $this->expectAttribute($html, 'multiple', 'multiple');
        $this->expectAttribute($html, 'required', 'required');
        $this->expectAttribute($html, 'data-descr', 'CV');
    }

    public function testOptGroup()
    {
        $optgroup = new Optgroup('Favorite Benelux Country');
        $optgroup->addOption(new Option('nl', 'Netherlands'));
        $optgroup->addOption(new Option('be', 'Belgium'));
        $optgroup->addOption(new Option('lu', 'Luxemburg'));

        $html = $optgroup->render();
        $this->assertEquals('', $html, 'Response should be empty as no renderer is known');

        $form = new Form();
        $optgroup->setForm($form);
        $this->assertEquals($form, $optgroup->getForm());

        ob_start();
        echo $optgroup;
        $html = ob_get_clean();

        $this->expectAttribute($html, 'label', 'Favorite Benelux Country');
    }

    public function testOption()
    {
        $option = new Option('name', 'Label');
        $option->setSelected(true);

        $html = $option->render();
        $this->assertEquals('', $html, 'Response should be empty as no renderer is known');

        $form = new Form();
        $option->setForm($form);

        ob_start();
        echo $option;
        $html = ob_get_clean();

        $this->expectAttribute($html, 'value', 'name');
        $this->expectAttribute($html, 'selected', 'selected');
        $this->assertContains('Label', $html);
    }

    public function testSelectField()
    {
        $form = new Form();
        $field = $form->selectField('country');
        $field->setMultiple(true);

        $optgroup = new Optgroup('Benelux');
        $optgroup->addOptionsAsArray(['nl', 'be', 'lu'], false);
        $field->addOptgroup($optgroup);
        $field->addOption(new Option('-', 'Other'));

        ob_start();
        echo $field;
        $html = ob_get_clean();

        $this->expectAttribute($html, 'name', 'country[]');
        $this->expectAttribute($html, 'multiple', 'multiple');
        $this->assertContains('<select', $html);
        $this->assertContains('<option', $html);
        $this->assertContains('</option', $html);
        $this->assertContains('</select', $html);
        $this->expectAttribute($html, 'value', 'be');
    }

    public function testCheckbox()
    {
        $form = new Form('');
        $field = $form->checkBox('test', '1');
        $field->setChecked(true);
        $field->setDisabled(true);
        $field->setLabel('Should we test?');

        ob_start();
        echo $field;
        $html = ob_get_clean();

        $this->expectAttribute($html, 'type', 'checkbox');
        $this->expectAttribute($html, 'value', '1');
        $this->expectAttribute($html, 'checked', 'checked');
        $this->expectAttribute($html, 'disabled', 'disabled');
        $this->assertContains('<label', $html);
        $this->assertContains($field->getLabel(), $html);
        $this->assertNotEmpty($field->getId());
    }

    public function testHiddenField()
    {
        $form = new Form();
        $field = $form->hiddenField('test');

        ob_start();
        echo $field;
        $html = ob_get_clean();

        // We don't render hidden fields by default.
        // They are rendered with the <form> tag.
        $this->assertEmpty($html);
    }

    public function testImageButton()
    {
        $form = new Form();
        $button = $form->imageButton('submit', 'test.png');

        ob_start();
        echo $button;
        $html = ob_get_clean();

        $this->expectAttribute($html, 'type', 'image');
        $this->expectAttribute($html, 'src', 'test.png');
    }

    public function testPassField()
    {
        $form = new Form();
        $field = $form->passField('password');
        $field->setMaxlength(15);
        $field->setReadonly(true);

        ob_start();
        echo $field;
        $html = ob_get_clean();

        $this->expectAttribute($html, 'type', 'password');
        $this->expectAttribute($html, 'name', 'password');
        $this->expectAttribute($html, 'readonly', 'readonly');
        $this->expectAttribute($html, 'maxlength', '15');
    }

    public function testTextArea()
    {
        $form = new Form();
        $field = $form->textArea('message');

        ob_start();
        echo $field;
        $html = ob_get_clean();

        $this->expectAttribute($html, 'name', 'message');
        $this->expectAttribute($html, 'cols', 40);
        $this->expectAttribute($html, 'rows', 7);
        $this->assertContains('<textarea', $html);
    }

    public function testTextField()
    {
        $form = new Form();
        $field = $form->textField('phone');
        $field->setType(TextField::TYPE_TEL);

        ob_start();
        echo $field;
        $html = ob_get_clean();

        $this->expectAttribute($html, 'name', 'phone');
        $this->expectAttribute($html, 'type', 'tel');
        $this->assertContains('<input', $html);
    }

    /**
     * Test the HTML form tags of the form
     */
    public function testFormTag()
    {
        $form = new Form('/form');
        $form->setMethod(Form::METHOD_GET);
        $form->setAcceptCharset('UTF-8');
        $form->setTarget('_blank');
        $form->setAccept('text/plain');
        $form->setEnctype(Form::ENCTYPE_PLAIN);

        ob_start();
        echo $form;
        $html = ob_get_clean();

        $this->expectAttribute($html, 'method', 'get');
        $this->expectAttribute($html, 'accept-charset', 'UTF-8');
        $this->expectAttribute($html, 'accept', 'text/plain');
        $this->expectAttribute($html, 'enctype', 'text/plain');
        $this->expectAttribute($html, 'target', '_blank');
        $this->assertContains('<form', $html);
    }

    public function testSubmitButton()
    {
        $form = new Form();
        $button = $form->submitButton('submit', 'Send');

        ob_start();
        echo $button;
        $html = ob_get_clean();

        $this->expectAttribute($html, 'type', 'submit');
        $this->expectAttribute($html, 'name', 'submit');
        $this->expectAttribute($html, 'value', 'Send');
    }

    public function testRadioButton()
    {
        $form = new Form();
        $field = $form->radioButton('gender', 'm')->setLabel('Male')->setChecked(true);

        ob_start();
        echo $field;
        $html = ob_get_clean();

        $this->expectAttribute($html, 'type', 'radio');
        $this->expectAttribute($html, 'name', 'gender');
        $this->expectAttribute($html, 'value', 'm');
        $this->expectAttribute($html, 'checked', 'checked');
        $this->assertContains('<label', $html);
        $this->assertContains('Male', $html);
        $this->assertNotEmpty($field->getId());
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        Form::setDefaultRenderer(new XhtmlRenderer());
    }
}
