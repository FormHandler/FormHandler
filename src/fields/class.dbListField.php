<?php
/**
 * class dbListField
 *
 * Create a listfield from records retrieved from the db
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */

class dbListField extends ListField
{

    /**
     * dbListField::dbListField()
     *
     * Create a new dbListField object
     *
     * @param object &$oForm: the form where the datefield is located on
     * @param string $sName: the name of the datefield
     * @param object $oDb: object of the database handler
     * @param string $sTable: the table to get the fields from
     * @param mixed $mFields: array of string with the names of the fields which data we should get
     * @param string $sExtraSQL: extra SQL statements
     * @return dbListField
     * @access public
     * @author Teye Heimans
     */
    function dbListField( &$oForm, $sName, &$oDb, $sTable, $mFields, $sExtraSQL = null )
    {
		// make sure that the fields are set in an array
		$aFields = !is_array($mFields) ? array( $mFields ) : $mFields;

		// generate the query to retrieve the records
		$sQuery =
		  'SELECT '. implode(', ', $aFields).
		  ' FROM '. $oDb->quote( $sTable).' '.$sExtraSQL;

		// get the records and load the options
		$aOptions = array();

		// execute the query
		$sql = $oDb->query( $sQuery );

		// query succeeded?
		if( $sql )
		{
		    // fetch the results
    		while( $row = $oDb->getRecord( $sql ) )
    		{

    			if( sizeof( $row ) == 1 )
    			{
    				$aOptions[] = array_shift( $row );
    			}
    			else
    			{
    		        $aOptions[array_shift( $row )] = array_shift( $row );
    		    }
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

		// call the constructor of the listfield with the new options
		parent::ListField( $oForm, $sName, $aOptions );

		// if two fields are given, use the first field as value
		$this->useArrayKeyAsValue( sizeof( $aFields) == 2 );
    }
}

?>