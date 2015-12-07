<?php
/**
 * Yadal interface for Microsoft Access databse
 *
 * This class works only on windows and is sometimes unstable (it wont run).
 * If it works, its just fine
 *
 * @package Yadal
 */


/**
 * class Access
 *
 * Yadal - Yet Another Database Abstraction Layer
 * Microsoft Access database class.
 * This class works only on Windows!
 *
 * @author Teye Heimans
 * @package Yadal
 */
include_once('class.Yadal.php');
class Access extends Yadal
{
    var $_cursor;   // integer: what was the cursor position? (Used for recordCount)

    /**
     * Access::Access()
     *
     * Constructor
     *
     * @param string $db: The database to connect to
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function Access( $db )
    {
    	parent::Yadal( $db );
        $this->_nameQuote = array('[',']');
    }

    /**
     * Access::connect()
     *
     * Make a connection with the database and
     * select the database.
     *
     * @param string $connStr: Connection string data other then userid, password and datasource
     * @param string $username: the username which should be used to login
     * @param string $password: the password which should be used to login
     * @return resource: The connection resource or false on failure
     * @access public
     * @author Teye Heimans
     */
    function connect( $connStr = '', $username = '', $password = '' )
    {
    	// make connection with the database
        $this->_conn = new COM('ADODB.Connection');
        if( !$this->_conn ) {
            die(
              'Error, could not create ADODB connection with COM. <br />'.
              'This only works on windows systems!'
            );
        }

        $this->_conn->Provider = 'Microsoft.Jet.OLEDB.4.0';
        $this->_conn->LockType = 3;

        // connect to the database
        $connStr .=
        'Data Source='.$this->_db.';'.
        'User Id='.$username.';'.
        'Password='.$password.';';

        $this->_conn->Open( $connStr );

        // no error occoured and connection is open ?
        if( $this->_conn->Errors->Count == 0 && $this->_conn->State )
        {
            $this->_isConnected = true;

            // return the connection resource
            return $this->_conn;
        }

        return false;
    }

    /**
     * Access::close()
     *
     * Close the connection
     *
     * @return bool
     * @access public
     * @author Teye Heimans
     */
    function close()
    {
        if( $this->_isConnected )
        {
            $this->_isConnected = false;
            return $this->_conn->Close();
        }
    }

    /**
     * Access::query()
     *
     * Execute the query
     *
     * @param string $query: the query to execute
     * @return record set
     * @access public
     * @author Teye Heimans
     */
    function query( $query )
    {
    	// save the last query...
    	$this->_lastQuery = $query;

        $this->_cursor = 0;

        // execute the query
        $rs = $this->_conn->Execute( $query ) ;

        if( !$rs )
        {
            return false;
        }
        else
        {
	        // request numer of columns (otherwise delete wont work :-S )
	        if(!strtoupper(substr(trim($query), 0, 6)) == 'SELECT') {
	            $rs->Fields->Count;
	        }

	        return $rs;
        }
    }

    /**
     * Access::getInsertId()
     *
     * Get the id of the last inserted record. Because MS Access
     * can't fetch the last inserted id we just fetch the highest id
     *
     * @param string $table: the table to fetch the last key from
     * @return int
     * @access public
     * @author Teye Heimans
     */
    function getInsertId( $table )
    {
        $keys = $this->getPrKeys( $table );
        $k    = each( $keys );
        $rs   = $this->query(
          'SELECT MAX('. $this -> quote( $k[1] ).') AS id FROM '. $this -> quote( $table )
        );
        reset( $this->_keys );
        $result = (!$rs->EOF)  ? $rs->Fields[0]->Value : -1;
        $rs->Close();
        $rs->Release();
        return $result;
    }

    /**
     * Access::getError()
     *
     * Return the last eror
     *
     * @return string
     * @access public
     * @author Teye Heimans
     */
    function getError()
    {
    	// are there errors?
        $errc = $this->_conn->Errors;

        if ($errc->Count == 0)
		{
			return '';
		}
		// get the last error message
		$err = $errc->Item( $errc->Count-1 );

		// return the description
		return $err->Description;
    }

    /**
     * Access::recordCount()
     *
     * Public: return the number of records found by the query
     *
     * @param recordset $rs: The recordset where the records should be counted from
     * @return int
     * @access public
     * @author Teye Heimans
     */
    function recordCount( $rs )
    {
        // go to the first record
        if( !$rs->BOF )
        {
        	$rs->MoveFirst();
        }

        // count the records
        $result = 0;
        while(!$rs->EOF)
        {
            $result++;
            $rs->MoveNext();
        }

        // go back to the record we where before calling this function
        if( !$rs->BOF ) $rs->MoveFirst();
        for($i = 0; $i < $this->_cursor; $i++ )
        {
            $rs->MoveNext();
        }

        return $result;
    }

    /**
     * Access::getFieldTypes()
     *
     * Return the types of the fields retrieved from the given table
     *
     * @param string $table
     * @return array
     * @access public
     * @author Teye Heimans
     */
    function getFieldTypes( $table )
    {
        return array(); // TODO!!
    }

    /**
     * Access::getRecord()
     *
     * Public: fetch a record in assoc mode and return it
     *
     * @param recordset $rs: $the recordset where we should get a record from
     * @return array
     * @access public
     * @author Teye Heimans
     */
    function getRecord( $rs )
    {
        // are we at the end of the records ?
        if( $rs->EOF ) {
        	//$rs->Close();
            //$rs->Release();
            return false;
        }
        else
        {
        	// save the record data in an array
            $result = array();
            for( $i = 0; $i < $rs->Fields->Count; $i++ )
            {
                $type  = $rs->Fields[$i]->Type;
                $value = $rs->Fields[$i]->Value;

                switch( $type )
                {
                  case 1: // null value
    				$value = null;
    				break;
    			  case 6: // currency is not supported properly;
    				echo '<br /><b>'.$rs->Fields[$i]->Name.': currency type not supported by PHP</b><br />';
    				$value = (float) $value;
    				break;
    		      case 7: // adDate
    				$value = date('Y-m-d H:i',(integer)$rs->Fields[$i]->Value);
    				break;
                  case 133:// A date value (yyyymmdd)
    				$value = substr($value,0,4).'-'.substr($value,4,2).'-'.substr($value,6,2);
    				break;
    			}

                $result[ $rs->Fields[$i]->Name ] = trim( $value );
            }
            // move to the next record
            $rs->MoveNext();
            $this->_cursor++;

            // return the data
            return $result;
        }
    }

    /**
	 * Access::getFieldNames()
	 *
	 * retrieve the field names used in the table
	 *
	 * @param string $table: table to retrieve the field names from
	 * @return array of field names
	 * @access public
     * @author Teye Heimans
	 */
	function getFieldNames( $table )
	{
		$table = strtolower( $table );

		// return the data from the cache if it exists
        if( isset( $this->_cache['fields'][$table] ) )
        {
            return $this->_cache['fields'][$table];
        }

		// open schema 4: adSchemaColumns
	    $rs = $this->_conn->OpenSchema( 4 );

	    // get the fields..
		$tbl = $rs->Fields( 2 );
		$fld = $rs->Fields( 3 );
	    $idx = $rs->Fields( 6 );

	    // save the field names
		$result = array();
		while( !$rs->EOF )
		{
			if (strtolower($tbl->Value) == $table)
			{
				$result[$idx->Value-1] = $fld->Value;
			}
			$rs->MoveNext();
		}
		// close the schema
		$rs->Close();

		// sort the field names and return them
		ksort( $result );

		// save the result in the cache
        $this->_cache['fields'][$table] = $result;

		return $result;
	}


	/**
     * Access::getNotNullFields()
     *
     * Retrieve the fields that can not contain NULL
     *
     * @param string $table: The table which fields we should retrieve
     * @return array
     * @access public
     * @author Teye Heimans
     */
    function getNotNullFields ( $table )
    {
    	$table = strtolower($table);

    	// return the data from the cache if it exists
        if( isset( $this->_cache['notnull'][$table] ) )
        {
            return $this->_cache['notnull'][$table];
        }

        // open schema adSchemaColumns
        $rs = $this->_conn->OpenSchema( 4 );

        // the fields we are using
		$tbl  = $rs->Fields( 2 );
		$null = $rs->Fields( 10 );

		// save the primary key fields in an array
		$result = array();
		while(!$rs->EOF)
		{
			// primary field data of the table we want to have ?
			if (strtolower($tbl->Value) == $table && (bool)$null->Value == false )
			{
				// get the field and index of the field
				$fld  = $rs->Fields( 3 );
				$idx  = $rs->Fields( 6 );

				$result[$idx->Value-1] = $fld->Value;
			}
			// go to the next record
			$rs->MoveNext();
		}
		// close the recordset
		$rs->Close();

		// sort the result and return it
		ksort( $result );

		// save the result in the cache
        $this->_cache['notnull'][$table] = $result;

		return $result;
    }

	/**
     * Access::getPrKeys()
     *
     * Get the primary keys from the table
     *
     * @param  string $table: The table where we should fetch the primary keys from
     * @return array: primary keys
     * @access public
     * @author Teye Heimans
     */
    function getPrKeys( $table )
    {
        $table = strtolower( $table );

        // return the data from the cache if it exists
        if( isset( $this->_cache['keys'][$table] ) )
        {
            return $this->_cache['keys'][$table];
        }

    	// open schema adSchemaPrimaryKeys
        $rs = $this->_conn->OpenSchema( 28 );

        // the fields we are using
		$tbl  = $rs->Fields( 2 );
		$type = $rs->Fields( 7 );

		// save the primary key fields in an array
		$result = array();
		while(!$rs->EOF)
		{
			// primary field data of the table we want to have ?
			if (strtolower($tbl->Value) == $table && strtolower(substr($type->Value, 0, 10)) == 'primarykey')
			{
				// get the field and index of the field
				$fld  = $rs->Fields( 3 );
				$idx  = $rs->Fields( 6 );
				$result[$idx->Value-1] = $fld->Value;
			}
			// go to the next record
			$rs->MoveNext();
		}
		// close the recordset
		$rs->Close();

		// sort the result and return it
		ksort( $result );

		// save the result in the cache
        $this->_cache['keys'][$table] = $result;

		return $result;
    }

   /**
     * Access::dbDate()
     *
     * Convert the given date to the correct database format.
     *
     * @param string $y: The year of the date which should be converteds
     * @param string $m: The month of the date which should be converteds
     * @param string $d: The day of the date which should be converteds
     * @return string the date in the correct format or null when the date could not be converted
     * @access public
     * @author Teye Heimans
     */
    function dbDate( $y, $m, $d )
    {
    	return " # $d-$m-$y # ";
    }

    /**
     * Access::escapeString()
     *
     * Escape the string we are going to save from dangerous characters
     *
     * @param string $string
     * @return string
     * @access public
     * @author Teye Heimans
     */
    function escapeString( $string )
    {
        return str_replace("'", "''", $string);
    }

    /**
     * Access::getUniqueFields()
     *
     * fetch the unique fields from the table
     *
     * @param string $table: The table to fetch the unique fields from
     * @return array
     * @access public
     * @author Teye Heimans
     */
    function getUniqueFields( $table )
    {
        // Access does not know unique fields... but primary key fields are also unique...
        return array('Primary Key' => $this->getPrKeys( $table ));
    }


    /**
     * Access::displaySchemas()
     *
     * Help function to display the schema's
     *
     * @param int $schema: the schema to display. leave blank to display all schemas
     * @return void
     * @access private
     * @author Teye Heimans
     */
    /*
    function displaySchemas( $schema = null) {

	    for( $x = 1; $x <= 38; $x++ )
	    {
	    	if (is_null($schema) || $schema == $x )
	    	{
	    		print_Var( $schema, $x );
		        echo "SCHEMA $x\n";
	    	    echo "<table border='1' style='border: 1px solid black'>\n";

    	    	for( $i = 0; $i <= 50; $i++ ) {
					try {
	    	        	$rs = $this->_conn->OpenSchema( $x );

	    	        	echo
						"  <tr>\n".
						"    <td>".$i ."</td>\n";

						if($rs) {
						    $record = @$rs->Fields ( $i );
						    if($record) {
							    while( !$rs->EOF ) {
							        echo "    <td>".($record->Value==''?'&nbsp;':$record->Value)."</td>\n";
							        flush();
							        $rs->MoveNext();
							    }
						    } else {
						        break;
						    }
						    $rs->Close();
						    $rs->Release();
						} else {
						    echo "<td>Error.. $x failure</td>\n";
						    break;
						}
						echo "  </tr>";
					} catch ( Exception  $e) {
						echo 'Caught exception: ',  $e;
						break;
					}
	    	    }
	    	    echo "</table> <br />";
    	    }

	    }

	    return;
	}
	*/


}

/*
	adSchemaCatalogs	= 1,
	adSchemaCharacterSets	= 2,
	adSchemaCollations	= 3,
	adSchemaColumns	= 4,
	adSchemaCheckConstraints	= 5,
	adSchemaConstraintColumnUsage	= 6,
	adSchemaConstraintTableUsage	= 7,
	adSchemaKeyColumnUsage	= 8,
	adSchemaReferentialContraints	= 9,
	adSchemaTableConstraints	= 10,
	adSchemaColumnsDomainUsage	= 11,
	adSchemaIndexes	= 12,
	adSchemaColumnPrivileges	= 13,
	adSchemaTablePrivileges	= 14,
	adSchemaUsagePrivileges	= 15,
	adSchemaProcedures	= 16,
	adSchemaSchemata	= 17,
	adSchemaSQLLanguages	= 18,
	adSchemaStatistics	= 19,
	adSchemaTables	= 20,
	adSchemaTranslations	= 21,
	adSchemaProviderTypes	= 22,
	adSchemaViews	= 23,
	adSchemaViewColumnUsage	= 24,
	adSchemaViewTableUsage	= 25,
	adSchemaProcedureParameters	= 26,
	adSchemaForeignKeys	= 27,
	adSchemaPrimaryKeys	= 28,
	adSchemaProcedureColumns	= 29,
	adSchemaDBInfoKeywords	= 30,
	adSchemaDBInfoLiterals	= 31,
	adSchemaCubes	= 32,
	adSchemaDimensions	= 33,
	adSchemaHierarchies	= 34,
	adSchemaLevels	= 35,
	adSchemaMeasures	= 36,
	adSchemaProperties	= 37,
	adSchemaMembers	= 38

*/


?>