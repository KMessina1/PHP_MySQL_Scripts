<?php
/*--------------------------------------------------------------------------------------
     File: orderIssues.php
   Author: Kevin Messina
  Created: Mar. 28, 2018
 Modified:

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:
--------------------------------------------------------------------------------------*/

$version = "1.04";


/* INITIALIZE */
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');

/* INIT DEFAULTS */
require_once('../../funcs.php');
$dashes = "----------------------------------------------";
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$timestamp = getServerDateTime();
$stack = array("*** Script ".$scriptName." started @ ".$timestamp." ***",$dashes);

/* FUNCTIONS */
try {
    /* INIT DB */
    $connection = connectToCMS();

    /* GET INPUT PARAMS */

    /* PERFORM QUERY: Orders */
    $records = null;
    $query = "SELECT * FROM orders WHERE statusID='Issue' ORDER BY orderDate DESC;";
    array_push($stack,$dashes,'Query: '.$query);
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) {
        $records[] = $r;
    }
    $found = (int)count($records);
    array_push($stack,'--> Found: '.$found.' orders.');

    /* PERFORM QUERY: Issues */
    $issueRecords = null;
    $query = "SELECT * FROM issues ORDER BY id DESC,status ASC;";
    array_push($stack,$dashes,'Query: '.$query);
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) {
        $issueRecords[] = $r;
    }
    $foundIssues = (int)count($issueRecords);
    array_push($stack,'--> Found: '.$foundIssues.' issues.');

    /* PROCESS RESULTS */
    $msg = 'Found: '.$found.' orders and Found: '.$foundIssues.'issues.';
    array_push($stack,$dashes,$msg);
    $success = (bool)($found > 0);
    if ($success == true) {
        logSuccess($msg);
    }else{
        logFailure($msg);
    }
} catch (Exception $e) {
    logFailure("Connect to CMS Server: ".$e->getMessage());
    $success = false;
}

/* RETURN RESULTS */
if ($records == null) { $records = array(); }
echo json_encode([
    "version" => $version,
    "stack" => $stack,
    "found" => $found,
    "success" => $success,
    "issues" => $issueRecords,
    "records" => $records
]);

?>
