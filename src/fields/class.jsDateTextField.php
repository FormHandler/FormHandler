<?php
/**
 * class jsDateTextField
 *
 * Create a jsDateTextField
 *
 * @author Thomas Branius
 * @package FormHandler
 * @subpackage Fields
 * @since 16-03-2010
 */
class jsDateTextField extends DateTextField
{
	var $_sJsMask; // string: how to display the fields (d-m-y) or other

	/**
     * Constructor: create a new jsDateTextField object
     *
     * @param object &$oForm: the form where the datefield is located on
     * @param string $sName: the name of the datefield
     * @param string $sMask: the mask which is used to display the fields
     * @param bool $bParseOtherPresentations: try to parse other presentations of dateformat
     * @return jsDateTextField
     * @access public
     * @author Thomas Branius
     * @since 16-03-2010
     */
	function jsDateTextField( &$oForm, $sName, $sMask = null, $bParseOtherPresentations = false, $bIncludeJS )
	{
		parent::DateTextField( $oForm, $sName, $sMask, $bParseOtherPresentations);
		
		static $bSetJS = false;

		// needed javascript included yet ?
		if(!$bSetJS)
		{

			$bSetJS = true;
			// add the needed javascript
			if( $bIncludeJS == true  )
			{
				$oForm->_setJS(
				FH_FHTML_DIR."js/calendar_popup.js", true
				);

				$oForm->_setJS(
				"document.write(getCalendarStyles());\n"				
				);
			}
		}		
	}

	/**
     * Set the display of the fields
     *
     * @param string $sMast: how we have to display the datefield (day-month-year combination)
     * @return void
     * @access public
     * @author Thomas Branius
     * @since 16-03-2010
     */
	function setMask( $sMask )
	{
		parent::setMask($sMask);

		$this->_sJsMask = $sMask;

		// convert to Javascript-Mask
		$this->_sJsMask = str_replace('d', 'dd', $this->_sJsMask);
		$this->_sJsMask = str_replace('D', 'd', $this->_sJsMask);
		$this->_sJsMask = str_replace('m', 'MM', $this->_sJsMask);
		$this->_sJsMask = str_replace('y', 'yy', $this->_sJsMask);
		$this->_sJsMask = str_replace('Y', 'yyyy', $this->_sJsMask);
	}

	/**
     * return the field
     *
     * @return string: the field
     * @author Thomas Branius
     * @access public
     * @since 16-03-2010
     */
	function getField()
	{
		// view mode enabled ?
		if( $this -> getViewMode() )
		{
			// get the view value..
			return $this -> _getViewValue();
		}

		$html = parent::getField();

		// add the javascript needed for the js calendar field
		$this -> _oForm -> _setJS(
		"// create popup calendar\n".
		"if( document.getElementById('".$this->_sName."_span') ) \n".
		"{\n".
		"   var cal_".$this->_sName." = new CalendarPopup('".$this->_sName."_span');\n".
		"   cal_".$this->_sName.".setMonthNames('" . $this->_oForm->_text( 1 ) . "','" . $this->_oForm->_text( 2 ) . "','" . $this->_oForm->_text( 3 ) . "','" . $this->_oForm->_text( 4 ) . "','" . $this->_oForm->_text( 5 ) . "','" . $this->_oForm->_text( 6 ) . "','" . $this->_oForm->_text( 7 ) . "','" . $this->_oForm->_text( 8 ) . "','" . $this->_oForm->_text( 9 ) . "','" . $this->_oForm->_text( 10 ) . "','" . $this->_oForm->_text( 11 ) . "','" . $this->_oForm->_text( 12 ) . "');\n".
		"   cal_".$this->_sName.".setDayHeaders(".$this->_oForm->_text( 43 ).");\n".
		"   cal_".$this->_sName.".setWeekStartDay(".FH_JSCALENDARPOPUP_STARTDAY.");\n".
		"   cal_".$this->_sName.".setTodayText('".addslashes( $this->_oForm->_text( 42 ) )."');\n".
		"   cal_".$this->_sName.".showYearNavigation();\n".
		"   cal_".$this->_sName.".showYearNavigationInput();\n".
		(FH_JSCALENDARPOPUP_USE_DROPDOWN ? "   cal_".$this->_sName.".showNavigationDropdowns();\n" : "").
		"}\n", 0, 0
		);

		$html .=
		"<a href='javascript:;' ".
		"onclick=\"if( cal_".$this->_sName." ) cal_".$this->_sName.".select(document.forms['".$this -> _oForm->_name."'].elements['".$this->_sName."'], 'anchor_".$this->_sName."', '".$this->_sJsMask."'); return false;\" ".
		" name='anchor_".$this->_sName."' id='anchor_".$this->_sName."'>".
		"<img src='".FH_FHTML_DIR."images/calendar.gif' border='0' alt='Select Date' ". FH_XHTML_CLOSE ."></a>\n".
		"<span id='".$this->_sName."_span' ".
		" style='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></span>\n";

		return $html;
	}
}
?>