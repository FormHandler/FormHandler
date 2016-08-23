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

/**
 * class Email
 *
 * Create an email field
 *
 * @author Marien den Besten
 * @package FormHandler
 * @subpackage Field
 */
class Email extends \FormHandler\Field\Text
{
    private $enableViewModeLink = true;

    /**
     * Get view value
     * @return string
     */
    public function _getViewValue()
    {
        $v = \FormHandler\Utils::html($this->getValue());

        if($this->getEnableViewModeLink()
            && trim($v) != '')
        {
            $this->setViewModeLink('mailto:' . $v);
        }
        return parent::_getViewValue();
    }

    /**
     * getField()
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
            return $this->_getViewValue();
        }

        return sprintf(
                '<input type="email" name="%s" id="%1$s" value="%s" size="%d" %s' . \FormHandler\Configuration::get('xhtml_close') . '>%s',
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

    /**
     * Is view mode link enabled?
     *
     * @return boolean
     */
    function getEnableViewModeLink()
    {
        return $this->enableViewModeLink;
    }

    /**
     * Show mailto link on view mode
     *
     * @param type $enableViewModeLink
     * @return static
     */
    function setEnableViewModeLink($enableViewModeLink)
    {
        $this->enableViewModeLink = $enableViewModeLink;
        return $this;
    }
}