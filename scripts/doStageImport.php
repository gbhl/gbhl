<?php
/**
 * Import pre-loaded datasets from the import area to the productive bidlist
 * De-Duplication is applied during the import
 *
 * @author Wolfgang Koller <wolfgang.koller@nhm-wien.ac.at>
 */

require( "variables.php" );

// Connect to database
$link = mysql_connect( $DBINFO['HOST'], $DBINFO['USER'], $DBINFO['PASS'] );
mysql_select_db( $DBINFO['NAME'], $link );

// In the first run, make an inner deduplication
/*$result = mysql_query( "SELECT `id`, `bib_id`, `oclc` FROM import_holdings" );
while( $row = mysql_fetch_assoc( $result ) ) {
    $holdings_id = $row['id'];
    $bib_id = $row['bib_id'];
    $oclc = $row['oclc'];

    // Find any matches
    if( !empty($oclc) ) {
        // Try to make an OCLC match
        $match_result = mysql_query( "SELECT `bib_id` FROM import_holdings WHERE `oclc` LIKE '%" . $oclc . "%' AND `bib_id` != '$bib_id' LIMIT 0,1", $link );
        while( $match_row = mysql_fetch_assoc( $match_result ) ) {
            echo "Found Inner Match for '$bib_id' (OCLC: $oclc): " . $match_row['bib_id'] . "\n";

            updateMatch( $bib_id, $match_row['bib_id'] );
        }
    }
}*/

// First of all, fetch all entries from the holdings table to find matches using the OCLC number
//$result = mysql_query( 'SELECT `id`, `bib_id`, `oclc` FROM import_holdings WHERE `oclc` IS NOT NULL AND `oclc` != ""', $link );
//$result = mysql_query( 'SELECT t1.`id`, t1.`author`, t1.`title`, t1.`260` AS `publisher`, t2.`oclc` FROM import_bibs AS t1 LEFT JOIN import_holdings AS t2 ON t1.`id` = t2.`bib_id`', $link );
$result = mysql_query( 'SELECT `bib_id`, `oclc` FROM `import_holdingsIndex`', $link );

while( $row = mysql_fetch_assoc( $result ) ) {
    $bib_id = $row['bib_id'];
    //$author = $row['author'];
    //$title = $row['title'];
    //$publisher = $row['publisher'];
    $oclc = trim($row['oclc']);

     // Try to make an OCLC match
    if( $oclc > 0 ) {
        $match_result = mysql_query( "SELECT `bib_id` FROM `holdingsIndex` WHERE `oclc` = '$oclc' LIMIT 0,1", $link );
        while( $match_row = mysql_fetch_assoc( $match_result ) ) {
            echo "Found Match for '$bib_id' (OCLC: $oclc): " . $match_row['bib_id'] . "\n";

            moveMatch( $bib_id, $match_row['bib_id'] );
        }
    }
}

$result = mysql_query( 'SELECT `bib_id`, `title`, `author`, `publisher` FROM `import_bibsIndex`', $link );

while( $row = mysql_fetch_assoc( $result ) ) {
    $bib_id = $row['bib_id'];
    $author = $row['author'];
    $title = $row['title'];
    $publisher = $row['publisher'];

    $match_result = mysql_query( "SELECT `bib_id` FROM `bibsIndex` WHERE `title` LIKE '%$title%' AND `author` LIKE '%$author%' AND `publisher` LIKE '%$publisher%' LIMIT 0,1", $link );
    while( $match_row = mysql_fetch_assoc( $match_result ) ) {
        echo "Found Match for '$bib_id' (TAP): " . $match_row['bib_id'] . "\n";

        moveMatch( $bib_id, $match_row['bib_id'] );
    }

    /*// Next try: title + author + publisher
    if( !$bFoundMatch ) {
        $match_result = mysql_query( "SELECT `bib_id` FROM `bibsIndex` WHERE `title` LIKE '%$title%' AND `author` LIKE '%$author%' AND `publisher` LIKE '%$publisher%' LIMIT 0,1", $link );
        while( $match_row = mysql_fetch_assoc( $match_result ) ) {
            $bFoundMatch = true;
            echo "Found Match for '$bib_id' TAP match): " . $match_row['bib_id'] . "\n";

            moveMatch( $bib_id, $match_row['bib_id'] );
        }
    }*/

    /*if( !$bFoundMatch && !empty($title) ) {
        // If not found, try to make an author / title / place match
        //echo "SELECT `id` FROM bibs WHERE `author` LIKE '%" . mysql_escape_string($author) . "%' AND `title` LIKE '%" . mysql_escape_string($title) . "%' AND `260` LIKE '%" . mysql_escape_string($publisher) . "%'";
        //$match_result = mysql_query( "SELECT `id` FROM bibs WHERE `author` SOUNDS LIKE '" . mysql_escape_string($author) . "' AND `title` SOUNDS LIKE '" . mysql_escape_string($title) . "' AND `260` SOUNDS LIKE '" . mysql_escape_string($publisher) . "'" );
        $match_result = mysql_query( "SELECT `id`, `author`, `title`, `260` AS `publisher` FROM bibs WHERE ( `title` SOUNDS LIKE '" . mysql_escape_string($title) . "' AND `author` SOUNDS LIKE '" . mysql_escape_string($author) . "' ) OR ( `260` SOUNDS LIKE '" . mysql_escape_string($publisher) . "' AND `title` SOUNDS LIKE '" . mysql_escape_string($title) . "' )" );
        while( $match_row = mysql_fetch_assoc( $match_result ) ) {
            $bFoundMatch = true;
            echo "Found Match for '$bib_id' (ATP): " . $match_row['id'] . ": '$title' / '$author' / '$publisher' MATCH '" . $match_row['title'] . "' / '" . $match_row['author'] . "' / '" . $match_row['publisher'] . "'\n";
        }
    }*/

    // If no match was found, add entry as new one
    //if( !$bFoundMatch ) moveEntry( $bib_id );
}

mysql_close( $link );

function updateMatch( $old_bib_id, $new_bib_id ) {
    global $link;

    mysql_query( "UPDATE import_bibs SET `depreciated` = '1', `new_id` = '$new_bib_id' WHERE `id` = '$old_bib_id'", $link );
    mysql_query( 'UPDATE import_holdings SET `bib_id` = "' . $new_bib_id . '" WHERE `bib_id` = "' . $old_bib_id . '"', $link );
}

function moveMatch( $old_bib_id, $new_bib_id ) {
    global $link;
    
    // Add original entry as deprecated one
    mysql_query( "
        INSERT INTO bibs
        (
        `001`,
        `002`,
        `008`,
        `022`,
        `author`,
        `abbrev_title`,
        `title`,
        `pub`,
        `match_basis`,
        `depreciated`,
        `subjects`,
        `places`,
        `new_id`,
        `new_title`,
        `found_match`,
        `checked`,
        `reckey_inst`,
        `ldr`,
        `001_b`,
        `002_b`,
        `008_b`,
        `035`,
        `210`,
        `245`,
        `260`,
        `site_b`,
        `flag_b`,
        `newtitle_b`,
        `t245stripped`
        )
        (
        SELECT
        `001`,
        `002`,
        `008`,
        `022`,
        `author`,
        `abbrev_title`,
        `title`,
        `pub`,
        `match_basis`,
        '1',
        `subjects`,
        `places`,
        '$new_bib_id',
        `new_title`,
        `found_match`,
        `checked`,
        '0',
        `ldr`,
        `001_b`,
        `002_b`,
        `008_b`,
        `035`,
        `210`,
        `245`,
        `260`,
        `site_b`,
        `flag_b`,
        `newtitle_b`,
        `t245stripped`
        FROM import_bibs
        WHERE `id` = '$old_bib_id'
        OR `new_id` = '$old_bib_id'
        )
        "
    , $link );

    // Delete bib entry from import table
    mysql_query( "DELETE FROM import_bibs WHERE `id` = '$old_bib_id' OR `new_id` = '$old_bib_id'", $link );

    // Update import holdings to refer to new bib entry
    mysql_query( 'UPDATE import_holdings SET `bib_id` = "' . $new_bib_id . '" WHERE `bib_id` = "' . $old_bib_id . '"', $link );

    // Transfer entries into productive holdings table
    mysql_query( 'INSERT INTO holdings ( `bib_id`, `sourceid`, `035`, `hol_1`, `hol_2`, `hol_3`, `hol_4`, `subject`, `e_856`, `place`, `match_basis`, `oclc`, `user_id`, `orig_bib_id` ) ( SELECT `bib_id`, `sourceid`, `035`, `hol_1`, `hol_2`, `hol_3`, `hol_4`, `subject`, `e_856`, `place`, \'oclc\', `oclc`, `user_id`, `orig_bib_id` FROM import_holdings WHERE `bib_id` = "' . $new_bib_id . '" )', $link );
    mysql_query( 'DELETE FROM import_holdings WHERE `bib_id` = "' . $new_bib_id . '"', $link );
}

function moveEntry( $import_bib_id ) {
    global $link;

    // Move import entry to production table
    mysql_query( "
        INSERT INTO bibs
        (
        `001`,
        `002`,
        `008`,
        `022`,
        `author`,
        `abbrev_title`,
        `title`,
        `pub`,
        `match_basis`,
        `depreciated`,
        `subjects`,
        `places`,
        `new_id`,
        `new_title`,
        `found_match`,
        `checked`,
        `reckey_inst`,
        `ldr`,
        `001_b`,
        `002_b`,
        `008_b`,
        `035`,
        `210`,
        `245`,
        `260`,
        `site_b`,
        `flag_b`,
        `newtitle_b`,
        `t245stripped`
        )
        (
        SELECT
        `001`,
        `002`,
        `008`,
        `022`,
        `author`,
        `abbrev_title`,
        `title`,
        `pub`,
        `match_basis`,
        `depreciated`,
        `subjects`,
        `places`,
        `new_id`,
        `new_title`,
        `found_match`,
        `checked`,
        '0',
        `ldr`,
        `001_b`,
        `002_b`,
        `008_b`,
        `035`,
        `210`,
        `245`,
        `260`,
        `site_b`,
        `flag_b`,
        `newtitle_b`,
        `t245stripped`
        FROM import_bibs
        WHERE `id` = '$import_bib_id'
        OR `new_id` = '$import_bib_id'
        )
        "
    , $link );

    $new_bib_id = mysql_insert_id( $link );

    // Delete bib entry from import table
    mysql_query( "DELETE FROM import_bibs WHERE `id` = '$import_bib_id' OR `new_id` = '$import_bib_id'", $link );

    // Update import holdings to refer to new bib entry
    mysql_query( 'UPDATE import_holdings SET `bib_id` = "' . $new_bib_id . '" WHERE `bib_id` = "' . $import_bib_id . '"', $link );

    // Transfer entries into productive holdings table
    mysql_query( 'INSERT INTO holdings ( `bib_id`, `sourceid`, `035`, `hol_1`, `hol_2`, `hol_3`, `hol_4`, `subject`, `e_856`, `place`, `match_basis`, `oclc`, `user_id`, `orig_bib_id` ) ( SELECT `bib_id`, `sourceid`, `035`, `hol_1`, `hol_2`, `hol_3`, `hol_4`, `subject`, `e_856`, `place`, \'oclc\', `oclc`, `user_id`, `orig_bib_id` FROM import_holdings WHERE `bib_id` = "' . $new_bib_id . '" )', $link );
    mysql_query( 'DELETE FROM import_holdings WHERE `bib_id` = "' . $new_bib_id . '"', $link );
}
