<?php
namespace FormHandler\Encoding;

interface InterfaceEncodingFilter
{

    /**
     * Initialisation of this encoder.
     * This is requested when the encoding filter is first set in the Form object.
     * Here you could some changes in the Form object, like set the "accept-charset"
     */
    public function init(Form &$form);

    /**
     * Filter the given value and make sure that it is a valid encoded string.
     *
     * @param string $value
     * @return string
     */
    public function filter($value);
}