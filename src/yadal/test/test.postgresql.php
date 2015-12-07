<?php
/**
 * Test file for the postgreSQL "layer"
 *
 * @package Yadal
 */

include('../class.Yadal.php');

// the test table (can be any table)
$table = "persoon";

echo "<pre>";

// create a new connection
$db = newYadal('assessment', 'postgresql');
$db -> connect( 'localhost', 'root', 'development' );

// start the test secuence
include( 'test.php' );

?>