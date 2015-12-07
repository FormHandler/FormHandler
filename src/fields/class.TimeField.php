<?php

/**
 * class TimeField
 *
 * Create a new TimeField class
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */
class TimeField extends Field
{
    var $_iFormat;   // integer: hour format: {12, 24}
    var $_oHour;     // SelectField: object of the hour selectfield
    var $_oMinute;   // SelectField: object of the minute selectfield
    var $_bRequired; // boolean: if the field is required or if we have to give the option to leave this field empty

    /**
     * TimeField::TimeField()
     *
     * Constructor: create a new TimeField on the given form
     *
     * @param object $oForm: The form where the field is located on
     * @param string $sName: The name of the field
     * @return TimeField
     * @author Teye Heimans
     */
    function TimeField( &$oForm, $sName )
    {
        // set the default hour format
        $this->setHourFormat( FH_TIMEFIELD_DEFAULT_HOUR_FORMAT );

        // set if the field is required
        $this->setRequired( FH_TIMEFIELD_DEFAULT_REQUIRED );

        // make the hour and minute fields
        $this->_oHour   = new SelectField($oForm, $sName.'_hour');
        $this->_oMinute = new SelectField($oForm, $sName.'_minute');

        parent::Field( $oForm, $sName );

        // posted or edit form? Then load the value of the time
        if( $oForm->isPosted() || (isset($oForm->edit) && $oForm->edit) )
        {
            $this->_mValue = $this->_oHour->getValue().':'.$this->_oMinute->getValue();
        }
    }

    /**
     * TimeField::setExtra()
     *
     * Set some extra tag information of the fields
     *
     * @param string $sExtra: The extra information to inglude with the html tag
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setExtra( $sExtra )
    {
    	$this->_oHour->setExtra  ( $sExtra );
    	$this->_oMinute->setExtra( $sExtra );
    }

    /**
     * TimeField::setHourFormat()
     *
     * Set the hour format (eg. 12 or 24)
     *
     * @param integer $iFormat: The hour format
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setHourFormat( $iFormat )
    {
        if($iFormat == 12 || $iFormat == 24)
        {
            $this->_iFormat = $iFormat;
        }
        else
        {
        	trigger_error(
        	  'Invalid value as hour format! Only 12 or 24 are allowed!',
        	  E_USER_WARNING
        	);
        }
    }

    /**
     * TimeField::setRequired()
     *
     * PSet if the timefield is required or if we have to give the user
     * the option to select an empty value
     *
     * @param boolean $bStatus: The status
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setRequired( $bStatus )
    {
        $this->_bRequired = $bStatus;
    }


    /**
     * TimeField::setValue()
     *
     * Set the value of the field
     *
     * @param string $sValue: The new value of the field
     * @return void
     * @access Public
     * @author Teye Heimans
     */
    function setValue( $sValue )
    {
    	if( strpos($sValue,':') !== false)
    	{
            list($sHour, $sMinute) = explode(':', $sValue);

            $this->_oHour->setValue   ( $sHour );
            $this->_oMinute->setValue ( $sMinute );
            $this->_mValue = $sValue;
        }
        // possibility to set "no" value when the field is not required
        elseif( (strtolower($sValue )== "null" || empty( $sValue ) ) && !$this->_bRequired )
        {
            $this->_mValue = "";
        }
    }

    /**
     * TimeField::getValue()
     *
     * Return the current value of the field
     *
     * @return string: the value of the field
     * @access public
     * @author Teye Heimans
     */
    function getValue()
    {
        if($this->_oHour->getValue() == '' && $this->_oMinute->getValue() == '')
        {
            return '';
        }
        else
        {
        	$this->_mValue = $this->_oHour->getValue().':'.$this->_oMinute->getValue();
            return $this->_mValue;
        }
    }


    /**
     * TimeField::getField()
     *
     * Return the HTML of the field
     *
     * @return string: the html of the field
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


    	// set the currect time if wanted
        if( !$this->_oForm->isPosted() &&
            (!isset($this->_oForm->edit) || !$this->_oForm->edit) &&
            $this->_bRequired &&
            $this->getValue() == '' &&
            FH_TIMEFIELD_SET_CUR_TIME)
        {
        	$this->setValue( date('H').':'.date('i') );
        }

        // generate the hour options
        $aHours = array();
        if(!$this->_bRequired)
        {
            $aHours[''] = '';
        }
        for($i = 0; $i <= ($this->_iFormat-1); $i++ )
        {
            $aHours[sprintf('%02d', $i)] = sprintf('%02d', $i);
        }

        // generate the minutes options
        $aMinutes = array();
        if(!$this->_bRequired)
        {
            $aMinutes[''] = '';
        }
        $i = 0;
        while($i <= 59)
        {
            $aMinutes[sprintf("%02d", $i)] = sprintf("%02d", $i);
            $i += FH_TIMEFIELD_MINUTE_STEPS;
        }

        // set the options
        $this->_oHour->setOptions  ( $aHours );
        $this->_oMinute->setOptions( $aMinutes );

        // make sure that the minutes option can be displayed
        if( $this -> _bRequired ||  $this -> getValue() != "" )
        {
            $this->_oHour->_mValue += $this->_getNearestMinute( $this->_oMinute->_mValue );
            if($this->_oHour->_mValue == 24) $this->_oHour->_mValue = 0;
        }

        //debug
        //print_Var( $this -> _mValue, $this->_oHour->_mValue, $this->_oMinute->_mValue );

        // return the fields
        return
          $this->_oHour->getField() . " : " .
          $this->_oMinute->getField().
          (isset($this->_sExtraAfter) ? $this->_sExtraAfter :'');
    }

    /**
     * TimeField::_getNearestMinute()
     *
     * Get the nearest minute in the minutes list
     *
     * @param int $minute
     * @return int: 1 or 0 if the hour should be increased
     * @access private
     * @author Teye Heimans
     */
    function _getNearestMinute( &$minute )
    {
        // get the nearest value at the minutes...
    	for($i = 0; $i < $minute; $i += FH_TIMEFIELD_MINUTE_STEPS);

    	$i = abs( $minute - $i ) < abs( $minute - ($i - FH_TIMEFIELD_MINUTE_STEPS)) ?
    	$i : ($i - FH_TIMEFIELD_MINUTE_STEPS);

    	$minute = $i;

    	if($minute == 60)
    	{
    	    $minute = 0;
    	    return 1;
    	}
    	else
    	{
    	    return 0;
    	}
    }
}

?>