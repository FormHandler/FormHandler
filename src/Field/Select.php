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
 * class Select
 *
 * Create a Select
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Field
 */
class Select extends \FormHandler\Field\Field
{
    protected $size;
    private $multiple;
    private $options_classes;
    private $disable_options;

    /**
     * Constructor
     *
     * Create a select field
     *
     * @param FormHandler $form The form where the field is located on
     * @param string $name The name of the form
     * @return \FormHandler\Field\Select
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function __construct(FormHandler $form, $name)
    {
        // call the constructor of the Field class
        return parent::__construct($form, $name)
            ->setJsSelectorValue('#' . $form->getFormName() . ' select[name="' . $name . '"]')
            ->setSize(1)
            ->useArrayKeyAsValue(\FormHandler\Configuration::get('default_usearraykey'))
            ->setMultiple(false);
    }

    /**
     * Select::getValue()
     *
     * Return the value of the field
     *
     * @return mixed
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function getValue()
    {
        $value = parent::getValue();

        // are multiple selects possible?
        if($this->multiple && $value !== '')
        {
            //force string
            if(is_numeric($value))
            {
                $value = (string) $value;
            }

            if(is_string($value))
            {
                return explode(', ', $value);
            }
            if(is_array($value))
            {
                return $value;
            }
            return array();
        }

        if($this->multiple === false && $value === '')
        {
            $options = $this->getOptions();

            if(count($options) > 0)
            {
                reset($options);
                $value = key($options);
            }
        }

        return $value;
    }

    /**
     * Select::getField()
     *
     * Public: return the HTML of the field
     *
     * @return string the html
     * @author Teye Heimans
     * @author Marien den Besten
     * @since 12-08-2008 Altered by Johan Wiegel, repaired valid html </optgroup> thanks to Roland van Wanrooy
     */
    public function getField()
    {
        // view mode enabled ?
        if($this->getViewMode())
        {
            // get the view value..
            return $this->_getViewValue();
        }

        // multiple selected items possible?
        $aSelected = array();
        if($this->multiple && $this->getValue() !== '')
        {
            $prepare = $this->getValue();

            // when there is a value..
            if(!is_array($prepare))
            {
                // split a string like 1, 4, 6 into an array
                $prepare = explode(',', $prepare);
            }

            //force strings
            if(is_array($prepare))
            {
                $aSelected = array_map(function($value){return (string) $value;}, $prepare);
            }
        }
        elseif($this->getValue() !== '')
        {
            $aSelected = !is_array($this->getValue())
                ? array((string) $this->getValue())
                : $this->getValue();
        }

        // create the options list
        $sOptions = '';

        // added by Roland van Wanrooy: flag to indicate an optgroup, in order to close it properly
        $bOptgroup = false;
        // added by Roland van Wanrooy: string with the close tag
        $sOGclose = "\t</optgroup>\n";
        $options = $this->getOptions();

        foreach($options as $iKey => $sValue)
        {
            // use the array value as field value if wanted
            if(!$this->getUseArrayKeyAsValue())
            {
                $iKey = $sValue;
            }

            if(strpos($iKey, 'LABEL'))
            {
                // added by Roland van Wanrooy: close the optgroup if there is one
                $sOptions .= ($bOptgroup ? $sOGclose : '');
                $sOptions .= "\t<optgroup label=\"" . $sValue . "\">\n";

                // added by Roland van Wanrooy: flag opgroup as true
                $bOptgroup = true;
            }
            else
            {
                $disable = is_array($this->disable_options) && in_array($iKey, $this->disable_options)
                    ? 'disabled="disabled"'
                    : '';

                $sOptions .= sprintf(
                    "\t<option %s value=\"%s\"".$disable." %s>%s</option>\n", $this->options_classes[$iKey],
                    $iKey,
                    (in_array((string) $iKey, $aSelected) ? ' selected="selected"' : ''),
                    str_replace(' ', '&nbsp;', $sValue)
                );
            }
        }

        // when no options are set, set an empty options for XHML compatibility
        if(empty($sOptions))
        {
            $sOptions = "\t<option>&nbsp;</option>\n\t";
        }
        // added by Roland van Wanrooy:
        // $sOptions is not empty, so if there was an <opgroup> then close is properly
        else
        {
            $sOptions .= ($bOptgroup ? $sOGclose : '');
        }

        // return the field
        return sprintf(
            '<select name="%s" id="%s" size="%d"%s>%s</select>%s',
            $this->name . ( $this->multiple ? '[]' : ''),
            $this->name,
            is_null($this->size) ? 1 : $this->size,
            ($this->multiple ? ' multiple="multiple"' : '' )
                . (isset($this->tab_index) ? ' tabindex="' . $this->tab_index . '" ' : '')
                . (isset($this->extra) ? ' ' . $this->extra : '' )
                . ($this->getDisabled() && !$this->getDisabledInExtra() ? 'disabled="disabled" ' : '')
                . 'data-option-count="'.count($options).'" ',
            $sOptions,
            (isset($this->extra_after) ? $this->extra_after : '')
        );
    }

    /**
     * Added by sid benachenhou for handling styles
     *
     * @param string $class_option
     * @return \FormHandler\Field\Select
     */
    public function setCOptions($class_option)
    {
        $this->options_classes = $class_option;
        return $this;
    }

    /**
     * Select::setMultiple()
     *
     * Set if multiple items can be selected or not
     *
     * @param boolean $multiple
     * @return \FormHandler\Field\Select
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setMultiple($multiple)
    {
        if(!is_null($multiple))
        {
            $this->multiple = (bool) $multiple;
        }
        return $this;
    }

    /**
     * Select::setSize()
     *
     * Set the size of the field
     *
     * @param integer|null $size the new size
     * @return \FormHandler\Field\Select
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setSize($size)
    {
        if(!is_null($size))
        {
            $this->size = $size;
        }
        return $this;
    }

    /**
     * Get the display value of the field
     *
     * @return string
     */
    public function getDisplayValue()
    {
        $value = $this->getValue();
        $options = $this->getOptions();

        return (is_scalar($value) && is_array($options) && array_key_exists($value, $options))
            ? $options[$value]
            : '';
    }

    public function setDisabled($bool = null)
    {
        if(is_array($bool))
        {
            $this->disable_options = $bool;
        }
        else
        {
            $this->disable_options = null;
            parent::setDisabled($bool);
        }
        return $this;
    }
}