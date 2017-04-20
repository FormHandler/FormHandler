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
 * UrlHost
 *
 * @author Marien den Besten
 */
class UrlHost extends Url implements ValidatorInterface
{
    public function validate($value)
    {
        if(!parent::validate($value) || strlen($value) <= 3)
        {
            return false;
        }

        if(!preg_match("/^[a-z]+:/i", $value))
        {
            $value = 'http://' . $value;
        }
        if(preg_match("/^http:\/\//", $value))
        {
            //quick lookup if is available,
            //skipping long default timeout settings
            $fp = @fsockopen($value, 80, $errno, $errstr, 1);
            if($fp)
            {
                fclose($fp);
                return true;
            }
        }
        return false;
    }
}
