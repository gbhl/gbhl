<?php
/*
 * Build the index for deduplication
 */

require( "variables.php" );

// Connect to database
$link = mysql_connect( $DBINFO['HOST'], $DBINFO['USER'], $DBINFO['PASS'] );
mysql_select_db( $DBINFO['NAME'], $link );

$result = mysql_query( "SELECT `author`, `title`, `260` as `publisher` " );