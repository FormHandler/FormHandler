<?php

/**
 * class ListField
 *
 * Create a listfield on the given form
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */
class ListField extends Field
{
    var $_aOptions;             // Array: the options of the selectfield
    var $_oHidden;              // HiddenField: the fielf where the value is saved in
    var $_oOn;                  // SelectField: the field where the items are displayed which are "on"
    var $_oOff;                 // SelectField: the field where the items are displayed which are "off"
    var $_sOnTitle;             // String: the title used for the on section
    var $_sOffTitle;            // String: the title used for the off section
    var $_bUseArrayKeyAsValue;  // Boolean: if the keys of the array should be used as values
	var $_bVerticalMode; 		// Boolean: if field is stacked horizontal or vertical
    /**
     * ListField::ListField()
     *
     * Constructor: Create a new ListField
     *
     * @param object &$oForm: The form where this field is located on
     * @param string $sName: The name of the field
     * @param array  $aOptions: The options of the field
     * @return ListField
     * @access public
     * @author Teye Heimans
     */
    function ListField( &$oForm, $sName, $aOptions )
    {
    	$this->_mValue = array();
    	static $bSetJS = false;

    	// needed javascript included yet ?
        if(!$bSetJS)
        {
            $bSetJS = true;
            $oForm->_setJS( FH_FHTML_DIR."js/listfield.js", true);
        }

    	// set the options
        $this->_aOptions = $aOptions;

        parent::Field( $oForm, $sName, $aOptions );

        // make the fields of the listfield
        $this->_oHidden = new HiddenField($oForm, $sName);
        $this->_oOn     = new SelectField($oForm, $sName.'_ListOn');
        $this->_oOff    = new SelectField($oForm, $sName.'_ListOff');
        $this->_oOn->setMultiple ( true );
        $this->_oOff->setMultiple( true );

        // set some default values
        $this->useArrayKeyAsValue ( FH_DEFAULT_USEARRAYKEY );
        $this->setSize	   		  ( FH_DEFAULT_LISTFIELD_SIZE );
        $this->setOffTitle 		  ( $oForm->_text( 29 ) );
        $this->setOnTitle  		  ( $oForm->_text( 30 ) );
    }

    /**
     * ListField::SetVerticalMode()
     * 
     * Set the stack mode of the list field
     *
     * @param boolean $bVerticalMode
     * @access public
     * @author Rick de Haan
     * @since 20-03-2008 added by Johan Wiegel
     */
    
    function setVerticalMode($bVerticalMode)
    {
        $this->_bVerticalMode = $bVerticalMode;
    }     
    
    /**
     * ListField::setValue()
     *
     * Set the value of the field
     *
     * @param array | string $aValue: The new value of the field
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setValue( $aValue )
    {
    	// make an array from the value
        if(!is_array($aValue))
        {
            $aValue = explode(',', $aValue);
            foreach($aValue as $iKey => $sValue)
            {
            	$sValue = trim($sValue);

            	// dont save an empty value when it does not exists in the
            	// options array!
            	if( isset($sValue) ||
            	   (in_array( $sValue, $this->_aOptions ) ||
            	    array_key_exists( $sValue, $this->_aOptions )))
            	{
            		$aValue[$iKey] = $sValue;
            	}
            	else
            	{
            		unset($aValue[$iKey]);
            	}
            }
        }

        $this->_mValue = $aValue;
    }

    /**
     * ListField::setExtra()
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
    	$this->_oOff->setExtra ( $sExtra );
    	$this->_oOn->setExtra  ( $sExtra );
    }

    /**
     * ListField::setOnTitle()
     *
     * Set the title of the ON selection of the field
     *
     * @param strint $sTitle: The title
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setOnTitle($sTitle)
    {
        $this->_sOnTitle = $sTitle;
    }

    /**
     * ListField::setOffTitle()
     *
     * Set the title of the OFF selection of the field
     *
     * @param string $sTitle: The title
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setOffTitle($sTitle)
    {
        $this->_sOffTitle = $sTitle;
    }

    /**
     * ListField::getField()
     *
     * Return the HTML of the field
     *
     * @return string: The html
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

        // set the value for the hidden field
        if( !empty( $this->_mValue ))
        {
            $this->_oHidden->setValue( implode(',', $this->_mValue) );
        }

        // get the selected and unselected values
        $aSelected   = array();
        $aUnselected = array();
        foreach($this->_aOptions as $iIndex => $sValue)
        {
            $sKey = (!$this->_bUseArrayKeyAsValue) ? $sValue : $iIndex;

            if(in_array($sKey, $this->_mValue))
            {
                $aSelected[$iIndex] = $sValue;
            }
            else
            {
                $aUnselected[$iIndex] = $sValue;
            }
        }

        $this->_oOn->setOptions ( $aSelected );
        $this->_oOff->setOptions( $aUnselected );

        // add the double click event
        $this->_oOn->_sExtra .= " ondblclick=\"changeValue('".$this->_sName."', false)\"";
        $this->_oOff->_sExtra .= " ondblclick=\"changeValue('".$this->_sName."', true)\"";

        return
        $this->_oHidden->getField()."\n".
        str_replace(
          array(
            '%onlabel%',
            '%offlabel%',
            '%onfield%',
            '%offfield%',
            '%name%',
            '%ontitle%',
            '%offtitle%'
          ),
          array(
            $this->_sOnTitle,
            $this->_sOffTitle,
            $this->_oOn->getField(),
            $this->_oOff->getField(),
            $this->_sName,
            sprintf( $this->_oForm->_text( 34 ), htmlentities( strip_tags($this->_sOffTitle), null, FH_HTML_ENCODING) ),
            sprintf( $this->_oForm->_text( 34 ), htmlentities( strip_tags($this->_sOnTitle), null, FH_HTML_ENCODING) )
          ),
          (!empty($this->_bVerticalMode) && $this->_bVerticalMode) ? FH_LISTFIELD_VERTICAL_MASK : FH_LISTFIELD_HORIZONTAL_MASK
        ) .
        (isset($this->_sExtraAfter) ? $this->_sExtraAfter :''); 
    }

    /**
     * ListField::setSize()
     *
     * Set the size (height) of the field (default 4)
     *
     * @param integer $iSize: The size
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setSize( $iSize )
    {
        $this->_oOn->setSize ( $iSize );
        $this->_oOff->setSize( $iSize );
    }

    /**
     * ListField::useArrayKeyAsValue()
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
        $this->_oOn->useArrayKeyAsValue  ( $bMode );
        $this->_oOff->useArrayKeyAsValue ( $bMode );
    }
}
?>