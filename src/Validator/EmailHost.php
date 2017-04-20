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
 * EmailHost
 *
 * @author Marien den Besten
 */
class EmailHost extends Email implements ValidatorInterface
{
    /**
     *
     * @link http://www.linuxjournal.com/article/9585?page=0,3
     * @param string $value
     * @return boolean
     */
    public function validate($value)
    {
        if(!parent::validate($value))
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
}
