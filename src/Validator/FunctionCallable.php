<?php

/**
 * Copyright (C) 2015 Marien <marien@colorconcepts.nl>.
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
 * FunctionCallable
 *
 * @author Marien <marien@colorconcepts.nl>
 */
class FunctionCallable extends Validator implements ValidatorInterface
{
    private $callable;
    private $form_object;

    /**
     * Set the callable.
     *
     * Callable needs to return boolean. When string is returned an error is
     * assumed and the string is used as error message.
     *
     * @param callable $callable
     * @param \FormHandler\FormHandler $form_object
     */
    public function __construct($callable, \FormHandler\FormHandler $form_object = null)
    {
        if(!is_callable($callable))
        {
            trigger_error('Given variable is not callable', E_USER_WARNING);
        }

        $this->callable = $callable;
        $this->form_object = $form_object;
    }

    /**
     * Validate the field
     *
     * @param mixed $value
     * @return boolean
     */
    public function validate($value)
    {
        $result = call_user_func($this->callable, $value, $this->form_object, $this->getField());

        if($result !== true && $result !== 1)
        {
            $this->setMessage($result);
            return false;
        }
        return $result;
    }

    /**
     * Get registered callable
     *
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    public function getFormObject()
    {
        return $this->form_object;
    }

    public function setFormObject(\FormHandler\FormHandler $form_object)
    {
        $this->form_object = $form_object;
        return $this;
    }
}
