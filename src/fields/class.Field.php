<?php
/**
 * class Field
 *
 * Class to create a field.
 * This class contains code which is used by all the other fields
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */

class Field
{
	var $_oForm;         // object: the form where the field is located in
	var $_sName;         // string: name of the field
	var $_sValidator;    // string: callback function to validate the value of the field
	var $_mValue;        // mixed: the value of the field
	var $_sError;        // string: if the field is not valid, this var contains the error message
	var $_sExtra;        // string: extra data which should be added into the HTML tag (like CSS or JS)
	var $_iTabIndex;     // int: tabindex or null when no tabindex is set
	var $_sExtraAfter;   // string: extra data which should be added AFTER the HTML tag
	var $_viewMode;      // boolean: should we only display the value instead of the field ?


	/**
     * Field::Field()
     *
     * Public abstract constructor: Create a new field
     *
     * @param object $oForm: The form where the field is located on
     * @param string $sName: The name of the field
     * @return Field
     * @access public
     * @author Teye Heimans
     */
	function Field( &$oForm, $sName )
	{
		// save the form and nome of the field
		$this->_oForm = &$oForm;
		$this->_sName = $sName;

		// check if there are spaces in the fieldname
		if(strpos($sName,' ') !== false)
		{
			trigger_error('Warning: There are spaces in the field name "'.$sName.'"!', E_USER_WARNING );
		}

		// get the value of the field
		if( $oForm->isPosted() )
		{
			// make sure that the $_POST array is global
			if(!_global) global $_POST;

			// get the value if it exists in the $_POST array
			if( isset( $_POST[$sName] ) )
			{
				// is the posted value a string
				if( is_string( $_POST[$sName] ) )
				{
					// save the value...
					$this->setValue(
					get_magic_quotes_gpc() ? stripslashes($_POST[$sName]) : $_POST[$sName]
					);
				}
				// the posted value is an array
				else if( is_array( $_POST[$sName] ) )
				{
					// escape the incoming data if needed and pass it to the field
					$item = array();
					foreach ( $_POST[$sName] as $key => $value )
					{
						$item[$key] = get_magic_quotes_gpc() ? stripslashes($value) : $value;
					}
					$this->setValue($item);
				}
			}

			/*
			* When the form is posted but this field is not found in the $_POST array,
			* keep the data from the db
			* (This happens when the DISABLED attribute in the field's tag is used)
			* Problem is that datefield's are never in the post array (because
			* they have 3 fields: {name}_day, etc.). Because of this, the old value always
			* will be kept...
			*
			* see (dutch topics!):
			* http://www.formhandler.net/FH3/index.php?pg=12&id=1333#1333
			* http://www.formhandler.net/FH3/index.php?pg=12&id=1296#1296
			*
			* TODO!!
			*/
			/*
			elseif ( $oForm->edit )
			{
			if( isset( $oForm->_dbData[$sName] ) )
			{
			$this->setValue( $oForm->_dbData[$sName] );
			}
			}*/

		}
		// The form is not posted, load database value if exists
		else if( isset( $oForm->edit) && $oForm -> edit )
		{
			// does a db value exists for this field ?
			if( isset( $oForm->_dbData[$sName] ) )
			{
				// load the value into the field
				$this->setValue( $oForm->_dbData[$sName] );
			}
		}

		// check if the user got another value for this field.
		if( isset($oForm ->_buffer[ $sName ] ) )
		{
			list( $bOverwrite, $sValue ) = $oForm->_buffer[ $sName ];

			// if the field does not exists in the database
			if($bOverwrite || (!isset($oForm->_dbData[$sName]) && !$oForm->isPosted() ))
			{
				$this->setValue( $sValue );
			}

			// remove the value from the buffer..
			unset( $oForm->_buffer[ $sName ] );
		}
	}

	/**
     * Field::isValid()
     *
     * Check if the value of the field is valid. If not,
     * set the error message and return false
     *
     * @return boolean: If the value of the field is valid
     * @author Teye Heimans
     * @access public
     * @since 11-04-2008 ADDED POSSIBILITY TO USE MULTIPLE VALIDATORS 
     * @author Remco van Arkelen & Johan Wiegel
     */
	function isValid()
	{
		// done this function before... return the prefious value
		if( isset( $this->_isValid ) )
		{
			return $this->_isValid;
		}

		// field in view mode?
		if( $this -> getViewMode() )
		{
			return $this->_isValid = true;
		}

		// is a validator set?
		if(isset($this->_sValidator) && $this->_sValidator != null)
		{
			// if it's an array, it's a method
			if (!is_array($this->_sValidator))
			{
				// Is there an | , there are more validators
				if( strpos( $this->_sValidator, '|' ) > 0 )
				{
					$aValidator = explode( '|', $this->_sValidator );
					foreach( $aValidator AS $val )
					{
						// is the validator a user-specified function?
						if( function_exists($this->_sValidator) )
						{
							$value = $this->getValue();
							$v = is_string($value) ? trim( $value) : $value;
							$error = call_user_func( $this->_sValidator, $v, $this->_oForm );
						}
						else
						{
							$v = new Validator();
							// is this a defined function? translate it to the correct function
							if( defined( $val ) )
							{
								$aVal = get_defined_constants();
								$val = $aVal[ $val ];
							}

							if( is_object( $v ) && method_exists($v, $val ) )
							{
								// call the build in  validator function
								$value = $this->getValue();
								if( is_string( $value) )
								$value = trim( $value );
								$error = $v->{$val}( $value );
							}
							else
							{
								trigger_error('Unknown validator: "'.$val.'" used in field "'.$this->_sName.'"');
								$error = false;
							}
							unset( $v );
						}
						// Stop processing validators if 1 fails.
						if( true !== $error )
						{
							break;
						}
					}
				}
				else
				{
					// is the validator a user-spicified function?
					if( function_exists($this->_sValidator) )
					{
							$value = $this->getValue();
							$v = is_string($value) ? trim( $value) : $value;
							$error = call_user_func( $this->_sValidator, $v, $this->_oForm );
					}
					else
					{
						$v = new Validator();
						if( is_object( $v ) && method_exists($v, $this->_sValidator) )
						{
							// call the build in  validator function
							$value = $this->getValue();
							if( is_string( $value) )
							$value = trim( $value );
							$error = $v->{$this->_sValidator}( $value );
						}
						else
						{
							trigger_error('Unknown validator: "'.$this->_sValidator.'" used in field "'.$this->_sName.'"');
							$error = false;
						}
						unset( $v );
					}
				}
			}
			// method given
			else
			{
				if( method_exists( $this->_sValidator[0], $this->_sValidator[1] ) )
				{
					$value = $this->getValue();
					$value = (is_array ($value)) ? $value : trim ($value);
					$error = call_user_func(array(&$this->_sValidator[0], $this->_sValidator[1]), $value );
				}
				else
				{
					trigger_error(
					"Error, the validator method '".$this->_sValidator[1]."' does not exists ".
					"in object '".get_class($this->_sValidator[0])."'!",
					E_USER_ERROR
					);
					$error = false;
				}
			}

			// set the error message
			$this->_sError =
			is_string($error) ? $error :
			(!$error ? $this->_oForm->_text( 14 ) :
			(isset($this->_sError) ? $this->_sError : ''));
		}

		$this->_isValid = empty( $this->_sError );
		return $this->_isValid;
	}
	/**
	 * Field::getValidator()
	 * 
	 * Returns the validator fromm this field
	 * Added in order to use ajax validation
	 * 
	 * @return string
	 * @access public
	 * @author Johan Wiegel
	 * @since 04-12-2008
	 */
	function getValidator( )
	{
		return $this->_sValidator;
	}

	/**
     * Field::setValidator()
     *
     * Set the validator which is used to validate the value of the field
     * This can also be an array.
     * If you want to use a method to validate the value use it like this:
     * array(&$obj, 'NameOfTheMethod')
     *
     * @param string $sValidator: the name of the validator
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function setValidator( $sValidator )
	{
		$this->_sValidator = $sValidator;

		/*
		if( $this->_oForm->_ajaxValidator === true )
		{
		echo 'JAJA';
		require_once( FH_INCLUDE_DIR . 'includes/class.AjaxValidator.php' );
		$oAjaxValidator = new AjaxValidator( $this );
		$oAjaxValidator->AjaxValidator( $this );
		}
		*/

	}

	/**
     * Field::setTabIndex()
     *
     * Set the tabindex of the field
     *
     * @param int $iIndex
     * @return void
     * @author Teye Heimans
     * @access public
     */
	function setTabIndex( $iIndex )
	{
		$this->_iTabIndex = $iIndex;
	}

	/**
     * Field::setExtraAfter()
     *
     * Set some extra HTML, JS or something like that (to use after the html tag)
     *
     * @param strint $sExtra: the extra html to insert into the tag
     * @return void
     * @author Teye Heimans
     * @access public
     */
	function setExtraAfter( $sExtraAfter )
	{
		$this->_sExtraAfter = $sExtraAfter;
	}

	/**
     * Field::setError()
     *
     * Set a custom error
     *
     * @param string $sError: the error to set into the tag
     * @return void
     * @access public
     * @author Filippo Toso - filippotoso@libero.it
     */
	function setError( $sError )
	{
		$this->_sError = $sError;
	}

	/**
     * Field::getValue()
     *
     * Return the value of the field
     *
     * @return mixed: the value of the field
     * @access public
     * @author Teye Heimans
     */
	function getValue()
	{
		return isset( $this->_mValue ) ? $this->_mValue : '';
	}

	/**
     * Field::getError()
     *
     * Return the error of the field (if the field-value is not valid)
     *
     * @return string: the error message
     * @access public
     * @author Teye Heimans
     */
	function getError()
	{
		return isset( $this->_sError ) && strlen($this->_sError) > 0 ? sprintf( FH_ERROR_MASK, $this->_sName ,$this->_sError): '';
	}

	/**
     * Field::setValue()
     *
     * Set the value of the field
     *
     * @param mixed $mValue: The new value for the field
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function setValue( $mValue )
	{
		$this->_mValue = $mValue;
	}

	/**
     * Field::setExtra()
     *
     * Set some extra CSS, JS or something like that (to use in the html tag)
     *
     * @param strint $sExtra: the extra html to insert into the tag
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function setExtra( $sExtra )
	{
		$this->_sExtra = $sExtra;
	}

	/**
     * Field::getField()
     *
     * Return the HTML of the field.
     * This function HAS TO BE OVERWRITTEN by the child class!
     *
     * @return string: the html of the field
     * @access public
     * @author Teye Heimans
     */
	function getField()
	{
		trigger_error('Error, getField has not been overwritten!', E_USER_WARNING);
		return '';
	}

	/**
     * Field::getViewMode()
     *
     * Return if this field is set to view mode
     *
     * @return bool
     * @access public
     * @author Teye Heimans
     */
	function getViewMode()
	{
		return (isset( $this -> _viewMode) && $this -> _viewMode) ||
		(isset( $this -> _oForm -> _viewMode ) && $this -> _oForm -> _viewMode);
	}

	/**
     * Field::setViewMode()
     *
     * Enable or disable viewMode for this field
     *
     * @param boolean $mode
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function setViewMode( $mode = true )
	{
		$this -> _viewMode = (bool) $mode;
	}

	/**
	 * Field::_getViewValue()
	 *
	 * Return the value of the field
	 *
	 * @return mixed: the value of the field
	 * @access private
	 * @author Teye Heimans
	 */
	function _getViewValue()
	{
		// edit form and posted ? then first get the database value!
		if( isset( $this -> _oForm -> edit ) && $this -> _oForm -> edit && $this -> _oForm -> isPosted() )
		{
			$this -> setValue( $this -> _oForm -> _dbData[ $this -> _sName ] );
		}

		// get the value for the field
		$val = $this->getValue();

		// implode arrays
		$save = is_array( $val) ? implode( ',', $val) : $val;

		// are there mulitple options ?
		if( isset( $this->_aOptions ) )
		{
			// is the key returned while we should show the "label" to the user ?
			if( isset($this->_bUseArrayKeyAsValue) && $this->_bUseArrayKeyAsValue )
			{
				// is the value an array?
				if( is_array( $val) )
				{
					// save the labels instead of the index keys as view value
					foreach ( $val as $key => $value )
					{
						$val[$key] = $this->_aOptions[$value];
					}
				}
				// is there a "label" for this value ?
				else if( array_key_exists( $val, $this->_aOptions ) )
				{
					// get the "label" instead of the index
					$val = $this->_aOptions[$val];
				}
			}
		}

		// when the value is an array
		if( is_array($val) )
		{
			// is there only one item?
			if( sizeof($val) == 1 )
			{
				$result = $val[0];
			}
			else
			{
				// make a list of the selected items
				$result = "\t<ul>\n";
				foreach($val as $item )
				{
					$result .= "\t  <li>".$item."</li>\n";
				}
				$result .= "\t</ul>\n";
			}
		}
		else
		{
			$result = $val;
		}

		// return the value
		return $result;
	}
}
?>