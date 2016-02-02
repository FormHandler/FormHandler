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
 * class Number
 *
 * Create a field
 *
 * @author Marien den Besten
 * @package FormHandler
 * @subpackage Field
 */
class Number extends \FormHandler\Field\Field
{
    private $min;
    private $max;
    private $step;
    private $empty;
    private $allow_empty = false;
    private $allow_empty_text;

    public function __construct($form, $name)
    {
        $this->allow_empty_text = 'Value unknown';
        $this->empty = new \FormHandler\Field\CheckBox($form, $name .'_empty');
        $this->empty->setOptions(array(1 => '<em>'. $this->allow_empty_text .'</em>'));

        $empty = $this->empty->getValue();
        $this->value = !empty($empty) ? null : $this->value;

        parent::__construct($form, $name);

        $form->_setJS("$(document).ready(function(){\n"
            . "$('#". $name ."_empty_1').on('change',function()\n"
            . "{\n"
            . " var state = !!$(this).prop('checked');console.log(state);\n"
            . " $('#". $name ."').prop('disabled',state);\n"
            . "});\n"
            . "});"
        );

        return $this;
    }

    /**
     * Set if field is allowed to be empty/unknown
     *
     * @param boolean $bool
     * @author Marien den Besten
     * @return \FormHandler\Field\Temperature
     */
    public function allowEmpty($bool)
    {
        $this->allow_empty = (bool) $bool;

        if((bool) $bool === false
            && is_null($this->getValue()))
        {
            $this->setValue(0);
        }
        return $this;
    }

    /**
     * Set empty text
     *
     * @param string $text
     * @return static
     */
    public function setEmptyText($text)
    {
        if(is_string($text) && trim($text) != '')
        {
            $this->allow_empty_text = $text;
            $this->empty->setOptions(array(1 => '<em>'. $this->allow_empty_text .'</em>'));
        }
    }

    /**
     * Get empty text
     *
     * @return string
     */
    public function getEmptyText()
    {
        return $this->allow_empty_text;
    }

    public function setValue($value, $forced = false)
    {
        $this->empty->setValue(0, $forced);

        if(is_null($value))
        {
            $this->empty->setValue(1, $forced);
            return parent::setValue(null, $forced);
        }

        return parent::setValue($value, $forced);
    }

    /**
     * Return the current value of the field
     *
     * @return array the value of the field
     * @author MarienRuben de Vos
     */
    public function getValue()
    {
        $empty = $this->empty->getValue();

        return !empty($empty)
            ? null
            : parent::getValue();
    }

    /**
     * Set minimum value
     *
     * @param integer $min
     * @return \FormHandler\Field\Number
     * @author Marien den Besten
     */
    public function setMin($min)
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Set maximum
     *
     * @param integer $max
     * @return \FormHandler\Field\Number
     * @author Marien den Besten
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Set the allowed steps
     *
     * @param integer $step
     * @return \FormHandler\Field\Number
     * @author Marien den Besten
     */
    public function setStep($step)
    {
        $this->step = $step;
        return $this;
    }

    /**
     * Get view value
     *
     * @author Ruben de Vos
     * @return string
     */
    public function _getViewValue()
    {
        return (is_null($this->getValue()))
            ? '-'
            : $this->getValue();
    }

    /**
     * getField()
     *
     * Return the HTML of the field
     *
     * @return string the html
     * @author Marien den Besten
     * @author Ruben de Vos
     */
    public function getField()
    {
        // view mode enabled ?
        if($this->getViewMode())
        {
            // get the view value..
            return $this->_getViewValue();
        }

        if(is_null($this->getValue()))
        {
            $this->empty->setValue(1);
            $this->setDisabled(true);
            $this->empty->setDisabled(false);
        }

        return sprintf(
            '<input type="number" name="%s" id="%1$s" value="%s" %s' . \FormHandler\Configuration::get('xhtml_close') . '>%s',
            $this->name,
            htmlspecialchars($this->getValue()),
            (!is_null($this->min) ? 'min="' . $this->min . '" ' : '') .
                (!is_null($this->max) ? 'max="' . $this->max . '" ' : '') .
                (!is_null($this->step) ? 'step="' . $this->step . '" ' : '') .
                (isset($this->tab_index) ? 'tabindex="' . $this->tab_index . '" ' : '') .
                (isset($this->extra) ? ' ' . $this->extra . ' ' : '')
                . ($this->getDisabled() && !$this->getDisabledInExtra() ? 'disabled="disabled" ' : ''),
            (isset($this->extra_after) ? $this->extra_after : '')
        ) . ($this->allow_empty === true
            ? '<div class="number-field-unknown">'. $this->empty->getField() .'</div>'
            : '');
    }

    /**
     * Set disabled
     *
     * @param boolean $bool
     * @return \FormHandler\Field\Field
     */
    public function setDisabled($bool = true)
    {
        $this->empty->setDisabled($bool);
        return parent::setDisabled($bool);
    }

    /**
     * Get if the field is in error state
     *
     * @return boolean
     * @author Marien den Beste©n
     */
    public function getErrorState()
    {
        $empty_value = $this->empty->getValue();

        if($this->allow_empty === true
            && !empty($empty_value))
        {
            return false;
        }

        return parent::getErrorState();
    }
}