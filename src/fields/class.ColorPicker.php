<?php
require_once(dirname(__FILE__)."/class.TextField.php");

/**
 * class ColorPicker
 *
 * Allows the user to pick a color
 *
 * @author Rick den Haan
 * @package FormHandler
 * @subpackage Fields
 * @since 02-07-2008
 */
class ColorPicker extends TextField
{
	var $sTitleAdd = "";

	/**
     * ColorPicker::ColorPicker()
     *
     * Constructor: Create a new ColorPicker object
     *
     * @param object &$oForm: The form where this field is located on
     * @param string $sName: The name of the field
     * @return ColorPicker
     * @access public
     * @author Rick den Haan
     */
	function ColorPicker( &$oForm, $sName )
	{
		parent::TextField($oForm, $sName);

		static $bSetJS = false;

		// needed javascript included yet ?
		if(!$bSetJS)
		{
			// include the needed javascript
			$bSetJS = true;
			$oForm->_setJS(FH_FHTML_DIR."js/jscolor/jscolor.js", true);
		}

	}
	/**
     * ColorPicker::getField()
     *
     * Return the HTML of the field
     *
     * @return string: the html of the field
     * @access public
     * @author Rick den Haan
     */
	function getField()
	{
		// view mode enabled ?
		if( $this -> getViewMode() )
		{
			// get the view value..
			return $this -> _getViewValue();
		}

		// check if the user set a class
		if(isset($this->_sExtra) && preg_match("/class *= *('|\")(.*)$/i", $this->_sExtra))
		{
			// put the function into a onchange tag if set
			$this->_sExtra = preg_replace("/class *= *('|\")(.*)$/i", "class=\"color \\2", $this->_sExtra);
		}
		else
		{
			$this->_sExtra = "class=\"color\"".(isset($this->_sExtra) ? $this->_sExtra : '');
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