<?php

/**
 * class TextArea
 *
 * Create a textarea
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */
class TextArea extends Field {

    var $_iCols;        // int: number of colums which the textarea should get
    var $_iRows;        // int: number of rows which the textarea should get
    var $_iMaxLength;   // int: the number of characters allowed
    var $_bShowMessage; // boolean: should we display the limit message

    /**
     * TextArea::TextArea()
     *
     * Constructor: create a new textarea
     *
     * @param object &$oForm: The form where this field is located on
     * @param string $sName: The name of the field
     * @return TextArea
     * @author Teye Heimans
     * @access public
     */
    function TextArea( &$oform, $sName )
    {
        // call the constructor of the Field class
        parent::Field( $oform, $sName );

        $this->setCols( 40 );
        $this->setRows( 7 );
    }

    /**
     * TextArea::setCols()
     *
     * Set the number of cols of the textarea
     *
     * @param integer $iCols: the number of cols
     * @return void
     * @author Teye Heimans
     * @access public
     */
    function setCols( $iCols )
    {
        $this->_iCols = $iCols;
    }

    /**
     * TextArea::setMaxLength()
     *
     * Set the max length of the input. Use false or 0 to disable the limit
     *
     * @param int $iMaxLength
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setMaxLength( $iMaxLength, $bDisplay )
    {
        $this -> _iMaxLength   = $iMaxLength;
        $this -> _bShowMessage = $bDisplay;
    }

    /**
     * TextArea::isValid()
     *
     * Check if the field's value is valid
     *
     * @return boolean
     * @access public
     * @author Teye Heimans
     */
    function isValid()
    {
        // is a max length set ?
        if( isset( $this -> _iMaxLength ) && $this -> _iMaxLength > 0 )
        {
            // is there to many data submitted ?
            $iLen = strlen( $this -> _mValue );
            if( $iLen > $this -> _iMaxLength )
            {
                // set the error message
                $this -> _sError = sprintf(
                  $this -> _oForm -> _text( 40 ),
                  $this -> _iMaxLength,
                  $iLen,
                  abs($iLen - $this->_iMaxLength)
                );

                // return false because the value is not valid
                return false;
            }
        }

        // everything ok untill here, use the default validator
        return parent::isValid();
    }

    /**
     * TextArea::setRows()
     *
     * Set the number of rows of the textarea
     *
     * @param integer $iRows: the number of rows
     * @return void
     * @author Teye Heimans
     * @access public
     */
    function setRows( $iRows )
    {
        $this->_iRows = $iRows;
    }

    /**
     * TextArea::getField()
     *
     * Return the HTML of the field
     *
     * @return string: the html of the field
     * @author Teye Heimans
     * @access public
     */
    function getField()
    {
        // view mode enabled ?
        if( $this -> getViewMode() )
        {
            // get the view value..
            return $this -> _getViewValue();
        }

        // is a limit set ?
        if( isset( $this -> _iMaxLength ) && $this -> _iMaxLength > 0  )
        {
            // the message
            $sMessage = $this-> _oForm -> _text( 36 );

            // set the event
            $this -> _sExtra .=
              sprintf(
                " onkeyup=\"displayLimit('%s', '%s', %d, %s, '%s');\"",
                $this -> _oForm -> getFormName(),
                $this -> _sName,
                $this -> _iMaxLength,
                ( $this -> _bShowMessage ? 'true' : 'false'),
                htmlspecialchars( $sMessage, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING )
              )
            ;

            // should the message be displayed ?
            if( $this -> _bShowMessage )
            {
                // add the javascript to the fields "extra" argument
                $this -> setExtraAfter(
                  "<br ". FH_XHTML_CLOSE ."><div id='". $this -> _sName."_limit'></div>\n"
                );
            }

            // make sure that when the page is loaded, the message is displayed
            $this -> _oForm -> _setJS(
              sprintf(
                "displayLimit('%s', '%s', %d, %s, '%s');\n",
                $this -> _oForm -> getFormName(),
                $this -> _sName,
                $this -> _iMaxLength,
                ( $this -> _bShowMessage ? 'true' : 'false'),
                $sMessage
              ),
              false,
              false
            );
        }

        // return the field
        return sprintf(
          '<textarea name="%s" id="%1$s" cols="%d" rows="%d"%s>%s</textarea>%s',
          $this->_sName,
          $this->_iCols,
          $this->_iRows,
          (isset($this->_iTabIndex) ? ' tabindex="'.$this->_iTabIndex.'" ' : '').
          (isset($this->_sExtra) ? ' '.$this->_sExtra :''),
          (isset($this->_mValue) ? htmlspecialchars($this->_mValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING) : ''),
          (isset($this->_sExtraAfter) ? $this->_sExtraAfter :'')
        );
    }
}

?>