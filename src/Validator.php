<?php

/**
 * Validator
 *
 * Look for more info at http://www.formhandler.net
 *
 * Copyright (C) 2015 FormHandler
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 * 
 * @package FormHandler
 */

namespace FormHandler;

/**
 * class Validator
 *
 * Static functions to check if the given value validates a specific format
 *
 * @author Teye Heimans
 * @author Marien den Besten
 * @package FormHandler
 */
class Validator
{
    /**
     * Set a regex validator for a field. The validator will check if regex is matched on form correct.
     *
     * @param Field $field The field
     * @param string $pattern The pattern to match. This function does not validate given pattern
     * @param string|null $help_text Optionally set placeholder text for field i.e. DD-MM-YYYY
     */
    public static function validateRegularExpression(Field $field, $pattern, $help_text = null)
    {
        $field->setExtra(' pattern="'. $pattern .'"', true)
            ->setValidator(function($value) use ($pattern)
        {
            return is_string($value) && preg_match($pattern, $value);
        });

        if(!is_null($help_text))
        {
            $field->setExtra(' placeholder="'. $help_text .'"');
        }
    }

    /**
     * Validator::IsString()
     *
     * Any string that doesn't have control characters (ASCII 0 - 31) but spaces are allowed
     *
     * @param string $value The string to check
     * @return bool
     */
    public function IsString($value)
    {
        return preg_match("/^[^\x-\x1F]+$/", $value);
    }

    /**
     * Validator::_IsString()
     *
     * Public: same as IsString, only now the value is also valid if it is empty
     *
     * @param string $value
     * @return bool
     */
    public function _IsString($value)
    {
        return StrLen($value) == 0 || Validator::IsString($value);
    }

    /**
     * Validator::IsAlpha()
     *
     * Public: only letters a-z and A-Z
     *
     * @param string $value
     * @return bool
     */
    public function IsAlpha($value)
    {
        return (bool) preg_match("/^[a-z]+$/i", $value);
    }

    /**
     * Validator::_IsAlpha()
     *
     * Public: same as IsAlpha, only now the value is also valid if it is empty
     *
     * @param string $value
     * @return bool
     */
    public function _IsAlpha($value)
    {
        return StrLen($value) == 0 || Validator::IsAlpha($value);
    }

    /**
     * Validator::IsDigit()
     *
     * Public: only numbers 0-9
     *
     * @param string $value
     * @return bool
     */
    public function IsDigit($value)
    {
        return (bool) preg_match("/^[0-9]+$/", $value);
    }

    /**
     * Validator::_IsDigit()
     *
     * Public: same as IsDigit, only now the value is also valid if it is empty
     *
     * @param string $value
     * @return bool
     */
    public function _IsDigit($value)
    {
        return StrLen($value) == 0 || Validator::IsDigit($value);
    }

    /**
     * Validator::IsAlphaNum()
     *
     * Public: letters and numbers
     *
     * @param string $value
     * @return bool
     */
    public function IsAlphaNum($value)
    {
        return (bool) preg_match("/^[a-z0-9]+$/i", $value);
    }

    /**
     * Validator::_IsAlphaNum()
     *
     * Public: same as IsAlphaNum, only now the value is also valid if it is empty
     *
     * @param string $value
     * @return bool
     */
    public function _IsAlphaNum($value)
    {
        return StrLen($value) == 0 || Validator::IsAlphaNum($value);
    }

    /**
     * Validator::IsFloat()
     *
     * Public: only numbers 0-9 and an optional - (minus) sign (in the beginning only)
     *
     * @param string $value
     * @return bool
     */
    public function IsFloat($value)
    {
        return (bool) preg_match("/^-?([0-9]*\.?,?[0-9]+)$/", $value);
    }

    /**
     * Validator::_IsFloat()
     *
     * Public: same as IsFloat, only now the value is also valid if it is empty
     *
     * @param string $value
     * @return bool
     */
    public function _IsFloat($value)
    {
        return StrLen($value) == 0 || Validator::IsFloat($value);
    }

    /**
     * Validator::IsInteger()
     *
     * Public: only numbers 0-9 and an optional - (minus) sign (in the beginning only)
     *
     * @param string $value
     * @return bool
     */
    public function IsInteger($value)
    {
        return (bool) preg_match("/^-?[0-9]+$/", $value);
    }

    /**
     * Validator::_IsInteger()
     *
     * Public: same as IsInteger, only now the value is also valid if it is empty
     *
     * @param string $value
     * @return bool
     */
    public function _IsInteger($value)
    {
        return StrLen($value) == 0 || Validator::IsInteger($value);
    }

    /**
     * Validator::IsFilename()
     *
     * Public: a valid file name (including dots but no slashes and other forbidden characters)
     *
     * @param string $value
     * @return bool
     */
    public function IsFilename($value)
    {
        return preg_match("{^[^\\/\*\?\:\,]+$}", $value);
    }

    /**
     * Validator::_IsFilename()
     *
     * Public: same as IsFilename, only now the value is also valid if it is empty
     *
     * @param string $value
     * @return bool
     */
    public function _IsFilename($value)
    {
        return StrLen($value) == 0 || Validator::IsFilename($value);
    }

    /**
     * Validator::IsBool()
     *
     * Public: a boolean (case-insensitive "true"/"1" or "false"/"0")
     *
     * @param string $value
     * @return bool
     */
    public function IsBool(&$value)
    {
        if(preg_match("/^true$|^1|^false|^0$/i", $value))
        {
            $value = true;
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Validator::_IsBool()
     *
     * Public: same as IsBool, only now the value is also valid if it is empty
     *
     * @param string $value
     * @return bool
     */
    public function _IsBool($value)
    {
        return StrLen($value) == 0 || Validator::IsBool($value);
    }

    // a valid variable name (letters, digits, underscore)
    public function IsVariabele($value)
    {
        if($value == '_')
        {
            return false;
        }
        return (preg_match("/^[a-zA-Z_][a-zA-Z0-9_]*$/i", $value));
    }

    public function _IsVariabele($value)
    {
        return StrLen($value) == 0 || Validator::IsVariabele($value);
    }

    // a valid password (alphanumberic + some other characters but no spaces. Only allow ASCII 33 - 126)
    public function IsPassword($value)
    {
        return preg_match("/^[\41-\176]+$/", $value);
    }

    public function _IsPassword($value)
    {
        return StrLen($value) == 0 || Validator::IsPassword($value);
    }

    // check for a valid url
    public function IsURL($value)
    {
        $regex = '/^((http|ftp|https):\/{2})?(([0-9a-zA-Z_-]+\.)+[a-zA-Z]+)((:[0-9]+)?)((\/([0-9a-zA-Z=%\.\/_-]+)?'
            . '(\?[0-9a-zA-Z%\/&=_-]+)?)?)$/';
        return preg_match($regex, $value, $match);
    }

    public function _IsURL($value)
    {
        return StrLen($value) == 0 || Validator::IsURL($value);
    }

    // a valid URL (http connection is used to check if url exists!)
    public function IsURLHost($href)
    {
        if(strlen($href) <= 3)
        {
            return false;
        }

        if(!preg_match("/^[a-z]+:/i", $href))
        {
            $href = 'http://' . $href;
        }
        if(preg_match("/^http:\/\//", $href))
        {
            $fp = @fopen($href, 'r');
            if($fp)
            {
                fclose($fp);

                return true;
            }
        }
        return false;
    }

    public function _IsURLHost($value)
    {
        return StrLen($value) == 0 || Validator::IsURLHost($value);
    }

    // a valid email address (only checks for valid format: xxx@xxx.xxx)
    public function IsEmail($value)
    {
        return preg_match("/^[a-z0-9_\.-]+@([a-z0-9]+([\-]+[a-z0-9]+)*\.)+[a-z]{2,7}$/i", $value);
    }

    public function _IsEmail($value)
    {
        return StrLen($value) == 0 || Validator::IsEmail($value);
    }

    // like IsMail only with host check
    public function IsEmailHost($value)
    {
        $regex = "/^[0-9A-Za-z_]([-_.]?[0-9A-Za-z_])*@[0-9A-Za-z][-.0-9A-Za-z]*\\.[a-zA-Z]{2,3}[.]?$/";
        $check = array();
        if(!preg_match($regex, $value, $check))
        {
            return false;
        }

        $host = substr(strstr($check[0], '@'), 1) . ".";

        if(function_exists('getmxrr'))
        {
            $tmp = null;
            if(getmxrr($host, $tmp))
            {
                return true;
            }
            // this will catch dns that are not mx.
            if(checkdnsrr($host, 'ANY'))
            {
                return true;
            }
        }
        return ($host != gethostbyname($host));
    }

    public function _IsEmailHost($value)
    {
        return StrLen($value) == 0 || Validator::IsEmailHost($value);
    }

    // like IsString, but newline characters and tabs are allowed
    public function IsText($value)
    {
        return
            preg_match("/^([^\x-\x1F]|[\r\n\t])+$/", $value);
    }

    public function _IsText($value)
    {
        return StrLen($value) == 0 || Validator::IsText($value);
    }

    // is a valid dutch postcode (eg. 9999 AA)
    public function IsPostcode($value)
    {
        return preg_match('/^[1-9][0-9]{3} ?[a-zA-Z]{2}$/', $value);
    }

    public function _IsPostcode($value)
    {
        return StrLen($value) == 0 || Validator::IsPostcode($value);
    }

    // is a valid dutch phone-number
    public function IsPhone($value)
    {
        $regex = '/^[0-9]{2,4}[-]?[0-9]{6,8}$/';
        $value = str_replace(array(' ', '-'), array('', ''), $value);
        return (strLen($value) == 10 && preg_match($regex, $value));
    }

    public function _IsPhone($value)
    {
        return StrLen($value) == 0 || Validator::IsPhone($value);
    }

    // check if the value is not empty
    public function notEmpty($value)
    {
        if(!is_array($value))
        {
            $value = trim($value);
            if($value != '')
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return (bool) (count($value) > 0);
        }
    }

    // check if it's a valid ip adres
    public function IsIp($ip)
    {
        return preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}.\d{1,3}:?\d*$/', $ip);
    }

    public function _IsIp($ip)
    {
        return StrLen($ip) == 0 || Validator::IsIp($ip);
    }

    // check if the value does not contains any html
    public function NoHTML($value)
    {
        return strip_tags($value) == $value && strlen($value) > 0;
    }

    public function _NoHTML($value)
    {
        return StrLen($value) == 0 || Validator::noHTML($value);
    }

    /**
     * Check the capthcafield using Securimage
     *
     * @param string $value
     * @return boolean
     * @author Johan Wiegel
     * @since 27-11-2008
     */
    public function FH_CAPTCHA($value)
    {
        require(FH_FHTML_INCLUDE_DIR . 'securimage/securimage.php');
        $img = new Securimage();
        $valid = $img->check($value);
        return ($valid == true);
    }

    /**
     * Check if file is uploaded correctly
     *
     * @param array $value
     * @return boolean
     * @author Ruben de Vos
     * @since 18-07-2014
     */
    public function isUploaded($value)
    {
        return (is_array($value) && $value['error'] == 0 && is_file($value['tmp_name']));
    }
}
