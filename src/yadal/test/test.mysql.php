<?php
/**
 * Test file for the mysql "layer"
 *
 * @package Yadal
 */

include('../class.Yadal.php');

// the test table (can be any table)
$table = "criteria";

echo "<pre>";

// create a new connection
$db = newYadal("assessment", "mysql");
$db -> connect( 'localhost', 'root', '' );

// start the test secuence
include( 'test.php' );

?>
