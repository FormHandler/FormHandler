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
 * class Text
 *
 * Create a text field
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Field
 */
class Text extends \FormHandler\Field\Field
{
    private $size = null;
    private $max_length = null;

    /**
     * Constructor
     *
     * Create a new text field
     *
     * @param FormHandler $form The form where this field is located on
     * @param string $name The name of the field
     * @return \FormHandler\Field\Text
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function __construct(FormHandler $form, $name)
    {
        // call the constructor of the Field class
        return parent::__construct($form, $name)
            ->setSize(20)
            ->setMaxlength(0);
    }

    /**
     * Set the new size of the field
     *
     * @param integer|null $size the new size
     * @author Teye Heimans
     * @author Marien den Besten
     * @return \FormHandler\Field\Text
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Get size
     *
     * @author Marien den Besten
     * @return integer|null
     */
    public function getSize()
    {
        return empty($this->size) ? 20 : $this->size;
    }

    /**
     * Check the maxlength of the field
     *
     * @param integer $iLength the maxlength
     * @return boolean
     * @author Johan Wiegel
     * @since 17-04-2009
     */
    public function checkMaxLength($iLength)
    {
        if(strlen($this->getValue()) > $iLength)
        {
            $this->error = $this->form_object->_text(14);
            return false;
        }
        return true;
    }

    /**
     * Check the minlength of the field
     *
     * @param integer $iLength the maxlength
     * @return boolean
     * @author Johan Wiegel
     * @since 17-04-2009
     */
    public function checkMinLength($iLength)
    {
        if(strlen($this->getValue()) < $iLength)
        {
            $this->error = $this->form_object->_text(14);
            return false;
        }
        return true;
    }

    /**
     * Set the new maxlength of the field
     *
     * @param integer $max_length the new maxlength
     * @author Teye Heimans
     * @author Marien den Besten
     * @return \FormHandler\Field\Text
     */
    public function setMaxlength($max_length)
    {
        if((int) $max_length > 0)
        {
            $this->max_length = (int) $max_length;
        }
        return $this;
    }

    /**
     * Get max length of field
     *
     * @author Marien den Besten
     * @return integer|null
     */
    public function getMaxLength()
    {
        return $this->max_length;
    }

    /**
     * Return the HTML of the field
     *
     * @return string the html
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

        return sprintf(
                '<input type="text" name="%s" id="%1$s" value="%s" size="%d" %s' . \FormHandler\Configuration::get('xhtml_close') . '>%s',
                $this->name,
                htmlspecialchars($this->getValue()),
                $this->getSize(),
                (!is_null($this->getMaxLength()) ? 'maxlength="' . $this->getMaxLength() . '" ' : '') .
                    (isset($this->tab_index) ? 'tabindex="' . $this->tab_index . '" ' : '') .
                    (isset($this->extra) ? ' ' . $this->extra . ' ' : '')
                    . ($this->getDisabled() && !$this->getDisabledInExtra() ? 'disabled="disabled" ' : ''),
                (isset($this->extra_after) ? $this->extra_after : '')
        );
    }
}