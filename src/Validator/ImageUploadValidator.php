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
     *
     * @var int|null
     */
    protected ?int $allowAspectRatioX = null;

    /**
     * Only allow files with this Y aspect ratio
     *
     * @var int|null
     */
    protected ?int $allowAspectRatioY = null;

    /**
     * Deny files with this X aspect ratio
     *
     * @var int|null
     */
    protected ?int $denyAspectRatioX = null;

    /**
     * Deny files with this Y aspect ratio
     *
     * @var int|null
     */
    protected ?int $denyAspectRatioY = null;

    /**
     * Set the minimum required width of the image.
     *
     * @var int|null
     */
    protected ?int $minimumWidth = null;

    /**
     * Set the minimum required height of the image.
     *
     * @var int|null
     */
    protected ?int $minimumHeight = null;

    /**
     * Set the maximum width of the image.
     *
     * @var int|null
     */
    protected ?int $maximumWidth = null;

    /**
     * Set the maximum height of the image.
     *
     * @var int|null
     */
    protected ?int $maximumHeight = null;

    /**
     * ImageUploadValidator constructor.
     *
     * @param bool  $required
     * @param array $messages
     */
    public function __construct(bool $required, array $messages = [])
    {
        $default = [
            'not_an_image'        =>
                'It seems that the uploaded file is not an image. Please upload a valid image file.',
            'aspect_ratio'        =>
                'The aspect ratio of the uploaded file (%s) is not the same as the required aspect ratio (%s).',
            'aspect_ratio_denied' =>
                'The aspect ratio of the uploaded file (%s) is not allowed.',
            'size_height_max'     =>
                'The height of the uploaded image (%spx) is larger than the maximum allowed height (%spx)',
            'size_height_min'     =>
                'The height of the uploaded image (%spx) is smaller than the minimum allowed height (%spx)',
            'size_width_max'      =>
                'The width of the uploaded image (%spx) is larger than the maximum allowed width (%spx)',
            'size_width_min'      =>
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
     * @param int|null $width
     * @param int|null $height
     */
    public function setMinimumProportions(int $width = null, int $height = null): self
    {
        $this->minimumWidth  = $width;
        $this->minimumHeight = $height;

        return $this;
    }

    /**
     * Set the maximum proportions of the uploaded image.
     *
     * If the width of height is bigger than the given values, the upload will be marked as invalid.
     * If you give a 0 or null value, the value will be ignored.
     *
     * @param int|null $width
     * @param int|null $height
     *
     * @return ImageUploadValidator
     */
    public function setMaximumProportions(?int $width = null, ?int $height = null): self
    {
        $this->maximumWidth  = $width;
        $this->maximumHeight = $height;

        return $this;
    }

    /**
     * Set the aspect ratio.
     * By setting these,
     * only images with this aspect ratio can be uploaded.
     *
     * @param int $x
     * @param int $y
     *
     * @return ImageUploadValidator
     */
    public function setAllowAspectRatio(int $x, int $y): self
    {
        $this->allowAspectRatioX = $x;
        $this->allowAspectRatioY = $y;

        return $this;
    }

    /**
     * Set the "denied" aspect ratio.
     * By setting these,
     * images with this aspect ratio are denied for uploading.
     *
     * @param int $x
     * @param int $y
     *
     * @return ImageUploadValidator
     */
    public function setDenyAspectRatio(int $x, int $y): self
    {
        $this->denyAspectRatioX = $x;
        $this->denyAspectRatioY = $y;

        return $this;
    }

    /**
     * Check if the given field is valid or not.
     *
     * @return boolean
     */
    public function isValid(): bool
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
        [$width, $height] = $size;

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
     *
     * @param int $width
     * @param int $height
     *
     * @return bool
     */
    protected function validateAspectRatio(int $width, int $height): bool
    {
        /**
         * Allow aspect ratio given?
         */
        if ($this->allowAspectRatioX && $this->allowAspectRatioY) {
            $gcd = $this->gcd($width, $height);
            $x   = $width / $gcd;
            $y   = $height / $gcd;

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
            $x   = $width / $gcd;
            $y   = $height / $gcd;

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
     * @param int $a
     * @param int $b
     *
     * @return int
     */
    protected function gcd(int $a, int $b): int
    {
        while ($b != 0) {
            $remainder = $a % $b;
            $a         = $b;
            $b         = $remainder;
        }

        return abs($a);
    }

    /**
     * Validate the size. If the size is invalid, we will also push an error message with setErrorMessage.
     *
     * @param int $width
     * @param int $height
     *
     * @return bool
     */
    protected function validateSize(int $width, int $height): bool
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
     * @return int|null
     */
    public function getMinimumWidth(): ?int
    {
        return $this->minimumWidth;
    }

    /**
     * @param int $minimumWidth
     *
     * @return ImageUploadValidator
     */
    public function setMinimumWidth(int $minimumWidth): self
    {
        $this->minimumWidth = $minimumWidth;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMinimumHeight(): ?int
    {
        return $this->minimumHeight;
    }

    /**
     * @param int $minimumHeight
     *
     * @return ImageUploadValidator
     */
    public function setMinimumHeight(int $minimumHeight): self
    {
        $this->minimumHeight = $minimumHeight;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaximumWidth(): ?int
    {
        return $this->maximumWidth;
    }

    /**
     * @param int $maximumWidth
     *
     * @return ImageUploadValidator
     */
    public function setMaximumWidth(int $maximumWidth): self
    {
        $this->maximumWidth = $maximumWidth;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaximumHeight(): ?int
    {
        return $this->maximumHeight;
    }

    /**
     * @param int $maximumHeight
     *
     * @return ImageUploadValidator
     */
    public function setMaximumHeight(int $maximumHeight): self
    {
        $this->maximumHeight = $maximumHeight;

        return $this;
    }
}
