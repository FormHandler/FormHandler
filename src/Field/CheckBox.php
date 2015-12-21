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
 * class CheckBox
 *
 * Create a checkbox on the given form object
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Field
 */
class CheckBox extends \FormHandler\Field\Field
{
    private $mask;
    private $loader;

    /**
     * CheckBox::CheckBox()
     *
     * Constructor: Create a new checkbox object
     *
     * @param FormHandler $form The form where this field is located on
     * @param string $name The name of the field
     * @param mixed array|string $options - The options for the field
     * @return \FormHandler\Field\CheckBox
     * @author Teye Heimans
     */
    public function __construct(FormHandler $form, $name, $options = array())
    {
        $this->setDefaultValue(array());
        $this->value = array();
        $this->value_forced = array();
        $this->value_default = array();
        $nameClean = str_replace('[]','', $name);

        //when no checkboxes are selected no data is posted
        if($form->isPosted()
            && !isset($_POST[$nameClean]))
        {
            $this->value_post = array();
        }

        // call the constructor of the Field class
        return parent::__construct($form, $nameClean)
            ->setJsSelectorValue('#' . $form->getFormName() . ' input[name="' . $nameClean . '\[\]"]')
            ->setOptions($options)
            ->setMask(\FormHandler\Configuration::get('default_glue_mask'))
            ->useArrayKeyAsValue(\FormHandler\Configuration::get('default_usearraykey'))
            ->setFocusName($nameClean .'_1');
    }

    /**
     * CheckBox::setValue()
     *
     * Set the value of the field
     *
     * With the append parameter you are able to control how multiple calls behave.
     *
     * @param string|array $value the value to set
     * @param boolean $forced Force value?
     * @param boolean $append Append value to already set values?
     * @return static
     * @author Marien den Besten
     */
    public function setValue($value, $forced = false, $append = false)
    {
        // make an array from the value
        if(!is_array($value))
        {
            $value = explode(',', $value);
            foreach($value as $key => $item)
            {
                $item = trim($item);

                // dont save an empty value when it does not exists in the
                // options array!
                if(!empty($item)
                    || (in_array($item, $this->getOptions())
                        || array_key_exists($item, $this->getOptions())))
                {
                    $value[$key] = $item;
                }
                else
                {
                    unset($value[$key]);
                }
            }
        }

        //mimic parent behavior
        if($forced === true)
        {
            $this->value_forced = ($append) ? array_merge($this->value_forced, $value) : $value;
        }
        else
        {
            $this->value = ($append) ? array_merge($this->value, $value) : $value;
        }
        return $this;
    }

    /**
     * Set default value
     *
     * With the append parameter you are able to control how multiple calls behave.
     *
     * @param mixed $default_value
     * @param boolean $append
     */
    public function setDefaultValue($default_value, $append = false)
    {
        $value_processed = (!is_array($default_value)) ? array($default_value) : $default_value;
        $this->value_default = ($append) ? array_merge($this->value_default, $value_processed) : $value_processed;
        return $this;
    }

    /**
     * Set disabled state
     *
     * @param boolean $value
     * @return static
     * @author Marien den Besten
     */
    public function setDisabled($value = null)
    {
        if(is_bool($value) || is_null($value))
        {
            return parent::setDisabled($value);
        }

        //convert to array
        $this->disabled = !is_array($this->disabled) ? array() : $this->disabled;
        $value_processed = (!is_array($value)) ? array($value) : $value;

        $this->disabled = array_merge($this->disabled, $value_processed);
        return $this;
    }

    /**
     * Field::getValue()
     *
     * Return the value of the field
     *
     * @return mixed the value of the field
     * @author Marien den Besten
     */
    public function getValue()
    {
        $value_default = (isset($this->value_default)) ? $this->value_default : array();
        $value = (isset($this->value)) ? array_merge($value_default, $this->value) : $value_default;
        $value_post = (isset($this->value_post)) ? $this->value_post : $value;
        $value_forced = (isset($this->value_forced)) ? array_merge($this->value_forced, $value_post) : $value_post;
        return array_unique($value_forced);
    }

    /**
     * CheckBox::setMask()
     *
     * Set the glue used to glue multiple checkboxes. This can be a mask
     * where %field% is replaced with a checkbox!
     *
     * @param string $mask
     * @return \FormHandler\Field\CheckBox
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setMask($mask)
    {
        if(!is_null($mask))
        {
            // when there is no %field% used, put it in front of the mask/glue
            if(strpos($mask, '%field%') === false)
            {
                $mask = '%field%' . $mask;
            }

            $this->mask = $mask;
        }
        return $this;
    }

    /**
     * Load mask loader if not done already
     *
     * @author Marien den Besten
     */
    private function maskLoader()
    {
        // create a MaskLoader object when it does not exists yet
        if(!isset($this->loader) || is_null($this->loader))
        {
            $this->loader = new MaskLoader();
            $this->loader->setMask($this->mask);
            $this->loader->setSearch('/%field%/');
        }
    }

    /**
     * Get view value
     *
     * @author Marien den Besten
     * @return string
     */
    public function _getViewValue()
    {
        $text = '';

        $val = $this->getValue();
        $val = (!is_array($val)) ? array($val) : $val;

        $this->maskLoader();

        foreach($this->getOptions() as $key => $value)
        {
            if(!$this->getUseArrayKeyAsValue())
            {
                $key = $value;
            }

            $options = (in_array($key,$val)) ? ' checked="checked"' : '';
            $options .= isset($this->extra) ? ' ' . trim($this->extra) : '';

            $field = '<input type="checkbox"'. $options .' disabled="disabled"><label class="noStyle">'.
                    htmlspecialchars($value) .'</label>';

            $text .= $this->loader->fill($field);
        }

        return $text;
    }

    /**
     * CheckBox::getField()
     *
     * Return the HTML of the field
     *
     * @return string the html of the field
     * @author Teye Heimans
     */
    function getField()
    {
        // view mode enabled ?
        if($this->getViewMode())
        {
            // get the view value..
            return $this->_getViewValue();
        }

        if(count($this->getOptions()) === 0)
        {
            $sResult = '';
        }
        else
        {
            $sResult = '';

            // get the checkboxes
            foreach($this->getOptions() as $key => $value)
            {
                // use the array key as value?
                $key = (!$this->getUseArrayKeyAsValue()) ? $value : $key;

                $sResult .= $this->getCheckBox($key, $value,true);
            }

            // get a possible half filled mask
            $sResult .= $this->loader->fill();
        }

        return $sResult . (isset($this->extra_after) ? $this->extra_after : '');
    }

    /**
     * CheckBox::_getCheckBox()
     *
     * Return an option of the checkbox with the given value
     *
     * @param string $value the value for the checkbox
     * @param string $title the title for the checkbox
     * @param bool $use_mask do we have to use the mask after the field?
     * @return string the HTML for the checkbox
     * @author Teye Heimans
     */
    private function getCheckBox($value, $title, $use_mask = false)
    {
        static $iCounter = array();

        if(!array_key_exists($this->name,$iCounter))
        {
            $iCounter[$this->name] = 1;
        }

        $this->maskLoader();

        // get the field HTML
        $mask = (trim($title) == '') ? '' : '<label for="%2$s_%3$d" class="noStyle">%s</label>';

        //process disabled
        $disabled_value = $this->getDisabled();
        $disabled_global = (is_bool($disabled_value) && $disabled_value === true);
        $disabled_values = (is_array($disabled_value) && in_array($value, $disabled_value));

        $field = sprintf(
            '<input type="checkbox" name="%s" id="%s_%d" value="%s" %s' . \FormHandler\Configuration::get('xhtml_close') . '>' . $mask,
            $this->name . (is_array($this->getOptions()) ? '[]' : ''),
            $this->name,
            $iCounter[$this->name]++,
            htmlspecialchars($value),
            (isset($this->tab_index) ? 'tabindex="' . $this->tab_index . '" ' : '')
                . ((in_array($value, $this->getValue())) ? 'checked="checked" ' : '')
                . (isset($this->extra) ? $this->extra . ' ' : '')
                . (($disabled_global || $disabled_values) && !$this->getDisabledInExtra() ? 'disabled="disabled" ' : ''),
            $title
        );

        // do we have to use the mask ?
        if($use_mask)
        {
            $field = $this->loader->fill($field);
        }
        return $field;
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
}