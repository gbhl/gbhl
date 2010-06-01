<?php
/*
 * Build the bibs index used during import for deduplication
 */

require( "variables.php" );

// Connect to database
$link = mysql_connect( $DBINFO['HOST'], $DBINFO['USER'], $DBINFO['PASS'] );
mysql_select_db( $DBINFO['NAME'], $link );

$result = mysql_query( "SELECT `id`, `author`, `title`, `260` as `publisher` FROM `bibs`" );

while( $row = mysql_fetch_assoc($result) ) {
    $bib_id = $row['id'];
    $author = $row['author'];
    $title = $row['title'];
    $publisher = $row['publisher'];

    $author = preg_replace( '/\W/i', "", $author );
    $title = preg_replace( '/\W/i', "", $title );
    $publisher = preg_replace( '/\W/i', "", $publisher );

    mysql_query( "INSERT INTO `bibsIndex` ( `bib_id`, `title`, `author`, `publisher` ) values ( '$bib_id', '$title', '$author', '$publisher' )" );
}

$result = mysql_query( "SELECT `bib_id`, `oclc` FROM `holdings`" );

while( $row = mysql_fetch_assoc($result) ) {
    $bib_id = $row['bib_id'];
    $oclc = $row['oclc'];

    $oclc = preg_replace( '/\D/i', "", $oclc );

    mysql_query( "INSERT INTO `holdingsIndex` ( `bib_id`, `oclc` ) values ( '$bib_id', '$oclc' )" );
}
