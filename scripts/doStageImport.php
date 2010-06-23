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

// Before we start, remove any entries which have been previously inserted (using place and source_id)
$result = mysql_query( 'SELECT `bib_id`, `sourceid`, `place` FROM `import_holdings`', $link );
while( $row = mysql_fetch_assoc( $result ) ) {
    $bib_id = $row['bib_id'];
    $sourceid = $row['sourceid'];
    $place = $row['place'];

    // Now check if this entry is already in the database
    //$match_result = mysql_query( "SELECT `bib_id` FROM `holdings` WHERE ( `sourceid` = '$sourceid' AND `place` = '$place' )", $link );
    $match_result = mysql_query( "SELECT `source_id` FROM `import_seenIDs` WHERE `source` = '$place' AND `source_id` = '$sourceid'", $link );
    if( mysql_num_rows($match_result) > 0 ) {
        echo "Removing previously imported entry: sourceid '$sourceid', place '$place'\n";

        // Remove entry from import tables
        removeImportEntry( $bib_id );
    }
}

// Fetch all entries and dedup & insert them
$main_result = mysql_query( 'SELECT `bib_id` FROM `import_bibsIndex`', $link );
while( $main_row = mysql_fetch_assoc( $main_result ) ) {
    $bib_id = $main_row['bib_id'];

    // Insert place & source into seen table
    mysql_query( "INSERT INTO `import_seenIDs` ( `source`, `sourceid` ) ( SELECT `sourceid`, `place` FROM `import_holdings` WHERE `bib_id` = '$bib_id' )" );

    // First of all, fetch all entries from the holdings table to find matches using the OCLC number
    $result = mysql_query( "SELECT `oclc` FROM `import_holdingsIndex` WHERE `bib_id` = '$bib_id'", $link );
    while( $row = mysql_fetch_assoc( $result ) ) {
        $oclc = trim($row['oclc']);

         // Try to make an OCLC match
        if( $oclc > 0 ) {
            $match_result = mysql_query( "SELECT `bib_id` FROM `holdingsIndex` WHERE `oclc` = '$oclc' LIMIT 0,1", $link );
            while( $match_row = mysql_fetch_assoc( $match_result ) ) {
                echo "Found Match for '$bib_id' (OCLC: $oclc): " . $match_row['bib_id'] . "\n";

                moveMatch( $bib_id, $match_row['bib_id'], 'OCLC' );
            }
        }
    }

    // Make an title author publisher match (we don't have to care about the OCLC match because the index will be updated when there is a match, thus the query would return zero records)
    $result = mysql_query( "SELECT `title`, `author`, `publisher` FROM `import_bibsIndex` WHERE `bib_id` = '$bib_id'", $link );
    while( $row = mysql_fetch_assoc( $result ) ) {
        //$bib_id = $row['bib_id'];
        $author = $row['author'];
        $title = $row['title'];
        $publisher = $row['publisher'];

        $match_result = mysql_query( "SELECT `bib_id` FROM `bibsIndex` WHERE `title` LIKE '%$title%' AND `author` LIKE '%$author%' AND `publisher` LIKE '%$publisher%' LIMIT 0,1", $link );
        while( $match_row = mysql_fetch_assoc( $match_result ) ) {
            echo "Found Match for '$bib_id' (TAP): " . $match_row['bib_id'] . "\n";

            moveMatch( $bib_id, $match_row['bib_id'], 'ATP' );
        }
    }

    // If no match was found, move the entry to the production table
    $result = mysql_query( "SELECT `bib_id` FROM `import_bibsIndex` WHERE `bib_id` = '$bib_id'", $link );
    while( $row = mysql_fetch_assoc( $result ) ) {
        echo "Creating new entry for '" . $row['bib_id'] . "'\n";

        moveEntry( $row['bib_id'] );
    }
}

// Collapse the holdings information (only for internet archive)
$merge_result = mysql_query( "SELECT `id`, `bib_id`, `035`, count(*) AS `count` FROM `holdings` WHERE `place` = 'IA' GROUP BY `bib_id`, `035` HAVING `count` > 1", $link );
while( $row = mysql_fetch_assoc($merge_result) ) {
    $holding_id = $row['id'];
    $bib_id = $row['bib_id'];
    $local_ctrl = $row['035'];
    $count = $row['count'];

    echo "Merging holdings for '$bib_id' ( '$count' records )\n";

    // Now fetch all other holdings and merge them
    $result = mysql_query( "SELECT `id` FROM `holdings` WHERE `035` = '$local_ctrl' AND `bib_id` = '$bib_id' AND `id` != '$holding_id'", $link );
    while( $sub_row = mysql_fetch_assoc( $result ) ) {
        $sub_holding_id = $sub_row['id'];

        mergeHoldings( $holding_id, $sub_holding_id );
    }
}


// Last step: Clear the index
clearImportIndex();

// Close DB connection
mysql_close( $link );

/*
 *
 * FUNCTIONS START
 *
 */


/*function updateMatch( $old_bib_id, $new_bib_id ) {
    global $link;

    mysql_query( "UPDATE import_bibs SET `depreciated` = '1', `new_id` = '$new_bib_id' WHERE `id` = '$old_bib_id'", $link );
    mysql_query( 'UPDATE import_holdings SET `bib_id` = "' . $new_bib_id . '" WHERE `bib_id` = "' . $old_bib_id . '"', $link );
}*/

/**
 * Updates the dedup-index for the given entry
 *
 * @global mysql-resource $link MySQL Link identifier
 * @param int $bib_id ID of bib-entry to update the index for
 * @param bool $bRemove Remove entry from index
 */
function updateIndex( $bib_id ) {
    global $link;

    $result = mysql_query( "SELECT `author`, `title`, `260` as `publisher` FROM `bibs` WHERE `id` = '$bib_id'", $link );
    $row = mysql_fetch_assoc($result);

    $author = $row['author'];
    $title = $row['title'];
    $publisher = $row['publisher'];

    $author = preg_replace( '/\W/i', "", $author );
    $title = preg_replace( '/\W/i', "", $title );
    $publisher = preg_replace( '/\W/i', "", $publisher );

    mysql_query( "INSERT INTO `bibsIndex` ( `bib_id`, `title`, `author`, `publisher` ) values ( '$bib_id', '$title', '$author', '$publisher' )" );

    $result = mysql_query( "SELECT `oclc` FROM `holdings` WHERE `bib_id` = '$bib_id'" );
    $row = mysql_fetch_assoc($result);

    $oclc = $row['oclc'];

    $oclc = preg_replace( '/\D/i', "", $oclc );

    mysql_query( "INSERT INTO `holdingsIndex` ( `bib_id`, `oclc` ) values ( '$bib_id', '$oclc' )" );
}

/**
 * Clear the import index
 *
 * @global mysql-resource $link MySQL Link identifier
 */
function clearImportIndex() {
   global $link;

   mysql_query( "DELETE FROM `import_bibsIndex`" );
   mysql_query( "DELETE FROM `import_holdingsIndex`" );
}

/**
 * Move a found duplicate to the productive table
 *
 * @global mysql-resource $link Mysql Link identifier
 * @param int $old_bib_id Old bib_id (of duplicate)
 * @param int $new_bib_id New bib_id (of master)
 */
function moveMatch( $old_bib_id, $new_bib_id, $match_method = 'OCLC' ) {
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
        )
        "
    , $link );
    //    OR `new_id` = '$old_bib_id'

    // Delete bib entry from import table
    //mysql_query( "DELETE FROM import_bibs WHERE `id` = '$old_bib_id' OR `new_id` = '$old_bib_id'", $link );

    // Update import holdings to refer to new bib entry
    //mysql_query( 'UPDATE import_holdings SET `bib_id` = "' . $new_bib_id . '" WHERE `bib_id` = "' . $old_bib_id . '"', $link );

    // Transfer entries into productive holdings table
    mysql_query( "INSERT INTO holdings ( `bib_id`, `sourceid`, `035`, `hol_1`, `hol_2`, `hol_3`, `hol_4`, `subject`, `e_856`, `place`, `match_basis`, `oclc`, `user_id`, `orig_bib_id` ) ( SELECT '$new_bib_id', `sourceid`, `035`, `hol_1`, `hol_2`, `hol_3`, `hol_4`, `subject`, `e_856`, `place`, '$match_method', `oclc`, `user_id`, `orig_bib_id` FROM `import_holdings` WHERE `bib_id` = '$old_bib_id' )", $link );
    //mysql_query( 'DELETE FROM import_holdings WHERE `bib_id` = "' . $new_bib_id . '"', $link );

    //updateIndex( $new_bib_id );
    
    // Update import index
    //mysql_query( "DELETE FROM `import_bibsIndex` WHERE `bib_id` = '$old_bib_id'", $link );
    //mysql_query( "DELETE FROM `import_holdingsIndex` WHERE `bib_id` = '$old_bib_id'", $link );

    // Remove import entry
    removeImportEntry( $old_bib_id );
}

/**
 * Moves a given entry to the production table
 *
 * @global mysql-resource $link MySQL link identifier
 * @param int $import_bib_id bib_id of entry to import
 */
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
        )
        "
    , $link );
    //    OR `new_id` = '$import_bib_id'

    $new_bib_id = mysql_insert_id( $link );

    // Delete bib entry from import table
    //mysql_query( "DELETE FROM import_bibs WHERE `id` = '$import_bib_id' OR `new_id` = '$import_bib_id'", $link );

    // Update import holdings to refer to new bib entry
    //mysql_query( "UPDATE import_holdings SET `bib_id` = ' . $new_bib_id . ' WHERE `bib_id` = ' . $import_bib_id . '", $link );
    // Transfer entries into productive holdings table
    mysql_query( "INSERT INTO holdings ( `bib_id`, `sourceid`, `035`, `hol_1`, `hol_2`, `hol_3`, `hol_4`, `subject`, `e_856`, `place`, `match_basis`, `oclc`, `user_id`, `orig_bib_id` ) ( SELECT '$new_bib_id', `sourceid`, `035`, `hol_1`, `hol_2`, `hol_3`, `hol_4`, `subject`, `e_856`, `place`, 'NO MATCH', `oclc`, `user_id`, `orig_bib_id` FROM import_holdings WHERE `bib_id` = '$import_bib_id' )", $link );

    //mysql_query( "DELETE FROM import_holdings WHERE `bib_id` = ' . $new_bib_id . '", $link );

    // Update index (because we might need this entry as a later master)
    updateIndex( $new_bib_id );

    // Remove import entry
    removeImportEntry( $import_bib_id );
}

/**
 * Remove an entry from the import table
 *
 * @global mysql-resource $link MySQL link identifier
 * @param int $bib_id bib-id of entry to remove
 */
function removeImportEntry( $bib_id ) {
    global $link;

    // Delete entries
    mysql_query( "DELETE FROM `import_bibs` WHERE `id` = '$bib_id'", $link );
    mysql_query( "DELETE FROM `import_holdings` WHERE `bib_id` = '$bib_id'", $link );

    // Update index
    mysql_query( "DELETE FROM `import_bibsIndex` WHERE `bib_id` = '$bib_id'", $link );
    mysql_query( "DELETE FROM `import_holdingsIndex` WHERE `bib_id` = '$bib_id'", $link );
}


// TODO: Finish merging function
function mergeHoldings( $primary_hol_id, $secondary_hol_id ) {
    global $link;

    // Get holding information from secondary holdings entry
    $result = mysql_query( "SELECT `hol_1`, `hol_2`, `hol_3`, `hol_4` FROM `holdings` WHERE `id` = '$secondary_hol_id' OR `id` = '$primary_hol_id'" );

    // Concatenate holdings information
    $holdings = array();
    $holdingsString = "";
    while( ($row = mysql_fetch_assoc($result)) ) {
        for( $i = 1; $i <= 4; $i++ ) {
            if( !empty($row['hol_' . $i]) ) {
                $holdings[] = $row['hol_' . $i];
            }
        }
    }
    $holdingsString = join( '; ', $holdings );
    $holdingsString = mysql_escape_string($holdingsString);

    // Update primary holdings information
    mysql_query( "UPDATE `holdings` SET `hol_1` = '$holdingsString', `hol_2` = '', `hol_3` = '', `hol_4` = '' WHERE `id` = '$primary_hol_id'" );

    // Remove secondary holding information
    mysql_query( "DELETE FROM `holdings` WHERE `id` = '$secondary_hol_id'" );
}
