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
 * Configuration
 *
 * @author Marien den Besten
 */
class Configuration
{
    private $directory;
    private $data;
    private $default;
    static $instance;

    /**
     * Constructor
     *
     * @author Marien den Besten
     */
    private final function __construct()
    {
    }

    private function loadDefault()
    {
        if(is_null($this->default))
        {
            //hard coded default configuration
            $this->directory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
            $this->default = $this->directory  . 'default.ini';
            self::loadFile($this->default);

            //load other files from default config directory
            self::loadDirectory($this->directory);
        }
    }

    /**
     * Singleton
     *
     * @author Marien den Besten
     * @return Configuration
     */
    public static function getInstance()
    {
        if(!self::$instance instanceof self)
        {
            self::$instance = new self();
            self::$instance->loadDefault();
        }
        return self::$instance;
    }

    /**
     * Load all configuration files from a given directory.
     *
     * It parses all .ini files.
     *
     * @param string $directory
     * @return boolean
     */
    public static function loadDirectory($directory)
    {
        $instance = self::getInstance();
        if(!is_dir($directory))
        {
            return false;
        }

        //load configured
        $files = glob($directory . '*.ini');
        foreach($files as $file)
        {
            //when the file is the same as default configuration, skip it
            if(realpath($file) === realpath($instance->default))
            {
                continue;
            }
            self::loadFile($file);
        }
        return true;
    }

    /**
     *
     * @param string $file
     * @return void
     */
    public static function loadFile($file)
    {
        $instance = self::getInstance();
        
        if(!file_exists($file))
        {
            return;
        }

        $params = parse_ini_file($file);

        if(is_array($params))
        {
            foreach($params as $name => $value)
            {
                self::set($name, $value);
            }
        }
    }

    /**
     * Set configuration value
     *
     * @author Marien den Besten
     * @param string $name
     * @param mixed $value
     */
    public static function set($name, $value)
    {
        $instance = self::getInstance();
        $instance->data[strtolower($name)] = $value;
    }

    /**
     * Get configuration value
     *
     * @author Marien den Besten
     * @param string $name
     * @return mixed|null
     */
    public static function get($name)
    {
        $instance = self::getInstance();

        $lookup = strtolower($name);
        if(array_key_exists($lookup, $instance->data))
        {
            return $instance->data[$lookup];
        }
        return null;
    }

    /**
     * Get loaded configuration
     *
     * @return array
     */
    public static function getAll()
    {
        $instance = self::getInstance();
        return $instance->data;
    }
}
