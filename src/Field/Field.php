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
use \FormHandler\Validator;

/**
 * class Field
 *
 * Class to create a field.
 * This class contains code which is used by all the other fields
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Field
 */
class Field
{
    /** @var FormHandler */
    protected $form_object;
    protected $name;
    protected $validators = array();
    protected $value;
    protected $value_default;
    protected $value_post;
    protected $value_forced;
    protected $error;
    protected $extra;
    protected $tab_index;
    protected $extra_after;
    protected $view_mode = false;
    protected $view_link;
    protected $focus_name = null;
    private $error_state = null;
    private $help;
    private $options;
    private $use_array_key_as_value;
    private $appearance_conditions = array();
    private $jsSelectorValue;
    protected $disabled = false;

    /**
     * Register the field with FormHandler
     *
     * @param FormHandler $form
     * @param string $title
     * @param string $name
     * @param mixed $validator
     * @return static Instance of
     * @author Marien den Besten
     */
    static function set(FormHandler $form, $title, $name, $validator = null)
    {
        $class = get_called_class();

        //create the field
        $fld = new $class($form, $name);

        if(!is_null($validator))
        {
            $fld->setValidator($validator);
        }

        //register the field
        $form->registerField($name, $fld, $title)
            ->setOnCorrectField($name);

        return $fld;
    }

    /**
     * Field::Field()
     *
     * Public abstract constructor: Create a new field
     *
     * @param FormHandler $form The form where the field is located on
     * @param string $name The name of the field
     * @return static
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function __construct($form, $name)
    {
        // save the form and nome of the field
        $this->form_object = $form;
        $this->name = $name;
        $this->setFocusName($name)
            ->setJsSelectorValue('#' . $form->getFormName() . ' input[name="' . $name . '"]')
            ->useArrayKeyAsValue(\FormHandler\Configuration::get('default_usearraykey'));

        // check if there are spaces in the fieldname
        if(strpos($name, ' ') !== false)
        {
            trigger_error('Warning: There are spaces in the field name "' . $name . '"!', E_USER_WARNING);
        }

        //get the value of the field
        if($form->isPosted()
            && isset($_POST[$name])
            && (is_string($_POST[$name])
                || is_array($_POST[$name])))
        {
            $this->value_post = $_POST[$name];
        }
        return $this;
    }

    /**
     * Hide field value from onCorrect function
     *
     * @param boolean $bool
     * @return static
     */
    public function hideFromOnCorrect($bool = true)
    {
        if($bool)
        {
            $this->form_object->unsetOnCorrectField($this->name);
        }
        else
        {
            $this->form_object->setOnCorrectField($this->name);
        }
        return $this;
    }


    /**
     * Insert this field before an existing field
     *
     * @param \FormHandler\Field\Field|string $field
     * @return \Field
     * @author Marien den Besten
     */
    public function insertBefore($field)
    {
        return $this->moveBefore($field);
    }

    /**
     * Insert this field after an existing field
     *
     * @param \FormHandler\Field\Field|string $field
     * @return \Field
     * @author Marien den Besten
     */
    public function insertAfter($field)
    {
        return $this->moveAfter($field);
    }

    /**
     * Move this field before an existing field
     *
     * @param \FormHandler\Field\Field|string $field
     * @return \Field
     * @author Marien den Besten
     */
    public function moveBefore($field)
    {
        $this->form_object->moveFieldBefore($field, $this);
        return $this;
    }

    /**
     * Move this field after an existing field
     *
     * @param \FormHandler\Field\Field|string $field
     * @return \Field
     * @author Marien den Besten
     */
    public function moveAfter($field)
    {
        $this->form_object->moveFieldAfter($field, $this);
        return $this;
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
     * Get if field is disabled
     *
     * @return boolean
     * @author Marien den Besten
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Get the JS selector for the field value
     * @return string
     * @author Marien den Besten
     */
    public function getJsSelectorValue()
    {
        return $this->jsSelectorValue;
    }

    /**
     * Instruction to jQuery on how to find the field value
     *
     * Value will be passed into jquery(...); method
     *
     * @param string $jsSelectorValue
     * @return \Field
     * @author Marien den Besten
     */
    protected function setJsSelectorValue($jsSelectorValue)
    {
        $this->jsSelectorValue = $jsSelectorValue;
        return $this;
    }

    /**
     * Set appearance condition
     *
     * The callable receives the following parameters:
     * - Values from watch field(s), format is always: $watchField => $value
     * - From field
     * - FormHandler object
     *
     * @param string|object|array $watchField Field to watch the value, multiple fields can be attached through array
     * @param callable $callback
     * @return \Field
     * @author Marien den Besten
     */
    public function setAppearanceCondition($watchField, $callback)
    {
        //convert to array for easy processing
        $watches = !is_array($watchField) ? array($watchField) : $watchField;

        $this->appearance_conditions[] = array($watches, $callback);
        return $this;
    }

    /**
     * Get definede appearance conditions
     *
     * @return array
     * @author Marien den Besten
     */
    public function getAppearanceCondition()
    {
        return $this->appearance_conditions;
    }

    /**
     * Get the defined name for this field
     *
     * @return string
     * @author Marien den Besten
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get default value
     *
     * @return mixed
     * @author Marien den Besten
     */
    public function getDefaultValue()
    {
        return $this->value_default;
    }

    /**
     * Set default value
     *
     * @param mixed $default_value
     * @return \Field
     * @author Marien den Besten
     */
    public function setDefaultValue($default_value)
    {
        $this->value_default = $default_value;
        return $this;
    }


    /**
     * Set help text for this field
     *
     * @param string $text
     * @param string $title
     * @return \Field
     * @author Marien den Besten
     */
    public function setHelp($text, $title = null)
    {
        $this->help = array($text, $title);
        return $this;
    }

    /**
     * Get help
     *
     * @return array When help is not set both values are null
     * @author Marien den Besten
     */
    public function getHelp()
    {
        return !is_array($this->help) ? array(null, null) : $this->help;
    }

    /**
     * Set a link when a field is in viewmode
     *
     * You can use {$value} as a placeholder, this will be replaced by the fields value
     *
     * @author Marien den Besten
     * @param string $url
     * @return \Field Field instance
     */
    public function setViewModeLink($url, $target_blank = false)
    {
        $this->view_link = array($url, $target_blank);
        return $this;
    }

    /**
     * Get the field name to focus
     *
     * @author Marien den Besten
     * @return string|null
     */
    public function getFocus()
    {
        return $this->focus_name;
    }

    /**
     * Set the focus name
     *
     * Set to 'null' when the field is not focusable.
     *
     * @param string|null $name
     * @return \Field
     * @author Marien den Besten
     */
    protected function setFocusName($name)
    {
        if(is_string($name) || is_null($name))
        {
            $this->focus_name = $name;
        }
        return $this;
    }

    /**
     * Override the fields valid state
     *
     * True means the field is not valid
     *
     * @param boolean $bool
     * @return static
     * @author Marien den Besten
     */
    public function setErrorState($bool)
    {
        $this->error_state = (bool) $bool;
        return $this;
    }

    /**
     * Get if field is in error state
     *
     * A null value is returned when no error processing is done
     *
     * @return boolean|null
     * @author Marien den Besten
     */
    public function getErrorState()
    {
        return $this->error_state;
    }

    /**
     * Field::processValidators()
     *
     * Check the validators of this field, result will be available
     * through 'getErrorState' and 'getErrorMessage'
     *
     * @return static
     * @author Teye Heimans
     * @author Remco van Arkelen
     * @author Johan Wiegel
     * @author Marien den Besten
     */
    public function processValidators()
    {
        $is_valid = true;
        $value = $this->getValue();
        $parameters = array((is_string($value) ? trim($value) : $value), $this->form_object, $this->name);
        $v = new Validator();

        foreach($this->validators as $validator)
        {
            //convert constants to a callable
            $is_constant = (is_string($validator) && method_exists($v, $validator));
            $validator = ($is_constant) ? array($v, $validator) : $validator;

            if(!is_callable($validator))
            {
                trigger_error('Unknown validator: "' . $validator . '" used in field "' . $this->name . '"');
            }

            $is_valid = call_user_func_array($validator, $parameters);

            //weak typing is intentional because functions can also non booleans
            if($is_valid !== true && $is_valid !== 1)
            {
                break;
            }
        }

        if($is_valid !== true && $is_valid !== 1)
        {
            //a validator can trigger custom error messages
            $message = is_string($is_valid) ? $is_valid : $this->form_object->_text(14);
            $this->setErrorMessage($message);
        }

        $this->setErrorState($is_valid !== true && $is_valid !== 1);
        return $this;
    }

    /**
     * Field::getValidators()
     *
     * @return array
     * @author Marien den Besten
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Field::setValidator()
     *
     * Set the validator which is used to validate the value of the field
     * This can also be an array.
     * If you want to use a method to validate the value use it like this:
     * array($obj, 'NameOfTheMethod')
     *
     * @param string|callable $validator the name of the validator
     * @return static
     * @author Teye Heimans
     */
    public function setValidator($validator)
    {
        //deprecated use of passing multiple validators
        if(is_string($validator) && strpos($validator, '|') !== false)
        {
            $validators = explode('|', $validator);
            foreach($validators as $one_piece)
            {
                if(trim($one_piece) != '')
                {
                    $this->setValidator($one_piece);
                }
            }
            return $this;
        }

        if(!is_null($validator))
        {
            $this->validators[] = $validator;
        }
        return $this;
    }

    /**
     * Reset the field validation
     *
     * @return static
     */
    public function disableValidation()
    {
        $this->validators = array();
        return $this;
    }

    /**
     * Field::setTabIndex()
     *
     * Set the tabindex of the field
     *
     * @param integer $iIndex
     * @return static
     * @author Teye Heimans
     */
    public function setTabIndex($iIndex)
    {
        $this->tab_index = $iIndex;
        return $this;
    }

    /**
     * Field::setExtraAfter()
     *
     * Set some extra HTML, JS or something like that (to use after the html tag)
     *
     * @param string $sExtraAfter the extra html to insert into the tag
     * @return static
     * @author Teye Heimans
     */
    public function setExtraAfter($sExtraAfter)
    {
        $this->extra_after = $sExtraAfter;
        return $this;
    }

    /**
     * Field::setError()
     *
     * Set a custom error
     *
     * @param string $sError the error to set into the tag
     * @return static
     * @author Filippo Toso - filippotoso@libero.it
     */
    public function setErrorMessage($sError)
    {
        $this->error = $sError;
        return $this;
    }

    /**
     * Field::getValue()
     *
     * Return the value of the field
     *
     * @return mixed the value of the field
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function getValue()
    {
        //value's in order of importance
        if(isset($this->value_forced))
        {
            return $this->value_forced;
        }
        if(isset($this->value_post))
        {
            return $this->value_post;
        }
        if(isset($this->value))
        {
            return $this->value;
        }
        if(isset($this->value_default))
        {
            return $this->value_default;
        }
        return '';
    }

    /**
     * Field::getError()
     *
     * Return the error of the field (if the field-value is not valid)
     *
     * @return string the error message
     * @author Teye Heimans
     */
    public function getErrorMessage()
    {
        if(isset($this->error))
        {
            if($this->error === false)
            {
                $this->error = $this->form_object->_text(14);
            }

            if(strlen($this->error) > 0)
            {
                return sprintf(\FormHandler\Configuration::get('error_mask'), $this->name, $this->error);
            }
        }
        return '';
    }

    /**
     * Field::setValue()
     *
     * Set the value of the field
     *
     * @param mixed $value The new value for the field
     * @return static
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setValue($value, $forced = false)
    {
        $processed_value = (!is_array($value) && !is_null($value) && !is_object($value)) ? trim($value) : $value;

        if($forced === true)
        {
            $this->value_forced = $processed_value;
        }
        else
        {
            $this->value = $processed_value;
        }
        return $this;
    }

    /**
     * Field::setExtra()
     *
     * Set some extra CSS, JS or something like that (to use in the html tag)
     *
     * @param string $extra the extra html to insert into the tag
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
     * Field::getField()
     *
     * Return the HTML of the field.
     * This function HAS TO BE OVERWRITTEN by the child class!
     *
     * @return string the html of the field
     * @author Teye Heimans
     */
    public function getField()
    {
        trigger_error('Error, getField has not been overwritten!', E_USER_WARNING);
        return '';
    }

    /**
     * Method called on post
     *
     * @param FormHandler $form
     * @return null
     */
    public function onPost(FormHandler $form)
    {
        return;
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
     * Field::getViewMode()
     *
     * Return if this field is set to view mode
     *
     * @return bool
     * @author Teye Heimans
     */
    public function getViewMode()
    {
        return $this->view_mode || $this->form_object->isViewMode();
    }

    /**
     * Field::setViewMode()
     *
     * Enable or disable viewMode for this field
     *
     * @param boolean $mode
     * @return static
     * @author Teye Heimans
     */
    public function setViewMode($mode = true)
    {
        $this->view_mode = (bool) $mode;
        return $this;
    }

    /**
     * Field::_getViewValue()
     *
     * Return the value of the field
     *
     * @return string the value of the field in HTML
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function _getViewValue()
    {
        // get the value for the field
        $val = $this->getValue();

        // are there multiple options?
        if(!is_null($this->options) && $this->getUseArrayKeyAsValue() === true)
        {
            $array = (!is_array($val)) ? array($val) : $val;

            $tmp = array();
            // save the labels instead of the index keys as view value
            foreach($array as $key => $value)
            {
                if(array_key_exists($value, $this->options))
                {
                    $tmp[$value] = $this->options[$value];
                }
            }
            $val = $tmp;
            asort($val);
        }

        $processed = !is_array($val) ? array($val) : $val;

        $enable_link = false;
        if(is_array($this->view_link))
        {
            $link = $this->view_link[0];
            $target = ($this->view_link[1] !== false) ? ' target="_blank"' : '';
            $enable_link = true;
        }

        $result = '';
        // is there only one item?
        if(count($processed) == 1)
        {
            reset($processed);
            $result = current($processed);
            $key = ($this->getUseArrayKeyAsValue()) ? key($processed) : $result;

            if($enable_link)
            {
                $link = str_replace('{$value}', $key, $link);
                $result = '<a href="' . $link . '"' . $target . '>' . nl2br($result) . '</a>';
            }
        }
        elseif(count($processed) != 0)
        {
            // make a list of the selected items
            $result = "\n\t<ul>\n";
            foreach($processed as $key => $value)
            {
                if($enable_link)
                {
                    $tmp = str_replace('{$value}', $key, $link);
                    $value = '<a href="' . $tmp . '"' . $target . '>' . $value . '</a>';
                }

                $result .= "\t\t<li>". nl2br($value) ."</li>\n";
            }
            $result .= "\t</ul>\n";
        }

        if(trim($result) == '')
        {
            $result = '-';
        }

        return $result;
    }

    /**
     * Field::setOptions()
     *
     * Set the options of the field
     *
     * @param array $options the options for the field
     * @return static
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get defined options
     * @return array
     * @author Marien den Besten
     */
    public function getOptions()
    {
        return !is_array($this->options) ? array() : $this->options;
    }

    /**
     * Field::useArrayKeyAsValue()
     *
     * Set if the array keys of the options has to be used as values for the field
     *
     * @param boolean|null $mode The mode
     * @return \Field
     * @author Teye Heimans
     * @author Marien den Besten
     */
    public function useArrayKeyAsValue($mode)
    {
        if(!is_null($mode))
        {
            $this->use_array_key_as_value = (bool) $mode;
        }
        return $this;
    }

    /**
     * Get the use array key as value setting
     *
     * @author Marien den Besten
     * @return bool
     */
    public function getUseArrayKeyAsValue()
    {
        return $this->use_array_key_as_value;
    }
}