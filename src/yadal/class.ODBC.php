<?php
/**
 * Yadal interface for ODBC
 *
 * @todo This file is not finished at all!!!!
 *
 * @package Yadal
 */

/**
 * NOTE!! THIS VERSION DOES NOT WORK
 * SEE test/test.odbc.php!!
 */

/**
 * class ODBC
 *
 * Yadal - Yet Another Database Abstraction Layer
 * ODBC
 *
 * @author Teye Heimans
 * @package Yadal
 */
class ODBC extends Yadal {
    var $_dsn;		// dsn

    /**
     * ODBC::ODBC()
     *
     * Constructor
     *
     * @return ODBC
     * @access public
     * @author Teye Heimans
     */
    function ODBC( $db ) {
        $this->Yadal( $db );
        $this->_nameQuote = "'";
    }

    /**
     * ODBC::connect()
     *
     * Make a connection with the database and
     * select the database.
     *
     * @param string dns: the dns to connect to
     * @param string username: the username which should be used to login
     * @param string password: the password which should be used to login
     * @return resource: The connection resource or false on failure
     * @access public
     * @author Teye Heimans
     */
    function connect( $dsn = '', $username = '', $password = '' ) {
    	// connect with odbc
    	$this->_conn = odbc_connect( $dsn, $username, $password );
    	$this->_dsn  = $dsn;

    	if( $this->_conn ) {
    	   $this->_isConnected = true;

    	   // return the connection resource
    	   return $this->_conn;
    	}

    	return false;
    }

    /**
     * ODBC::query()
     *
     * Execute the query
     *
     * @param string $query: the query
     * @return resource
     * @access public
     * @author Teye Heimans
     */
    function query( $query ) {
    	$this->_lastQuery = $query;

        // execute the query
        return odbc_exec( $this->_conn, $query );
    }

    /**
     * ODBC::getInsertId()
     *
     * Get the id of the last inserted record
     *
     * @return int
     * @access public
     * @author Teye Heimans
     */
    function getInsertId() {
        //
    }

    /**
     * ODBC::getError()
     *
     * Return the last error
     *
     * @return string
     * @access public
     * @author Teye Heimans
     */
    function getError() {
        return odbc_errormsg( $this->_conn );
    }

    /**
     * ODBC::recordCount()
     *
     * Return the number of records found by the query
     *
     * @param resource $sql: The sql resource we have to count
     * @return int
     * @access public
     * @author Teye Heimans
     */
    function recordCount( $sql) {
        return odbc_num_rows( $sql );
    }

    /**
     * ODBC::getRecord()
     *
     * Fetch a record in assoc mode and return it
     *
     * @param resource $sql: The sql resource where we have to get a row from
     * @return: assoc array or false when there are no records left
     * @access public
     * @author Teye Heimans
     */
    function getRecord( $sql ) {
        return mysql_fetch_assoc( $sql );
    }

    /**
     * ODBC::getFieldNames()
     *
     * Return the field names of the table
     *
     * @param string $table: the table where we should fetch the field names from
     * @return array
     * @access public
     * @author Teye Heimans
     */
    function getFieldNames( $table  ) {

    	$sql = odbc_columns( $this->_conn );

        $result = array();

        $num = odbc_num_fields( $sql );
		for ( $i = 1; $i <= $num; $i++ ) {
		  	$result[$i-1] = odbc_field_name($sql, $i);
		}

		$num = odbc_num_rows( $sql );
		echo "Aantal rows: $num<br />\n";
		for( $i = 0; $i <= $num; $i++ ) {
			echo odbc_result( $sql, 4) ."<br >\n";
		}

		return $result;
    }


    /**
     * ODBC::escapeString()
     *
     * Public: escape the string we are going to save from dangerous characters
     *
     * @param string $string
     * @return string
     */
    function escapeString( $string ) {
        return mysql_real_escape_string( $string );
    }

    /**
     * ODBC::fetchKeys()
     *
     * Public: fetch the keys from the table
     *
     * @return array of the keys which are found
     */
    function fetchKeys( $table = null) {
        $table = is_null($table) ? $this->_table : $table;

        $tmp = $this->_sql;

        //odbc_primarykeys( $this->_sql

        $sql = $this->query("SHOW KEYS FROM `".$table."`");
        $keys = array();
        while( $r = $this->getRecord() ) {
            if ( $r['Key_name'] == 'PRIMARY' ) {
                $keys['PR'][] = $r['Column_name'];
            } else {
            	$keys[$r['Key_name']][] = $r['Column_name'];
            }
        }

        mysql_free_result($sql);
        $this->_sql = $tmp;

        // if no keys are found...
        if(sizeof($keys) == 0) {
            trigger_error(
              "Error, could not fetch the indexes from table '".$table."'! ".
              "If you didn't define a primary key or another index type, ".
              "please set the name of the field (which should be used for indexing) ".
              "manually in the dbinfo() function!",
              E_USER_WARNING
            );
            return null;
        }

        if(isset($keys['PR'])) {
        	return $keys['PR'];
        } else {
        	$d = each( $keys );
        	return $d[1];
        }
    }

    /**
     * ODBC::fetchUniqueFields()
     *
     * Public: fetch the unique fields from the table
     *
     * @param string $table
     * @return array
     */
    function fetchUniqueFields( $table = null ) {
        $table = is_null($table) ? $this->_table : $table;

        $tmp = $this->_sql;

        $sql = $this->query("SHOW KEYS FROM `".$table."`");
        $unique = array();
        while( $r = $this->getRecord() ) {
            if ( $r['Non_unique'] == 0) {
                $unique[] = $r['Column_name'];
            }
        }

        mysql_free_result($sql);
        $this->_sql = $tmp;

        if(sizeof($unique) > 0) {
        	return $unique;
        } else {
        	return array();
        }
    }
}

?>