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
 * Not Empty
 *
 * @author Marien den Besten
 */
class NotEmpty extends Validator implements ValidatorInterface
{
    public function validate($value)
    {
        //not empty for a string is everything except whitespace
        if(is_string($value)
            && trim($value) != '')
        {
            return true;
        }
        return !empty($value);
    }
}
