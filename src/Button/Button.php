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
 * class Button
 *
 * Create a button on the given form
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Button
 */
class Button
{
    const TYPE_BUTTON = 'button';
    const TYPE_SUBMIT = 'submit';
    const TYPE_RESET = 'reset';

    protected $form;
    protected $name;
    protected $type;
    protected $extra;
    protected $caption;
    protected $tab_index;
    protected $disable_on_submit;
    protected $confirmation;
    protected $confirmation_description;
    private $focus_name;
    private $disabled;
    private $processors = array();

    /**
     * Register the field with FormHandler
     *
     * @param FormHandler $form
     * @param string|null $caption
     * @param string|null $name
     * @param mixed $validator
     * @return static Instance of
     */
    public static function set(
        FormHandler $form,
        $caption = null,
        $name = null)
    {
        $class = get_called_class();
        $processed_name = (empty($name)) ? $form->getNewButtonName() : $name;

        // create the field
        $fld = new $class($form, $processed_name);
        $fld->setCaption($caption);

        // register the field
        $form->registerField($processed_name, $fld);
        return $fld;
    }

    /**
     * Constructor: create a new Button object
     *
     * @param FormHandler $form the form where the button is located on
     * @param string $name the name of the button
     * @return static
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function __construct(FormHandler $form, $name)
    {
        //set the button name and caption
        $this->form = $form;
        $this->name = $name;

        return $this
            ->setType(self::TYPE_BUTTON)
            ->disableOnSubmit(\FormHandler\Configuration::get('default_disable_submit_btn'))
            ->setFocusName($name);
    }

    /**
     * Set disabled state
     *
     * @param boolean $bool
     * @return static
     * @author Marien den Besten
     */
    public function setDisabled($bool = null)
    {
        $this->disabled = is_null($bool) ? true : (bool) $bool;
        return $this;
    }

    /**
     * Get if button is disabled
     *
     * @return boolean
     * @author Marien den Besten
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Check if there is a disabled entry in the extra string
     * @return boolean
     */
    protected function getDisabledInExtra()
    {
        $haystack = strtolower($this->extra);
        return (substr($haystack,-8,8) === 'disabled'
            || strpos($haystack, 'disabled ') !== false
            || strpos($haystack, 'disabled='));
    }

    /**
     * Set button type
     *
     * @param string $type Constant of the Button class
     * @return \FormHandler\Button\Button
     */
    protected function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set a processor to run when this button is used to submit the form
     *
     * Callable will get the form object as parameter
     *
     * @param callable $callable
     */
    public function onClick($callable)
    {
        if(is_callable($callable))
        {
            $this->processors[] = $callable;
        }
        return $this;
    }

    /**
     * Method called on post
     *
     * @param FormHandler $form
     * @return null
     */
    public function onPost(FormHandler $form)
    {
        //start processing click handlers when this button is used to submit
        if(isset($_POST[$this->name]))
        {
            foreach($this->processors as $processor)
            {
                call_user_func($processor, $form);
            }
        }
    }

    /**
     * Get the current confirmation message
     *
     * @return string|null
     */
    public function getConfirmation()
    {
        return $this->confirmation;
    }

    /**
     * Get the current confirmation description
     *
     * @return string|null
     */
    public function getConfirmationDescription()
    {
        return $this->confirmation_description;
    }

    /**
     * Set the confirmation message which will be displayed on click
     *
     * @param string $confirmation
     * @return \FormHandler\Button\Button
     */
    public function setConfirmation($confirmation)
    {
        $this->confirmation = $confirmation;
        return $this;
    }

    /**
     * Set the confirmation description message which will be displayed on click
     *
     * Will only be applied when setConfirmation is called
     *
     * @param string $confirmationDescription
     * @return \FormHandler\Button\Button
     */
    public function setConfirmationDescription($confirmationDescription)
    {
        $this->confirmation_description = $confirmationDescription;
        return $this;
    }

    /**
     * Field::setTabIndex()
     *
     * set the tabindex of the field
     *
     * @param integer $iIndex
     * @return static
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setTabIndex($iIndex)
    {
        $this->tab_index = (int) $iIndex;
        return $this;
    }

    /**
     * SubmitButton::disableOnSubmit()
     *
     * Set if the button has to be disabled after pressing it
     * (avoid double submit!)
     *
     * @param boolean $bStatus
     * @return static
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function disableOnSubmit($bStatus)
    {
        $this->disable_on_submit = (bool) $bStatus;
        return $this;
    }


    /**
     * Button::setCaption()
     *
     * Set the caption of the button
     *
     * @param string $caption The caption of the button
     * @return static
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setCaption($caption)
    {
        if(!is_null($caption))
        {
            $this->caption = $caption;
        }
        return $this;
    }

    /**
     * Button::getButton()
     *
     * Return the HTML of the button
     *
     * @return string the button
     * @author Teye Heimans
     */
    public function getButton()
    {
        $confirmation = $this->getConfirmation();
        $confirmationDescription = $this->getConfirmationDescription();

        $confirmationAttribute = (!is_null($confirmation) && trim($confirmation) != '')
            ? ' data-confirmation="'. trim($confirmation) .'"'
            : '';

        if(!is_null($confirmationDescription)
            && trim($confirmationDescription) != '')
        {
            $confirmationAttribute .= ' data-confirmation-description="'. trim($confirmationDescription) .'"';
        }

        if($this->getDisabled())
        {
            $this->extra .= ' disabled=disabled';
        }

        // set the javascript disable dubble submit option if wanted
        if($this->disable_on_submit
            && $this->getDisabledInExtra() === false)
        {
            $this->extra = (isset($this->extra)) ? $this->extra .' ' : '';
            $this->extra = $this->extra . 'data-disable="1"';
        }

        return sprintf(
          '<button type="'. $this->type .'" name="%s" id="%1$s"%s>%s</button>',
          $this->name,
          (isset($this->extra) ? ' '. $this->extra : '') . $confirmationAttribute .
          (isset($this->tab_index) ? ' tabindex="'.$this->tab_index.'"' : ''),
          $this->caption
        );
    }

    /**
     * Button::setExtra()
     *
     * Set extra tag information, like CSS or Javascript
     *
     * @param string $extra the CSS, JS or other extra tag info
     * @param boolean $append Append extras to already defined values
     * @return static
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setExtra($extra, $append = false)
    {
        if(!is_null($extra))
        {
            $this->extra = ($append === true ? $this->extra . ' ' : '') . $extra;
        }
        return $this;
    }

    /**
     * Get extra string
     *
     * @return null|string
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Set the html string to focus on
     *
     * @param string $name
     * @return \FormHandler\Button\Button
     */
    protected function setFocusName($name)
    {
        $this->focus_name = $name;
        return $this;
    }

    /**
     * Get the name to focus
     *
     * @author Marien den Besten
     * @return string
     */
    public function getFocus()
    {
        return $this->focus_name;
    }
}