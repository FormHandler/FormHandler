<?php
namespace FormHandler\Utils;

use FormHandler\Field\UploadField;
use FormHandler\Form;

/**
 * This class contains some static helper methods which can be handy.
 *
 * @package form
 */
class FormUtils
{
    const MODE_RENAME = 1;
    const MODE_OVERWRITE = 2;
    const MODE_EXCEPTION = 3;

    /**
     * Add query string params as hidden field to the given form.
     * This can be handy if you want to pass along the data in a $_POST way.
     * The values will be HTML escaped properly, so you don't have to bother about that.
     *
     * @param Form $form
     * @param array $whitelist
     * @param array $blacklist
     * @return void
     */
    public static function queryStringToForm(Form &$form, array $whitelist = null, array $blacklist = null)
    {
        if (!empty($_GET)) {
            foreach ($_GET as $key => $value) {
                if (is_array($whitelist) && !in_array($key, $whitelist)) {
                    continue;
                }
                if (is_array($blacklist) && in_array($key, $blacklist)) {
                    continue;
                }

                $form->hiddenField($key)->setValue($value);
            }
        }
    }

    /**
     * Move the uploaded file of the given field to the given destination.
     *
     * If the destination ends with a slash (either "/" or "\"), the original name will be kept.
     * Example:
     * ```php
     * // here the original name will be kept
     * $newfile = FormUtils::moveUploadedFile( $field, '/var/www/vhosts/mysite.com/uploads/' );
     * ```
     *
     * Another example:
     * ```php
     * // here, the file will be saved with the name "image.jpg"
     * $newfile = FormUtils::moveUploadedFile( $field, '/var/www/vhosts/mysite.com/uploads/image.jpg' );
     * ```
     *
     * As third parameter, you can influence what happens if the file trying to write exists.
     * You can use the MODE_* constants for this.
     * Example:
     * ```php
     * // here the original name will be kept
     * // if it exists, it will be renamed
     * $newfile = FormUtils::moveUploadedFile(
     *     $field,
     *     '/var/www/vhosts/mysite.com/uploads/',
     *     FormUtils::MODE_RENAME
     *  );
     * ```
     *
     * As final parameter you can decide if the upload path should be created it it does not exists.
     * This is default disabled (false). Set to true to enable this functionality.
     * If you want to give the created directory a specific chmod, then enter this instead of true.
     * Example:
     * ```php
     * $newfile = FormUtils::moveUploadedFile(
     *     $field,                                    // the field
     *     '/var/www/vhosts/mysite.com/uploads/',     // directory where to save the file
     *     FormUtils::MODE_RENAME,                 // what to do if exists
     *     0644                                       // create destination dir if not exists with this mode
     * );
     * ```
     *
     * NOTE: Uploading multiple files using 1 uploadfield is supported, but obly if the given destination is a folder.
     * If using multiple file uploads, and $existMode is using MODE_EXCEPTION, then it could be that
     * the first two files are uploaded and the third one us causing an exception. This method will not "clean up" the
     * first two moved files!
     *
     * @param UploadField $field The field where the file was uploaded in
     * @param string $destination The destination where to save the file
     * @param int $existMode Mode what to do if the file exists. Default: rename
     * @param int|boolean $createDestIfNotExist Create the $destination path if not exists or not.
     * You can also give a umask here (like 644).
     * @return string|string[] The destination of the new file or null on an error.
     * When multiple files are uploaded, this will be an array of destinations
     *
     * @throws \UnexpectedValueException
     * @throws \Exception
     */
    public static function moveUploadedFile(
        UploadField &$field,
        $destination,
        $existMode = FormUtils::MODE_RENAME,
        $createDestIfNotExist = false
    ) {


        $filedata = $field->getValue();

        // is multiple file uploads enabled?
        if ($field->isMultiple()) {
            // not ending with a slash?
            $lastChar = substr($destination, -1);
            if (!($lastChar == '/' || $lastChar == '\\')) {
                throw new \Exception(
                    'You have given a destination filename. This uploadfield allows ' .
                    'multiple files to be uploaded. We can\'t handle that!'
                );
            }
        }

        // to walk "something", make an array of the name, even if we are not using multiple file uploads.
        if (!is_array($filedata['name'])) {
            $filedata['name'] = [$filedata['name']];
            $filedata['tmp_name'] = [$filedata['tmp_name']];
        }

        $originalDestination = $destination;

        $result = [];

        // walk all uploaded files
        foreach ($filedata['name'] as $index => $filename) {
            $tmpName = $filedata['tmp_name'][$index];

            // keep the original filename if wanted
            $lastChar = substr($originalDestination, -1);
            if ($lastChar == '/' || $lastChar == '\\') {
                $destination = $originalDestination . $filename;
            } else {
                $destination = $originalDestination;
            }

            // if the file exists...
            if (file_exists($destination)) {
                // throw exception wanted ?
                if ($existMode == FormUtils::MODE_EXCEPTION) {
                    throw new \Exception(sprintf(
                        'Could not upload the file "%s" to destination "%s" because ' .
                        'a file with this name already exists in this folder!',
                        $filename,
                        $destination
                    ));
                } // should we rename the file...
                else {
                    if ($existMode == FormUtils::MODE_RENAME) {
                        $destination = FormUtils::getNonExistingFilename($destination);
                    } // a different unkown mode is given, throw exception
                    elseif ($existMode != FormUtils::MODE_OVERWRITE) {
                        throw new \UnexpectedValueException(
                            'Incorrect "exists" mode given! You have to use one of the ' .
                            'MODE constants of the FormUtils as mode!'
                        );
                    }
                }
            }

            $dirname = dirname($destination);
            // should we create the destination path if not exists?
            if ($createDestIfNotExist) {
                if (!is_dir($dirname) &&
                    !mkdir(
                        $dirname,
                        is_bool($createDestIfNotExist) ? 0755 : $createDestIfNotExist,
                        true
                    )
                ) {
                    throw new \Exception(sprintf('Failed to create the destination directory "%s"', $dirname));
                }
            }

            if (!is_writable($dirname)) {
                throw new \Exception(
                    sprintf(
                        'Failed to move uploaded file because the destination ' .
                        'directory is not writable! Directory: "%s"',
                        $dirname
                    )
                );
            }

            // move the file
            if (move_uploaded_file($tmpName, $destination)) {
                $result[] = $destination;
            } else {
                throw new \Exception(sprintf(
                    'Error, we failed to move file "%s" to destination "%s"',
                    $tmpName,
                    $destination
                ));
            }
        }

        return $field->isMultiple() ? $result : $result[0];
    }

    /**
     * This function takes a path to a file. If the file exists,
     * the filename will be altered and a digit will be added to make the
     * filename unique (non-existing).
     *
     *  So, if /tmp/image.jpg exists, it becomes /tmp/image(1).jpg.
     *
     * @param string $destination
     * @return string
     */
    public static function getNonExistingFilename($destination)
    {
        // find a unique name
        $dir = dirname($destination);
        $file = basename($destination);
        $ext = FormUtils::getFileExtension($file);
        if ($ext) {
            $ext = '.' . $ext;
            $file = substr($file, 0, 0 - strlen($ext));
        }

        $extra = '';
        $index = 1;

        while (file_exists($dir . DIRECTORY_SEPARATOR . $file . $extra . $ext)) {
            $extra = '(' . $index++ . ')';
        }

        return $dir . DIRECTORY_SEPARATOR . $file . $extra . $ext;
    }

    /**
     * Return the extension of a filename, or null if no extension could be found.
     * The extension is lower-cased and does NOT contain a leading dot.
     * When no extension can be found, we will return an empty string.
     *
     * @param string $filename
     * @return string
     */
    public static function getFileExtension($filename)
    {
        $filename = basename($filename);

        // remove possible query string
        $pos = strpos($filename, '?');
        if ($pos !== false) {
            $filename = substr($filename, 0, $pos);
        }

        // retrieve the extension
        $pos = strrpos($filename, '.');
        if ($pos !== false) {
            return strtolower(substr($filename, $pos + 1));
        }

        return '';
    }

    /**
     * Return the max upload size in bytes
     * @return int
     */
    public static function getMaxUploadSize()
    {
        if (!ini_get('file_uploads')) {
            return 0;
        }

        $max = 0;

        try {
            $max = FormUtils::sizeToBytes(ini_get('upload_max_filesize'));
        } catch (\Exception $e) {
        }

        try {
            $max2 = FormUtils::sizeToBytes(ini_get('post_max_size'));
            if ($max2 < $max || $max == 0) {
                $max = $max2;
            }
        } catch (\Exception $e) {
        }

        return $max;
    }

    /**
     * Make a size like 2M to bytes
     *
     * Some examples:
     * '1024b' => '1024',
     * '1B' => '1',
     * '1kb' => '1024',
     * '21k' => '21504',
     * '5m' => '5242880',
     * '5M' => '5242880',
     * '1G' => '1073741824',
     * '4g' => '4294967296',
     * '1.4mb' => '1468006'
     *
     * @param string $str
     * @return int
     * @throws \Exception
     */
    public static function sizeToBytes($str)
    {
        if (!preg_match('/^(\d+(\.\d+)?)([bkmg]*)$/i', trim($str), $parts)) {
            throw new \Exception('Failed to convert string to bytes, incorrect size given: ' . $str);
        }

        switch (substr(strtolower($parts[3]), 0, 1)) {
            case 'g':
                $size = (int)($parts[1] * 1073741824);
                break;
            case 'm':
                $size = (int)($parts[1] * 1048576);
                break;
            case 'k':
                $size = (int)($parts[1] * 1024);
                break;
            case 'b':
            default:
                $size = (int)$parts[1];
                break;
        }

        return $size;
    }
}
