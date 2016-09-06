<?php
namespace FormHandler\Tests\Utils;

use FormHandler\Field\HiddenField;
use FormHandler\Form;
use FormHandler\Utils\FormUtils;
use PHPUnit\Framework\TestCase;

class FormUtilsTest extends TestCase
{
    public function testGetFileExtension()
    {
        $tests = [
            'test.pdf' => 'pdf',
            'justAFile.CsV' => 'csv',
            'anoter.test.xhtml' => 'xhtml',
            'filewithnoextension' => '',
            'test.psd?with=query&string=1' => 'psd'
        ];

        foreach ($tests as $filename => $extension) {
            $this->assertEquals($extension, FormUtils::getFileExtension($filename));
        }
    }

    public function testMoveMultipleUploadFileWithName()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');
        $field->setMultiple(true); // multiple files allowed

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/multiple files/');

        FormUtils::moveUploadedFile($field, __DIR__ . '/_tmp/moved.pdf');
    }

    public function testMoveUploadedFile()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        $dest = FormUtils::moveUploadedFile($field, __DIR__ . '/_tmp/moved.pdf');
        $this->assertFileExists($dest);

        @unlink($dest);
    }

    public function testMoveUploadedFileExistsRename()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        @touch(__DIR__ . '/_tmp/moved.pdf');
        $dest = FormUtils::moveUploadedFile($field, __DIR__ . '/_tmp/moved.pdf', FormUtils::MODE_RENAME);
        $this->assertFileExists(__DIR__ . '/_tmp/moved(1).pdf');

        @unlink($dest);
        @unlink(__DIR__ . '/_tmp/moved.pdf');
    }

    public function testMoveUploadedFileExistsException()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        @touch(__DIR__ . '/_tmp/moved.pdf');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/already exists/');

        FormUtils::moveUploadedFile($field, __DIR__ . '/_tmp/moved.pdf', FormUtils::MODE_EXCEPTION);
    }

    public function testIncorrectExistsValue()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        @touch(__DIR__ . '/_tmp/moved.pdf');

        $this->expectException(\UnexpectedValueException::class);

        FormUtils::moveUploadedFile($field, __DIR__ . '/_tmp/moved.pdf', 'wrong');
    }

    public function testCreateDirIfNotExists()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        $dest = FormUtils::moveUploadedFile($field, __DIR__ . '/_new/', FormUtils::MODE_OVERWRITE, true);

        $this->assertEquals(__DIR__ . '/_new/test.pdf', $dest);
        $this->assertTrue(is_dir(__DIR__ . '/_new/'));
        $this->assertFileExists($dest);

        @unlink($dest);
        @rmdir(__DIR__ . '/_new');
    }

    public function testCreateDirFailure()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/Failed to create the destination directory/');

        $GLOBALS['mock_mkdir_response'] = false;

        FormUtils::moveUploadedFile($field, __DIR__ . '/_abc123/', FormUtils::MODE_OVERWRITE, true);
    }

    public function testIsNotWritable()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/directory is not writable/');

        $GLOBALS['mock_is_writable_response'] = false;

        FormUtils::moveUploadedFile($field, __DIR__ . '/_new/', FormUtils::MODE_OVERWRITE, true);
    }

    public function testMoveFailed()
    {
        $form = new Form('', false);
        $field = $form->uploadField('cv');

        $GLOBALS['mock_move_uploaded_file_response'] = false;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/we failed to move file/');

        FormUtils::moveUploadedFile($field, __DIR__ . '/_tmp/blaat.tmp', FormUtils::MODE_OVERWRITE, true);
    }

    public function testMoveMultipleFiles()
    {
        $_FILES = array(
            'cv' => array(
                'name' => ['test.pdf', 'test1.pdf'],
                'type' => ['application/pdf', 'application/pdf'],
                'size' => [542, 541],
                'tmp_name' => [ __DIR__ . '/_tmp/test.pdf', __DIR__ . '/_tmp/test1.pdf' ],
                'error' => [0, 0]
            )
        );

        @touch(__DIR__ .'/_tmp/test1.pdf');

        $form = new Form('', false);
        $field = $form->uploadField('cv');
        $field->setMultiple(true); // multiple files allowed

        $dest = FormUtils::moveUploadedFile($field, __DIR__ . '/_new/', FormUtils::MODE_OVERWRITE, true);

        $this -> assertCount(2, $dest);
        $this -> assertEquals([ __DIR__ . '/_new/test.pdf', __DIR__ . '/_new/test1.pdf'], $dest);

        @unlink(__DIR__ . '/_new/test.pdf');
        @unlink(__DIR__ . '/_new/test1.pdf');
        @rmdir(__DIR__.'/_new');
    }

    public function testMoveMultipleFilesException()
    {
        $_FILES = '1';
        $x = array(
            'cv' => array(
                'name' => ['test.pdf', 'test1.pdf'],
                'type' => ['application/pdf', 'application/pdf'],
                'size' => [542, 541],
                'tmp_name' => [ __DIR__ . '/_tmp/test.pdf', __DIR__ . '/_tmp/test1.pdf' ],
                'error' => [0, 0]
            )
        );

        $form = new Form('', false);
        $field = $form->uploadField('cv');
        $field -> setMultiple(true);

        $GLOBALS['mock_move_uploaded_file_response'] = false;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/we failed to move file/');

        FormUtils::moveUploadedFile($field, __DIR__ . '/_tmp/', FormUtils::MODE_OVERWRITE, true);
    }

    public function testGetNonExistingFilename()
    {
        // existing file
        $filename = __DIR__ . '/_tmp/test.pdf';

        $newfile = FormUtils::getNonExistingFilename($filename);

        $this->assertEquals($newfile, __DIR__ . '/_tmp/test(1).pdf');
    }

    public function testGdVersion()
    {
        $GLOBALS['mock_extension_not_loaded'] = 'gd';
        $this->assertEquals(0, FormUtils::getGDVersion(false));

        unset($GLOBALS['mock_extension_not_loaded']);
        $GLOBALS['mock_extension_loaded'] = 'gd';
        $GLOBALS['mock_function_exists'] = 'gd_info';

        $GLOBALS['mock_gd_info'] = [
            'GD Version' => 'bundled (2.1.0 compatible)',
            // other keys are not of any interest, so we ignore those here.
        ];
        $this->assertEquals(2, FormUtils::getGDVersion(false));

        // just a funky test :-)
        $GLOBALS['mock_gd_info'] = ['GD Version' => '16.2'];
        $this->assertEquals(16, FormUtils::getGDVersion(false));

        $GLOBALS['mock_gd_info'] = ['GD Version' => '1.62'];
        $this->assertEquals(1, FormUtils::getGDVersion(false));


        unset($GLOBALS['mock_function_exists']);
        unset($GLOBALS['mock_gd_info']);
        $GLOBALS['mock_function_not_exists'] = 'gd_info';

        // test when phpinfo is disabled
        $GLOBALS['mock_ini_get']['disable_functions'] = 'phpinfo';
        $this->assertEquals(1, FormUtils::getGDVersion(false));

        unset($GLOBALS['mock_ini_get']);

        // test the phpinfo
        $GLOBALS['mock_php_info'] =
            'other extension' . PHP_EOL .
            'lot of spects here.' . PHP_EOL .
            PHP_EOL .
            'gd' . PHP_EOL .
            'gd version 2.1.0' . PHP_EOL;

        $this->assertEquals(2, FormUtils::getGDVersion(false));
    }

    public function testSizeToBytes()
    {
        $tests = [
            '1024b' => '1024',
            '1B' => '1',
            '1kb' => '1024',
            '21k' => '21504',
            '5m' => '5242880',
            '5M' => '5242880',
            '1G' => '1073741824',
            '4g' => '4294967296',
            '1.4mb' => '1468006'
        ];

        foreach ($tests as $size => $expected) {
            $this->assertEquals(
                $expected,
                FormUtils::sizeToBytes($size),
                'Convert ' . $size . ' to bytes. We expect: ' . $expected
            );
        }

        // test incorrect string given
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/incorrect size given/');

        FormUtils::sizeToBytes('wrong');
    }

    public function testMaxUploadSize()
    {
        // disable file uploads
        $GLOBALS['mock_ini_get']['file_uploads'] = 0;

        $this->assertEquals(0, FormUtils::getMaxUploadSize());

        unset($GLOBALS['mock_ini_get']);

        $GLOBALS['mock_ini_get']['upload_max_filesize'] = '5m';
        $GLOBALS['mock_ini_get']['post_max_size'] = '2m';

        $this->assertEquals((2 * 1024 * 1024), FormUtils::getMaxUploadSize());


        $GLOBALS['mock_ini_get']['upload_max_filesize'] = '2kb';
        $GLOBALS['mock_ini_get']['post_max_size'] = '2m';

        $this->assertEquals((2 * 1024), FormUtils::getMaxUploadSize());


        $GLOBALS['mock_ini_get']['upload_max_filesize'] = '2q'; // wrong
        $GLOBALS['mock_ini_get']['post_max_size'] = '2m';

        $this->assertEquals((2 * 1024 * 1024), FormUtils::getMaxUploadSize());

        $GLOBALS['mock_ini_get']['upload_max_filesize'] = '2m';
        $GLOBALS['mock_ini_get']['post_max_size'] = 'left'; // wrong

        $this->assertEquals((2 * 1024 * 1024), FormUtils::getMaxUploadSize());
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

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        @mkdir(__DIR__ . '/_tmp');
        @touch(__DIR__ . '/_tmp/test.pdf');

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

    protected function tearDown()
    {
        foreach ($GLOBALS as $key => $value) {
            if (substr($key, 0, 5) == 'mock_') {
                unset($GLOBALS[$key]);
            }
        }
        @unlink(__DIR__ . '/_tmp/moved.pdf');
        @unlink(__DIR__ . '/_tmp/test.pdf');
        @rmdir(__DIR__ . '/_tmp');
        @rmdir(__DIR__ . '/_new');
        $_GET = [];
    }
}
