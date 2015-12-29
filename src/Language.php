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
 * Language
 *
 * @author Marien den Besten
 */
class Language
{
    static $language_loaded = null;
    static $language = array();
    static $override = array();

    /**
     * Check if a given language exists in the framework
     *
     * @param string $language
     * @return boolean
     */
    public static function exists($language)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'Language' . DIRECTORY_SEPARATOR;

        //detect incorrect characters
        return (preg_match('/\.|\/|\\\/', $language))
            ? false
            : file_exists($dir . $language . '.php');
    }

    /**
     * Read currently loaded language
     *
     * @return string|null Null when nothing is loaded
     */
    public static function active()
    {
        return self::$language_loaded;
    }

    /**
     * Load language based upon iso
     *
     * @param string $language
     * @return boolean
     */
    public static function load($language)
    {
        if(!self::exists($language))
        {
            return false;
        }

        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'Language' . DIRECTORY_SEPARATOR;

        self::$language = include $dir . $language . '.php';
        self::$language_loaded = $language;
        return true;
    }

    /**
     * Detect language based upon given accept language
     *
     * @param string $accept_language
     * @return string|null Language string of the language supported by FormHandler
     */
    public static function detect($accept_language)
    {
        // standard  for HTTP_ACCEPT_LANGUAGE is defined under
        // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
        // pattern to find is therefore something like this:
        //    1#( language-range [ ";" "q" "=" qvalue ] )
        // where:
        //    language-range  = ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" )
        //    qvalue         = ( "0" [ "." 0*3DIGIT ] )
        //            | ( "1" [ "." 0*3("0") ] )
        preg_match_all("/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" .
                       "(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i",
                       $accept_language, $hits, PREG_SET_ORDER);

        // default language (in case of no hits) is the first in the array
        $bestlang = null;
        $bestqval = 0;

        foreach($hits as $arr)
        {
            // read data from the array of this hit
            $langprefix = strtolower ($arr[1]);
            if(!empty($arr[3]))
            {
                $langrange = strtolower ($arr[3]);
                $language = $langprefix . "-" . $langrange;
            }
            else
            {
                $language = $langprefix;
            }
            $qvalue = 1.0;
            if(!empty($arr[5]))
            {
                $qvalue = floatval($arr[5]);
            }

            // find q-maximal language
            if(self::exists($language) && ($qvalue > $bestqval))
            {
                $bestlang = $language;
                $bestqval = $qvalue;
            }
            // if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does)
            elseif(self::exists($langprefix) && (($qvalue*0.9) > $bestqval))
            {
                $bestlang = $langprefix;
                $bestqval = $qvalue*0.9;
            }
        }
        return $bestlang;
    }

    /**
     * Set a specific language override
     *
     * @param integer $index
     * @param string $value
     */
    public static function set($index, $value)
    {
        self::$override[$index] = $value;
    }

    /**
     * Remove language entries. Remove only items added with the self::set() function
     *
     * @param integer $index
     * @return boolean
     * @see self::set()
     */
    public static function remove($index)
    {
        if(array_key_exists($index, self::$override))
        {
            unset(self::$override[$index]);
            return true;
        }
        return false;
    }

    /**
     * Get language item
     *
     * @param integer $index
     * @return string
     */
    public static function get($index)
    {
        if(array_key_exists($index, self::$override))
        {
            return self::$override[$index];
        }
        if(array_key_exists($index, self::$language))
        {
            return self::$language[$index];
        }
        return '';
    }

    /**
     * Reset language instance
     */
    public static function reset()
    {
        self::$language_loaded = null;
        self::$language = array();
        self::$override = array();
    }
}
