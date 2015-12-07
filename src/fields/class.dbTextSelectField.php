<?php
/**
 * class dbTextSelectField
 *
 * Create a TextSelect field from records retrieved from the db
 *
 * @author Johan Wiegel
 * @package FormHandler
 * @subpackage Fields
 * @since 22-10-2008
 */

class dbTextSelectField extends TextSelectField
{
    var $_oDb;
	var $_aOptions;		

    /**
     * dbTextSelectField::dbTextSelectField()
     *
     * Public constructor: create a new dbTextSelectField object
     *
     * @param object &$oForm: the form where the TextSelectfield is located on
     * @param string $sName: the name of the datefield
     * @param object $oDb: object of the database handler
     * @param string $sTable: the table to get the fields from
     * @param mixed $sField: array of string with the name of the field which data we should get
     * @param string $sExtraSQL: extra SQL statements
     * @return dbTextSelectField
     * @access public
     * @since 22-10-2008
     * @author Johan Wiegel
     */
	function dbTextSelectField( &$oForm, $sName, &$oDb, $sTable, $sField, $sExtraSQL = null, $sMask = null )
	{
		// generate the query to retrieve the records
		$sQuery =
		  'SELECT '.$sField.
		  ' FROM '. $oDb->quote( $sTable).' '.$sExtraSQL;

		$this->_aOptions = array();

		
		// execute the query
		$sql = $oDb->query( $sQuery );

		// query succeeded
		if( $sql )
		{
    		while( $row = $oDb->getRecord( $sql ) )
    		{
    			$this->_aOptions[] = $row[$sField];
    		}
		}
		// query failed
		else
		{
		    trigger_error(
		      "Error, could not retrieve records.<br '. FH_XHTML_CLOSE .'>\n".
		      "Error message: ". $oDb->getError()."<br '. FH_XHTML_CLOSE .'>\n".
		      "Query: ". $sQuery,
		      E_USER_WARNING
		    );
		}
			    // call the constructor of the selectfield
		parent::TextSelectField( $oForm, $sName, $this->_aOptions );

 	}
}
?>