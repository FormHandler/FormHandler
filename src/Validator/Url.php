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
 * Url
 *
 * @author Marien den Besten
 */
class Url extends Validator implements ValidatorInterface
{
    public function validate($value)
    {
        $regex = '/^((http|ftp|https):\/{2})?(([0-9a-zA-Z_-]+\.)+[a-zA-Z]+)((:[0-9]+)?)'
            . '((\/([0-9a-zA-Z=%\.\/_-]+)?(\?[0-9a-zA-Z%\/&=_-]+)?)?)$/';
        return (bool) preg_match($regex, $value, $match);
    }
}
