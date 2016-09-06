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
     * @param boolean $createDestinationIfNotExist Create the $destination path if not exists or not.
     *                                              You can also give a umask here (like 644).
     * @return string                               The destination of the new file or null on an error.
     *                                              When multiple files are uploaded, this will be an array
     *
     * @throws \UnexpectedValueException
     * @throws \Exception
     */
    public static function moveUploadedFile(
        UploadField &$field,
        $destination,
        $existMode = FormUtils::MODE_RENAME,
        $createDestinationIfNotExist = false
    ) {

        $filedata = $field->getValue();

        // is multiple file uploads enabled?
        if ($field->getMultiple()) {
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
            $filedata['name'] = array($filedata['name']);
        }

        $originalDestination = $destination;

        $result = array();

        // walk all uploaded files
        foreach ($filedata['name'] as $index => $filename) {
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
                    else {
                        if ($existMode != FormUtils::MODE_OVERWRITE) {
                            throw new \UnexpectedValueException(
                                'Incorrect "exists" mode given! You have to use one of the ' .
                                'MODE constants of the FormUtils as mode!'
                            );
                        }
                    }
                }
            }

            $dirname = dirname($destination);
            // should we create the destination path if not exists?
            if ($createDestinationIfNotExist) {
                if (!is_dir($dirname) &&
                    !mkdir(
                        $dirname,
                        is_bool($createDestinationIfNotExist) ? 0777 : $createDestinationIfNotExist,
                        true
                    )
                ) {
                    throw new \Exception(sprintf(
                        'Failed to create the destination directory "%s"',
                        $dirname
                    ));
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

            if (is_array($filedata['tmp_name'])) {
                // move the file
                if (move_uploaded_file($filedata['tmp_name'][$index], $destination)) {
                    $result[$index] = $destination;
                }
            } // not an array (e.g. not multiple file uploads)
            else {
                // move the file
                if (move_uploaded_file($filedata['tmp_name'], $destination)) {
                    return $destination;
                }
            }
        }

        return $result;
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

        $extra = "";
        $i = 1;

        while (file_exists($dir . DIRECTORY_SEPARATOR . $file . $extra . $ext)) {
            $extra = '(' . $i++ . ')';
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
     * Merge two images together.
     *
     * This function merges an image on top of the given $original image. This is usually done to add a
     * stamp, logo or watermark to the image.
     *
     * $original is the source where the $stamp should be added on top. Only gif, jpg and png files are supported.
     * Be aware: The original file will be changed!
     *
     * $stamp is the image which will be added on top of the original. Only gif, jpg and png files are supported.
     *
     * With the $align and the $verticalAlign params you can set the position where the $stamp image should be placed
     * in the original image. You can set the position in pixels, percents or using a keyword like:
     * - bottom
     * - top
     * - center
     * - left
     * - right
     * - middle
     *
     * If you want a specific color transparant, then you should set the color code
     * in the $transparant param. This can either be an array with r, g and b, values,
     * or a hexadecimal value like #ff99ee.
     * Please note that transparancy only works for PNG24 files.
     * {@see http://www.formhandler.net/topic/2848/MergeImage_with_transparencey.html#msg3053)
     *
     * Example code:
     * <code>
     * FormUtils::mergeImage(
     *       '/var/www/vhosts/mysite.com/images/myimage.jpg',
     *     '/var/www/vhosts/mysite.com/mystamp.png',
     *     'right',    # you can also use precentages (like "90%") or pixels (like "20" or "20px")
     *     'bottom',    # idem
     *     '#ff0000'    # replace the red color for transparant
     * );
     * </code>
     *
     * @param string $original
     * @param string $stamp
     * @param string|int $align
     * @param string|int $verticalAlign
     * @param array|string $transparant
     * @throws \Exception
     * @return void
     */
    public static function mergeImage($original, $stamp, $align, $verticalAlign, $transparant = null)
    {
        // check if the source exists
        if (!is_file($original) || !($orgSize = getimagesize($original))) {
            throw new \Exception(sprintf(
                'Could not find or read the original image for merging: %s',
                $original
            ));
        }

        // check if the stamp exists
        if (!is_file($stamp) || !($stampSize = getimagesize($stamp))) {
            throw new \Exception(sprintf(
                'Could not find or read the stamp image for merging: %s',
                $stamp
            ));
        }

        if (!function_exists('imagecopyresampled')) {
            throw new \Exception(
                'The required function "imagecopyresampled" does not exists!'
            );
        }

        // make an rgb color of the given color
        if ($transparant) {
            if (!is_array($transparant)) {
                if (substr($transparant, 0, 1) == '#') {
                    $transparant = substr($transparant, 1);
                }

                if (strlen($transparant) == 6) {
                    $transparant = array(
                        hexdec($transparant[0] . $transparant[1]),
                        hexdec($transparant[2] . $transparant[3]),
                        hexdec($transparant[4] . $transparant[5])
                    );
                } elseif (strlen($transparant) == 3) {
                    $transparant = array(
                        hexdec($transparant[0] . $transparant[0]),
                        hexdec($transparant[1] . $transparant[1]),
                        hexdec($transparant[2] . $transparant[2])
                    );
                }
            }
        }

        $ext = FormUtils::getFileExtension($original);

        // Open the current file (get the resource )
        $imageSource = FormUtils::openImage($original, $ext);

        // create the "new" file recourse with the size of the original image
        $merged = imagecreatetruecolor($orgSize[0], $orgSize[1]);

        // Open the stamp image
        $stampSource = FormUtils::openImage($stamp);

        // Transparant color...
        if (is_array($transparant) && sizeof($transparant) >= 3) {
            $color = imagecolorallocate($stampSource, $transparant[0], $transparant[1], $transparant[2]);
            imagecolortransparent($stampSource, $color);
        }

        // Copy the current file to the new one
        imagecopy($merged, $imageSource, 0, 0, 0, 0, $orgSize[0], $orgSize[1]);
        imagealphablending($merged, true); //allows us to apply a 24-bit watermark over $image
        imagedestroy($imageSource); // close the original one, not needed anymore

        // retrieve the new position for the stamp
        $posX = FormUtils::getPosition($orgSize[0], $stampSize[0], $align);
        $posY = FormUtils::getPosition($orgSize[1], $stampSize[1], $verticalAlign);

        // copy the stamp to the new image
        // we do NOT use imagecopymerge here because transparancy in a PNG file is not copied along.
        //imagecopymerge( $merged, $stampSource, $posX, $posY, 0, 0, $stampSize[0], $stampSize[1], 100 );
        imagecopy($merged, $stampSource, $posX, $posY, 0, 0, $stampSize[0], $stampSize[1]);

        // save the image (overwrite the original file)
        FormUtils::closeImage($ext, $merged, $original, 100);

        // close the resources
        imagedestroy($stampSource);
        imagedestroy($merged);
    }

    /**
     * Open an image based on it's extension
     * @param string $file
     * @param string $ext
     * @return resource
     * @throws \Exception
     */
    protected static function openImage($file, $ext = null)
    {
        if ($ext == null) {
            $ext = FormUtils::getFileExtension($file);
        }

        // get the new image instance
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $image = @imagecreatefromjpeg($file);
            if (!$image) {
                throw new \Exception('Failed to open JPG. Maybe the file is not a JPG after all?');
            }
        } elseif ($ext == 'png') {
            if (!FormUtils::isPngFile($file)) {
                throw new \Exception('The PNG file seems to be invalid!');
            }

            $image = @imagecreatefrompng($file);
            if (!$image) {
                throw new \Exception('Failed to open PNG. Maybe the file is not a PNG after all?');
            }
        } elseif ($ext == 'gif') {
            if (!function_exists('imagecreatefromgif')) {
                throw new \Exception(
                    'GIF images can not be resized because the function "imagecreatefromgif" is not available.'
                );
            }
            $image = @imagecreatefromgif($file);
            if (!$image) {
                throw new \Exception('Failed to open GIF. Maybe the file is not a GIF after all?');
            }
        } else {
            throw new \Exception(
                'Only images with the following extension are allowed: jpg, jpeg, png, gif'
            );
        }

        return $image;
    }

    /**
     * Check if a file is a PNG file. Does not depend on the file's extension
     *
     * @param string $filename Full file path
     * @return boolean
     */
    public static function isPngFile($filename)
    {
        // check if the file exists
        if (!file_exists($filename)) {
            return false;
        }

        // define the array of first 8 png bytes
        $pngHeader = array(137, 80, 78, 71, 13, 10, 26, 10);
        // or: array(0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A);

        // open file for reading
        $f = fopen($filename, 'r');

        // read first 8 bytes from the file and close the resource
        $header = fread($f, 8);
        fclose($f);

        // convert the string to an array
        $chars = preg_split('//', $header, -1, PREG_SPLIT_NO_EMPTY);

        // convert each charater to its ascii value
        $chars = array_map('ord', $chars);

        // return true if there are no differences or false otherwise
        return (count(array_diff($pngHeader, $chars)) === 0);
    }

    /**
     * Get the position of the stamp image based on the original size image
     *
     * @param int $size : the size of the image (width of height)
     * @param int $stampSize : the size of the stamp (width of height)
     * @param string $where : position where to put the stamp on the image
     * @return int
     */
    protected static function getPosition($size, $stampSize, $where)
    {
        // percentage ?
        if (strpos($where, '%') !== false) {
            $percent = str_replace('%', '', $where);
            $part = $size / 100;
            $x = ceil($percent * $part);
        } else {
            if (is_numeric(str_replace('px', '', strtolower($where)))) {
                $x = $where;
            } else {
                // get the pos for the copyright stamp
                switch (strtolower($where)) {
                    case 'top':
                    case 'left':
                        $x = 0;
                        break;
                    case 'middle':
                    case 'center':
                        $x = ceil($size / 2) - ceil($stampSize / 2);
                        break;
                    case 'bottom':
                    case 'right':
                        $x = $size - $stampSize;
                        break;
                    default:
                        $x = 0;
                }
            }
        }

        return $x;
    }

    /**
     * Close an image based on its extension
     *
     * @param string $ext The image extension without leading dot.
     * @param resource $image
     * @param string $destination
     * @param int $quality
     * @return bool
     * @throws \Exception
     */
    protected static function closeImage($ext, $image, $destination, $quality = 80)
    {
        if ($ext == 'jpg' || $ext == 'jpeg') {
            return imagejpeg($image, $destination, $quality);
        } elseif ($ext == 'png') {
            return imagepng($image, $destination);
        } elseif ($ext == 'gif' && function_exists('imagegif')) {
            return imagegif($image, $destination);
        } else {
            throw new \Exception(
                'Only images with the following extension are allowed: jpg, jpeg, png, gif'
            );
        }
    }

    /**
     * This is a function to both resize and crop images.
     * Note: this function has only been minimally tested, and recursively calls itself.
     * It should not be used for any serious use-case yet, but is a start.
     *
     * @param string $original
     * @param string $destination
     * @param integer $targetX
     * @param integer $targetY
     * @param integer $quality
     * @return void
     * @throws \Exception
     */
    public static function resizeAndCropImage($original, $destination, $targetX, $targetY, $quality = 80)
    {
        if (!$destination) {
            $destination = $original;
        }

        // check if the source exists
        if (!is_file($original) || !($size = getimagesize($original))) {
            throw new \Exception(sprintf(
                'Could not find or read the original image for cropping: %s',
                $original
            ));
        }

        // get the original size
        list($orgWidth, $orgHeight) = $size;

        $aspectRatio = $orgWidth / $orgHeight;

        if (($orgWidth / $orgHeight) == ($targetX / $targetY)) {
            FormUtils::resizeImage($original, $destination, $targetX, $targetY, $quality);
            return;
        }

        $targetXResize = $targetX;
        $targetYResize = $orgHeight / $aspectRatio;

        $fromY = ($targetYResize / 2) - ($targetY / 2);

        FormUtils::resizeImage($original, $destination, $targetXResize, $targetYResize, 100);
        FormUtils::cropImage($destination, $destination, 0, $fromY, $targetX, $targetY, $quality);
    }

    /**
     * Resize an image by using GD.
     *
     * $source is the path to the source image file. Only jpg, gif and png files are allowed.
     * Gif images are only supported if the function "imagecreatefromgif" exists.
     *
     * $destination is the full path to the image how it should be saved. If the file exists,
     * it will be overwritten. If the destination ends with a slash (both are allowed), then the original
     * file name is kept and saved in the $destination directory.
     * The destination folder needs to exists!
     * If the destination file extension is different from the source,
     * the image will also be converted to the new file type.
     *
     * $newWidth and $newHeight are the new image size in pixels.
     * If both $newWidth and $newHeight are given, these will be used for the new image. If only one of both are given,
     * and $constrainProportions is set to true (default), then the other value will be calculated automatically
     * to constrain the proportions.
     *
     * $quality is the quality of the saved resized image if this is a JPG image.
     * For the other formats, this parameter is ignored.
     *
     * If $constrainProportions is set to false, the original size will be used for the missing size.
     * If both sizes are missing, the original will be used.
     *
     * Example:
     * <code>
     * FormUtils::resizeImage(
     *     'images/image.jpg',   // the original image
     *     'images/thumbs/',     // save the resized image in this dir, keep the original filename.
     *                           // If exists, it will be overwritten.
     *
     *     250,                  // make the new image 250 pixels width
     *     null,                 // no height given
     *     80,                   // quality to safe the image in (in percentage), default 80
     *     true                  // auto calculate the missing size so that the proportions are kept of the file?
     *                           // (default true)
     * );
     * </code>
     *
     * @param string $source
     * @param string $destination
     * @param int $newWidth
     * @param int $newHeight
     * @param int $quality
     * @param boolean $constrainProportions
     * @return string The location where the image was saved
     * @throws \Exception
     */
    public static function resizeImage(
        $source,
        $destination = null,
        $newWidth = null,
        $newHeight = null,
        $quality = 80,
        $constrainProportions = true
    ) {

        // check if the source exists
        if (!is_file($source) || !($size = getimagesize($source))) {
            throw new \Exception(sprintf('Could not find or read the file to resize: %s'), $source);
        }

        // no destination given? Then overwrite the original one!
        if (!$destination) {
            $destination = $source;
        }

        // get the original size
        list($orgWidth, $orgHeight) = $size;
        // store the requested size
        $myNewWidth = $newWidth ? $newWidth : $orgWidth;
        $myNewHeight = $newHeight ? $newHeight : $orgHeight;

        // should we keep the proportions?
        if ($constrainProportions) {
            // both sizes are given? Then only use the size which is the largest of the original image.
            if (!($newWidth xor $newHeight)) {
                if ($orgWidth > $orgHeight) {
                    $newHeight = null;
                } else {
                    $newWidth = null;
                }
            }

            if ($newWidth) {
                $newHeight = ($newWidth / ($orgWidth / 100)) * ($orgHeight / 100);
                // Check again if the images size is not out of proportion
                if ($newHeight > $myNewHeight) {
                    $newHeight = $myNewHeight;
                    $newWidth = ($newHeight / ($orgHeight / 100)) * ($orgWidth / 100);
                }
            } else {
                $newWidth = ($newHeight / ($orgHeight / 100)) * ($orgWidth / 100);
                // Check again if the images size is not out of proportion
                if ($newWidth > $myNewWidth) {
                    $newWidth = $myNewWidth;
                    $newHeight = ($newWidth / ($orgWidth / 100)) * ($orgHeight / 100);
                }
            }
        } // dont keep proportions
        else {
            if (!$newWidth) {
                $newWidth = $orgWidth;
            }

            if (!$newHeight) {
                $newHeight = $orgHeight;
            }
        }

        // add the original filename
        $lastChar = substr($destination, -1);
        if ($lastChar == '/' || $lastChar == '\\') {
            $destination .= basename($source);
        }

        $gdVersion = FormUtils::getGDVersion();
        if (!$gdVersion) {
            throw new \Exception('Could not resize image because GD is not installed!');
        }

        $ext = FormUtils::getFileExtension($source);
        $destExt = FormUtils::getFileExtension($destination);

        // open the image
        $image = FormUtils::openImage($source, $ext);

        // generate the new image
        if ($gdVersion >= 2) {
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $orgWidth, $orgHeight);
        } else {
            $resized = imagecreate($newWidth, $newHeight);
            imagecopyresized($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $orgWidth, $orgHeight);
        }

        // close the image
        $result = FormUtils::closeImage($destExt, $resized, $destination, $quality);

        // clean up
        imagedestroy($image);
        imagedestroy($resized);

        // quality assurance
        if (!$result) {
            throw new \Exception('Error while writing image file after resize process in FormUtils.');
        }

        return $destination;
    }

    /**
     * Return the (major) GD version installed.
     * For exmaple, this will return 2, but not 2.1.0.
     *
     * Returns 0 when no version installed.
     *
     * When GD functions are available but no version information could be fetched, we will assume
     * version 1 so that we are always safe.
     *
     * @param bool $cacheVersion - By default, we will analyse our environment and get the version once. After that,
     *                             we will remember the version. The second time when this function is called, we
     *                             will return that cached version. This can be unwanted behaviour for unit testing,
     *                             thus this argument allows you to disable this behaviour.
     * @return int
     */
    public static function getGDVersion($cacheVersion = true)
    {
        static $version = null;

        if ($version === null || !$cacheVersion) {
            if (!extension_loaded('gd')) {
                $version = 0;
                return $version;
            }

            // use the gd_info() function if possible.
            if (function_exists('gd_info')) {
                $info = gd_info();
                if (!empty($info['GD Version']) && preg_match('/\d+/', $info['GD Version'], $match)) {
                    $version = $match[0];
                    return $version;
                }
            }

            if (!preg_match('/phpinfo/', ini_get('disable_functions'))) {
                // ...otherwise use phpinfo().
                ob_start();
                phpinfo(INFO_MODULES);
                $info = ob_get_contents();
                ob_end_clean();

                $info = stristr($info, 'gd version');
                if ($info && preg_match('/\d+/', $info, $match)) {
                    $version = $match[0];
                    return $version;
                }
            }

            // When gf functions are available, but no version information could be fetched, then assume version 1
            $version = 1;
        }

        return $version;
    }

    /**
     * Crop an image.
     *
     * Example:
     * <code>
     * FormUtils::cropImage( 'path/to/file.jpg', '', 10, 10, 600, 600 );
     * </code>
     *
     * @param string $original The file which should be cropped. Supported formats are jpg, gif and png
     * @param string $destination The file where the cropped image should be saved in.
     *                              When an empty string is given, the original file is overwritten
     * @param int $x The x coordinate where we should start cutting
     * @param int $y The x coordinate where we should start cutting
     * @param int $width The width of the cut
     * @param int $height The height of the cut
     * @param int $quality
     * @return string Returns the full path to the destination file, or null if something went wrong.
     * @throws \Exception
     */
    public static function cropImage($original, $destination, $x, $y, $width, $height, $quality = 80)
    {
        if (!$destination) {
            $destination = $original;
        }

        // check if the source exists
        if (!is_file($original) || !($size = getimagesize($original))) {
            throw new \Exception(sprintf(
                'Could not find or read the original image for cropping: %s',
                $original
            ));
        }

        // check if gd is supported
        $gdVersion = FormUtils::getGDVersion();
        if (!$gdVersion) {
            throw new \Exception('Could not resize image because GD is not installed!');
        }

        $ext = FormUtils::getFileExtension($original);

        // open the image
        $image = FormUtils::openImage($original, $ext);

        // generate the new image
        if ($gdVersion >= 2) {
            $cropped = imagecreatetruecolor($width, $height);
            imagecopyresampled($cropped, $image, 0, 0, $x, $y, $width, $height, $width, $height);
        } else {
            $cropped = imagecreate($width, $height);
            imagecopyresized($cropped, $image, 0, 0, $x, $y, $width, $height, $width, $height);
        }

        // close the image
        FormUtils::closeImage($ext, $cropped, $destination, $quality);

        // clean up
        imagedestroy($image);
        imagedestroy($cropped);

        return $destination;
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
                return (int)($parts[1] * 1073741824);
            case 'm':
                return (int)($parts[1] * 1048576);
            case 'k':
                return (int)($parts[1] * 1024);
            case 'b':
            default:
                return (int)$parts[1];
        }
    }
}
