<?php

/**
 * Copyright (C) 2016 Ruben de Vos <ruben@color-base.com>.
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

namespace FormHandler\Field;

/**
 * Utils
 *
 * @author Ruben de Vos <ruben@color-base.com>
 */
class Utils
{

    /**
     * This function does charset safe conversion of HTML entities
     *
     * @author Ruben de Vos <ruben@color-base.com>
     * @param string $string The input string
     * @return string The converted string
     */
    static public function html($string, $flags = null, $charset = 'UTF-8')
    {
        $flags = (!is_null($flags)) ? $flags : ENT_COMPAT | ENT_IGNORE;
        return @htmlentities($string, $flags, $charset);
    }
}
