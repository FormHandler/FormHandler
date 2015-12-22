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
 * class Length
 *
 * @author Marien den Besten
 * @package FormHandler
 * @subpackage Field
 */
class Length extends \FormHandler\Field\Number
{
    const MILLIMETER = 'mm';
    const CENTIMETER = 'cm';
    const METER = 'm';
    const INCH = 'in';
    const FEET = 'ft';

    private $length;
    private $unit;
    private $preferred_unit;
    private $value_set = false;
    private $units = array(
        self::MILLIMETER => 'Millimeter',
        self::CENTIMETER => 'Centimeter',
        self::METER => 'Meter',
        self::INCH => 'Inch',
        self::FEET => 'Feet',
    );
    private $conversion_table = array(
        'ft' => array(
            'ft' => 1,
            'in' => 12,
            'm' => 0.3048,
            'cm' => 30.48,
            'mm' => 304.8,
        ),
        'in' => array(
            'ft' => 0.083333333333,
            'in' => 1,
            'm' => 0.0254,
            'cm' => 2.54,
            'mm' => 25.4,
        ),
        'm' => array(
            'ft' => 3.2808399,
            'in' => 39.3700787,
            'm' => 1,
            'cm' => 100,
            'mm' => 1000,
        ),
        'cm' => array(
            'ft' => 0.032808399,
            'in' => 0.393700787,
            'm' => 0.01,
            'cm' => 1,
            'mm' => 10,
        ),
        'mm' => array(
            'ft' => 0.0032808399,
            'in' => 0.0393700787,
            'm' => 0.001,
            'cm' => 0.1,
            'mm' => 1,
        ),
    );

    /**
     * Constructor
     *
     * Create a new Length field object
     *
     * @param FormHandler $form The form where this field is located on
     * @param string $name The name of the field
     * @return \FormHandler\Field\Length
     * @author Marien den Besten
     */
    public function __construct(FormHandler $form, $name)
    {
        $this->length = new \FormHandler\Field\Number($form, $name . '_value');
        $this->length->setValidator(function($value)
        {
            return trim($value) == '' || is_numeric($value);
        });

        $this->unit = new \FormHandler\Field\Select($form, $name . '_unit');
        $this->unit->setOptions($this->units);

        //Classes
        $this->length->setExtra('class="length-field"');
        $this->unit->setExtra('class="length-field"');

        parent::__construct($form, $name)
            ->setFocusName($name . '_value');

        $form->_setJS(''
            . "var el = $('#" . $name . "_unit');\n"
            . "el.data('oldvalue', el.val());\n"
            . "el.on('change',function()\n"
            . "{\n"
            . " var from_value = $(this).data('oldvalue'),\n"
            . "     to_value = $(this).val(),\n"
            . "     length = $('#" . $name . "_value').val(),\n"
            . "     conversion = " . json_encode($this->conversion_table) . ";\n"
            . " if(from_value in conversion && to_value in conversion[from_value])\n"
            . " {\n"
            . "     $('#" . $name . "_value').val(Math.round((length*100)*conversion[from_value][to_value])/100);"
            . " }\n"
            . " $(this).data('oldvalue',to_value);\n"
            . "});\n", false, false);

        return $this;
    }

    /**
     * Set validator
     *
     * @param callable $validator
     * @return \FormHandler\Field\Length
     * @author Marien den Besten
     */
    public function setValidator($validator)
    {
        $this->length->setValidator($validator);
        return $this;
    }

    /**
     * Process validators
     *
     * @return \FormHandler\Field\Length
     */
    public function processValidators()
    {
        $this->length->processValidators();
        return $this;
    }

    /**
     * Get if the field is in error state
     *
     * @return boolean
     * @author Marien den Besten
     */
    public function getErrorState()
    {
        return $this->length->getErrorState();
    }

    /**
     * Get error message
     *
     * @return string
     * @author Marien den Besten
     */
    public function getErrorMessage()
    {
        return $this->length->getErrorMessage();
    }

    /**
     * Set step
     *
     * @param integer $step
     * @return \FormHandler\Field\Length
     */
    public function setStep($step)
    {
        $this->length->setStep($step);
        return $this;
    }

    /**
     * Set the value of the field
     *
     * @param integer|array $value The new value of the field
     * @return \FormHandler\Field\Length
     * @author Marien den Besten
     */
    public function setValue($value, $forced = false)
    {
        if(is_object($value)
            && method_exists($value, 'getValue')
            && method_exists($value, 'getUnit'))
        {
            $this->length->setValue($value->getValue(), $forced);
            $this->unit->setValue($value->getUnit(), $forced);

            if(is_null($value->getValue()))
            {
                //return when value is empty
                return $this;
            }
        }
        elseif(is_array($value) && count($value) == 2)
        {
            $this->length->setValue($value[0], $forced);
            $this->unit->setValue($value[1], $forced);
        }
        elseif(is_numeric($value))
        {
            $unit = key($this->units);

            $this->length->setValue($value, $forced);
            $this->unit->setValue($unit, $forced);
        }
        else
        {
            //fail silently
            return $this;
        }

        $current_unit = $this->unit->getValue();
        $preferred_unit = $this->preferred_unit;

        if(!is_null($this->length->getValue()) && !is_null($preferred_unit) && $current_unit != $preferred_unit)
        {
            $conversion = $this->convert(
                $this->length->getValue(), $current_unit, $preferred_unit, 2
            );
            $this->length->setValue($conversion, $forced);
            $this->unit->setValue($preferred_unit, $forced);
        }

        $this->value_set = true;
        return $this;
    }

    /**
     * Set preferred unit
     *
     * @param string $unit
     * @author Marien den Besten
     */
    public function setUnit($unit)
    {
        if(array_key_exists($unit, $this->units) && !$this->form_object->isPosted()) //skip preference when posted
        {
            $current = $this->unit->getValue();
            $this->unit->setValue($unit);
            $this->preferred_unit = $unit;

            if(is_null($this->length->getValue()) || $current == $unit)
            {
                return $this;
            }

            if($this->value_set && $this->length->getValue() != '')
            {
                $value = $this->convert($this->length->getValue(), $current, $unit, 2);

                $this->length->setValue($value);
                $this->unit->setValue($unit);
            }
        }
        return $this;
    }

    /**
     * Get value
     *
     * @return array
     */
    public function getValue()
    {
        return is_null($this->length->getValue()) || trim($this->length->getValue()) == '' 
            ? null
            : array($this->length->getValue(), $this->unit->getValue());
    }

    /**
     * Convert between formats
     *
     * @author Marien den Besten
     * @param integer $value
     * @param string $from
     * @param string $to
     * @param integer $round
     * @return type
     */
    private function convert($value, $from, $to, $round = null)
    {
        if($to_unit == $from_unit)
        {
            //return if no conversion is needed
            return (!is_null($round)) ? (string) round($from_measure, $round) : (string) $from_measure;
        }

        if(!array_key_exists($from_unit, $this->conversion_table) || !array_key_exists($to_unit, $this->conversion_table[$from_unit]))
        {
            return 0;
        }

        //set precision
        $round = (is_null($round)) ? 50 : $round;
        return self::remove_bc_trailing_zeros(bcmul($from_measure, $this->conversion_table[$from_unit][$to_unit], $round));
    }

    /**
     * Set disabled
     * 
     * @param boolean $bool
     * @return \FormHandler\Field\Field
     */
    public function setDisabled($bool = true)
    {
        $this->length->setDisabled($bool);
        $this->unit->setDisabled($bool);
        return parent::setDisabled($bool);
    }

    /**
     * Return the HTML of the field
     *
     * @return string the html
     * @author Marien den Besten
     */
    public function getField()
    {
        // view mode enabled ?
        if($this->getViewMode())
        {
            $extra = ($this->length->getValue() == '' || is_null($this->length->getValue())) ? '' : ' ' . $this->unit->_getViewValue();
            return $this->length->_getViewValue() . $extra;
        }

        return $this->length->getField() . ' ' . $this->unit->getField();
    }
}
