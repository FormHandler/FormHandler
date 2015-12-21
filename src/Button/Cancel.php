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

namespace FormHandler\Button;

use \FormHandler\FormHandler;

/**
 * class Cancel
 *
 * Create a cancel button on the given form
 *
 * @author Marien den Besten
 * @package FormHandler
 * @subpackage Button
 */
class Cancel extends \FormHandler\Button\Button
{
    private $url = null;

    /**
     * Constructor: Create a new ImageButton object
     *
     * @param FormHandler $form the form where the image button is located on
     * @param string $name the name of the button
     * @return \FormHandler\Button\Cancel
     * @author Marien den Besten
     */
    public function __construct(FormHandler $form, $name)
    {
        return parent::__construct($form, $name);
    }

    /**
     * Set the url location for button click
     *
     * @author Marien den Besten
     * @param string $url
     * @return \FormHandler\Button\Cancel
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get the button
     *
     * @author Marien den Besten
     * @return string
     */
    public function getButton()
    {
        $this->extra .= ' onclick="';
        $this->extra .= (is_null($this->url))
            ? 'history.back(-1)'
            : 'document.location.href=\''.$this->url.'\'';
        $this->extra .= '"';

        if(is_null($this->caption))
        {
            $this->caption = $this->form->_text(28);
        }

        return parent::getButton();
    }
}