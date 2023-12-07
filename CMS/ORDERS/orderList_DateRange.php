<?php
/*------------------------------------------------------------------------------------------------------------
    File: listAllNewOrders.php
  Author: Kevin Messina
 Created: Jun 7, 2018
Modified:

©2018 Creative App Solutions, LLC. - All Rights Reserved.
--------------------------------------------------------------------------------------------------------------
NOTES:
------------------------------------------------------------------------------------------------------------*/

$version = "1.07a";

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

$date = (string)$_GET['fromDate'];
$month = $date[0].$date[1];
$day = $date[3].$date[4];
$year = $date[6].$date[7].$date[8].$date[9];
$fromDate = $year."/".$month."/".$day;

$date = (string)$_GET['toDate'];
$month = $date[0].$date[1];
$day = $date[3].$date[4];
$year = $date[6].$date[7].$date[8].$date[9];
$toDate = $year."/".$month."/".$day;

/* FUNCTIONS */
try {
    /* INIT DB */
    $connection = connectToCMS();

    /* PERFORM QUERY */
    $query = "SELECT * FROM orders WHERE orderNum>=100 AND (CAST(orderDate AS DATE) BETWEEN CAST('2018/05/01' AS DATE) AND CAST('2018/06/07' AS DATE)) ORDER BY statusID ASC;";
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
