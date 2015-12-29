<?php

/**
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
 */

namespace FormHandler\Validator;

/**
 * Email
 *
 * @author Marien den Besten
 */
class Email extends Validator implements ValidatorInterface
{
    /**
     *
     * @link http://www.linuxjournal.com/article/9585?page=0,3
     * @param string $value
     * @return boolean
     */
    public function validate($value)
    {
        $atIndex = strrpos($value, "@");
        if(is_bool($atIndex) && !$atIndex)
        {
            return false;
        }

        $domain = substr($value, $atIndex + 1);
        $local = substr($value, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);

        if($localLen < 1 || $localLen > 64)
        {
            // local part length exceeded
            return false;
        }
        elseif($domainLen < 1 || $domainLen > 255)
        {
            // domain part length exceeded
            return false;
        }
        elseif($local[0] == '.' || $local[$localLen - 1] == '.')
        {
            // local part starts or ends with '.'
            return false;
        }
        elseif(preg_match('/\\.\\./', $local))
        {
            // local part has two consecutive dots
            return false;
        }
        elseif(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
        {
            // character not valid in domain part
            return false;
        }
        elseif(preg_match('/\\.\\./', $domain))
        {
            // domain part has two consecutive dots
            return false;
        }
        elseif(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local)))
        {
            // character not valid in local part unless
            // local part is quoted
            if(!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local)))
            {
                return false;
            }
        }
        return true;
    }
}
