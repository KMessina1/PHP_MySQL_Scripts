<?php
/*------------------------------------------------------------------------------------------------------------
    File: listAllNewOrders.php
  Author: Andrew Watson (Moltin)
 Created: Jan 11, 2018
Modified: Jun 4, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
--------------------------------------------------------------------------------------------------------------
NOTES:
------------------------------------------------------------------------------------------------------------*/

$version = "1.06d";

/* INITIALIZE */
require_once($_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/scripts/funcs.php");
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');
$dashes = "----------------------------------------------";
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$timestamp = getServerDateTime();
$stack = array("*** Script ".$scriptName." started @ ".$timestamp." ***",$dashes);

/* INIT DEFAULTS */
$records = null;
$success = false;
$found = 0;

/* FUNCTIONS */
try {
    /* INIT DB */
    $connection = connectToCMS();

    /* PERFORM QUERY */
    $query = "SELECT * FROM orders WHERE orderNum>=100 AND (statusID='New' OR statusID='Shipped' OR statusID='Processing') ORDER BY statusID ASC;";
    array_push($stack,$dashes,'Query: '.$query);
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) { $records[] = $r; }
    $found = (int)count($records);
    $msg = 'Found: '.$found.' records.';
    array_push($stack,$dashes,$msg);

    /* PROCESS RESULTS */
    $success = (bool)($found > 0);
    ($success == true) ?logSuccess($msg) :logFailure($msg);
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
    "records" => $records
]);

?>
