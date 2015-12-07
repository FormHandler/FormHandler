<?php

/**
 * class PassField
 *
 * Create a PassField
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */
class PassField extends TextField
{
    var $_sPre;

    /**
     * PassField::PassField()
     *
     * Constructor: Create a new passfield object
     *
     * @param object $oForm: The form where the field is located on
     * @param string $sName: The name of the form
     * @return PassField
     * @author Teye Heimans
     * @access public
     */
    function PassField(&$oForm, $sName)
    {
        // call the constructor of the Field class
        parent::Field($oForm, $sName);

        $this->_sPre = '';
        $this->setSize( 20 );
        $this->setMaxlength( 0 );
    }

    /**
     * PassField::getField()
     *
     * Return the HTML of the field
     *
     * @return string: the html
     * @author Teye Heimans
     * @access public
     */
    function getField()
    {
        // view mode enabled ?
        if( $this -> getViewMode() )
        {
            // get the view value..
            return '****';
        }

        return sprintf(
          '%s<input type="password" name="%s" id="%2$s" size="%d" %s'. FH_XHTML_CLOSE .'>%s',
          $this->_sPre,
          $this->_sName,
          $this->_iSize,
          (!empty($this->_iMaxlength) ? 'maxlength="'.$this->_iMaxlength.'" ':'').
          (isset($this->_iTabIndex) ? ' tabindex="'.$this->_iTabIndex.'" ' : '').
          (isset($this->_sExtra) ? $this->_sExtra.' ' :''),
          (isset($this->_sExtraAfter) ? $this->_sExtraAfter :'')
        );
    }

    /**
     * PassField::setPre()
     *
     * Set the message above the passfield
     *
     * @param string $sMsg: the message
     * @return void
     * @author Teye Heimans
     * @access public
     */
    function setPre( $sMsg)
    {
        $this->_sPre = $sMsg;
    }

    /**
     * PassField::checkPassword()
     *
     * Check the value of this field with another passfield
     *
     * @param object $oObj
     * @return boolean: true if the values are correct, false if not
     * @author Teye Heimans
     * @access public
     */
    function checkPassword( &$oObj )
    {
        // if the fields doesn't match
        if($this->getValue() != $oObj->getValue())
        {
            $this->_sError = $this->_oForm->_text( 15 );
            return false;
        }
        else
        {
            // when there is no value
            if($this->getValue() == '')
            {
                // it's an edit form.. keep the original
                if(isset($this->_oForm->edit) && $this->_oForm->edit)
                {
                    $this->_oForm->_dontSave[] = $this->_sName;
                    $this->_oForm->_dontSave[] = $oObj->_sName;

                    // make sure that no validator is overwriting the messages...
                    $this->setValidator( null );
                    $oObj->setValidator( null );
                }
                // insert form. PassField is required! error!
                else
                {
                    $this->_sError = $this->_oForm->_text( 16 );
                    return false;
                }
            }
            else
            {
                // is the password not to short ?
                if(strLen($this->getValue()) < FH_MIN_PASSWORD_LENGTH )
                {
                    $this->_sError = sprintf( $this->_oForm->_text( 17 ), FH_MIN_PASSWORD_LENGTH );
                    return false;
                }
                // is it an valif password ?
                else if( ! Validator::IsPassword($this->getValue()) )
                {
                    $this->_sError = $this->_oForm->_text( 18 );
                    return false;
                }
            }
        }
        // everything is OK!
        return true;
    }
}

?>