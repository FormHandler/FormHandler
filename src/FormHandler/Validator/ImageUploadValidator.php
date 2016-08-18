<?php
namespace FormHandler\Validator;

/**
 * Image Upload validator, will validate an uploaded image.
 *
 * This validator adds some image specific validation functionality to the
 * upload validator
 *
 * @see UploadValidator
 */
class ImageUploadValidator extends UploadValidator
{

    protected $allow_aspect_ratio_x;

    protected $allow_aspect_ratio_y;

    protected $deny_aspect_ratio_x;

    protected $deny_aspect_ratio_y;

    protected $minimum_width;

    protected $minimum_height;

    protected $maximum_width;

    protected $maximum_height;

    /**
     * Set the minimum proportions of the uploaded image.
     *
     * If the width of height is smaller than the given values, the upload will be marked as invalid.
     * If you give a 0 or null value, the value will be ignored.
     *
     * @param int $width
     * @param int $height
     */
    public function setMinimumProportions($width = null, $height = null)
    {
        $this->minimum_width = $width;
        $this->minimum_height = $height;
    }

    /**
     * Set the maximum proportions of the uploaded image.
     *
     * If the width of height is bigger than the given values, the upload will be marked as invalid.
     * If you give a 0 or null value, the value will be ignored.
     *
     * @param int $width
     * @param int $height
     */
    public function setMaximumProportions($width = null, $height = null)
    {
        $this->maximum_width = $width;
        $this->maximum_height = $height;
    }

    /**
     * Set the aspect ratio.
     * By setting these,
     * only images with this aspect ratio can be uploaded.
     */
    public function setAllowAspectRatio($x, $y)
    {
        $this->allow_aspect_ratio_x = (int) $x;
        $this->allow_aspect_ratio_y = (int) $y;
    }

    /**
     * Set the "denied" aspect ratio.
     * By setting these,
     * images with this aspect ratio are denied for uploading.
     */
    public function setDenyAspectRatio($x, $y)
    {
        $this->deny_aspect_ratio_x = (int) $x;
        $this->deny_aspect_ratio_y = (int) $y;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid()
    {
        /**
         * First, check if the upload is valid or not
         */
        if (! parent::isValid()) {
            return false;
        }

        $value = $this->field->getValue();

        if (! $value || ! isset($value['error']) || $value['error'] == UPLOAD_ERR_NO_FILE) {
            // If here, and nothing is uploaded, than the field is also not required.
            // Just validate the field as valid
            return true;
        }

        // if here, we know that the field is uploaded.

        // check if it's an image, and get it's size
        $size = @getimagesize($value['tmp_name']);
        if ($size === false) {
            $this->setErrorMessage(dgettext('d2frame', 'It seems that the uploaded file is not an image. Please upload a valid image file.'));
            return false;
        }
        list ($width, $height) = $size;

        /**
         * Allow aspect ratio given?
         */
        if ($this->allow_aspect_ratio_x && $this->allow_aspect_ratio_y) {
            $gcd = $this->gcd($width, $height);
            $x = $width / $gcd;
            $y = $height / $gcd;

            if ($x != $this->allow_aspect_ratio_x || $y != $this->allow_aspect_ratio_y) {
                $this->setErrorMessage(sprintf(dgettext('d2frame', 'The aspect ratio of the uploaded file (%s) is not the same as the required aspect ratio (%s).'), $x . ':' . $y, $this->allow_aspect_ratio_x . ':' . $this->allow_aspect_ratio_y));
                return false;
            }
        }

        /**
         * Deny aspect ratio given?
         */
        if ($this->deny_aspect_ratio_x && $this->deny_aspect_ratio_y) {
            $gcd = $this->gcd($width, $height);
            $x = $width / $gcd;
            $y = $height / $gcd;

            if ($x == $this->deny_aspect_ratio_x && $y == $this->deny_aspect_ratio_y) {
                $this->setErrorMessage(sprintf(dgettext('d2frame', 'The aspect ratio of the uploaded file (%s) is not allowed.'), $x . ':' . $y));
                return false;
            }
        }

        /**
         * Validate the proportions
         */

        if ($this->maximum_height && $height > $this->maximum_height) {
            $this->setErrorMessage(sprintf(dgettext('d2frame', 'The height of the uploaded image (%spx) is larger than the maximum allowed height (%spx)'), $height, $this->maximum_height));
            return false;
        }

        if ($this->maximum_width && $width > $this->maximum_width) {
            $this->setErrorMessage(sprintf(dgettext('d2frame', 'The width of the uploaded image (%spx) is larger than the maximum allowed width (%spx)'), $width, $this->maximum_width));
            return false;
        }

        if ($this->minimum_height && $height < $this->minimum_height) {
            $this->setErrorMessage(sprintf(dgettext('d2frame', 'The height of the uploaded image (%spx) is smaller than the minimum allowed height (%spx)'), $height, $this->minimum_height));
            return false;
        }

        if ($this->minimum_width && $width < $this->minimum_width) {
            $this->setErrorMessage(sprintf(dgettext('d2frame', 'The width of the uploaded image (%spx) is smaller than the minimum allowed width (%spx)'), $width, $this->minimum_width));
            return false;
        }

        // if here, everything is ok!
        return true;
    }

    /**
     * Calculate the greatest common divisor
     *
     * @return int
     */
    protected function gcd($a, $b)
    {
        while ($b != 0) {
            $remainder = $a % $b;
            $a = $b;
            $b = $remainder;
        }
        return abs($a);
    }
}