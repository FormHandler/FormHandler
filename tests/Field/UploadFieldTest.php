<?php

namespace FormHandler\Tests\Field;

use FormHandler\Form;
use PHPUnit\Framework\TestCase;

class UploadFieldTest extends TestCase
{
    public function testIsUploaded()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        $this->assertTrue($field->isUploaded());
    }

    public function testIsNotUploaded()
    {
        $form = new Form('', false);
        $field = $form->uploadField('image');

        $this->assertFalse($field->isUploaded());
    }

    public function testAccept()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        $field->setAccept('image/jpg');
        $this->assertEquals('image/jpg', $field->getAccept());
    }

    public function testSize()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        $field->setSize(20);
        $this->assertEquals(20, $field->getSize());
    }

    public function testMultiple()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        $this->assertFalse($field->isMultiple());
        $field->setMultiple(true);
        $this->assertTrue($field->isMultiple());
    }


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        mkdir(__DIR__ . '/_tmp');

        $GLOBALS['mock_file_size'] = 542;
        $GLOBALS['mock_finfo_file'] = 'application/pdf';


        $_FILES = array(
            'cv' => array(
                'name' => 'test.pdf',
                'type' => 'application/pdf',
                'size' => 542,
                'tmp_name' => __DIR__ . '/_tmp/test.pdf',
                'error' => 0
            )
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($_FILES);
        unset($GLOBALS['mock_file_size']);
        unset($GLOBALS['mock_finfo_file']);
        @unlink(__DIR__ . '/_tmp/test.pdf');
        @rmdir(__DIR__ . '/_tmp');
    }
}
