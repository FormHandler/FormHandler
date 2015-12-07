<?php
/**
 *
 * Test file for the access "layer"
 *
 * THIS DOES NOT WORK
 *
 * We need to specify the database type which should be
 * used with odbc, like odbc_mssql, odbc_db2, etc.
 * The ways for fetching field names, getting the insert id, etc
 * is to different for each type.
 *
 * @package Yadal
 */


include('../class.Yadal.php');

// the test table (can be any table)
$table = "Customers";

echo "<pre>";

// create a new connection
$db = newYadal("Northwind", "odbc");
$db -> connect( 'ODBCTEST', '', '' );

print_var( $db -> getFieldNames( $table ) );
// start the test secuence
//include( 'test.php' );

?>