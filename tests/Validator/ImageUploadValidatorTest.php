<?php

namespace FormHandler\Tests\Validator;

use FormHandler\Form;
use FormHandler\Tests\TestCase;
use FormHandler\Validator\ImageUploadValidator;

/**
 * Class ImageUploadValidatorTest
 *
 * @package FormHandler\Tests\Validator
 */
class ImageUploadValidatorTest extends TestCase
{
    public function testValidUpload()
    {
        $form  = new Form();
        $field = $form->uploadField('pic');

        $validator = new ImageUploadValidator(true);
        $validator->setMaxFilesize(1024 * 1024);
        $validator->setAllowedExtensions(['gif']);

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertTrue(
            $valid,
            'File should be valid, good extension and not too large'
        );
    }

    public function testValidSize()
    {
        $form = new Form();

        $field = $form->uploadField('pic');

        $validator = new ImageUploadValidator(true);
        $validator->setMaximumProportions(400, 500);

        $this->assertEquals(400, $validator->getMaximumWidth());
        $this->assertEquals(500, $validator->getMaximumHeight());

        $validator->setMinimumProportions(50, 100);

        $this->assertEquals(50, $validator->getMinimumWidth());
        $this->assertEquals(100, $validator->getMinimumHeight());

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertTrue(
            $valid,
            'File should be valid, file size is between min and max '
        );
    }

    public function testTooWide()
    {
        $form = new Form();

        $messages = ['size_width_max' => 'Too wide man!'];
        $field    = $form->uploadField('pic');

        $validator = new ImageUploadValidator(true, $messages);

        $validator->setMaximumWidth(50);

        $this->assertEquals(50, $validator->getMaximumWidth());

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, too wide'
        );

        $this->assertEquals([$messages['size_width_max']], $field->getErrorMessages());
    }

    public function testTooHigh()
    {
        $form = new Form();

        $messages = ['size_height_max' => 'Too high man!'];
        $field    = $form->uploadField('pic');

        $validator = new ImageUploadValidator(true, $messages);
        $validator->setMaximumHeight(50);

        $this->assertEquals(50, $validator->getMaximumHeight());

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, too high'
        );

        $this->assertEquals([$messages['size_height_max']], $field->getErrorMessages());
    }

    public function testNotWideEnough()
    {
        $form = new Form();

        $messages = ['size_width_min' => 'File is not wide enough'];
        $field    = $form->uploadField('pic');

        $validator = new ImageUploadValidator(true, $messages);

        $validator->setMinimumWidth(500);

        $this->assertEquals(500, $validator->getMinimumWidth());

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, not wide enough'
        );

        $this->assertEquals([$messages['size_width_min']], $field->getErrorMessages());
    }

    public function testNotHighEnough()
    {
        $form = new Form();

        $messages = ['size_height_min' => 'File is not high enough'];
        $field    = $form->uploadField('pic');

        $validator = new ImageUploadValidator(true, $messages);
        $validator->setMinimumHeight(500);

        $this->assertEquals(500, $validator->getMinimumHeight());

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, not high enough'
        );

        $this->assertEquals([$messages['size_height_min']], $field->getErrorMessages());
    }

    public function testValidDenyAspectRatio()
    {
        $form = new Form();

        $field = $form->uploadField('pic');

        $validator = new ImageUploadValidator(true);

        // we want a square image
        $validator->setDenyAspectRatio(1, 1);

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertTrue(
            $valid,
            'File should be valid, we deny squares, but none given'
        );
    }

    public function testInvalidDenyAspectRatio()
    {
        $form = new Form();

        $messages = ['aspect_ratio_denied' => 'We dont allow 2:1 images!'];
        $field    = $form->uploadField('pic');

        $validator = new ImageUploadValidator(true, $messages);

        // we want a square image
        $validator->setDenyAspectRatio(2, 1);

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, aspect ratio denied'
        );

        $this->assertEquals([$messages['aspect_ratio_denied']], $field->getErrorMessages());
    }

    public function testInvalidAspectRatio()
    {
        $form = new Form();

        $messages = ['aspect_ratio' => 'We want a square!'];
        $field    = $form->uploadField('pic');

        $validator = new ImageUploadValidator(true, $messages);

        // we want a square image
        $validator->setAllowAspectRatio(1, 1);

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, given file is not the correct aspect ratio'
        );

        $this->assertEquals([$messages['aspect_ratio']], $field->getErrorMessages());
    }

    public function testValidAspectRatio()
    {
        $form = new Form();

        $messages = ['aspect_ratio' => 'We want a 2:1 aspect ratio'];
        $field    = $form->uploadField('pic');

        $validator = new ImageUploadValidator(true, $messages);

        // we want a square image
        $validator->setAllowAspectRatio(2, 1);

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertTrue(
            $valid,
            'File should be valid, correct aspect ratio given'
        );
    }

    public function testNotAnImage()
    {
        $_FILES['pic']['tmp_name']  = __DIR__ . '/_tmp/test.pdf';
        $GLOBALS['mock_image_size'] = false;

        $form  = new Form();
        $field = $form->uploadField('pic');

        $messages = ['not_an_image' => 'Please supply an image'];

        $validator = new ImageUploadValidator(true, $messages);
        $validator->setMaxFilesize(1024 * 1024);
        $validator->setAllowedExtensions(['gif']);

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, given file is not an image'
        );

        $this->assertEquals([$messages['not_an_image']], $field->getErrorMessages());
    }

    public function testInvalidUpload()
    {
        $form  = new Form();
        $field = $form->uploadField('pic');

        $validator = new ImageUploadValidator(true);
        $validator->setMaxFilesize(1024 * 1024);
        $validator->setAllowedExtensions(['jpg']);

        $field->setValidator($validator);

        $valid = $field->isValid();
        $this->assertFalse(
            $valid,
            'File should be invalid, incorrect extension'
        );
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        mkdir(__DIR__ . '/_tmp');

        $GLOBALS['mock_file_size']  = 542;
        $GLOBALS['mock_image_size'] = [
            200, // width
            100, // height
            IMAGETYPE_GIF,
            'height="100" width="200"',
        ];

        $_FILES = [
            'pic' => [
                'name'     => 'avatar.gif',
                'type'     => 'image.gif',
                'size'     => 542,
                'tmp_name' => __DIR__ . '/_tmp/avatar.gif',
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
        unset($GLOBALS['mock_file_size']);
        unset($GLOBALS['mock_image_size']);
        unset($_FILES);
        @unlink(__DIR__ . '/_tmp/avatar.gif');
        @rmdir(__DIR__ . '/_tmp');
    }
}
