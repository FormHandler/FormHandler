<?php
/**
 * class DateField
 *
 * Create a datefield
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */

class DateField extends Field
{
    var $_sMask;     // string: how to display the fields (d-m-y) or other
    var $_oDay;      // SelectField or TextField: object of the day selectfield
    var $_oMonth;    // SelectField or TextField: object of the month selectfield
    var $_oYear;     // SelectField or TextField: object of the year selectfield
    var $_sInterval; // string: interval of the year
    var $_bRequired; // boolean: if the field is required or if we have to give the option to leave this field empty

    /**
     * DateField::DateField()
     *
     * Constructor: create a new datefield object
     *
     * @param object &$oForm: the form where the datefield is located on
     * @param string $sName: the name of the datefield
     * @param string $sMask: the mask which is used to display the fields
     * @return DateField
     * @access public
     * @author Teye Heimans
     */
    function DateField( &$oForm, $sName, $sMask = null, $bRequired = null, $sInterval = null )
    {
        // set the default date display
        $this -> setMask( !is_null( $sMask ) ? $sMask : FH_DATEFIELD_DEFAULT_DISPLAY );

        // set the default interval
        $this -> setInterval( !is_null( $sInterval ) ? $sInterval : FH_DATEFIELD_DEFAULT_DATE_INTERVAL);

        // set if the field is required
        $this->setRequired( !is_null( $bRequired ) ? $bRequired : FH_DATEFIELD_DEFAULT_REQUIRED );

        // d = selectfield day
        // m = selectfield month
        // y = selectfield year
        // D = textfield day
        // M = textfield month
        // Y = textfield year

        // generate the objects for the fields
        $fields = $this -> _getFieldsFromMask();
        $len = strlen( $fields );

        for( $x = 0; $x < $len; $x++ )
        {
            $c = $fields{$x};

            switch ( $c ) {
                // year selectfield
            	case 'y':
            	    $this -> _oYear = new SelectField( $oForm, $sName.'_year');

                    // get the year interval
                	list( $iStart, $iEnd ) = $this->_getYearInterval();
                	$iEnd   = intval($iEnd);
                    $iStart = intval( $iStart );
                    $iYear = date('Y');

                    // set the years
                    $aYears = array();
                    if(!$bRequired) $aYears[''] = ''; // was 0000

                    // calculate the difference between the years
                    $iDiff = ($iYear + $iEnd) - ($iYear - $iStart);

                    $iCounter = 0;
                    while( $iDiff != $iCounter )
                    {
                        $i = ($iYear + $iEnd) - $iCounter;

                        $aYears[$i] = $i;

                        $iCounter += $iCounter < $iDiff ? 1 : -1;
                    }

                    // set the options
                    $this -> _oYear -> setOptions( $aYears );

            		break;

                // year textfield
            	case 'Y':
            	    $this -> _oYear = new TextField ( $oForm, $sName.'_year');
                    $this -> _oYear -> setSize( 4 );
                    $this -> _oYear -> setMaxlength( 4 );
                    $this -> _oYear -> setValidator( _FH_DIGIT );
                    break;

                // month selectfield
                case 'm':
                    $this -> _oMonth = new SelectField( $oForm, $sName.'_month');
                    // set the months in the field
                    $aMonths = array(
                      '01' => $oForm->_text( 1 ),
                      '02' => $oForm->_text( 2 ),
                      '03' => $oForm->_text( 3 ),
                      '04' => $oForm->_text( 4 ),
                      '05' => $oForm->_text( 5 ),
                      '06' => $oForm->_text( 6 ),
                      '07' => $oForm->_text( 7 ),
                      '08' => $oForm->_text( 8 ),
                      '09' => $oForm->_text( 9 ),
                      '10' => $oForm->_text( 10 ),
                      '11' => $oForm->_text( 11 ),
                      '12' => $oForm->_text( 12 )
                    );
                    if(!$bRequired )
                    {
                        $aMonths[''] = ''; // was 00
                        ksort($aMonths);
                    }

                    // set the options
                    $this -> _oMonth -> setOptions( $aMonths );
                    break;

                // month textfield
                case 'M':
                    $this -> _oMonth = new TextField ( $oForm, $sName.'_month' );
                    $this -> _oMonth -> setSize( 2 );
                    $this -> _oMonth -> setMaxlength( 2 );
                    $this -> _oMonth -> setValidator( _FH_DIGIT );
                    break;

                // day selectfield
                case 'd':
                    $this -> _oDay = new SelectField( $oForm, $sName.'_day');

                    // get the days
                    $aDays = array();
                    if(!$bRequired) $aDays[''] = ''; // was 00

                    for($i = 1; $i <= 31; $i++)
                    {
                        $aDays[sprintf('%02d', $i)] = sprintf('%02d', $i);
                    }
                    $this -> _oDay -> setOptions( $aDays );
                    break;

                // day textfield
                case 'D':
                    $this -> _oDay = new TextField( $oForm, $sName.'_day' );
                    $this -> _oDay -> setSize( 2 );
                    $this -> _oDay -> setMaxlength( 2 );
                    $this -> _oDay -> setValidator( _FH_DIGIT );
                    break;
            }
        }

        // call the Field constructor
        parent::Field( $oForm, $sName );
    }

    /**
     * DateField::setRequired()
     *
     * Set if the datefield is required or if we have to give the user
     * the option to select empty value
     *
     * @param boolean $bStatus: the status
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setRequired( $bStatus )
    {
        $this->_bRequired = $bStatus;

        if( isset( $this -> _oYear ) && is_object( $this -> _oYear ) )
          $this -> _oYear -> setValidator( $bStatus ? FH_DIGIT : _FH_DIGIT );

        if( isset( $this -> _oMonth ) && is_object( $this -> _oMonth ) )
          $this -> _oMonth -> setValidator( $bStatus ? FH_DIGIT : _FH_DIGIT );

        if( isset( $this -> _oDay ) && is_object( $this -> _oDay ) )
          $this -> _oDay -> setValidator( $bStatus ? FH_DIGIT : _FH_DIGIT );
    }

    /**
     * DateField::setDisplay()
     *
     * Set the display of the fields
     * (use d,m,y and t for positioning, like "d-m-y", "t, d d" or "y/m/d" )
     *
     * @param string $sMast: how we have to display the datefield (day-month-year combination)
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setMask( $sMask )
    {
        $this->_sMask = $sMask ;
    }

    /**
     * DateField::setInterval()
     *
     * Set the year range of the years
     * The interval between the current year and the years to start/stop.
     * Default the years are beginning at 90 yeas from the current. It is also possible to have years in the future.
     * This is done like this: "90:10" (10 years in the future).
     *
     * @param string/int $sInterval: the interval we should use
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setInterval( $sInterval )
    {
        $this->_sInterval = $sInterval;
    }

    /**
     * DateField::setExtra()
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
    	if( isset( $this -> _oYear ) && is_object( $this -> _oYear ) )
    	  $this -> _oYear -> setExtra ( $sExtra );

    	if( isset( $this -> _oMonth ) && is_object( $this -> _oMonth ) )
    	  $this -> _oMonth -> setExtra ( $sExtra );

    	if( isset( $this -> _oDay ) && is_object( $this -> _oDay ) )
    	  $this -> _oDay -> setExtra ( $sExtra );
    }

    /**
     * DateField::getValue()
     *
     * return the value of the field (in d-m-Y format!) or when a
     * field is given, the value of that field
     * @param string $fld: the field where you want to value of
     * @return string: the current value of the field
     * @access public
     * @author Teye Heimans
     */
    function getValue( $fld = null)
    {
        // when no specific field is requested..
        if( $fld == null )
        {
            // get the values of all fields
            $d = $this -> getValue('d');
            $m = $this -> getValue('m');
            $y = $this -> getValue('y');

            // return the value of the datefield
            if( $d == '' && $m == '' && $y == '')
            {
                return '';
            }
            else
            {
                return $this->_fillMask( $d, $m, $y );
            }
        }
        // a specific field is requested
        else
        {
            // which field is requested ?
            switch ( strtolower( $fld ) )
            {
                case 'y':
                    if( isset( $this -> _oYear ) && is_object( $this -> _oYear ) )
    	            return $this -> _oYear -> getValue();
    	            break;

    	        case 'm':
    	            if( isset( $this -> _oMonth ) && is_object( $this -> _oMonth ) )
    	            return $this -> _oMonth -> getValue();
    	            break;

    	        case 'd':
    	            if( isset( $this -> _oDay ) && is_object( $this -> _oDay ) )
    	            return $this -> _oDay -> getValue();
    	            break;
            }

            // no field matched. Return an empty value
            return '';
        }
    }

    /**
     * DateField::getAsArray()
     *
     * Get the date value as an array: array(y,m,d)
     *
     * @return array
     * @access public
     * @author Teye Heimans
     * @since 25/11/2005
     */
    function getAsArray()
    {
        $d = $this -> getValue('d');
        $m = $this -> getValue('m');
        $y = $this -> getValue('y');

        return array( $y, $m, $d );
    }

    /**
     * DateField::isValid()
     *
     * Check if the date is valid (eg not 31-02-2003)
     *
     * @return boolean: true if the field is correct, false if not
     * @access public
     * @author Teye Heimans
     */
    function isValid()
    {
    	// the result has been requested before..
    	if( isset($this->_isValid))
    	{
    		return $this->_isValid;
    	}

    	// check if the year field is valid
        if( isset( $this -> _oYear ) && is_object( $this->_oYear) )
        {
            if( ! $this -> _oYear -> isValid() )
            {
                // get the error
                $this -> _sError = $this -> _oYear -> getError();
                return false;
            }
        }

        // check if the month field is valid
        if( isset( $this -> _oMonth ) && is_object( $this->_oMonth) )
        {
            if( ! $this -> _oMonth -> isValid() )
            {
                // get the error
                $this -> _sError = $this -> _oMonth -> getError();
                return false;
            }
        }

        // check if the day field is valid
        if( isset( $this -> _oDay ) && is_object( $this->_oDay) )
        {
            if( ! $this -> _oDay -> isValid() )
            {
                // get the error
                $this -> _sError = $this -> _oDay -> getError();
                return false;
            }
        }

        $d = $this -> getValue('d');
        $m = $this -> getValue('m');
        $y = $this -> getValue('y');
        $mask = strtolower( $this->_sMask );

        if( $y != '' && strlen( $y ) != 4 )
        {
            $this->_sError = $this->_oForm->_text( 13 );
            return false;
        }

    	// first of al check if the date is right when a valid date is submitted
    	// (but only when all fields are displayed (d m and y or t in the display string!)
    	if( strpos( $mask, 'd') !== false &&
    	    strpos( $mask, 'm') !== false &&
    	    strpos( $mask, 'y') !== false &&
    	    ($d != '00' && $d != '') &&
    	    ($m != '00' && $m != '') &&
    	    ($y != '0000' && $y != '') &&
            !checkdate( $m, $d, $y ))
        {
        	$this->_sError = $this->_oForm->_text( 13 );
            $this->_isValid = false;
            return $this->_isValid;
        }

        // if validator given, check the value with the validator
    	if(isset($this->_sValidator) && !empty($this->_sValidator))
    	{
    		$this->_isValid = parent::isValid();
    	}
    	// no validator is given.. value is always valid
    	else
    	{
    		$this->_isValid = true;
    	}

    	return $this->_isValid;
    }

    /**
     * DateField::getField()
     *
     * return the field
     *
     * @return string: the field
     * @access public
     * @author Teye Heimans
     */
    function getField()
    {
        // set the date when:
        // - the field is empty
    	// - its not an edit form
    	// - the form is not posted
    	// - the field is required
    	// - there is no value set...
    	if( !$this->_oForm->isPosted() && # not posted
    	    (!isset($this->_oForm->edit) || !$this->_oForm->edit) &&       # no edit form
    	    ($this->getValue() == $this->_fillMask() || # empty values
    	     $this->getValue() == '') &&  # there is no value
    	     $this->_bRequired )          # field is required
    	{
    		// set the current date if wanted
    		if( FH_DATEFIELD_SET_CUR_DATE )
    		{
    			$this->setValue( date('d-m-Y') );
    		}
    	}

    	// view mode enabled ?
        if( $this -> getViewMode() )
        {
            // get the view value..
            return $this -> _getViewValue();
        }

    	$year = isset( $this -> _oYear ) && is_object( $this -> _oYear ) ?
    	  $this -> _oYear -> getField() : '';

        $month = isset( $this -> _oMonth ) && is_object( $this -> _oMonth ) ?
          $this -> _oMonth -> getField() : '';

        $day = isset( $this -> _oDay ) && is_object( $this -> _oDay ) ?
    	  $this -> _oDay -> getField() : '';

        // replace the values by the fields..
        return $this->_fillMask(
          ' '.$day.' ', #day
          ' '.$month.' ', #month
          ' '.$year.' ' #year
        ) .
        (isset($this->_sExtraAfter) ? $this->_sExtraAfter :'');
    }

    /**
     * DateField::setValue()
     *
     * Set the value of the field. The value can be 4 things:
     * - "d-m-Y" like 02-04-2004
     * - "Y-m-d" like 2003-12-24
     * - Unix timestamp like 1104421612
     * - Mask style. If you gave a mask like d/m/y, this is valid: 02/12/2005
     *
     * @param string $sValue: the time to set the current value
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setValue( $sValue )
    {
        // remove the time part if the date is coming from a datetime field
    	$aMatch = array();
    	if( preg_match('/^([0-9]{4}-[0-9]{2}-[0-9]{2}) [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $sValue, $aMatch) )
    	{
    		$sValue = $aMatch[1];
    	}

    	// replace the d, m and y values
    	$regex = $this->_fillMask( '%%2%%', '%%2%%', '%%4%%' );

       	// next, escape dangerous characters for the regex
    	$metachar = array( '\\',   '/',  '^',  '$',  '.',  '[',  ']',  '|',  '(',  ')',  '?',  '*',  '+',  '{',  '}' );
    	$escape   = array( '\\\\', '\/', '\^', '\$', '\.', '\[', '\]', '\|', '\(', '\)', '\?', '\*', '\+', '\{', '\}' );
    	$regex    = str_replace( $metachar, $escape, $regex );

    	// now add the (\d+) for matching the day, month and year values
    	$regex = str_replace('%%2%%', '(\d+){1,2}', $regex );
    	$regex = str_replace('%%4%%', '(\d{4})', $regex );
    	$regex = '/'.$regex.'/';

    	// now find the results
    	$match = array();
    	if( preg_match($regex, $sValue, $match ) )
    	{
    	    // get the fields from the mask
    	    $str = $this->_getFieldsFromMask();

    	    // get the length of the buffer (containing the dmy order)
    	    $len = strlen( $str );

    	    // save the results in the vars $d, $m and $y
    	    for( $i = 0; $i < $len; $i++ )
    	    {
    	        $c  = $str{$i};
    	        $$c = $match[$i+1];
    	    }
    	}
    	// the given value does not match the mask... is it dd-mm-yyyy style ?
    	elseif( preg_match( '/^(\d{2})-(\d{2})-(\d{4})$/', $sValue, $match ) )
    	{
    	    $d = $match[1];
    	    $m = $match[2];
    	    $y = $match[3];
    	}
    	// is the given value in yyyy-mm-dd style ?
    	elseif( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $sValue, $match ) )
    	{
    	    $d = $match[3];
    	    $m = $match[2];
    	    $y = $match[1];
    	}
    	// is the given value a timestamp ?
    	elseif( strlen( $sValue ) >= 8 && Validator::IsDigit($sValue) )
    	{
    	    $d = date('d', $sValue );
    	    $m = date('m', $sValue );
    	    $y = date('Y', $sValue );
    	}
    	if( !empty( $t ) ) $y = $t;

    	// save the dates for the fields
    	if( isset( $this -> _oYear ) && is_object( $this -> _oYear ) && isset( $y ) )
    	  $this -> _oYear -> setValue( $y );

	    if( isset( $this -> _oMonth ) && is_object( $this -> _oMonth ) && isset( $m ) )
	      $this -> _oMonth -> setValue( $m );

        if( isset( $this -> _oDay ) && is_object( $this -> _oDay ) && isset( $d ))
          $this -> _oDay -> setValue( $d );
    }


    /**
     * DateField::_getFieldsFromMask()
     *
     * Get the fields from the mask.
     * For example: "select the \da\y: d" will result in "d".
     * "y/m/d" will result in "ymd"
     *
     * @param string $mask: The mask where we should get the fields from
     * @return string
     * @access private
     * @author Teye Heimans
     */
    function _getFieldsFromMask( $mask = null)
    {
        // when no mask is given, use the default mask
        if( is_null( $mask ) )
        {
            $mask = $this->_sMask;
        }

        // buffer
	    $str = '';
	    $len = strlen( $mask );
	    $placeholders = array( 'd', 'D', 'm', 'M', 'y', 'Y' );

	    // walk each character in the mask
	    for( $i = 0; $i < $len; $i++ )
	    {
	        // get the character
	        $c = $mask{ $i };

	        // day, month or year ?
    	    if( in_array( $c, $placeholders ) )
    	    {
	           // not the first char ?
	           if( $i != 0 )
	           {
	               // was the char not escaped?
	               if( $mask{ $i - 1 } != '\\' )
	               {
	                   $str .= $c;
	               }
	           }
	           // the first char
	           else
	           {
	               // just add it to the buffer
	               $str .= $c;
	           }
    	    }
	    }

	    return $str;
    }

    /**
     * DateField::_fillMask()
     *
     * Return the mask filled with the given values
     *
     * @param string $d: The replacement for the "d"
     * @param string $m: The replacement for the "m"
     * @param string $y: The replacement for the "y"
     * @return string
     * @access private
     * @author Teye Heimans
     */
    function _fillMask( $d = '', $m = '', $y = '', $mask = null )
    {
        // when no mask is given, use the default mask
        if( is_null( $mask ) )
        {
            $mask = $this->_sMask;
        }

        $placeholders = array( 'd', 'D', 'm', 'M', 'y', 'Y' );

        // make sure that the fields are not replacing other fields characters
        // and that escaped chars are possible, like "the \da\y is: d"
        $len = strlen( $mask );
        $str = '';
        for( $i = 0; $i < $len; $i++ )
        {
            $c = $mask{$i};

            // field char ?
            if( in_array( $c, $placeholders))
            {
                // first char ?
                if( $i == 0 )
                {
                    $str .= '%__'.strtolower($c).'__%';
                }
                else
                {
                    // check if the char is escaped.
                    if( $mask{$i - 1} == '\\' )
                    {
                        // the char is escaped, display the char without slash
                        $str = substr($str, 0, -1).$c;
                    }
                    // the char is not escaped
                    else
                    {
                        $str .= '%__'.strtolower($c).'__%';
                    }
                }
            }
            else
            {
                $str .= $c;
            }
        }

        // replace the values by the new values
        return str_replace(
          array('%__d__%', '%__m__%', '%__y__%' ),
          array( $d, $m, $y ),
          $str
        );
    }

    /**
     * DateField::_getYearInterval()
     *
     * Get the year interval
     *
     * @return array
     * @access private
     * @author Teye Heimans
     */
    function _getYearInterval ()
    {
    	$sInterval = $this->_sInterval;

        // get the year interval for the dates in the field
        if( strpos($sInterval, ':') )
        {
             list( $iStart, $iEnd ) = explode( ':', $sInterval, 2 );
        }
        // no splitter found, just change the start interval
        elseif( is_string($sInterval) || is_integer($sInterval) && !empty($sInterval) )
        {
            $iStart = $sInterval;
            $iEnd = 0;
        }
        // no interval given.. use the default
        else
        {
            $iStart = 90;
            $iEnd = 0;
        }

        return array( $iStart, $iEnd );
    }
}

?>