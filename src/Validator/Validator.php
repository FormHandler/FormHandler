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
 */

namespace FormHandler\Validator;

/**
 * Validator
 *
 * @author Marien <marien@colorconcepts.nl>
 */
class Validator
{
    protected $field;
    private $message;
    private $required = false;

    /**
     * Set field object
     *
     * @param \FormHandler\Field\Field $field
     * @return \FormHandler\Validator\Validator
     */
    public function setField(\FormHandler\Field\Field $field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * Field object
     *
     * @return \FormHandler\Field\Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Message to display when validation went wrong
     *
     * @return string
     */
    public function getMessage()
    {
        return (!empty($this->message)) ? $this->message : \FormHandler\Language::get(14);
    }

    /**
     * Set message
     *
     * @param string $message
     * @return static
     */
    public function setMessage($message)
    {
        if(is_string($message))
        {
            $this->message = $message;
        }
        return $this;
    }

    /**
     * Set required validator
     *
     * @param boolean $required
     * @return static
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;
        return $this;
    }

    /**
     * Get if required validator
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }
}
