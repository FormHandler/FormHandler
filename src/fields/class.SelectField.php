<?php
/**
* class SelectField
*
* Create a SelectField
*
* @author Teye Heimans
* @package FormHandler
* @subpackage Fields
*/
class SelectField extends Field
{
	var $_aOptions;              // array: the options of the selectfield
	var $_bUseArrayKeyAsValue;   // boolean: if the keys of the array should be used as values
	var $_iSize;                 // integer: set the size of the field
	var $_bMultiple;             // boolean: can multiple items be selected or not?
	var $_classOpt;
	/**
     * SelectField::SelectField()
     *
     * Public constructor: Create a selectfield object
     *
     * @param object $oForm: The form where the field is located on
     * @param string $sName: The name of the form
     * @return SelectField
     * @access public
     * @author Teye Heimans
     */
	function SelectField( &$oForm, $sName )
	{
		// call the constructor of the Field class
		parent::Field( $oForm, $sName );

		$this->setSize( 1 );
		$this->useArrayKeyAsValue( FH_DEFAULT_USEARRAYKEY );
		$this->setMultiple( false );
	}

	/**
     * SelectField::getValue()
     *
     * Return the value of the field
     *
     * @return mixed
     * @access public
     * @author Teye Heimans     
     */
	function getValue()
	{
		// are multiple selects possible?
		if( $this->_bMultiple )
		{
			// is there a value ?
			if( isset( $this->_mValue ) )
			{
				if( is_string( $this->_mValue) )
				{
					return explode(',', $this->_mValue );
				}
			}
			else
			{
				return array();
			}
		}

		return parent::getValue();
	}

	/**
     * SelectField::getField()
     *
     * Public: return the HTML of the field
     *
     * @return string: the html
     * @access public
     * @author Teye Heimans
	 * @since 12-08-2008 Altered by Johan Wiegel, repaired valid html </optgroup> thanks to Roland van Wanrooy
     */
	function getField()
	{
		// view mode enabled ?
		if( $this -> getViewMode() )
		{
			// get the view value..
			return $this -> _getViewValue();
		}

		// multiple selected items possible?
		$aSelected = array();
		if($this->_bMultiple)
		{
			if( isset( $this->_mValue ) )
			{			
				// when there is a value..
				if( !is_array( $this->_mValue ) )
				{
					// split a string like 1, 4, 6 into an array
					$aItems = explode(',', $this->_mValue );
					foreach( $aItems as $mItem )
					{
						$aSelected[] = trim( $mItem );
					}
				}
				// the value is an array
				else
				{
					$aSelected[] = $this->_mValue;
				}
			}
		}
		else if( isset($this->_mValue ) )
		{
			$aSelected[] = $this->_mValue;
		}

		// create the options list
		$sOptions = '';

		// added by Roland van Wanrooy: flag to indicate an optgroup, in order to close it properly
		$bOptgroup = false;
		// added by Roland van Wanrooy: string with the close tag
		$sOGclose = "\t</optgroup>\n";

		foreach ($this->_aOptions as $iKey => $sValue )
		{
			// use the array value as field value if wanted
			if(!$this->_bUseArrayKeyAsValue) $iKey = $sValue;


			if( strpos($iKey, 'LABEL') )
			{
				// added by Roland van Wanrooy: close the optgroup if there is one
				$sOptions .= ($bOptgroup ? $sOGclose : '');

				$sOptions .= "\t<optgroup label=\"". $sValue."\">\n";

				// added by Roland van Wanrooy: flag opgroup as true
				$bOptgroup = true;
			}
			else
			{
				if( isset( $aSelected[0] ) AND is_array( $aSelected[0] ) ){ $aSelected = $aSelected[0]; }
				$sOptions .= sprintf(
				"\t<option %s value=\"%s\" %s>%s</option>\n",
				isset( $this->_classOpt[$iKey] ) ? $this->_classOpt[$iKey] : '', // added by sid benachenhou for handling styles
				$iKey,
				( in_array( $iKey, $aSelected ) ?' selected="selected"':'' ),
				$sValue
				);
			}
		}

		// when no options are set, set an empty options for XHML compatibility
		if( empty($sOptions) )
		{
			$sOptions = "\t<option>&nbsp;</option>\n\t";
		}
		// added by Roland van Wanrooy:
		// $sOptions is not empty, so if there was an <opgroup> then close is properly
		else {
			$sOptions .= ($bOptgroup ? $sOGclose : '');
		}

		// return the field
		return sprintf(
		'<select name="%s" id="%s" size="%d"%s>%s</select>%s',
		$this->_sName. ( $this->_bMultiple ? '[]':''),
		$this->_sName,
		$this->_iSize,
		($this->_bMultiple ? ' multiple="multiple"' : '' ).
		(isset($this->_iTabIndex) ? ' tabindex="'.$this->_iTabIndex.'" ' : '').
		(isset($this->_sExtra) ? ' '.$this->_sExtra :'' ),
		$sOptions,
		(isset($this->_sExtraAfter) ? $this->_sExtraAfter :'')
		);
	}

	/**
     * SelectField::setOptions()
     *
     * Set the options of the field
     *
     * @param array $aOptions: the options for the field
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function setOptions( $aOptions )
	{
		$this->_aOptions = $aOptions;
	}
	
	// added by sid benachenhou for handling styles
	function setCOptions( $_classOpt )
	{
		$this->_classOpt = $_classOpt;
	}
	/**
     * SelectField::setMultiple()
     *
     * Set if multiple items can be selected or not
     *
     * @param boolean $bMultiple
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function setMultiple( $bMultiple )
	{
		$this->_bMultiple = $bMultiple;
	}

	/**
     * SelectField::setSize()
     *
     * Set the size of the field
     *
     * @param integer $iSize: the new size
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function setSize( $iSize )
	{
		$this->_iSize = $iSize;
	}

	/**
     * SelectField::useArrayKeyAsValue()
     *
     * Set if the array keys of the options has to be used as values for the field
     *
     * @param boolean $bMode: The mode
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function useArrayKeyAsValue( $bMode )
	{
		$this->_bUseArrayKeyAsValue = $bMode;
	}
}
?>