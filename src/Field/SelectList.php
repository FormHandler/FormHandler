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
 * class SelectList
 *
 * Create a SelectList field on the given form
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Field
 */
class SelectList extends \FormHandler\Field\Field
{
    private $field_values;
    private $field_on;
    private $field_off;
    private $field_on_title;
    private $field_off_title;
    private $vertical_mode;

    /**
     * Constructor
     *
     * Create a new SelectList
     *
     * @param FormHandler $form The form where this field is located on
     * @param string $name The name of the field
     * @param array $options The options of the field
     * @return \FormHandler\Field\SelectList
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function __construct(FormHandler $form, $name, $options = array())
    {
        $this->field_values = new \FormHandler\Field\Hidden($form, $name);
        $this->field_values->setDefaultValue(array());
        static $bSetJS = false;

        // needed javascript included yet ?
        if(!$bSetJS)
        {
            $bSetJS = true;
            $form->_setJS(\FormHandler\Configuration::get('fhtml_dir') . "js/listfield.js", true);
        }

        // make the fields of the SelectList
        $this->field_on = new \FormHandler\Field\Select($form, $name . '_ListOn');
        $this->field_off = new \FormHandler\Field\Select($form, $name . '_ListOff');
        $this->field_on->setMultiple(true);
        $this->field_off->setMultiple(true);

        return parent::__construct($form, $name)
            ->setOptions($options)
            ->useArrayKeyAsValue(\FormHandler\Configuration::get('default_usearraykey'))
            ->setSize(\FormHandler\Configuration::get('default_listfield_size'))
            ->setOffTitle($form->_text(29))
            ->setOnTitle($form->_text(30))
            ->setFocusName($name . '_ListOn');
    }

    /**
     * Set the stack mode of the list field
     *
     * @param boolean $vertical_mode
     * @author Rick de Haan
     * @author Marien den Besten
     * @since 20-03-2008 added by Johan Wiegel
     * @return \FormHandler\Field\SelectList
     */
    public function setVerticalMode($vertical_mode)
    {
        if(!is_null($vertical_mode))
        {
            $this->vertical_mode = $vertical_mode;
        }
        return $this;
    }

    /**
     * Set the value of the field
     *
     * @param array|string $aValue The new value of the field
     * @return \FormHandler\Field\SelectList
     * @author Teye Heimans
     */
    public function setValue($aValue, $forced = false)
    {
        $this->field_values->setValue($aValue, $forced);
        return $this;
    }
    
    /**
     * Get the value of the field
     * 
     * @return array
     * @author Marien den Besten
     */
    public function getValue()
    {
        return $this->field_values->getValue();
    }

    /**
     * Set some extra tag information of the fields
     *
     * @param string $extra The extra information to inglude with the html tag
     * @param boolean $append Append extras to already defined values
     * @return \FormHandler\Field\SelectList
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setExtra($extra, $append = false)
    {
        $this->field_off->setExtra($extra, $append);
        $this->field_on->setExtra($extra, $append);
        return $this;
    }

    /**
     * Set the title of the ON selection of the field
     *
     * @param string $sTitle The title
     * @return \FormHandler\Field\SelectList
     * @author Teye Heimans
     */
    public function setOnTitle($sTitle)
    {
        if(!is_null($sTitle))
        {
            $this->field_on_title = $sTitle;
        }
        return $this;
    }

    /**
     * Set the title of the OFF selection of the field
     *
     * @param string $sTitle The title
     * @return \FormHandler\Field\SelectList
     * @author Teye Heimans
     */
    public function setOffTitle($sTitle)
    {
        if(!is_null($sTitle))
        {
            $this->field_off_title = $sTitle;
        }
        return $this;
    }
    
    /**
     * Set disabled
     * 
     * @param boolean $bool
     * @return \FormHandler\Field\Field
     */
    public function setDisabled($bool = true)
    {
        $this->field_on->setDisabled($bool);
        $this->field_off->setDisabled($bool);
        return parent::setDisabled($bool);
    }

    /**
     * Return the HTML of the field
     *
     * @return string The html
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

        // get the selected and unselected values
        $current = !is_array($this->getValue()) ? array($this->getValue()) : $this->getValue();
        $aSelected = array();
        $aUnselected = array();
        foreach($this->getOptions() as $iIndex => $sValue)
        {
            $sKey = (!$this->getUseArrayKeyAsValue()) ? $sValue : $iIndex;

            if(in_array($sKey, $current))
            {
                $aSelected[$iIndex] = $sValue;
            }
            else
            {
                $aUnselected[$iIndex] = $sValue;
            }
        }

        $this->field_on->setOptions($aSelected);
        $this->field_off->setOptions($aUnselected);

        // add the double click event
        $this->field_on->extra .= " ondblclick=\"changeValue('" . $this->name . "', false)\"";
        $this->field_off->extra .= " ondblclick=\"changeValue('" . $this->name . "', true)\"";

        $mask = (!empty($this->vertical_mode) && $this->vertical_mode)
            ? \FormHandler\Configuration::get('listfield_vertical_mask')
            : \FormHandler\Configuration::get('listfield_horizontal_mask');

        return
            $this->field_values->getField() . "\n" .
            str_replace(
                array(
                '%onlabel%',
                '%offlabel%',
                '%onfield%',
                '%offfield%',
                '%name%',
                '%ontitle%',
                '%offtitle%'
                ), array(
                $this->field_on_title,
                $this->field_off_title,
                $this->field_on->getField(),
                $this->field_off->getField(),
                $this->name,
                sprintf($this->form_object->_text(34), \FormHandler\Utils::html(strip_tags($this->field_off_title))),
                sprintf($this->form_object->_text(34), \FormHandler\Utils::html(strip_tags($this->field_on_title)))),
                $mask
            ) .
            (isset($this->extra_after) ? $this->extra_after : '');
    }

    /**
     * Set the size (height) of the field (default 4)
     *
     * @param integer $size The size
     * @return \FormHandler\Field\SelectList
     * @author Teye Heimans
     */
    public function setSize($size)
    {
        $this->field_on->setSize($size);
        $this->field_off->setSize($size);
        return $this;
    }

    /**
     * Set if the array keys of the options has to be used as values for the field
     *
     * @param boolean $mode The mode
     * @return \FormHandler\Field\SelectList
     * @author Teye Heimans
     */
    public function useArrayKeyAsValue($mode)
    {
        if(!is_null($mode))
        {
            parent::useArrayKeyAsValue($mode);
            $this->field_on->useArrayKeyAsValue($mode);
            $this->field_off->useArrayKeyAsValue($mode);
        }
        return $this;
    }
}
