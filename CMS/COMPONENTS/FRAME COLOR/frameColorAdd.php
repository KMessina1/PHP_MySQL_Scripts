<?php
/*--------------------------------------------------------------------------------------
     File: frameColorAdd.php
   Author: Kevin Messina
  Created: Feb. 17, 2018
 Modified:

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:
--------------------------------------------------------------------------------------*/

$version = "1.00";


/* INITIALIZE */
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$timestamp = getServerDateTime();


/* FUNCTIONS */
function doQuery() {
    global $version,$scriptName,$timestamp;

    /* GET INPUT PARAMS */
    $id = (int)$_GET['id'];
    $name = (string)$_GET['name'];
    $description = (string)$_GET['description'];
    $code = (string)$_GET['code'];
    $active = (int)$_GET['active'];
    $notes = (string)$_GET['notes'];

    /* INIT DEFAULTS */
    $dashes = "----------------------------------------------";
    $scriptName = basename($_SERVER['SCRIPT_NAME']);
    $timestamp = getServerDateTime();
    $stack = array("*** Script ".$scriptName." started @ ".$timestamp." ***",$dashes);

    try {
        $records = null;

        /* INIT DB */
        $connection = connectToCMS();

        /* PERFORM QUERY */
        $query = "INSERT INTO frameColors VALUES (
            NULL,
            '$name',
            '$description',
            '$code',
            $active,
            '$notes'
        );";
        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);

        /* PROCESS RESULTS */
        $success = (bool)($results == "1");
        $msg = 'Insert of code: '.$code.' was successful.';

        array_push($stack,$msg);

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
    echo json_encode(["success" => $success]);
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
