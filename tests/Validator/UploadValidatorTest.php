<?php
// @codingStandardsIgnoreStart
namespace FormHandler\Validator {

    function filesize($file)
    {
        if ($file == __DIR__ . '/_tmp/test.pdf') {
            return 542;
        } else {
            return \filesize($file);
        }
    }

}

namespace FormHandler\Tests\Validator {

    use FormHandler\Form;
    use FormHandler\Validator\UploadValidator;
    use PHPUnit\Framework\TestCase;


    class UploadValidatorTest extends TestCase
    {
        public function testUpload()
        {
            $form = new Form();
            $field = $form->uploadField('cv');

            $validator = new UploadValidator(true);
            $validator->setMaxFilesize(1024 * 1024);
            $validator->setAllowedExtensions(['pdf']);

            $field->setValidator($validator);

            $valid = $form->isValid();
            var_dump($field->getErrorMessages());
            $this->assertTrue(
                $valid,
                'File should be valid, good extension and not too large'
            );


        }

        /**
         * Sets up the fixture, for example, opens a network connection.
         * This method is called before a test is executed.
         */
        protected function setUp()
        {
            mkdir(__DIR__ . '/_tmp');

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
            @unlink(__DIR__ . '/_tmp/test.pdf');
            @rmdir(__DIR__ . '/_tmp');
        }
    }
}
// @codingStandardsIgnoreEnd