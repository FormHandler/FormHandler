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

    public static function url_unparse($url)
    {
        $parsed_url = parse_url($url);
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        return array(
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port,
            'user' => $user,
            'pass' => $pass,
            'path' => $path,
            'query' => $query,
            'fragment' => $fragment,
        );
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
        $isRelative2C = substr($input, 0, 2) == './'
            || substr($input, 0, 2) == '//';
        $isRelative1C = !$isRelative2C && substr($input, 0, 1) == '/';
        $isRelative = $isRelative1C || $isRelative2C;

        $prefix = $isRelative1C
            ? substr($input, 0, 1)
            : ($isRelative2C ? substr($input, 0, 2) : '');

        if($prefix != '')
        {
            $input = substr($input, strlen($prefix));
        }

        $url = self::url_unparse($input);

        //return when parsing is not possible
        if($url === false)
        {
            return '';
        }

        //parse scheme
        $url['scheme'] = ($force_https ? 'https://' : $url['scheme']);
        if(empty($url['scheme']) && !$isRelative)
        {
            $url['scheme'] = 'http://';
        }

        $is_http = ($url['scheme'] === 'http://');

        $url['port'] = (!empty($url['port']) && !($url['port'] === 80 && $is_http) && !($url['port'] === 433 && !$is_http))
            ? $url['port']
            : '';

        //query data to array
        $query_data = array();
        empty($url['query'])
            ?: parse_str(substr($url['query'],1), $query_data);

        $query = array();
        foreach($query_data as $key => $value)
        {
            if(is_array($value))
            {
                foreach($value as $k => $v)
                {
                    $query[urlencode(urldecode($key))][urlencode(urldecode($k))] = urlencode(urldecode($v));
                }
                continue;
            }
            $query[urlencode(urldecode($key))] = urlencode(urldecode($value));
        }

        $url['query'] = empty($query)
            ? ''
            : '?'.http_build_query($query, null, '&');

        //make modifications for relative URLs
        if($isRelative)
        {
            $url['scheme'] = '';
        }

        //safely encode url parts here
        $pathList = explode('/', $url['path']);
        foreach($pathList as &$path)
        {
            $path = urlencode(urldecode($path));
        }
        $url['path'] = implode('/',$pathList);

        return $prefix . implode('', $url);
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
