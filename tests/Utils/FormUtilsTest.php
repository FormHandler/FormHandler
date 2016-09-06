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

    public function testMoveUploadedFile()
    {
    }

    public function testGetNonExistingFilename()
    {
        // setup
        $dir = dirname(__DIR__) . '/_test';

        if (!is_dir($dir)) {
            mkdir($dir);
        }

        // create existing files
        @touch($dir . '/test.pdf');
        @touch($dir . '/test(1).pdf');

        $filename = $dir . '/test.pdf';

        $newfile = FormUtils::getNonExistingFilename($filename);

        $this->assertEquals($newfile, $dir . '/test(2).pdf');

        // tear down
        @unlink($dir . '/test.pdf');
        @unlink($dir . '/test(1).pdf');
        @rmdir($dir);
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
                'Convert ' . $size .' to bytes. We expect: '. $expected
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

        $this -> assertEquals(0, FormUtils::getMaxUploadSize());

        unset($GLOBALS['mock_ini_get']);

        $GLOBALS['mock_ini_get']['upload_max_filesize'] = '5m';
        $GLOBALS['mock_ini_get']['post_max_size'] = '2m';

        $this -> assertEquals((2 * 1024 * 1024 ), FormUtils::getMaxUploadSize());


        $GLOBALS['mock_ini_get']['upload_max_filesize'] = '2kb';
        $GLOBALS['mock_ini_get']['post_max_size'] = '2m';

        $this -> assertEquals((2 * 1024 ), FormUtils::getMaxUploadSize());


        $GLOBALS['mock_ini_get']['upload_max_filesize'] = '2q'; // wrong
        $GLOBALS['mock_ini_get']['post_max_size'] = '2m';

        $this -> assertEquals((2 * 1024 * 1024 ), FormUtils::getMaxUploadSize());

        $GLOBALS['mock_ini_get']['upload_max_filesize'] = '2m';
        $GLOBALS['mock_ini_get']['post_max_size'] = 'left'; // wrong

        $this -> assertEquals((2 * 1024 * 1024 ), FormUtils::getMaxUploadSize());
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
    }
}
