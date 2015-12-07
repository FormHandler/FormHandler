<?php
/**
 * Yadal interface for the PostgreSQL database type
 *
 * @package Yadal
 */


/**
 * class PostgreSQL
 *
 * Yadal - Yet Another Database Abstraction Layer
 * PostgreSQL class
 *
 * @author Teye Heimans
 * @package Yadal
 */
class PostgreSQL extends Yadal
{

    /**
     * PostgreSQL::PostgreSQL()
     *
     * Constructor
     *
     * @param string $db: The database to use to
     * @author Teye Heimans
     */
    function PostgreSQL( $db )
    {
        $this->Yadal( $db );
        $this->_nameQuote    = array('"', '"');
        $this->_quoteNumbers = true;
    }

    /**
     * PostgreSQL::connect()
     *
     * Public: Make a connection with the database and
     * select the database.
     *
     * @param string host: the host to connect to
     * @param string username: the username which should be used to login
     * @param string password: the password which should be used to login
     * @return resource: The connection resource or false on failure
     * @access public
     * @author Teye Heimans
     */
    function connect( $host = '', $username = '', $password = '' )
    {
        // build connection string based on internal settings.
        $connStr = '';
        if(!empty($host))       $connStr .= "host="     . $host. " ";
        if(!empty($this->_db))  $connStr .= "dbname="   . $this->_db . " ";
        if(!empty($username))   $connStr .= "user="     . $username . " ";
        if(!empty($password))   $connStr .= "password=" . $password . " ";
        $connStr = trim($connStr);

        // make a connection
        $connID = pg_connect($connStr);

        // connected?
        if ( $connID )
        {
            $this->_conn = $connID;
            $this->query("set datestyle='ISO'");
            $this->query("set client_encoding = latin1;");
            $this->_isConnected = true;

            // return the connection resource
            return $this->_conn;
        }
        else
        {
	        // connection failed...
	        return false;
        }
    }

    /**
     * PostgreSQL::close()
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
            return pg_close( $this->_conn );
        }
    }

   	/**
   	 * PostgreSQL::query()
   	 *
   	 * Execute a query
   	 *
   	 * @param string $query: The query which should be executed
   	 * @return resource
   	 * @access public
   	 * @author Teye Heimans
   	 */
    function query( $query )
    {
    	$this->_lastQuery = $query;

        return pg_query( $this->_conn, $query );
    }

    /**
     * PostgreSQL::getInsertId()
     *
     * Get the id of the last inserted record
     *
     * @param string $table: de tabel waarvan de laatste id van terug gegeven moet worden
     * @return int
     * @access public
   	 * @author Teye Heimans
     */
    function getInsertId( $table )
    {
        $k = $this->getPrKeys( $table );

        // select the last insert id for that table
        $sql = $this->query("
          SELECT last_value
          FROM ".$this->quote($table."_".$k[0]."_seq")
        );

        // query failed?
		if (!$sql)
		{
			trigger_error(
    		  "Could not retrieve the last inserted id for the table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);
    		return false;
		}

		// get the last inserted id
		if( $this->recordCount( $sql ) == 1 )
		{
			$row = @pg_fetch_row( $sql, 0 );
			pg_freeresult( $sql );
			return $row[0];
		}
		else
		{
			pg_freeresult( $sql );

			trigger_error(
    		  "Could not retrieve the last inserted id for the table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);

    		return false;
		}
    }

    /**
     * PostgreSQL::getError()
     *
     * Return the last error
     *
     * @param resource $sql: When you give a sql resource as parameter the last error of that result will be returned
     * @return string
     * @access public
   	 * @author Teye Heimans
     */
    function getError( $sql = null)
    {
        return !is_null($sql) ? pg_result_error($this->_sql) : pg_last_error();
    }

    /**
     * PostgreSQL::result()
     *
     * Return a specific result of a sql resource
     *
     * @param resource $result: The sql result where you want to get a result from
     * @param int $row: The row where you want a result from
     * @param string $field: The field which result you want
     * @return string
     * @access public
     * @author Teye Heimans
     */
    function result( $result, $row = 0, $field = null )
    {
    	return pg_result( $result, $row, $field );
    }

    /**
     * PostgreSQL::recordCount()
     *
     * Return the number of records found by the query
     *
     * @return int
     * @access public
   	 * @author Teye Heimans
     */
    function recordCount( $sql)
    {
        return pg_numrows( $sql );
    }

    /**
     * PostgreSQL::getRecord()
     *
     * Fetch a record in assoc mode and return it
     *
     * @param resource $sql: The sql resource where we should get a record from
     * @return assoc array or false when there are no records left
     * @access public
   	 * @author Teye Heimans
     */
    function getRecord( $sql )
    {
        return pg_fetch_assoc( $sql );
    }

    /**
     * PostgreSQL::getFieldNames()
     *
     * Return the field names of the table
     *
     * @param string $table: The table to get the field names from
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

        $sql = $this->query("
          SELECT
            a.attname AS name
          FROM
            pg_class c,
            pg_attribute
            a,pg_type t
          WHERE
            relkind = 'r' AND
            c.relname='".$table."' AND
            a.attnum > 0 AND
            a.atttypid = t.oid AND
            a.attrelid = c.oid
          ORDER BY a.attnum"
        );

        // query failed ?
		if( ! $sql )
		{
			trigger_error(
    		  "Could not fetch fieldnames of the table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);
    		return false;
		}

        while( $row = $this->getRecord($sql) )
        {
            $result[] = $row['name'];
        }

        // save the result in the cache
        $this->_cache['fields'][$t] = $result;

        return $result;
    }

    /**
     * PostgreSQL::getTables()
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

        // selecteer de tabellen
        $sql = $this->query("
          SELECT tablename
          FROM pg_tables
          WHERE
            tablename NOT LIKE 'pg_%' AND
            tablename NOT LIKE 'sql_%'
          ORDER BY 1"
        );

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
     * PostgreSQL::getNotNullFields()
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

    	$result = array();

        $sql = $this->query("
          SELECT
            a.attname AS name
          FROM
            pg_class c,
            pg_attribute
            a,pg_type t
          WHERE
            relkind = 'r' AND
            c.relname='".$table."' AND
            a.attnum > 0 AND
            a.atttypid = t.oid AND
            a.attrelid = c.oid AND
            a.attnotnull = true
          ORDER BY a.attnum"
        );

        // query failed ?
		if( ! $sql )
		{
			trigger_error(
    		  "Could not fetch the not-null-fields of the table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);
    		return false;
		}

        while( $row = $this->getRecord($sql) )
        {
            $result[] = $row['name'];
        }

        // save the result in the cache
    	$this->_cache['notnull'][$t] = $result;

        return $result;
    }

    /**
     * PostgreSQL::getFieldTypes()
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
        $sql = $this->query("
          SELECT
            d.adnum as num,
            d.adsrc as def
          FROM
            pg_attrdef d,
            pg_class c
          WHERE
            d.adrelid = c.oid AND
            c.relname = '".$table."'
          ORDER BY
            d.adnum"
        );

        // query failed ?
        if( !$sql )
        {
            trigger_error(
    		  "Could not fetch the default values for the columns for table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_NOTICE
    		);

    		return false;
        }

        // walk all the results
        $default = array();
        while( $row = $this -> getRecord( $sql ) )
        {
            // is the value a "default" value, or an SQL funtion used by postgre
            if( substr($row['def'], 0, 1) == "'" )
            {
                $s = substr( $row['def'], 1);
                $default[$row['num']] = substr($s, 0, strpos($s, "'"));
            }
        }

        // get the meta data
        $sql = $this->query("
          SELECT
            a.attname AS fld,
            t.typname AS type,
            a.atttypmod AS length,
            a.attnum AS num
          FROM
            pg_class c,
            pg_attribute a,
            pg_type t
          WHERE
            relkind = 'r' AND
            c.relname='".$table."' AND
            a.attnum > 0 AND
            a.atttypid = t.oid AND
            a.attrelid = c.oid
          ORDER BY
            a.attnum"
        );

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
            $result[ $row['fld'] ] = array(
              $row['type'],
              ($row['length'] > 0 ? ($row['length']-4):($row['length']== -1 ? null : $row['length'])),
              (array_key_exists( $row['num'], $default) ? $default[$row['num']] : null),
            );
        }

        // save the result in the cache
    	$this->_cache['fieldtypes'][$t] = $result;

		return $result;
    }

    /**
     * PostgreSQL::escapeString()
     *
     * Escape the string we are going to save from dangerous characters
     *
     * @param string $string: The string which should be escaped
     * @return string
     * @access public
   	 * @author Teye Heimans
     */
    function escapeString( $string )
    {
        return pg_escape_string( $string );
    }

    /**
     * PostgreSQL::getPrKeys()
     *
     * Fetch the keys from the table
     *
     * @param string $table: The table where we should retrieve the keys from
     * @return array
     * @access public
   	 * @author Teye Heimans
     */
    function getPrKeys( $table )
    {
        $t = strtolower($table);

        // return the data from the cache if it exists
        if( isset( $this->_cache['keys'][$t] ) )
        {
            return $this->_cache['keys'][$t];
        }

        $sql = $this->query("
          SELECT
            a.attname AS column_name
          FROM
            pg_class bc,
            pg_class ic,
            pg_index i,
            pg_attribute a
          WHERE
            bc.oid = i.indrelid AND
            i.indisprimary = true AND
            ic.oid = i.indexrelid AND
            (i.indkey[0] = a.attnum OR
             i.indkey[1] = a.attnum OR
             i.indkey[2] = a.attnum OR
             i.indkey[3] = a.attnum OR
             i.indkey[4] = a.attnum OR
             i.indkey[5] = a.attnum OR
             i.indkey[6] = a.attnum OR
             i.indkey[7] = a.attnum) AND
            a.attrelid = bc.oid AND
            bc.relname = '".$table."'"
        );

        // query failed ?
		if( ! $sql )
		{
			trigger_error(
    		  "Could not fetch the primary key's of the table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);
    		return false;
		}

        $result = array();
        while( $row = $this->getRecord($sql) )
        {
            $result[] = $row['column_name'];
        }

        // save the result in the cache
        $this->_cache['keys'][$t] = $result;

        return $result;
    }

    /**
     * PostgreSQL::getUniqueFields()
     *
     * Fetch the unique keys from the table
     *
     * @param string $table: The table where we should fetch the unique fields from
     * @return array
     * @access public
   	 * @author Teye Heimans
     */
    function getUniqueFields( $table)
    {
        $t = strtolower( $table );

        // return the data from the cache if it exists
        if( isset( $this->_cache['unique'][$t] ) )
        {
            return $this->_cache['unique'][$t];
        }

        $sql = $this->query("
          SELECT
            ic.relname AS index_name,
            a.attname AS column_name,
            i.indisunique AS unique_key,
            i.indisprimary AS primary_key
          FROM
            pg_class bc,
            pg_class ic,
            pg_index i,
            pg_attribute a
          WHERE
            i.indisunique = true AND
            bc.oid = i.indrelid AND
            ic.oid = i.indexrelid AND
            (i.indkey[0] = a.attnum OR
             i.indkey[1] = a.attnum OR
             i.indkey[2] = a.attnum OR
             i.indkey[3] = a.attnum OR
             i.indkey[4] = a.attnum OR
             i.indkey[5] = a.attnum OR
             i.indkey[6] = a.attnum OR
             i.indkey[7] = a.attnum) AND
            a.attrelid = bc.oid AND
            bc.relname = '".$table."'"
        );

        // query failed ?
		if( ! $sql )
		{
			trigger_error(
    		  "Could not fetch the unique fields for the table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);
    		return false;
		}

        $result = array();
        while( $row = $this->getRecord($sql) )
        {
            $result[] = $row['column_name'];
        }

        // save the result in the cache
        $this->_cache['unique'][$t] = $result;

        return $result;
    }
}

?>