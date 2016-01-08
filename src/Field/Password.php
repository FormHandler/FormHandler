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

use \FormHandler\Validator;

/**
 * class Password
 *
 * Create a Password
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Field
 */
class Password extends \FormHandler\Field\Text
{
    private $pre = '';

    /**
     * Password::getField()
     *
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
            return '****';
        }

        return sprintf(
            '%s<input type="password" name="%s" id="%2$s" size="%d" %s' . \FormHandler\Configuration::get('xhtml_close') . '>%s',
            $this->pre,
            $this->name,
            $this->getSize(),
            (!is_null($this->getMaxLength()) ? 'maxlength="' . $this->getMaxLength() . '" ' : '')
                . (isset($this->tab_index) ? ' tabindex="' . $this->tab_index . '" ' : '')
                . (isset($this->extra) ? $this->extra . ' ' : '')
                . ($this->getDisabled() && !$this->getDisabledInExtra() ? 'disabled="disabled" ' : ''),
            (isset($this->extra_after) ? $this->extra_after : '')
        );
    }

    /**
     * Password::setPre()
     *
     * Set the message above the password field
     *
     * @param string $message the message
     * @return \FormHandler\Field\Password
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setPre($message)
    {
        $this->pre = $message;
        return $this;
    }

    /**
     * Password::checkPassword()
     *
     * Check the value of this field with another password field
     *
     * @param \FormHandler\Field\Password $object
     * @return boolean true if the values are correct, false if not
     * @author Teye Heimans
     */
    public function checkPassword($object)
    {
        // if the fields doesn't match
        if($this->getValue() != $object->getValue())
        {
            $this->setErrorMessage(\FormHandler\Language::get(15));
            $this->setErrorState(true);
            return;
        }

        // when there is no value
        if($this->getValue() == '' && !$this->form_object->edit)
        {
            $this->setErrorMessage(\FormHandler\Language::get(16));
            $this->setErrorState(true);
            return;
        }
        elseif($this->getValue() == '')
        {
            //in edit mode and value is empty
            return;
        }

        $validator = new Validator\Password();

        // is the password not to short ?
        if(strlen($this->getValue()) < \FormHandler\Configuration::get('min_password_length'))
        {
            $this->setErrorMessage(sprintf(
                \FormHandler\Language::get(17),
                \FormHandler\Configuration::get('min_password_length')
            ));
            $this->setErrorState(true);
            return;
        }
        // is it an valif password ?
        elseif(!$validator->validate($this->getValue()))
        {
            $this->setErrorMessage(\FormHandler\Language::get(18));
            $this->setErrorState(true);
            return;
        }
    }
}