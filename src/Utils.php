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

namespace FormHandler;

/**
 * Utils
 *
 * @author Marien den Besten
 */
class Utils
{
    /**
     * This function does charset safe conversion of HTML entities
     *
     * @author Marien den Besten
     * @param string $string The input string
     * @return string The converted string
     */
    public static function html($string, $flags = null, $charset = 'UTF-8')
    {
        $flags = (!is_null($flags)) ? $flags : ENT_COMPAT | ENT_IGNORE | ENT_QUOTES;
        return @htmlspecialchars($string, $flags, $charset);
    }

    /**
     * Use this function to remove trailing zeros received from the bc functions
     *
     * @author Marien den Besten
     * @param string $input
     * @return string
     */
    public static function removeBcTrailingZeros($input)
    {
        $patterns = array('/[\.][0]+$/','/([\.][0-9]*[1-9])([0]*)$/');
        $replaces = array('','$1');
        return preg_replace($patterns,$replaces,$input);
    }

    /**
     * Safely build a request URL
     *
     * @param string $protocol
     * @param string $base_url
     * @param string $request_uri
     * @return string
     */
    public static function buildRequestUrl($protocol, $base_url, $request_uri)
    {
        $params = array();
        $parts = parse_url($base_url . $request_uri);

        $scheme = array_key_exists('scheme', $parts)
            ? $parts['scheme'].'://'
            : '';

        $host = array_key_exists('host', $parts)
            ? $scheme . $parts['host']
            : $scheme;

        $page = array_key_exists('path', $parts)
            ? $host . $parts['path']
            : $host;

        //encode page parts
        $list = array();
        foreach(explode('/', $page) as $item)
        {
            $list[] = urlencode($item);
        }

        $page = implode('/', $list);

        $params = array();
        if(array_key_exists('query', $parts))
        {
            parse_str($parts['query'],$query_parts);

            foreach($query_parts as $key => $value)
            {
                $params[$key] = $value;
            }
        }
        $query = (!empty($params) ? '?'. http_build_query($params,null,'&') : '');
        
        return $protocol .'://'.  $page . $query;
    }
}
