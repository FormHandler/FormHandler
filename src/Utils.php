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
    static public function html($string,$with_forward_slash = true)
    {
        $replace = array(
            '&' => '&amp;',
            '<' => '&lt;',
            '>' => '&gt;',
            '"' => '&quot;',
            '\'' => '&#x27;',
        );

        if($with_forward_slash)
        {
            $replace['/'] = '&#x2F;';
        }

        return str_replace(array_keys($replace), $replace, $string);
    }

    /**
     * Format a given input to a valid url
     *
     * @param string $input URL to be formatted
     * @param boolean $force_https Force to a HTTPS link
     * @return string empty string when not possible to parse
     */
    public static function url($input, $force_https = false)
    {
        $url = parse_url($input);
        $isRelative = substr($input, 0, 2) == './';

        //return when parsing is not possible
        if($url === false)
        {
            return '';
        }

        //parse scheme
        $parsed_scheme = (!empty($url['scheme'])) ? $url['scheme'] : 'http';
        $scheme = ($force_https ? 'https' : $parsed_scheme) .'://';
        $is_http = ($scheme === 'http://');

        //parse parts
        $user = (!empty($url['user'])) ? $url['user'] : '';
        $password = (!empty($url['pass'])) ? $url['pass'] : '';
        $auth = (!empty($user) && !empty($password)) ? $user .':'. $password : $user . $password;
        $host = (!empty($url['host'])) ? $url['host'] : '';
        $path = (!empty($url['path'])) ? $url['path'] : '/';
        if(substr($path, 0, 1) == '/')
        {
            $path = substr($path, 1);
        }

        $port = (!empty($url['port']) && !($url['port'] === 80 && $is_http) && !($url['port'] === 433 && !$is_http))
            ? ':'. $url['port']
            : '';

        $query = (!empty($url['query'])) ? $url['query'] : '';
        $fragment = (!empty($url['fragment'])) ? '#'. $url['fragment'] : '';

        $host = $host != '' && (substr($host, -1, 1) != '/')
            ? $host . '/'
            : $host;

        //parse end slash if not existent
        $base = $isRelative
            ? $path
            : $scheme . $auth . (!empty($auth) ? '@' : '') . $host . $path . $port;

        $query_data = array();
        foreach(explode('&', $query) as $item)
        {
            $item = explode('=', $item);
            $key = $item[0];
            $value = array_key_exists(1, $item)
                ? $item[1]
                : '';

            if(trim($key) != '')
            {
                $query_data[$key] = self::html($value);
            }
        }

        $query = http_build_query($query_data, null, '&');
        if($query != '')
        {
            $query = '?'.$query;
        }

        //assemble and return
        return self::html($base,false) . $query . $fragment;
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
