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
 * Date
 *
 * @author Ruben de Vos
 */
class Date extends Validator implements ValidatorInterface
{
    /**
     * Validate date
     *
     * @param string $value
     * @return boolean
     */
    public function validate($value)
    {
        if(!is_string($value))
        {
            return false;
        }

        //format: yyyy-mm-dd
        $list = [null,null,null];
        foreach(explode('-', $value) as $key => $item)
        {
            $list[$key] = (int) $item;
        }

        $d = new \DateTime();
        $d->setDate(
            $list[0], //year
            $list[1], //month
            $list[2] //day
        );

        return $value == $d->format('Y-m-d');
    }
}
