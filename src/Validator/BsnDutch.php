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
 * Bsn Dutch
 *
 * @author Marien den Besten
 */
class BsnDutch extends Validator implements ValidatorInterface
{
    public function validate($bsn)
    {
        // remove dots and spaces
        $bsn = str_replace(array('.', ',', ' '), '', trim($bsn));

        // Only numbers are allowed
        if(!is_numeric($bsn))
        {
            $this->setMessage(\FormHandler\Language::get(45));
            return false;
        }

        // Remove preceding zeroes
        while(substr($bsn, 0, 1) == '0' && strlen($bsn > 0))
        {
            $bsn = substr($bsn, 1);
        }

        if(strlen($bsn) > 9 OR strlen($bsn) < 8)
        {
            $this->setMessage(\FormHandler\Language::get(46));
            return false;
        }

        $res = 0;
        $verm = strlen($bsn);

        for($i = 0; $i < strlen($bsn); $i++, $verm--)
        {
            if($verm == 1)
            {
                $verm = -1;
            }
            $res += substr($bsn, $i, 1) * $verm;
        }

        if($res % 11 == 0)
        {
            return true;
        }
        
        $this->setMessage(\FormHandler\Language::get(47));
        return false;
    }
}
