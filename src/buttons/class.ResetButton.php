<?php

/**
 * class ResetButton
 *
 * Create a resetbutton on the given form
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Buttons
 */
class ResetButton extends Button
{
    /**
     * ResetButton::ResetButton()
     *
     * constructor: Create a new reset button object
     *
     * @param object $form: the form where the button is located on
     * @param string $name: the name of the button
     * @return ResetButton
     * @access public
     * @author Teye Heimans
     */
    function ResetButton(&$oForm, $sName)
    {
        $this->Button($oForm, $sName);

        $this->setCaption( $oForm->_text( 27 ) );
    }

    /**
     * ResetButton::getButton()
     *
     * Return the HTMl of the button
     *
     * @return string: the html of the button
     * @access public
     * @author Teye Heimans
     */
    function getButton()
    {
        return sprintf(
          '<input type="reset" value="%s" name="%s" id="%2$s"%s '. FH_XHTML_CLOSE .'>',
          $this->_sCaption,
          $this->_sName,
          (isset($this->_sExtra) ? ' '.$this->_sExtra:'').
          (isset($this->_iTabIndex) ? ' tabindex="'.$this->_iTabIndex.'"' : '')
        );
    }
}

?>