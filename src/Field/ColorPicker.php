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

/**
 * class ColorPicker
 *
 * Allows the user to pick a color
 *
 * @author Rick den Haan
 * @package FormHandler
 * @subpackage Field
 * @since 02-07-2008
 */
class ColorPicker extends \FormHandler\Field\Text
{
    var $sTitleAdd = "";

    /**
     * ColorPicker::ColorPicker()
     *
     * Constructor: Create a new ColorPicker object
     *
     * @param object $form The form where this field is located on
     * @param string $name The name of the field
     * @return \FormHandler\Field\ColorPicker
     * @author Rick den Haan
     */
    public function __construct($form, $name)
    {
        parent::__construct($form, $name);

        static $bSetJS = false;

        // needed javascript included yet ?
        if(!$bSetJS)
        {
            // include the needed javascript
            $bSetJS = true;
            $form->_setJS(\FormHandler\Configuration::get('fhtml_dir') . "js/jscolor/jscolor.js", true);
        }
        return $this;
    }

    /**
     * ColorPicker::getField()
     *
     * Return the HTML of the field
     *
     * @return string: the html of the field
     * @author Rick den Haan
     */
    public function getField()
    {
        // view mode enabled ?
        if($this->getViewMode())
        {
            // get the view value..
            return $this->_getViewValue();
        }

        // check if the user set a class
        if(isset($this->extra) && preg_match("/class *= *('|\")(.*)$/i", $this->extra))
        {
            // put the function into a onchange tag if set
            $this->extra = preg_replace("/class *= *('|\")(.*)$/i", "class=\"color \\2", $this->extra);
        }
        else
        {
            $this->extra = "class=\"color\"" . (isset($this->extra) ? $this->extra : '');
        }

        $max_length = $this->getMaxLength();

        return sprintf(
            '<input type="text" name="%s" id="%1$s" value="%s" size="%d" %s' . \FormHandler\Configuration::get('xhtml_close') . '>%s',
            $this->name,
            htmlspecialchars($this->getValue()),
            $this->getSize(),
            (!empty($max_length) ? 'maxlength="' . $max_length . '" ' : '')
                . (isset($this->tab_index) ? 'tabindex="' . $this->tab_index . '" ' : '')
                . (isset($this->extra) ? ' ' . $this->extra . ' ' : ''),
            (isset($this->extra_after) ? $this->extra_after : '')
        );
    }
}