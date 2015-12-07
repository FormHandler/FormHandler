<?php
/**
 * Yadal interface for the MySQL database type
 *
 * @package Yadal
 */


/**
 * class YadalMySQLi
 *
 * Yadal - Yet Another Database Abstraction Layer
 * MySQL class
 *
 * @author Teye Heimans
 * @package Yadal
 */
class YadalMySQLi extends Yadal
{
	/**
     * YadalMySQLi::YadalMySQLi()
     *
     * Constructor: set the database we should be using
     *
     * @param string $db: The database which should be used
     * @author Teye Heimans
     */
	function YadalMySQLi( $db )
	{
		$this->Yadal( $db );
		$this->_quoteNumbers = true;
		$this->_nameQuote = '`';
	}

	/**
     * YadalMySQLi::connect()
     *
     * Make a connection with the database and
     * select the database.
     *
     * @param string host: the host to connect to
     * @param string username: the username which should be used to login
     * @param string password: the password which should be used to login
     * @return resource: The connection resource
     * @access public
     * @author Teye Heimans
     */
	function connect( $host = 'localhost', $username = '', $password = '' )
	{
		// connect with the mysql database
		$this->_conn = mysqli_connect( $host, $username, $password );

		// connection made?
		if( $this->_conn )
		{
			// select the database
			if(mysqli_select_db( $this->_conn, $this->_db ))
			{
				$this->_isConnected = true;

				// return the connection resource
				return $this->_conn;
			}
		}

		return false;
	}


	/**
     * YadalMySQLi::close()
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
			return mysqli_close( $this->_conn );
		}

		return true;
	}

	/**
     * YadalMySQLi::query()
     *
     * Execute the query
     *
     * @param string $query: the query which should be executed
     * @return resource
     * @access public
     * @author Teye Heimans
     */
	function query( $query )
	{
		$this->_lastQuery = $query;

		return mysqli_query( $this->_conn, $query );
	}

	/**
     * YadalMySQLi::getInsertId()
     *
     * Get the id of the last inserted record
     *
     * @return int
     * @access public
     * @author Teye Heimans
     */
	function getInsertId()
	{
		return mysqli_insert_id( $this->_conn );
	}

	/**
     * YadalMySQLi::result()
     *
     * Return a specific result of a sql resource
     *
     * @param resource $sql: The sql where you want to get a result from
     * @param int $row: The row where you want a result from
     * @param string $field: The field which result you want
     * @return string
     * @access public
     * @author Teye Heimans
     */
	function result( $sql, $row = 0, $field = 0 )
	{
		return $this->mysqli_result( $sql, $row, $field );
	}

	/**
     * YadalMySQLi::getError()
     *
     * Return the last error
     *
     * @return string
     * @access public
     * @author Teye Heimans
     */
	function getError()
	{
		return mysqli_error( $this->_conn );
	}

	/**
     * YadalMySQLi::getErrorNo()
     *
     * Return the error number
     *
     * @return int
     * @access public
     * @author Teye Heimans
     */
	function getErrorNo()
	{
		return mysqli_errno( $this->_conn );
	}

	/**
     * YadalMySQLi::recordCount()
     *
     * Return the number of records found by the query
     *
     * @param resource $sql: The resource which should be counted
     * @return int
     * @access public
     * @author Teye Heimans
     */
	function recordCount( $sql )
	{
		return mysqli_num_rows( $sql );
	}

	/**
     * YadalMySQLi::getRecord()
     *
     * Fetch a record in assoc mode and return it
     *
     * @param resource $sql: The resource which should be used to retireve a record from
     * @return assoc array or false when there are no records left
     * @access public
     * @author Teye Heimans
     */
	function getRecord( $sql )
	{
		return mysqli_fetch_assoc( $sql );
	}

	/**
     * YadalMySQLi::getFieldNames()
     *
     * Return the field names of the table
     *
     * @param string $table: The table where the field names should be collected from
     * @return array
     * @access public
     * @author Teye Heimans
     */
	function getFieldNames( $table )
	{
		$t = strtolower($table);

		// return the data from the cache if it exists
		if( isset( $this->_cache['fields'][$t] ) )
		{
			return $this->_cache['fields'][$t];
		}

		$result = array();

		// check if we have a connection handler..
		// if so, fetch the column names
		if( $this->_conn && !empty($this->_db) )
		{
			$qry = "SHOW COLUMNS FROM ".$table;
			$res = $this->query( $qry );
			
			while( $rs = mysqli_fetch_array( $res ) )
			{
				$result[] = $rs['Field'];
			}			
		}
		// no connection handler available
		else
		{
			// try to get a record and fetch the field names..
			$sql = $this->query( 'DESCRIBE ' . $this->quote( $table ) );

			// query succeeded?
			if( $sql )
			{
				while( $row = mysqli_fetch_assoc( $sql ) )
				{
					$result[] = $row['Field'];
				}
			}
			else
			{
				trigger_error(
				"Could not retrieve the field names for the table '".$table."'.\n".
				"Query: ".$this->getLastQuery()."\n".
				"Error: ".$this->getError(),
				E_USER_WARNING
				);
				return false;
			}

			mysqli_free_result( $sql );
		}

		// save the result in the cache
		$this->_cache['fields'][$t] = $result;

		return $result;
	}

	/**
     * YadalMySQLi::getTables()
     *
     * Return the tables from the database
     *
     * @return array
     * @access public
     * @author Teye Heimans
     */
	function getTables()
	{		
		// return the data from the cache if it exists
		if( isset( $this->_cache['tables'] ) )
		{
			return $this->_cache['tables'];
		}
		$sql = $this->query('SHOW TABLES;');
	
		// query failed ?
		if( !$sql )
		{
			trigger_error(
			"Could not retrieve the tables from the database!\n".
			"Query: ".$this->getLastQuery()."\n".
			"Error: ".$this->getError(),
			E_USER_WARNING
			);
			return false;
		}

		// save the table names in an array and return them
		$result = array();
		
		$num = $this->recordCount( $sql );
		for( $i = 0; $i < $num; $i++ )
		{
			$result[] = $this->result( $sql, $i);			
		}
		// save the result in the cache
		$this->_cache['tables'] = $result;

		return $result;
	}

	/**
     * YadalMySQLi::getNotNullFields()
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
		$t = strtolower($table);

		// return the data from the cache if it exists
		if( isset( $this->_cache['notnull'][$t] ) )
		{
			return $this->_cache['notnull'][$t];
		}

		$sql = $this->query('DESCRIBE '.$this->quote( $table ) );

		if( $sql )
		{
			// save the not null fields in an array
			$result = array();
			while( $r = mysqli_fetch_assoc( $sql ) ) {
				if( $r['Null'] == 'NO' || empty($r['Null']) ) {
					$result[] = $r['Field'];
				}
			}
		}
		else
		{
			// display the error message when the not null fields could not be retrieved
			trigger_error(
			"Could not retrieve the not-null-field from the table '".$table."'.\n".
			"Query: ".$this->getLastQuery()."\n".
			"Error: ".$this->getError(),
			E_USER_WARNING
			);
			return false;
		}

		// save the result in the cache
		$this->_cache['notnull'][$t] = $result;

		return $result;
	}

	/**
     * YadalMySQLi::getFieldTypes()
     *
     * Retrieve the field types of the given table
     *
     * @param string $table: The table where we should fetch the fields and their types from
     * @return array
     * @access public
     * @author Teye Heimans
     */
	function getFieldTypes( $table )
	{
		$t = strtolower($table);

		// return the data from the cache if it exists
		if( isset( $this->_cache['fieldtypes'][$t] ) )
		{
			return $this->_cache['fieldtypes'][$t];
		}

		// Get the default values for the fields
		$sql = $this->query("DESCRIBE ".$this->quote($table));

		// query failed ?
		if( !$sql )
		{
			trigger_error(
			"Could not fetch the meta data of the columns for table '".$table."'.\n".
			"Query: ".$this->getLastQuery()."\n".
			"Error: ".$this->getError(),
			E_USER_WARNING
			);
			return false;
		}

		$result = array();
		while( $row = $this->getRecord( $sql ) )
		{
			// split the size from the type
			if( preg_match('/^(.*)\((\d+)\)$/', $row['Type'], $match) )
			{
				$type = $match[1];
				$length = $match[2];
			}
			else
			{
				$type   = $row['Type'];
				$length = null;
			}

			$result[ $row['Field'] ] = array(
			$type,
			$length,
			$row['Default']
			);
		}

		// save the result in the cache
		$this->_cache['fieldtypes'][$t] = $result;

		return $result;
	}

	/**
     * YadalMySQLi::escapeString()
     *
     * Escape the string we are going to save from dangerous characters
     *
     * @param string $string: The string to escape
     * @return string
     * @access public
     * @author Teye Heimans
     */
	function escapeString( $string )
	{
		return mysqli_real_escape_string( $this->_conn, $string );
	}

	/**
     * YadalMySQLi::getPrKeys()
     *
     * Fetch the keys from the table
     *
     * @param string $table: The table where we should fetch the keys from
     * @return array of the keys which are found
     * @access public
     * @author Teye Heimans
     */
	function getPrKeys( $table )
	{
		$t = strtolower($table);

		// return the data from the cache if it exists
		if( isset( $this->_cache['keys'][$t] ) ) {
			return $this->_cache['keys'][$t];
		}

		$sql = $this->query("SHOW KEYS FROM `".$table."`");

		$keys = array();
		while( $r = $this->getRecord($sql) ) {
			if ( $r['Key_name'] == 'PRIMARY' ) {
				$keys[] = $r['Column_name'];
			}
		}

		mysqli_free_result($sql);

		// save the result in the cache
		$this->_cache['keys'][$t] = $keys;

		return $keys;
	}

	/**
     * YadalMySQLi::getUniqueFields()
     *
     * Fetch the unique fields from the table
     *
     * @param string $table: The table where the unique-value-field should be collected from
     * @return array: multidimensional array of the unique indexes on the table
     * @access public
     * @author Teye Heimans
     */
	function getUniqueFields( $table )
	{
		$t = strtolower( $table );

		// return the data from the cache if it exists
		if( isset( $this->_cache['unique'][$t] ) )
		{
			return $this->_cache['unique'][$t];
		}

		// get the keys
		$sql = $this->query("SHOW KEYS FROM ". $this->quote($table) );

		$unique = array();

		// save all keys which have to be unique
		while( $r = $this->getRecord($sql) )
		{
			if ( $r['Non_unique'] == 0 )
			{
				$unique[$r['Key_name']][] = $r['Column_name'];
			}
		}

		mysqli_free_result($sql);

		// save the result in the cache
		$this->_cache['unique'][$t] = $unique;

		return $unique;
	}

	function mysqli_result( $res, $row, $field=0 )
	{
		$res->data_seek($row);
		$datarow = $res->fetch_array();

		//echo '<hr />'.$datarow[ $field ].'<br /><hr/>';
		return $datarow[$field];
	}
}
?>