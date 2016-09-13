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
    /**
     * Only allow files with this X aspect ratio
     * @var float
     */
    protected $allowAspectRatioX;

    /**
     * Only allow files with this Y aspect ratio
     * @var float
     */
    protected $allowAspectRatioY;

    /**
     * Deny files with this X aspect ratio
     * @var float
     */
    protected $denyAspectRatioX;

    /**
     * Deny files with this Y aspect ratio
     * @var float
     */
    protected $denyAspectRatioY;

    /**
     * Set the minimum required width of the image.
     * @var float
     */
    protected $minimumWidth;

    /**
     * Set the minimum required height of the image.
     * @var float
     */
    protected $minimumHeight;

    /**
     * Set the maximum width of the image.
     * @var float
     */
    protected $maximumWidth;

    /**
     * Set the maximum height of the image.
     * @var float
     */
    protected $maximumHeight;

    /**
     * ImageUploadValidator constructor.
     * @param bool $required
     * @param array $messages
     */
    public function __construct($required, array $messages = [])
    {
        $default = [
            'not_an_image' =>
                'It seems that the uploaded file is not an image. Please upload a valid image file.',
            'aspect_ratio' =>
                'The aspect ratio of the uploaded file (%s) is not the same as the required aspect ratio (%s).',
            'aspect_ratio_denied' =>
                'The aspect ratio of the uploaded file (%s) is not allowed.',
            'size_height_max' =>
                'The height of the uploaded image (%spx) is larger than the maximum allowed height (%spx)',
            'size_height_min' =>
                'The height of the uploaded image (%spx) is smaller than the minimum allowed height (%spx)',
            'size_width_max' =>
                'The width of the uploaded image (%spx) is larger than the maximum allowed width (%spx)',
            'size_width_min' =>
                'The width of the uploaded image (%spx) is smaller than the minimum allowed width (%spx)',
        ];

        if (sizeof($messages) > 0) {
            $default = array_merge($default, $messages);
        }

        parent::__construct($required, $default);
    }

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
        $this->minimumWidth = $width;
        $this->minimumHeight = $height;
    }

    /**
     * Set the maximum proportions of the uploaded image.
     *
     * If the width of height is bigger than the given values, the upload will be marked as invalid.
     * If you give a 0 or null value, the value will be ignored.
     *
     * @param int $width
     * @param int $height
     * @return ImageUploadValidator
     */
    public function setMaximumProportions($width = null, $height = null)
    {
        $this->maximumWidth = $width;
        $this->maximumHeight = $height;
        return $this;
    }

    /**
     * Set the aspect ratio.
     * By setting these,
     * only images with this aspect ratio can be uploaded.
     * @param int $x
     * @param int $y
     * @return ImageUploadValidator
     */
    public function setAllowAspectRatio($x, $y)
    {
        $this->allowAspectRatioX = (int)$x;
        $this->allowAspectRatioY = (int)$y;

        return $this;
    }

    /**
     * Set the "denied" aspect ratio.
     * By setting these,
     * images with this aspect ratio are denied for uploading.
     * @param $x
     * @param $y
     * @return ImageUploadValidator
     */
    public function setDenyAspectRatio($x, $y)
    {
        $this->denyAspectRatioX = (int)$x;
        $this->denyAspectRatioY = (int)$y;

        return $this;
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
        if (!parent::isValid()) {
            return false;
        }

        $value = $this->field->getValue();

        // if here, we know that the field is uploaded.

        // check if it's an image, and get it's size
        $size = @getimagesize($value['tmp_name']);
        if ($size === false) {
            $this->setErrorMessage($this->messages['not_an_image']);
            return false;
        }
        list ($width, $height) = $size;

        /**
         * Validate the aspect ratio.
         */
        if (!$this->validateAspectRatio($width, $height)) {
            return false;
        }


        /**
         * Validate the proportions
         */
        if (!$this->validateSize($width, $height)) {
            return false;
        }

        // if here, everything is ok!
        return true;
    }

    /**
     * Validate the aspect ratio. If the size is invalid, we will also push an error message with setErrorMessage.
     * @param $width
     * @param $height
     * @return bool
     */
    protected function validateAspectRatio($width, $height)
    {
        /**
         * Allow aspect ratio given?
         */
        if ($this->allowAspectRatioX && $this->allowAspectRatioY) {
            $gcd = $this->gcd($width, $height);
            $x = $width / $gcd;
            $y = $height / $gcd;

            if ($x != $this->allowAspectRatioX || $y != $this->allowAspectRatioY) {
                $this->setErrorMessage(sprintf(
                    $this->messages['aspect_ratio'],
                    $x . ':' . $y,
                    $this->allowAspectRatioX . ':' . $this->allowAspectRatioY
                ));
                return false;
            }
        }

        /**
         * Deny aspect ratio given?
         */
        if ($this->denyAspectRatioX && $this->denyAspectRatioY) {
            $gcd = $this->gcd($width, $height);
            $x = $width / $gcd;
            $y = $height / $gcd;

            if ($x == $this->denyAspectRatioX && $y == $this->denyAspectRatioY) {
                $this->setErrorMessage(sprintf(
                    $this->messages['aspect_ratio_denied'],
                    $x . ':' . $y
                ));
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate the greatest common divisor
     *
     * @param $a
     * @param $b
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

    /**
     * Validate the size. If the size is invalid, we will also push an error message with setErrorMessage.
     * @param int $width
     * @param int $height
     * @return bool
     */
    protected function validateSize($width, $height)
    {
        if ($this->maximumHeight && $height > $this->maximumHeight) {
            $this->setErrorMessage(sprintf(
                $this->messages['size_height_max'],
                $height,
                $this->maximumHeight
            ));
            return false;
        }

        if ($this->maximumWidth && $width > $this->maximumWidth) {
            $this->setErrorMessage(sprintf(
                $this->messages['size_width_max'],
                $width,
                $this->maximumWidth
            ));
            return false;
        }

        if ($this->minimumHeight && $height < $this->minimumHeight) {
            $this->setErrorMessage(sprintf(
                $this->messages['size_height_min'],
                $height,
                $this->minimumHeight
            ));
            return false;
        }

        if ($this->minimumWidth && $width < $this->minimumWidth) {
            $this->setErrorMessage(sprintf(
                $this->messages['size_width_min'],
                $width,
                $this->minimumWidth
            ));
            return false;
        }

        return true;
    }

    /**
     * @return float
     */
    public function getMinimumWidth()
    {
        return $this->minimumWidth;
    }

    /**
     * @param float $minimumWidth
     * @return ImageUploadValidator
     */
    public function setMinimumWidth($minimumWidth)
    {
        $this->minimumWidth = $minimumWidth;
        return $this;
    }

    /**
     * @return float
     */
    public function getMinimumHeight()
    {
        return $this->minimumHeight;
    }

    /**
     * @param float $minimumHeight
     * @return ImageUploadValidator
     */
    public function setMinimumHeight($minimumHeight)
    {
        $this->minimumHeight = $minimumHeight;
        return $this;
    }

    /**
     * @return float
     */
    public function getMaximumWidth()
    {
        return $this->maximumWidth;
    }

    /**
     * @param float $maximumWidth
     * @return ImageUploadValidator
     */
    public function setMaximumWidth($maximumWidth)
    {
        $this->maximumWidth = $maximumWidth;
        return $this;
    }

    /**
     * @return float
     */
    public function getMaximumHeight()
    {
        return $this->maximumHeight;
    }

    /**
     * @param float $maximumHeight
     * @return ImageUploadValidator
     */
    public function setMaximumHeight($maximumHeight)
    {
        $this->maximumHeight = $maximumHeight;
        return $this;
    }
}
