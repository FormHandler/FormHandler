<?php
namespace FormHandler\Tests\Encoding;

use FormHandler\Form;
use FormHandler\Tests\TestCase;
use FormHandler\Encoding\Utf8EncodingFilter;

class Utf8EncodingTest extends TestCase
{
    public function testUtf8EncodingFilter()
    {
        $encoding = new Utf8EncodingFilter();

        $form = new Form();
        $encoding->init($form);

        // expect the form's accepted charset to utf-8
        $this->assertEquals('utf-8', $form->getAcceptCharset());

        // should be valid utf8
        $this->assertEquals("Fédération", $encoding->filter("FÃÂ©dération"));
        $this->assertEquals("Fédération", $encoding->filter("FÃ©dÃ©ration"));
        $this->assertEquals("Fédération", $encoding->filter("FÃÂ©dÃÂ©ration"));
        $this->assertEquals("Fédération", $encoding->filter("FÃÂÂÂÂ©dÃÂÂÂÂ©ration"));
        $this->assertEquals("ÿ", $encoding->filter(chr(0xFF)));
    }
}
