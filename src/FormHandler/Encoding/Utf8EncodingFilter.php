<?php
namespace FormHandler\Encoding;

use ForceUTF8\Encoding;
use FormHandler\Form;

/**
 * This class implements an UTF-8 filter which makes sure that the
 * given content is valid utf-8. Note however that we only allow 3-byte
 * sequence characters, not 4-byte characters. These are not allowed in MySQL's
 * UTF-8 fields. If you want this, you could create a new filter ;-)
 */
class Utf8EncodingFilter implements InterfaceEncodingFilter
{

    /**
     * Initialisation of this encoder.
     * This is requested when the encoding filter is first set in the Form object.
     * Here you could make some changes in the Form object, like set the "accept-charset"
     *
     * @param Form $form
     */
    public function init(Form &$form)
    {
        $form->setAcceptCharset("utf-8");
    }

    /**
     * Filter the given value and make sure that it is a valid UTF8 encoded string.
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        $value = Encoding::fixUTF8($value);

        // MySQL 5.4 and lower do not allow 4 byte sequences. Filter these out as well.
        // @see http://dev.mysql.com/doc/refman/5.5/en/charset-unicode-utf8mb4.html
//        for ($pos = strlen($value) - 1; $pos >= 0; $pos--) {
//            $char = substr($value, $pos, 1);
//            if (ord($char) > 0xEF) {
//                $value = substr($value, 0, $pos) . substr($value, $pos + 4);
//            }
//        }

        return $value;
    }
}
