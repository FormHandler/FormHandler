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
 * class FileBasic
 *
 * File basic class
 *
 * @author Marien den Besten
 * @package FormHandler
 * @subpackage Field
 */
class FileBasic extends \FormHandler\Field\Field
{
    private $is_required = false;

    /**
     * Constructor
     *
     * Create a new file basic field
     *
     * @param object $form The form where this field is located on
     * @param string $name The name of the field
     * @return \FormHandler\Field\FileBasic
     * @author Marien den Besten
     */
    public function __construct(FormHandler $form, $name)
    {
        // call the constructor of the Field class
        parent::__construct($form, $name);

        if($form->isPosted()
            && array_key_exists($name, $_FILES))
        {
            $this->value = $_FILES[$name];
        }

        $this->form_object->setEncoding(FormHandler::ENCODING_MULTIPART);

        return $this;
    }

    /**
     * Is required
     *
     * @param boolean $required
     * @return \FormHandler\Field\FileBasic
     * @author Marien den Besten
     */
    public function setRequired($required)
    {
        $this->is_required = (bool) $required;
        return $this;
    }

    /**
     * Is field valid
     *
     * @return \FormHandler\Field\FileBasic
     * @author Marien den Besten
     */
    public function processValidators()
    {
        $this->setErrorState(false);
        // when no file field was submitted (on multi-paged forms)
        if(!isset($_FILES[$this->name]))
        {
            return $this;
        }

        // is a own error handler used?
        if(count($this->validators) != 0)
        {
            parent::processValidators();
            return $this;
        }

        // easy name to work with (this is the $_FILES['xxx'] array )
        $file = $this->value;

        if($this->is_required === true
            && (!is_array($file)
                || trim($file['name']) == ''))
        {
            //no file uploaded
            $this->setErrorMessage(\FormHandler\Language::get(22));
            $this->setErrorState(true);
        }
        return $this;
    }

    /**
     * Return the HTML of the field
     *
     * @return string the html
     * @author Marien den Besten
     */
    public function getField()
    {
        // view mode enabled ?
        if($this->getViewMode())
        {
            // get the view value..
            return (is_array($this->value) && array_key_exists('name')) ? $this->value['name'] : '';
        }

        return sprintf(
            '<input type="file" name="%s" id="%1$s" %s'. \FormHandler\Configuration::get('xhtml_close') .'>%s',
            $this->name,
            (isset($this->tab_index) ? 'tabindex="' . $this->tab_index . '" ' : '')
                . (isset($this->extra) ? ' ' . $this->extra . ' ' : '')
                . ($this->getDisabled() && !$this->getDisabledInExtra() ? 'disabled="disabled" ' : ''),
            (isset($this->extra_after) ? $this->extra_after : '')
        );
    }

    /**
     * Set which file types are accepted
     *
     * @param string|array $format
     * @return \FormHandler\Field\FileBasic
     * @author Marien den Besten
     */
    public function setFileType($format)
    {
        if(is_array($format) && count($format) == 0)
        {
            return $this;
        }

        $this->extra .= ' accept="'. ((is_array($format))
            ? implode(', ', $format)
            : $format) .'"';

        return $this;
    }
}