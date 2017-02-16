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
 * class Date
 *
 * Create a date field
 *
 * @author Ruben de Vos
 * @package FormHandler
 * @subpackage Field
 */
class Date extends \FormHandler\Field\Field
{
    private $maxDate;
    private $minDate;

    /**
     * Check if date is in correct format
     *
     * @param string $date
     * @return boolean
     */
    public static function validate($date)
    {
        $validator = new \FormHandler\Validator\Date();
        return $validator->validate($date);
    }

    /**
     * Get max date
     *
     * @return string|null
     */
    public  function getMaxDate()
    {
        return $this->maxDate;
    }

    /**
     * Get min date
     *
     * @return string|null
     */
    public function getMinDate()
    {
        return $this->minDate;
    }

    /**
     * Set max date
     *
     * @param string $maxDate
     * @return \FormHandler\Field\Date
     */
    public function setMaxDate($maxDate)
    {
        if(!self::validate($maxDate))
        {
            throw new \Exception('Invalid date provided. Date should be in format Y-m-d');
        }
        $this->maxDate = $maxDate;
        return $this;
    }

    /**
     * Set min date
     *
     * @param string $minDate
     * @return \FormHandler\Field\Date
     */
    public function setMinDate($minDate)
    {
        if(!self::validate($minDate))
        {
            throw new \Exception('Invalid date provided. Date should be in format Y-m-d');
        }
        $this->minDate = $minDate;
        return $this;
    }

    /**
     * Return the HTML of the field
     *
     * @return string the html
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

        return sprintf(
                '<input type="date" name="%s" id="%1$s" min="%s" max="%s" value="%s" %s' . \FormHandler\Configuration::get('xhtml_close') . '>%s',
                $this->name,
                $this->getMinDate(),
                $this->getMaxDate(),
                htmlspecialchars($this->getValue()->format('Y-m-d')),
                    (isset($this->extra) ? ' ' . $this->extra . ' ' : '')
                    . ($this->getDisabled() && !$this->getDisabledInExtra() ? 'disabled="disabled" ' : ''),
                (isset($this->extra_after) ? $this->extra_after : '')
        );
    }
}