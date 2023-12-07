<?php
/*--------------------------------------------------------------------------------------
     File: customerComponentsList.php
   Author: Kevin Messina
  Created: Feb. 28, 2018
 Modified:

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:
--------------------------------------------------------------------------------------*/

$version = "1.01";


/* INITIALIZE */
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$timestamp = getServerDateTime();


/* FUNCTIONS */
function doQuery() {
    global $version,$scriptName,$timestamp;

    /* GET INPUT PARAMS */

    /* INIT DEFAULTS */
    $dashes = "----------------------------------------------";
    $scriptName = basename($_SERVER['SCRIPT_NAME']);
    $timestamp = getServerDateTime();
    $stack = array("*** Script ".$scriptName." started @ ".$timestamp." ***",$dashes);

    try {
        $customers = null;
        $addresses = null;

        /* INIT DB */
        $connection = connectToCMS();

        /* PERFORM QUERY */
        $query = "SELECT * FROM addresses ORDER BY lastName,firstName;";
        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);
        while ($r = mysqli_fetch_array($results)) {
            $addresses[] = $r;
        }
        $found = (int)count($addresses);
        array_push($stack,'ADDRESSES - Found: '.$found.' records.');

        $query = "SELECT * FROM customers ORDER BY lastName,firstName;";
        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);
        while ($r = mysqli_fetch_array($results)) {
            $customers[] = $r;
        }
        $found = (int)count($customers);
        array_push($stack,'CUSTOMERS - Found: '.$found.' records.');

        logSuccess("Finished listing all Customer components.");
    } catch (Exception $e) {
        logFailure("Connect to CMS Server: ".$e->getMessage());
    }

    /* RETURN RESULTS */
    if ($customers == null) { $customers = array(); }
    if ($addresses == null) { $addresses = array(); }

    $timestamp = getServerDateTime();
    array_push($stack,$dashes,"*** Script ".$scriptName." finished @ ".$timestamp." ***");
    echo json_encode([
        "version" => $version,
        "stack" => $stack,
        "customers" => $customers,
        "addresses" => $addresses
    ]);
}

function getServerDateTime(){ return Date('Y-m-d H:i:s'); }
function logSuccess($msg){ writeLog("✅",$msg); }
function logFailure($msg){ writeLog("❌",$msg); }

function writeLog($status,$msg){
    global $scriptName,$timestamp,$version;

    $timestamp = getServerDateTime();
    $calledfromApp = isset($_GET['calledFromApp']) ? $_GET['calledFromApp'] : 'n/a';
    $msg = "🗓".$timestamp." ".$status." (📜".$scriptName.", v".$version." 📝".$msg." 📱App: ".$calledfromApp.")".PHP_EOL;

    try {
        $fp = fopen('../../../Logs/SF-Admin_Log.txt', 'a');
        fwrite($fp, $msg);
        fclose($fp);
    } catch (Exception $e) {
        log_exception($e);
    }
}


/* RUN SCRIPT */
doQuery();

?>
