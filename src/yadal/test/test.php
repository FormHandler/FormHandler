<?php
/**
 * General test file which is used for every db layer to test it
 *
 * @package Yadal
 */

//
if( is_object( $db ) && method_exists( $db, 'getTables') )
{
    echo "Tables:\n";
    print_var( $db -> getTables() );
}

echo "Fetch fieldnames:\n";
print_r( $db -> getFieldNames( $table ) );

echo "Field types in table ".$table.":\n";
print_var( $db-> getFieldTypes( $table ) );

echo "\nGet primary key(s):\n";
print_r( $db -> getPrKeys( $table ) );

echo "\nGet not null fields:\n";
print_r( $db -> getNotNullFields( $table ) );

echo "\nGet fields which should be unique:\n";
print_r( $db -> getUniqueFields( $table ) );

echo "\nRun a query:\n";
$sql = $db -> query ( "SELECT * FROM ".$db->quote($table) );
print_r ( $sql );

echo "\n\nRecords count:\n";
echo $db -> recordCount($sql);

echo "\n\nGet record:\n<table border='1' cellspacing='0' cellpadding='2'>\n";
$first = true;
while( $row = $db -> getRecord($sql) ) {
	echo "<tr>\n";
	foreach( $row as $field => $value ) {
		if( $first ) {
			echo "<td valign='top' nowrap>$field<hr size='3' color='black'>$value</td>\n";
		} else {
			echo "<td nowrap>$value</td>\n";
		}
	}
	if( $first ) $first = false;
	echo "</tr>\n";
	flush();
}
echo "</table>\n";


echo "\n\nClose the connection:\n";
print_r( $db -> close() );
?>