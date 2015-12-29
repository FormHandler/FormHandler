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
 *
 * @package FormHandler
 * @subpackage Field
 */

namespace FormHandler\Field;

use \FormHandler\FormHandler;

/**
 * class Hidden
 *
 * Create a hidden field on the given form
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Field
 */
class Hidden extends \FormHandler\Field\Field
{
    /**
     * Custom definition for a hidden field
     *
     * @param FormHandler $form
     * @param string $name
     * @return \FormHandler\Field\Hidden
     */
    static function set(FormHandler $form, $name, $foo = null)
    {
        return parent::set($form, '__HIDDEN__', $name);
    }

    /**
     * Constructor
     *
     * @author Marien den Besten
     * @param FormHandler $form
     * @param string $name
     * @return \FormHandler\Field\Hidden
     */
    public function __construct(FormHandler $form, $name)
    {
        return parent::__construct($form, $name)
            ->setFocusName(null);
    }

    /**
     * This field is never required by user input
     * 
     * @return boolean
     */
    public function getRequired()
    {
        return false;
    }

    /**
     * Hidden::getValue();
     *
     * Return the value of the field
     *
     * @return mixed Value of the field
     */
    public function getValue()
    {
        $value = parent::getValue();

        if(is_string($value) && substr($value,0,11) == '__FH_JSON__')
        {
            $value = json_decode(substr($value,11),true);
        }

        return $value;
    }

    /**
     * Hidden::getField()
     *
     * Return the HTML of the field
     *
     * @return string The html of the field
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function getField()
    {
        $value = '';
        if(is_array($this->getValue()))
        {
            $value = '__FH_JSON__'. htmlspecialchars(json_encode($this->getValue()));
        }
        elseif(!is_array($this->getValue()))
        {
            $value = htmlspecialchars($this->getValue());
        }

        return sprintf(
          '<input type="hidden" name="%s" id="%1$s" value="%s" %s'. \FormHandler\Configuration::get('xhtml_close') .'>%s',
          $this->name,
          $value,
          (isset($this->extra) ? $this->extra.' ' :''),
          (isset($this->extra_after) ? $this->extra_after :'')
        );
    }
}