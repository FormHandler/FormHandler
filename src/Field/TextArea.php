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
 * class TextArea
 *
 * Create a textarea
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Field
 */
class TextArea extends \FormHandler\Field\Field
{
    private $columns;
    private $rows;
    private $max_length = null;
    private $show_message;

    /**
     * Constructor
     *
     * create a new textarea
     *
     * @param object $form The form where this field is located on
     * @param string $name The name of the field
     * @return \FormHandler\Field\TextArea
     * @author Teye Heimans
     */
    public function __construct(FormHandler $form, $name)
    {
        // call the constructor of the Field class
        return parent::__construct($form, $name)
            ->setJsSelectorValue('#' . $form->getFormName() . ' textarea[name="' . $name . '"]')
            ->setValidator(function($value,$form) use ($name)
            {
                $max_length = $form->getField($name)->getMaxLength();
                // is a max length set ?
                if(is_null($max_length))
                {
                    return true;
                }
                // is there to many data submitted ?
                $length = strlen($value);
                if($length > $max_length)
                {
                    return sprintf(
                        $form->_text(40),
                        $max_length,
                        $length,
                        abs($length - $max_length)
                    );
                }
                return true;
            });
    }

    /**
     * TextArea::setCols()
     *
     * Set the number of cols of the textarea
     *
     * @param integer|null $cols the number of cols
     * @return \FormHandler\Field\TextArea
     * @author Teye Heimans
     */
    public function setCols($cols)
    {
        $this->columns = $cols;
        return $this;
    }

    /**
     * TextArea::setMaxLength()
     *
     * Set the max length of the input. Use false or 0 to disable the limit
     *
     * @param integer $max_length
     * @param boolean $display_message
     * @return \FormHandler\Field\TextArea
     * @author Teye Heimans
     */
    public function setMaxLength($max_length, $display_message)
    {
        $this->max_length = (int) $max_length;
        $this->show_message = (bool) $display_message;
        return $this;
    }

    /**
     * Get defined max length
     *
     * @return null|integer
     */
    public function getMaxLength()
    {
        return $this->max_length;
    }

    /**
     * TextArea::setRows()
     *
     * Set the number of rows of the textarea
     *
     * @param integer|null $rows the number of rows
     * @return \FormHandler\Field\TextArea
     * @author Teye Heimans
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * Get View value
     *
     * @return string
     * @author Marien den Besten
     */
    public function _getViewValue()
    {
        return nl2br(parent::_getViewValue());
    }

    /**
     * TextArea::getField()
     *
     * Return the HTML of the field
     *
     * @return string the html of the field
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

        // is a limit set ?
        if(!is_null($this->max_length) && $this->max_length > 0)
        {
            // the message
            $message = $this->form_object->_text(36);

            // set the event
            $this->extra .= sprintf(
                " onkeyup=\"displayLimit('%s', '%s', %d, %s, '%s');\"",
                $this->form_object->getFormName(),
                $this->name,
                $this->max_length,
                ($this->show_message ? 'true' : 'false'),
                htmlspecialchars($message)
            );

            // should the message be displayed ?
            if($this->show_message)
            {
                // add the javascript to the fields "extra" argument
                $this->setExtraAfter(
                    "<div id='" . $this->name . "_limit'></div>\n"
                );
            }

            // make sure that when the page is loaded, the message is displayed
            $this->form_object->_setJS(
                sprintf(
                    "displayLimit('%s', '%s', %d, %s, '%s');\n",
                    $this->form_object->getFormName(),
                    $this->name,
                    $this->max_length,
                    ($this->show_message ? 'true' : 'false'),
                    $message
                ),
                false,
                false
            );
        }

        // return the field
        return sprintf(
            '<textarea name="%s" id="%1$s" cols="%d" rows="%d"%s>%s</textarea>%s',
            $this->name,
            is_null($this->columns) ? 40 : $this->columns,
            is_null($this->rows) ? 7 : $this->rows,
            (isset($this->tab_index) ? ' tabindex="' . $this->tab_index . '" ' : '')
                . (isset($this->extra) ? ' ' . $this->extra : '')
                . ($this->getDisabled() && !$this->getDisabledInExtra() ? 'disabled="disabled" ' : ''),
            htmlspecialchars($this->getValue()),
            (isset($this->extra_after) ? $this->extra_after : '')
        );
    }
}