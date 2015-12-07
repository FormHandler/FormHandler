<?php
/**
 * class HiddenField
 *
 * Create a hiddenfield on the given form
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */

class HiddenField extends Field {

    /**
     * HiddenField::getField()
     *
     * Return the HTML of the field
     *
     * @return string: The html of the field
     * @access public
     * @author Teye Heimans
     */
    function getField()
    {
        return sprintf(
          '<input type="hidden" name="%s" id="%1$s" value="%s" %s'. FH_XHTML_CLOSE .'>%s',
          $this->_sName,
          (isset( $this->_mValue ) ? htmlspecialchars( $this->_mValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING ) : ''),
          (isset($this->_sExtra) ? $this->_sExtra.' ' :''),
          (isset($this->_sExtraAfter) ? $this->_sExtraAfter :'')
        );
    }
}

?>