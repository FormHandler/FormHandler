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
 * class ImageButton
 *
 * Create a image button on the given form
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Button
 */
class Image extends \FormHandler\Button\Button
{
    private $image;

    /**
     * Constructor
     *
     * @param FormHandler $form the form where the image button is located on
     * @param string $name the name of the button
     * @param string $image the image we have to use as button
     * @return \FormHandler\Button\Image
     * @author Teye Heimans
     */
    public function __construct(FormHandler $form, $name)
    {
        return parent::__construct($form, $name);
    }

    /**
     * ImageButton::setImage();
     *
     * Set the image location to be used for the button
     *
     * @param string $image
     * @return \FormHandler\Button\Image
     * @author Marien den Besten
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * ImageButton::getButton()
     *
     * Return the HTML of the button
     *
     * @return string the HTML of the button
     * @author Teye Heimans
     */
    public function getButton()
    {
        // return the button
        return sprintf(
          '<input type="image" src="%s" name="%s" id="%2$s"%s '. \FormHandler\Configuration::get('xhtml_close') .'>',
          $this->image,
          $this->name,
          (isset($this->extra) ? ' '.$this->extra:'').
          (isset($this->tab_index) ? ' tabindex="'.$this->tab_index.'"' : '')
        );
    }
}