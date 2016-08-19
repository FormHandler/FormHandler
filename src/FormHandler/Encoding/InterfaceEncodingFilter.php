<?php
namespace FormHandler\Encoding;

use FormHandler\Form;

/**
 * An object which implements this interface should make sure that the given content
 * is valid for their encoding.
 *
 * So you could write an Encoding filter which only allows valid ISO 8859-1
 */
interface InterfaceEncodingFilter
{

    /**
     * Initialisation of this encoder.
     * This is requested when the encoding filter is first set in the Form object.
     * Here you could make some changes in the Form object, like set the "accept-charset"
     *
     * @param Form $form
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
