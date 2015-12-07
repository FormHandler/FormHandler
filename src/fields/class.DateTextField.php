<?php
/**
 * class DateTextField
 *
 * Create a DateTextfield
 *
 * @author Thomas Branius
 * @since 16-03-2010
 * @package FormHandler
 * @subpackage Fields
 * 
 *  validators added by Johan Wiegel
 */
class DateTextField extends TextField
{
	var $_sDateMask;				// string: how to display the fields with mask
	var $_sValParseRegExpr;			// string: how to parse the value
	var $_iDayPos;					// int: position of day in regular expression
	var $_iMonthPos;				// int: position of month in regular expression
	var $_iYearPos;					// int: position of year in regular expression
	var $_bParseOtherPresentations;	// bool: try to parse other presentations of dateformat

	/**
     * Constructor: create a new dateTextField object
     *
     * @param object &$oForm: the form where the datefield is located on
     * @param string $sName: the name of the datefield
     * @param string $sMask: the mask which is used to display the fields
     * @param bool $bParseOtherPresentations: try to parse other presentations of dateformat
     * @return dateTextField
     * @access public
     * @author Thomas Branius
     * @since 16-03-2010
     */
	function DateTextField( &$oForm, $sName, $sMask = null, $bParseOtherPresentations = false )
	{
		// set the default date display
		$this->setMask( !is_null( $sMask ) ? $sMask : FH_DATETEXTFIELD_DEFAULT_DISPLAY );

		$this->_bParseOtherPresentations = $bParseOtherPresentations;

		//$this->setValidator(array(&$this, "validate"));

		// call the constructor of the Field class
		parent::TextField($oForm, $sName);
	}

	/**
     * Set the display of the fields
     *
     * @param string $sMast: how we have to display the datefield (day-month-year combination)
     * @return void
     * @access public
     * @author Thomas Branius]
     * @since 16-03-2010
     */
	function setMask( $sMask )
	{
		// validate the mask
		$regex = '/^([dDmMyY])([\.\-\/])([dDmMyY])\2([dDmMyY])$/';

		if (preg_match($regex, $sMask, $data) == 0
		|| strtolower($data[1]) == strtolower($data[3])
		|| strtolower($data[1]) == strtolower($data[4])
		|| strtolower($data[3]) == strtolower($data[4]))
		trigger_error("Invalid mask ['{$sMask}']. Useable chars: d, D, m, M, y, Y, ., -, /", E_USER_ERROR);

		// set postion of day, month and year
		for ($i = 1; $i < 5; $i++)
		{
			if (strtolower($data[$i]) == "d")
			$this->_iDayPos = $i;
			else if (strtolower($data[$i]) == "m")
			$this->_iMonthPos =$i;
			else if (strtolower($data[$i]) == "y")
			$this->_iYearPos = $i;
		}

		$seperator = str_replace(array('/', '.'), array('\/', '\.'), $data[2]);
		$regExDay = '[0-9]{' . ($data[$this->_iDayPos] == 'D' ? '1,2' : '2' ) . '}';
		$regExMonth = '[0-9]{' . ($data[$this->_iMonthPos] == 'M' ? '1,2' : '2' ) . '}';
		$regExYear = '[0-9]{' . ($data[$this->_iYearPos] == 'y' ? '2' : '4' ) . '}';

		$this->_iDayPos = $this->_iDayPos > 1 ? $this->_iDayPos - 1 : $this->_iDayPos;
		$this->_iMonthPos = $this->_iMonthPos > 1 ? $this->_iMonthPos - 1 : $this->_iMonthPos;
		$this->_iYearPos = $this->_iYearPos > 1 ? $this->_iYearPos - 1 : $this->_iYearPos;

		if ($this->_iYearPos == 1)
		{
			if ($this->_iDayPos == 2)
			$this->_sValParseRegExpr = "/^({$regExYear}){$seperator}({$regExDay}){$seperator}({$regExMonth})$/";
			else
			$this->_sValParseRegExpr = "/^({$regExYear}){$seperator}({$regExMonth}){$seperator}({$regExDay})$/";
		}
		else if ($this->_iYearPos == 2)
		{
			if ($this->_iDayPos == 1)
			$this->_sValParseRegExpr = "/^({$regExDay}){$seperator}({$regExYear}){$seperator}({$regExMonth})$/";
			else
			$this->_sValParseRegExpr = "/^({$regExMonth}){$seperator}({$regExYear}){$seperator}({$regExDay})$/";
		}
		else if ($this->_iYearPos == 3)
		{
			if ($this->_iDayPos == 1)
			$this->_sValParseRegExpr = "/^({$regExDay}){$seperator}({$regExMonth}){$seperator}({$regExYear})$/";
			else
			$this->_sValParseRegExpr = "/^({$regExMonth}){$seperator}({$regExDay}){$seperator}({$regExYear})$/";
		}


		// mask for date-function
		$this->_sDateMask = str_replace(array("D", "M"), array("j", "n"), $sMask);
	}

	/**
     * Get the date value as an array: array(y,m,d)
     *
     * @return array
     * @access public
     * @author Thomas Branius
     * @since 16-03-2010
     */
	function getAsArray()
	{
		if ($this->getValue() == "")
		{
			return array("", "", ""); 
		}
		if (preg_match($this->_sValParseRegExpr, $this->getValue(), $data) == 0)
		trigger_error("Value is not a valid date [" . $this->getValue() . "]", E_USER_ERROR);
		if ($data[$this->_iYearPos] <= 50)
		$data[$this->_iYearPos] = $data[$this->_iYearPos] + 2000;
		if ($data[$this->_iYearPos] <= 100)
		$data[$this->_iYearPos] = $data[$this->_iYearPos] + 1900;

		return array( $data[$this->_iYearPos], $data[$this->_iMonthPos],  $data[$this->_iDayPos]);
	}

	/**
     * Return the value of the field
     *
     * @return mixed: the value of the field
     * @access public
     * @author Thomas Branius
     * @since 16-03-2010
     */
	function getValue()
	{
		$sValue = parent::getValue();

		if (preg_match($this->_sValParseRegExpr, $sValue))
		return $sValue;

		if ($this->_bParseOtherPresentations)
		$sValue = $this->parseOtherPresentations($sValue);

		return $sValue;
	}

	/**
     * Set the value of the field
     *
     * @param mixed $mValue: The new value for the field
     * @return void
     * @access public
     * @author Thomas Branius
     * @since 16-03-2010
     */
	function setValue( $mValue )
	{
		if ($this->_oForm->isPosted())
		return parent::setValue($mValue);

		// parse value from db
		$regex = '/([0-9]{4})-([0-9]{2})-([0-9]{2})/';

		if (preg_match("/0000-00-00/", $mValue))
		{
			$this->_mValue = null;
		}
		else if (preg_match($regex, $mValue, $data))
		{
			$timestamp = mktime(0, 0, 0, $data[2], $data[3], $data[1]);
			$this->_mValue = date($this->_sDateMask, $timestamp);
		}
		else
		{
			$this->_mValue = $mValue;
		}
	}

	/**
     T* try to parse other presentations of dateformat
     *
     * @return mixed: the value of the field
     * @access public
     * @author Thomas Branius
     * @since 16-03-2010
     */
	function parseOtherPresentations($sValue)
	{
		// dd.mm.YYYY, dd/mm/YYYY, dd-mm-YYYY
		// dd.mm.YY, dd/mm/YY, dd-mm-YY
		// d.m.YYYY, d/m/YYYY, d-m-YYYY
		// d.m.YY, d/m/YY, d-m-YY
		$regex1 = '^([0-3]?\d)([\-\.\/])([01]?\d)\2([0-9]{2})(\d\d){0,1}$';

		if (preg_match("/$regex1/", str_replace(' ', '', $this->_mValue), $data))
		{
			if (isset($data[5]))
			$year = $data[4] * 100 + $data[5];
			else if ($data[4] <= 50)
			$year = 2000 + $data[4];
			else
			$year = 1900 + $data[4];

			$day = $data[1];
			$month = $data[3];

			$timestamp = mktime(0, 0, 0, $month, $day, $year);
			$this->_mValue = date($this->_sDateMask, $timestamp);
			return date($this->_sDateMask, $timestamp);
		}

		// YYYY/mm/dd, YYYY-mm-dd
		// YY/mm/dd, YY-mm-dd
		// YYYY/m/d, YYYY-m-y
		// YY/m/d, YY-m-y
		$regex2 = '^([0-9]{2})(\d\d){0,1}([\-\/])([01]?\d)\3([0-3]?\d)$';

		if (preg_match("/$regex2/", str_replace(' ', '', $this->_mValue), $data))
		{
			if (isset($data[2]))
			$year = $data[1] * 100 + $data[2];
			else if ($data[1] <= 50)
			$year = 2000 + $data[1];
			else
			$year = 1900 + $data[1];

			$day = $data[5];
			$month = $data[4];

			$timestamp = mktime(0, 0, 0, $month, $day, $year);
			$this->_mValue = date($this->_sDateMask, $timestamp);
			return date($this->_sDateMask, $timestamp);
		}

		return $sValue;
	}

	/**
     * Check if the date is valid (eg not 31-02-2003)
     *
     * @return boolean: true if the field is correct, false if not
     * @access public
     * @author Thomas Branius
     */
	function isValid()
	{
		// the result has been requested before..
		if( isset( $this->_isValid ) )
		{
			return $this->_isValid;
		}

		if( $this->getValue() != "" )
		{
			if( preg_match( $this->_sValParseRegExpr, $this->getValue(), $data ) )
			{
				$data = $this->getAsArray();
				if( checkdate($data[1], $data[2], $data[0]) == false )
				{
					$this->_isValid = false;
				}
				else
				{
					$timestamp = mktime(0, 0, 0,$data[1], $data[2], $data[0]);
					$this->_mValue = date($this->_sDateMask, $timestamp);
				}
			}
			else
			{
				$this->_isValid = false;
			}
		}

		if( isset( $this->_isValid ) && $this->_isValid == false )
		{
			// set the error message
			$this->_sError = $this->_oForm->_text( 14 );
		}

		return parent::isValid();
	}
}
?>