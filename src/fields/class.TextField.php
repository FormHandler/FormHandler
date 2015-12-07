<?php

/**
 * class TextField
 *
 * Create a textfield
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */
class TextField extends Field
{
	var $_iSize;         // int: the size of the field
	var $_iMaxlength;    // int: the maxlength of the field

	/**
     * TextField::TextField()
     *
     * Constructor: Create a new textfield object
     *
     * @param object &$oForm: The form where this field is located on
     * @param string $sName: The name of the field
     * @return TextField
     * @author Teye Heimans
     * @access public
     */
	function TextField( &$oForm, $sName )
	{
		// call the constructor of the Field class
		parent::Field($oForm, $sName);

		$this->setSize( 20 );
		$this->setMaxlength( 0 );
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
	 * TextField::checkMaxLength()
	 *
	 * Check the maxlength of the field
	 *
	 * @param integer $iLength: the maxlength
	 * @return void
	 * @access public
	 * @author Johan Wiegel
	 * @since 17-04-2009
	 */

	function checkMaxLength( $iLength )
	{
		if( strlen( $this->getValue() ) > $iLength )
		{
			$this->_sError = $this->_oForm->_text( 14 );
			return false;
		}
	}

	/**
	 * TextField::checkMinLength()
	 *
	 * Check the minlength of the field
	 *
	 * @param integer $iLength: the maxlength
	 * @return void
	 * @access public
	 * @author Johan Wiegel
	 * @since 17-04-2009
	 */

	function checkMinLength( $iLength )
	{
		if( strlen( $this->getValue() ) < $iLength )
		{
			$this->_sError = $this->_oForm->_text( 14 );
			return false;
		}
	}

	/**
     * TextField::setMaxlength()
     *
     * Set the new maxlength of the field
     *
     * @param integer $iMaxlength: the new maxlength
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function setMaxlength( $iMaxlength )
	{
		$this->_iMaxlength = $iMaxlength;
	}

	/**
     * TextField::getField()
     *
     * Return the HTML of the field
     *
     * @return string: the html
     * @access public
     * @author Teye Heimans
     */
	function getField()
	{
		// view mode enabled ?
		if( $this -> getViewMode() )
		{
			// get the view value..
			return $this -> _getViewValue();
		}

		return sprintf(
		'<input type="text" name="%s" id="%1$s" value="%s" size="%d" %s'. FH_XHTML_CLOSE .'>%s',
		$this->_sName,
		(isset($this->_mValue) ? htmlspecialchars($this->_mValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING):''),
		$this->_iSize,
		(!empty($this->_iMaxlength) ? 'maxlength="'.$this->_iMaxlength.'" ':'').
		(isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
		(isset($this->_sExtra) ? ' '.$this->_sExtra.' ' :''),
		(isset($this->_sExtraAfter) ? $this->_sExtraAfter :'')
		);
	}
}

?>