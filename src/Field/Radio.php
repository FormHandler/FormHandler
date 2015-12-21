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
use \FormHandler\MaskLoader;

/**
 * class Radio
 *
 * Create a Radio field
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Field
 */
class Radio extends \FormHandler\Field\Field
{
    // string: what kind of "glue" should be used to merge the fields
    private $mask;
    // object: a maskloader object
    private $loader;

    /**
     * Constructor
     *
     * Create a new radio field
     *
     * @param object $form The form where this field is located on
     * @param string $name The name of the field
     * @param array|string $options The options for the field
     * @return \FormHandler\Field\Radio
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function __construct(FormHandler $form, $name, $options = array())
    {
        // call the constructor of the Field class
        return parent::__construct($form, $name)
            ->setOptions($options)
            ->setMask(\FormHandler\Configuration::get('default_glue_mask'))
            ->useArrayKeyAsValue(\FormHandler\Configuration::get('default_usearraykey'))
            ->setFocusName($name . '_1');
    }

    /**
     * Radio::setMask()
     *
     * Set the "glue" used to glue multiple radio fields
     *
     * @param string $sMask
     * @return \FormHandler\Field\Radio
     * @author Teye Heimans
     * @author Marien den Besten
     * @access public
     */
    public function setMask($sMask)
    {
        if(!is_null($sMask))
        {
            // when there is no %field% used, put it in front of the mask/glue
            if(strpos($sMask, '%field%') === false)
            {
                $sMask = '%field%' . $sMask;
            }

            $this->mask = $sMask;
        }
        return $this;
    }

    /**
     * Radio::getField()
     *
     * Return the HTML of the field
     *
     * @return string the html of the field
     * @access Public
     * @author Teye Heimans
     */
    public function getField()
    {
        // view mode enabled ?
        if($this->getViewMode())
        {
            // get the view value..
            return $this->_getViewValue();
        }

        $options = $this->getOptions();
        if(count($options) > 0)
        {
            $result = '';
            foreach($options as $key => $value)
            {
                if(!$this->getUseArrayKeyAsValue())
                {
                    $key = $value;
                }

                $result .= $this->getRadio($key, $value, true);
            }
        }
        elseif(count($options) === 0)
        {
            $result = ' ';
        }
        else
        {
            $result = $this->getRadio($options, '');
        }

        // when we still got nothing, the mask is not filled yet.
        // get the mask anyway
        if(empty($result))
        {
            $result = $this->loader->fill();
        }

        return $result . (isset($this->extra_after) ? $this->extra_after : '');
    }

    /**
     * Radio::_getRadio()
     *
     * Return the radio field with the given title and value
     *
     * @param string $value the value for the checkbox
     * @param string $title the title for the checkbox
     * @param bool $use_mask Do we need to use the mask ?
     * @return string the HTML for the checkbox
     * @author Teye Heimans
     */
    private function getRadio($value, $title, $use_mask = false)
    {
        static $counter = 1;

        $value = trim($value);

        if(is_null($this->loader))
        {
            $this->loader = new MaskLoader();
            $this->loader->setMask($this->mask);
            $this->loader->setSearch('/%field%/');
        }

        $field = sprintf(
            '<input type="radio" name="%s" id="%1$s_%d" value="%s" %s' . \FormHandler\Configuration::get('xhtml_close') . '>'
                . '<label for="%1$s_%2$d" class="noStyle">%s</label>',
            $this->name,
            $counter++,
            htmlspecialchars($value),
            ($value == $this->getValue() ? 'checked="checked" ' : '')
                . (isset($this->tab_index) ? 'tabindex="' . $this->tab_index . '" ' : '')
                . (!empty($this->extra) ? $this->extra . ' ' : '')
                . ($this->getDisabled() && !$this->getDisabledInExtra() ? 'disabled="disabled" ' : ''),
            trim($title)
        );

        // do we have to use the mask ?
        if($use_mask)
        {
            $field = $this->loader->fill($field);
        }
        return $field;
    }
}