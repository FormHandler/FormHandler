<?php

namespace FormHandler\Tests\Validator;

use Exception;
use FormHandler\Form;
use FormHandler\Tests\TestCase;
use FormHandler\Validator\UploadValidator;

class UploadValidatorTest extends TestCase
{
    public function testValidUpload()
    {
        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $validator = new UploadValidator(true);
        $validator->setMaxFilesize(1024 * 1024);
        $validator->setAllowedExtensions(['pdf']);

        $field->setValidator($validator);

        $valid = $form->isValid();

        $this->assertTrue(
            $valid,
            'File should be valid, good extension and not too large'
        );
    }

    public function testInvalidUpload()
    {
        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $validator = new UploadValidator(true);
        $validator->setMaxFilesize(1024 * 1024);
        $validator->setAllowedExtensions(['doc']);

        $field->setValidator($validator);

        $valid = $form->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, incorrect extension'
        );
    }

    public function testExtensionBlacklist()
    {
        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $messages  = [
            'wrong_extension' => 'You have to supply a .doc file!',
        ];
        $validator = new UploadValidator(true, $messages);
        $validator->setMaxFilesize(1024 * 1024);
        $validator->setDeniedExtensions(['pdf']);

        $this->assertEquals(['pdf'], $validator->getDeniedExtensions());

        $validator->addDeniedExtension('doc');

        $this->assertCount(2, $validator->getDeniedExtensions());
        $this->assertTrue(in_array('pdf', $validator->getDeniedExtensions()));
        $this->assertTrue(in_array('doc', $validator->getDeniedExtensions()));

        $this->assertTrue($validator->removeDeniedExtension('doc'));
        $this->assertFalse($validator->removeDeniedExtension('gif'));

        $this->assertEquals(['pdf'], $validator->getDeniedExtensions());

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, incorrect extension (blacklisted)'
        );

        $this->assertEquals([$messages['wrong_extension']], $field->getErrorMessages());
    }

    public function testExtensionWhitelist()
    {
        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $messages  = [
            'wrong_extension' => 'You have to supply a .doc file!',
        ];
        $validator = new UploadValidator(true, $messages);
        $validator->setMaxFilesize(1024 * 1024);
        $validator->setAllowedExtensions(['doc']);

        $this->assertEquals(['doc'], $validator->getAllowedExtensions());

        $validator->addAllowedExtension('pdf');

        $this->assertCount(2, $validator->getAllowedExtensions());
        $this->assertTrue(in_array('pdf', $validator->getAllowedExtensions()));
        $this->assertTrue(in_array('doc', $validator->getAllowedExtensions()));

        $this->assertTrue($validator->removeAllowedExtension('pdf'));
        $this->assertFalse($validator->removeAllowedExtension('gif'));

        $this->assertEquals(['doc'], $validator->getAllowedExtensions());

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, incorrect extension (not whitelisted)'
        );

        $this->assertEquals([$messages['wrong_extension']], $field->getErrorMessages());
    }

    public function testMimeTypeWhitelist()
    {
        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $messages  = [
            'wrong_type' => 'incorrect file type',
        ];
        $validator = new UploadValidator(true, $messages);
        $validator->setMaxFilesize(1024 * 1024);
        $validator->setAllowedMimeTypes(['image/jpg']);

        $this->assertEquals(['image/jpg'], $validator->getAllowedMimeTypes());

        $validator->addAllowedMimeType('application/pdf');

        $this->assertCount(2, $validator->getAllowedMimeTypes());
        $this->assertTrue(in_array('application/pdf', $validator->getAllowedMimeTypes()));
        $this->assertTrue(in_array('image/jpg', $validator->getAllowedMimeTypes()));

        $this->assertTrue($validator->removeAllowedMimeType('application/pdf'));
        $this->assertFalse($validator->removeAllowedMimeType('image/gif'));

        $this->assertEquals(['image/jpg'], $validator->getAllowedMimeTypes());

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, incorrect mime type'
        );

        $this->assertEquals([$messages['wrong_type']], $field->getErrorMessages());
    }

    public function testMimeContentType()
    {
        $GLOBALS['mock_function_not_exists'] = 'finfo_open';
        $GLOBALS['mock_function_exists']     = 'mime_content_type';

        $GLOBALS['mock_mime_content_type'] = 'application/pdf';

        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $validator = new UploadValidator(true);
        $validator->setMaxFilesize(1024 * 1024);
        $validator->setAllowedMimeTypes(['application/pdf']);

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertTrue(
            $valid,
            'File should be valid, correct mime type'
        );

        unset($GLOBALS['mock_function_not_exists']);
        unset($GLOBALS['mock_function_exists']);
        unset($GLOBALS['mock_mime_content_type']);
    }

    public function testMimeTypeBlacklist()
    {
        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $validator = new UploadValidator(true);
        $validator->setDeniedMimeTypes(['application/pdf']);

        $this->assertEquals(['application/pdf'], $validator->getDeniedMimeTypes());

        $validator->addDeniedMimeType('image/jpg');

        $this->assertCount(2, $validator->getDeniedMimeTypes());
        $this->assertTrue(in_array('application/pdf', $validator->getDeniedMimeTypes()));
        $this->assertTrue(in_array('image/jpg', $validator->getDeniedMimeTypes()));

        $this->assertTrue($validator->removeDeniedMimeType('image/jpg'));
        $this->assertFalse($validator->removeDeniedMimeType('image/gif'));

        $this->assertEquals(['application/pdf'], $validator->getDeniedMimeTypes());

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, mime type is denied'
        );
    }

    public function testgetImageSizeMimeType()
    {
        $GLOBALS['mock_image_size'] = ['mime' => 'image/jpg'];

        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $validator = new UploadValidator(true);
        $validator->setAllowedMimeTypes(['application/pdf']);

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, mime type is an image, and not a pdf'
        );
    }

    public function testMinSize()
    {
        $this->expectException(Exception::class);
        $this->expectErrorMessageMatches('/The minimal filesize cannot be a negative integer!/');

        $validator = new UploadValidator();
        $validator->setMinFilesize(-20);
    }

    public function testUploadFilesize()
    {
        $messages = [
            'file_too_big' => 'Its too big to fit in here',
        ];

        $_FILES['cv']['error'] = UPLOAD_ERR_INI_SIZE;

        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $field->setValidator(new UploadValidator(true, $messages));

        $this->assertFalse(
            $field->isValid(),
            'File should be invalid, too large'
        );

        $this->assertEquals(
            [$messages['file_too_big']],
            $field->getErrorMessages()
        );
    }

    public function testPartialUpload()
    {
        $messages = [
            'incomplete' => 'Whoops, only got a small part of your file',
        ];

        $_FILES['cv']['error'] = UPLOAD_ERR_PARTIAL;

        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $field->setValidator(new UploadValidator(true, $messages));

        $this->assertFalse(
            $field->isValid(),
            'File should be invalid, incomplete file'
        );

        $this->assertEquals(
            [$messages['incomplete']],
            $field->getErrorMessages()
        );
    }

    public function testCantWriteUpload()
    {
        $messages = [
            'cannot_write' => 'Whoops, we cannot write your file on disk',
        ];

        $_FILES['cv']['error'] = UPLOAD_ERR_CANT_WRITE;

        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $field->setValidator(new UploadValidator(true, $messages));

        $this->assertFalse(
            $field->isValid(),
            'File should be invalid, cannot write file'
        );

        $this->assertEquals(
            [$messages['cannot_write']],
            $field->getErrorMessages()
        );
    }

    public function testCantUploadError()
    {
        $messages = [
            'error' => 'Error while uploading your file',
        ];

        $_FILES['cv']['error'] = UPLOAD_ERR_EXTENSION;

        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $field->setValidator(new UploadValidator(true, $messages));

        $this->assertFalse(
            $field->isValid(),
            'File should be invalid, extension blocked file upload'
        );

        $this->assertEquals(
            [$messages['error']],
            $field->getErrorMessages()
        );
    }

    public function testTooLarge()
    {
        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $messages = [
            'file_larger_then' => 'The uploaded file is larger then allowed',
        ];

        $validator = new UploadValidator(true, $messages);
        $validator->setMaxFilesize(200); // 200 bytes
        $validator->setAllowedExtensions(['pdf']);

        $this->assertEquals(
            200,
            $validator->getMaxFilesize()
        );

        $field->setValidator($validator);

        $valid = $field->isValid();

        $this->assertFalse(
            $valid,
            'File should be invalid, too large'
        );

        $this->assertEquals(
            [$messages['file_larger_then']],
            $field->getErrorMessages()
        );
    }

    public function testTooSmall()
    {
        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $messages = [
            'file_smaller_then' => 'The uploaded file is too small!',
        ];

        $validator = new UploadValidator(true, $messages);
        $validator->setMinFilesize(1024);
        $validator->setAllowedExtensions(['pdf']);

        $this->assertEquals(
            1024,
            $validator->getMinFilesize()
        );

        $field->setValidator($validator);

        $valid = $field->isValid();

        $this->assertFalse(
            $valid,
            'File should be invalid, too small'
        );

        $this->assertEquals(
            [$messages['file_smaller_then']],
            $field->getErrorMessages()
        );
    }

    public function testNoExtension()
    {
        // no extension at all should also be false
        $_FILES['cv']['name'] = 'test';

        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $messages  = [
            'wrong_extension' => 'You have to supply a .doc file!',
        ];
        $validator = new UploadValidator(true, $messages);
        $validator->setMaxFilesize(1024 * 1024);
        $validator->setAllowedExtensions(['doc']);

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, no extension given'
        );
        $this->assertEquals([$messages['wrong_extension']], $field->getErrorMessages());
    }

    public function testInvalidField()
    {
        $this->expectException(Exception::class);
        $this->expectErrorMessageMatches('/only works on upload fields/');

        $form  = new Form('', false);
        $field = $form->textField('name');

        $messages  = [
            'wrong_extension' => 'You have to supply a .doc file!',
        ];
        $validator = new UploadValidator(true, $messages);

        $field->setValidator($validator);
    }

    public function testRequired()
    {
        // no uploads
        $_FILES = [];

        $form  = new Form('', false);
        $field = $form->uploadField('cv');

        $messages  = [
            'required' => 'Please upload your cv',
        ];
        $validator = new UploadValidator(true, $messages);
        $validator->setMaxFilesize(1024 * 1024);
        $validator->setAllowedExtensions(['doc']);

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, required but nothing uploaded'
        );

        $this->assertEquals([$messages['required']], $field->getErrorMessages());
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        mkdir(__DIR__ . '/_tmp');

        $GLOBALS['mock_file_size']  = 542;
        $GLOBALS['mock_finfo_file'] = 'application/pdf';

        $_FILES = [
            'cv' => [
                'name'     => 'test.pdf',
                'type'     => 'application/pdf',
                'size'     => 542,
                'tmp_name' => __DIR__ . '/_tmp/test.pdf',
                'error'    => 0,
            ],
        ];
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        unset($_FILES);
        unset($GLOBALS['mock_file_size']);
        unset($GLOBALS['mock_finfo_file']);
        @unlink(__DIR__ . '/_tmp/test.pdf');
        @rmdir(__DIR__ . '/_tmp');
    }
}
