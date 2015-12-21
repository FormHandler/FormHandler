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
 * class Temperature
 *
 * Create a Temperature field
 *
 * @author Marien den Besten
 * @package FormHandler
 * @subpackage Field
 */
class Temperature extends \FormHandler\Field\Field
{
    private $temperature;
    private $unit;
    private $empty;
    private $preferred_unit;
    private $allow_empty = false;
    private $value_set = false;
    private $units = array(
        'celsius' => 'Celsius',
        'fahrenheit' => 'Fahrenheit'
    );

    /**
     * Create a new temperature field object
     *
     * @param FormHandler $form The form where this field is located on
     * @param string $name The name of the field
     * @return \FormHandler\Field\Temperature
     * @author Marien den Besten
     */
    public function __construct(FormHandler $form, $name)
    {
        $this->empty = new \FormHandler\Field\CheckBox($form, $name .'_empty');
        $this->empty->setOptions(array(1 => 'Value unknown'));

        $empty = $this->empty->getValue();
        $this->value = !empty($empty) ? null : $this->value;

        $this->temperature = new \FormHandler\Field\Number($form, $name .'_temperature');
        $this->temperature->setMin(-1000);
        $this->temperature->setMax(1000);
        $this->temperature->setStep(0.1);
        $this->temperature->setValidator(function($value)
        {
            return is_numeric($value);
        });

        $this->unit = new \FormHandler\Field\Select($form, $name .'_unit');
        $this->unit->setOptions($this->units);

        if(empty($empty))
        {
            $this->value = array(
                $this->temperature->getValue(),
                $this->unit->getValue()
            );
        }

        //Classes
        $this->temperature->setExtra('class="temperature-field"');
        $this->unit->setExtra('class="temperature-field"');
        $this->empty->setExtra('class="temperature-field"');

        parent::__construct($form, $name)
            ->setFocusName($name .'_temperature');

        $form->_setJS(''
            . "$('#". $name ."_empty_1').on('change',function()\n"
            . "{\n"
            . " var state = !!$(this).attr('checked');\n"
            . " $('#". $name ."_temperature').attr('disabled',state);\n"
            . " $('#". $name ."_unit').attr('disabled',state);\n"
            . "});\n"
            . "$('#". $name ."_unit').on('change',function()\n"
            . "{\n"
            . " var val = $(this).val(),\n"
            . "     temp = $('#". $name ."_temperature').val();\n"
            . " if(val == 'celsius') $('#". $name ."_temperature').val(Math.round(((temp - 32) / 1.8)*10)/10);\n"
            . " if(val == 'fahrenheit') $('#". $name ."_temperature').val(Math.round(((temp * 1.8) + 32)*10)/10);\n"
            . "});\n",false,false);

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
     * Check if field is valid
     *
     * @author Marien den Besten
     * @return boolean
     */
    public function processValidators()
    {
        $this->temperature->processValidators();
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
        $empty_value = $this->empty->getValue();

        if($this->allow_empty === true
            && !empty($empty_value))
        {
            return false;
        }

        return $this->temperature->getErrorState();
    }

    /**
     * The error message
     *
     * @return string
     * @author Marien den Besten
     */
    public function getErrorMessage()
    {
        return $this->temperature->getErrorMessage();
    }

    /**
     * Set the validator
     *
     * @author Marien den Besten
     * @param string|array|callable $validator
     * @return \FormHandler\Field\Temperature
     */
    public function setValidator($validator)
    {
        $this->temperature->setValidator($validator);
        return $this;
    }

    /**
     * Get validator
     *
     * @author Marien den Besten
     * @return string|array|callable
     */
    public function getValidators()
    {
        return $this->temperature->getValidators();
    }

    /**
     * Function not implemented!
     *
     * @param string $sExtra The extra information to include with the html tag
     * @return void
     * @throws \Exception
     * @author Marien den Besten
     */
    public function setExtra($sExtra,$append = false)
    {
    	throw new \Exception('This function has not been implemented for the temperature field');
    }

    /**
     * Set the value of the field
     *
     * @param integer|array $value The new value of the field
     * @return \FormHandler\Field\Temperature
     * @author Marien den Besten
     */
    public function setValue($value, $forced = false)
    {
        $this->empty->setValue(0, $forced);

        if(is_object($value)
            && method_exists($value, 'getTemperature')
            && method_exists($value, 'getUnit'))
        {
            $unit = $value->getUnit() != 'fahrenheit' ? 'celsius' : 'fahrenheit';
            parent::setValue(array($value->getTemperature(), $unit), $forced);
            $this->temperature->setValue($value->getTemperature(), $forced);
            $this->unit->setValue($unit, $forced);
        }
        elseif(is_array($value) && count($value) == 2)
        {
            parent::setValue($value, $forced);
            $this->temperature->setValue($value[0], $forced);
            $this->unit->setValue($value[1], $forced);
        }
        elseif(is_numeric($value))
        {
            $this->temperature->setValue($value, $forced);
            parent::setValue(array($value,$this->unit->getValue()), $forced);
        }
        elseif(is_null($value))
        {
            $this->empty->setValue(1, $forced);
            parent::setValue(null, $forced);
        }

        $current_unit = $this->unit->getValue();
        $preferred_unit = $this->preferred_unit;

        if(!is_null($value)
            && !is_null($preferred_unit)
            && $current_unit != $preferred_unit)
        {
            $value = $this->convert(
                $this->temperature->getValue(),
                $current_unit,
                $preferred_unit
            );
            $this->temperature->setValue($value, $forced);
            $this->unit->setValue($preferred_unit, $forced);
            parent::setValue(array($value,$preferred_unit), $forced);
        }
        $this->value_set = true;
        return $this;
    }

    /**
     * Return the current value of the field
     *
     * @return array the value of the field
     * @author Marien den Besten
     */
    public function getValue()
    {
        $empty = $this->empty->getValue();

        return !empty($empty)
            ? null
            : array(
                $this->temperature->getValue(),
                $this->unit->getValue()
            );
    }

    /**
     * Set preferred unit
     *
     * @param string $unit
     * @author Marien den Besten
     */
    public function setUnit($unit)
    {
        //translate buggy definitions
        $unit = ($unit === 'celcius') ? 'celsius' : $unit;

        if(array_key_exists($unit,$this->units)
            && !$this->form_object->isPosted()) //skip preference when posted
        {
            $current = $this->unit->getValue();
            $this->unit->setValue($unit);
            $this->preferred_unit = $unit;

            if($this->getValue() === ''
                || $current == $unit)
            {
                return $this;
            }

            if($this->value_set)
            {
                $value = $this->convert($this->temperature->getValue(), $current, $unit);

                $this->value = array($value,$unit);
                $this->temperature->setValue($value);
            }
        }
        return $this;
    }

    /**
     * Convert between formats
     *
     * @author Marien den Besten
     * @param integer $value
     * @param string $from
     * @param string $to
     * @return type
     */
    private function convert($value,$from,$to)
    {
        if($from == 'fahrenheit')
        {
            return round(($value - 32) / 1.8,1);
        }
        if($from == 'celsius' || $from == 'celcius')
        {
            return round(($value * 1.8) + 32,1);
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
        return (is_null($this->getValue()))
            ? '-'
            : $this->temperature->getValue() .'&deg; '. $this->units[$this->unit->getValue()];
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
        $this->unit->setDisabled($bool);
        $this->temperature->setDisabled($bool);
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
            // get the view value..
            return $this->_getViewValue();
        }

        if(is_null($this->getValue()))
        {
            $this->empty->setValue(1);
            $this->unit->setDisabled();
            $this->temperature->setDisabled();
        }

        return
            $this->temperature->getField() .'&deg; '. $this->unit->getField()
            . ($this->allow_empty === true
                ? '<div class="temperature-field-unknown">'. $this->empty->getField() .'</div>'
                : '');
    }
}