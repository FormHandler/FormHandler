<?php
/**
 * Test file for the mssql "layer"
 *
 * @package Yadal
 */

include('../class.Yadal.php');

// the test table (can be any table)
$table = "orders";

echo "<pre>";

// create a new connection
$db = newYadal("Northwind", "mssql");
$db -> connect( 'TEYELAPTOP', '', '' );

// start the test secuence
include( 'test.php' );

?>