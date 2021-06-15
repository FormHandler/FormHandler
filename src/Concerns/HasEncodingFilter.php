<?php

namespace FormHandler\Concerns;

use FormHandler\Form;
use FormHandler\Encoding\InterfaceEncodingFilter;

trait HasEncodingFilter
{
    /**
     * The default encoding filter which will be used for all FormHandler instances.
     *
     * @var InterfaceEncodingFilter|null
     */
    protected static ?InterfaceEncodingFilter $defaultEncodingFilter = null;

    /**
     * An encoding filter which will be used to filter the data
     *
     * @var InterfaceEncodingFilter|null
     */
    protected ?InterfaceEncodingFilter $encodingFilter = null;

    /**
     * Return the default encoding filter.
     *
     * @return InterfaceEncodingFilter|null
     */
    public static function getDefaultEncodingFilter(): ?InterfaceEncodingFilter
    {
        return self::$defaultEncodingFilter;
    }

    /**
     * Set a default encoding filter.
     * This will be used to filter the input to make sure it's the correct encoding.
     *
     * This could be useful when creating multiple forms in your project, and
     * if you don't want to set a custom encoding filter for every Form object.
     *
     * Example:
     * ```php
     * // set the default formatter which should be used
     * Form::setDefaultEncodingFilter( new Utf8EncodingFilter() );
     * ```
     *
     * @param InterfaceEncodingFilter $filter
     */
    public static function setDefaultEncodingFilter(InterfaceEncodingFilter $filter): void
    {
        self::$defaultEncodingFilter = $filter;
    }

    /**
     * Return the EncodingFilter, which is used to filter all the input so that its of the correct character encoding.
     *
     * @return InterfaceEncodingFilter|null
     */
    public function getEncodingFilter(): ?InterfaceEncodingFilter
    {
        return $this->encodingFilter;
    }

    /**
     * Set an encodingFilter.
     * This filter will be used to filter all the input so that its of the correct character encoding.
     *
     * @param InterfaceEncodingFilter $value
     *
     * @return Form
     */
    public function setEncodingFilter(InterfaceEncodingFilter $value): Form
    {
        $this->encodingFilter = $value;

        // initialize the encoder.
        $this->encodingFilter->init($this);

        return $this;
    }
}