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
class Captcha extends \FormHandler\Field\Text
{
    private $image;
    private $width;
    private $height;

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
        static $bCaptcha = true;
        if(!$bCaptcha)
        {
            trigger_error("Please use only one Captcha field on a page", E_USER_WARNING);
        }

        $bCaptcha = false;

        if(!isset($_SESSION))
        {
            trigger_error("Please make sure you have an active session", E_USER_WARNING);
        }

        $this->image = new \FormHandler\Button\Image($form, $name .'_image');
        $this->image->setExtra('onclick="return false;" style="cursor:default;"');

        // call the constructor of the Field class
        parent::__construct($form, $name)
            ->setSize(\FormHandler\Configuration::get('captcha_length'))
            ->setWidth(\FormHandler\Configuration::get('captcha_width'))
            ->setMaxlength(0)
            ->setValidator(new \FormHandler\Validator\FunctionCallable(function($value)
            {
                require_once(__DIR__ . '/../FHTML/securimage/securimage.php');

                $img = new \Securimage();
                $valid = $img->check($value);
                return ($valid == true);
            }))
            ->setRequired(true);
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setWidth($width)
    {
        $this->width = (int) $width;
        return $this;
    }

    public function setHeight($height)
    {
        $this->height = (int) $height;
        return $this;
    }

    /**
     * Return the HTML of the field
     *
     * @return string the html
     * @author Teye Heimans
     */
    public function getField()
    {
        // empty the field if the value was not correct.
        if($this->form_object->isPosted() && !$this->form_object->isCorrect())
        {
            $this->setValue('', true);
        }

        // view mode enabled ?
        if($this->getViewMode())
        {
            // get the view value..
            return '';
        }

        $session_id = \session_id();

        //get url from configuration
        $configured_url = \FormHandler\Configuration::get('securimage_url');

        $url = (is_null($configured_url))
            ? \FormHandler\Configuration::get('fhtml_dir') .'securimage/securimage_show.php'
            : $configured_url;

        $url .=  '?sid=' . md5(uniqid(time())). '&session_id=' . $session_id
            . '&width=' . $this->width . '&height=' . $this->height . '&length=' . $this->getSize();

        $this->image->setImage($url);

        $refresh_text = \FormHandler\Language::get(44);
        $current_url = @htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], ENT_COMPAT, 'UTF-8');

        $refresh = '<a id="captcha_refresh" href="//' . $current_url
            . '" onclick="document.getElementById(\'' . $this->getName() . '_image\').src=\'' . $url . '\'; return false;">'
            . $refresh_text . '</a>';

        return $this->image->getButton() . "<br>" . $refresh . "<br>" . parent::getField();
    }
}