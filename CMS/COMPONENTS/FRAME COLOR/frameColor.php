<?php
/*--------------------------------------------------------------------------------------
     File: frameColor.php
   Author: Kevin Messina
  Created: Feb. 15, 2018
 Modified: Feb. 16, 2018

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:
--------------------------------------------------------------------------------------*/

$version = "1.04";


/* INITIALIZE */
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$timestamp = getServerDateTime();


/* FUNCTIONS */
function doQuery() {
    global $version,$scriptName,$timestamp;

    /* GET INPUT PARAMS */
    $action = (string)$_GET['action'];
    $code = (string)$_GET['code'];
    $id = (int)$_GET['id'];
    $name = (string)$_GET['name'];
    $description = (string)$_GET['description'];
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
        if ($action == 'LIST') {
            $query = "SELECT * FROM frameColors ORDER BY code;";
        }elseif ($action == 'EXISTS' || $action == 'SEARCH') {
            $query = "SELECT * FROM frameColors WHERE code=BINARY'$code' ORDER BY code;";
        }elseif ($action == 'ADD') {
            $query = "INSERT INTO frameColors VALUES (
                NULL,
                '$code',
                '$name',
                '$description',
                $active,
                '$notes'
            );";
        }elseif ($action == 'EDIT') {
            $query = "UPDATE frameColors SET
                code='$code',
                name='$name',
                description='$description',
                active=$active,
                notes='$notes'
            WHERE id=$id;";
        }elseif ($action == 'DELETE') {
            $query = "DELETE FROM frameColors WHERE id=$id;";
        }

        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);

        /* PROCESS RESULTS */
        if ($action == 'LIST' || $action == 'SEARCH') {
            while ($r = mysqli_fetch_array($results)) {
                $records[] = $r;
            }

            /* RESULTS */
            $found = (int)count($records);
            $success = (bool)($found > 0);

            $msg = 'Action: '.$action.' was successful. Found: '.$found.' records.';
        }else{
            $success = (bool)($results == "1");
            $msg = 'Action: '.$action.' was successful.';
        }

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
    if ($action == 'LIST' || $action == 'SEARCH') {
        if ($records == null) { $records = array(); }

        $timestamp = getServerDateTime();
        array_push($stack,$dashes,"*** Script ".$scriptName." finished @ ".$timestamp." ***");
        echo json_encode([
            "version" => $version,
            "stack" => $stack,
            "found" => $found,
            "success" => $success,
            "records" => $records
        ]);
    }else{
        echo json_encode(["success" => $success]);
    }
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
