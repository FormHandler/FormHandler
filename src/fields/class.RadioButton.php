<?php

/**
 * class RadioButton
 *
 * Create a RadioButton
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */
class RadioButton extends Field
{
    var $_aOptions;              // string: the value with is selected
    var $_bUseArrayKeyAsValue;   // boolean: if the keys of the array should be used as values
    var $_sMask;                 // string: what kind of "glue" should be used to merge the fields
    var $_oLoader;               // object: a maskloader object

    /**
     * RadioButton::RadioButton()
     *
     * Constructor: Create a new radiobutton object
     *
     * @param object $oForm: The form where this field is located on
     * @param string $sName: The name of the field
     * @param array|string $aOptions: The options for the field
     * @return RadioButton
     * @author Teye Heimans
     */
    function RadioButton( &$oForm, $sName, $aOptions )
    {
        // call the constructor of the Field class
        parent::Field( $oForm, $sName );

        $this->_aOptions = $aOptions;

        $this->setMask           ( FH_DEFAULT_GLUE_MASK );
        $this->useArrayKeyAsValue( FH_DEFAULT_USEARRAYKEY );
    }

    /**
     * RadioButton::useArrayKeyAsValue()
     *
     * Set if the array keys of the options has to be used as values for the field
     *
     * @param boolean $bMode:  The mode
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function useArrayKeyAsValue( $bMode )
    {
        $this->_bUseArrayKeyAsValue = $bMode;
    }

    /**
     * RadioButton::setMask()
     *
     * Set the "glue" used to glue multiple radiobuttons
     *
     * @param string $sMask
     * @return void
     * @author Teye Heimans
     * @access public
     */
    function setMask( $sMask )
    {
        // when there is no %field% used, put it in front of the mask/glue
        if( strpos( $sMask, '%field%' ) === false )
        {
            $sMask = '%field%' . $sMask;
        }

        $this->_sMask = $sMask;
    }

    /**
     * RadioButton::getField()
     *
     * Return the HTML of the field
     *
     * @return string: the html of the field
     * @access Public
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

		if( is_array( $this->_aOptions ) && count( $this->_aOptions )>0 )
        {
            $sResult = '';
            foreach( $this->_aOptions as $iKey => $sValue )
            {            
                if(!$this->_bUseArrayKeyAsValue)
                {
                    $iKey = $sValue;
                }
				
                $sResult .= $this->_getRadioButton( $iKey, $sValue, true );
            }
        }
        
        elseif( $this->_aOptions == '' || count( $this->_aOptions )===0 )
        {
        	$sResult = ' '; 
        }
        
        else
        {
            $sResult = $this->_getRadioButton( $this->_aOptions, '' );
        }

        // when we still got nothing, the mask is not filled yet.
        // get the mask anyway
        if( empty( $sResult ) )
        {
            $sResult = $this -> _oLoader -> fill();
        }

        return $sResult . (isset($this->_sExtraAfter) ? $this->_sExtraAfter :'');
    }

    /**
     * RadioButton::_getRadioButton()
     *
     * Return the radiobutton with the given title and value
     *
     * @param string $sValue: the value for the checkbox
     * @param string $sTitle: the title for the checkbox
     * @param bool $bUseMask: Do we need to use the mask ?
     * @return string: the HTML for the checkbox
     * @access Private
     * @author Teye Heimans
     */
    function _getRadioButton( $sValue, $sTitle, $bUseMask = false )
    {
        
        static $counter = 1;

        $sValue = trim( $sValue );
        $sTitle = trim( $sTitle );

        if( !isset( $this -> _oLoader ) ||is_null( $this -> _oLoader ) )
        {        
            $this -> _oLoader = new MaskLoader();
            $this -> _oLoader -> setMask( $this->_sMask );
            $this -> _oLoader -> setSearch( '/%field%/' );
        }

        $sField = sprintf(
          '<input type="radio" name="%s" id="%1$s_%d" value="%s" %s'. FH_XHTML_CLOSE .'><label for="%1$s_%2$d" class="noStyle">%s</label>',
          $this->_sName,
          $counter++,
          htmlspecialchars($sValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING),
          (isset($this->_mValue) && $sValue == $this->_mValue ? 'checked="checked" ':'').
          (isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
          (!empty($this->_sExtra) ? $this->_sExtra.' ':''),
          $sTitle
        );

        // do we have to use the mask ?
        if( $bUseMask )
        {
            $sField = $this -> _oLoader -> fill( $sField );
        }

        return $sField;
    }
}
?>