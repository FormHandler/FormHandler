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
     * getField()
     *
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
        );
    }
}