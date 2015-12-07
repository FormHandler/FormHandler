<?php
/**
 * class BrowserField
 *
 * Create a browserfield
 *
 * @author Johan Wiegel
 * @package FormHandler
 * @subpackage Fields
 */
class BrowserField extends Field
{
    var $_iSize;         // int: the size of the field
    var $_form;    		 // object: form

    /**
     * TextField::BrowserField()
     *
     * Constructor: Create a new textfield object
     *
     * @param object &$oForm: The form where this field is located on
     * @param string $sName: The name of the field
     * @param string $sPath: The path to browse
     * @return BrowserField
     * @author Johan Wiegel
     * @access public
     */
    function BrowserField( &$oForm, $sName, $sPath )
    {
        // call the constructor of the Field class
        parent::Field($oForm, $sName);
        $this->_path = $sPath;
		$this->_form = $oForm;
        $this->setSize( 20 );
		$bSetJS = true;
        $oForm->_setJS( 'function SetUrl( sUrl, sName ){document.getElementById( sName ).value=sUrl}', $isFile = false, $before = true);
    }

    /**
     * TextField::setSize()
     *
     * Set the new size of the field
     *
     * @param integer $iSize: the new size
     * @return void
     * @author Teye Heimans
     * @access public
     */
    function setSize( $iSize )
    {
        $this->_iSize = $iSize;
    }
  
    /**
     * TextField::getField()
     *
     * Return the HTML of the field
     *
     * @return string: the html
     * @access public
     * @author Johan Wiegel
     */
    function getField()
    {
        // view mode enabled ?
        if( $this -> getViewMode() )
        {
            // get the view value..
            return $this -> _getViewValue();
        }
		
        //$this->_form->_setJS( '<script>function SetUrl( sUrl ){document.getElementById(\'bestand\').value=sUrl}</script>', $isFile = false, $before = true);
        
        $oButton = new Button( $this->_form, 'Bladeren' );
        $oButton->setCaption( 'Bladeren' );
        $oButton->setExtra( "onclick=\"window.open( '".FH_FHTML_DIR."filemanager/browser/default/browser.html?Type=File&naam=".$this->_sName."&Connector=../../connectors/php/connector.php?ServerPath=".$this->_path."','','modal=yes,width=650,height=400');\"" );
		$sButton = $oButton->getButton();        
        
        return sprintf(
          '<input type="text" name="%s" id="%1$s" value="%s" size="%d" %s'. FH_XHTML_CLOSE .'>%s %s ',
          $this->_sName,
          (isset($this->_mValue) ? htmlspecialchars($this->_mValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING):''),
          $this->_iSize,
          (isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
          (isset($this->_sExtra) ? ' '.$this->_sExtra.' ' :''),
          (isset($this->_sExtraAfter) ? $this->_sExtraAfter :''),
          $sButton
        );
    }
}
?>