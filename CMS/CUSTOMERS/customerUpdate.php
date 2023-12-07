<?php
/*--------------------------------------------------------------------------------------
     File: customerUpdate.php
   Author: Kevin Messina
  Created: Feb. 27, 2018
 Modified: Jun. 27, 2018

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:
--------------------------------------------------------------------------------------*/

$version = "1.02a";

/* INITIALIZE */
require_once($_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/scripts/funcs.php");
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');

/* INIT DEFAULTS */
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$timestamp = getServerDateTime();
$stack = array('Line#'.__LINE__.' Func: '.__FUNCTION__.' 📝'."*** Script ".$scriptName." started @ ".$timestamp." ***",$dashes);
$success = false;
$records = null;
$found = (int)0;

/* FUNCTIONS */
try {
    /* INIT DB */
    $connection = connectToCMS();

    /* GET INPUT PARAMS */
    $id = (int)$_GET['id'];
    $firstName = (string)$_GET['firstName'];
    $lastName = (string)$_GET['lastName'];
    $phone = (string)$_GET['phone'];
    $email = (string)$_GET['email'];
    $mailingList = (int)$_GET['mailingList'];
    $notes = (string)$_GET['notes'];

    /* PERFORM QUERY */
    $records = null;
    $query = "UPDATE customers SET
        firstName='$firstName',
        lastName='$lastName',
        phone='$phone',
        email='$email',
        mailingList=$mailingList,
        notes='$notes'
    WHERE id=$id;";
    array_push($stack,$dashes,'Query: '.$query);
    $results = mysqli_query($connection, $query);
    $success = (bool)($results == "1");

    /* PROCESS RESULTS */
    if ($success == true) {
        $msg = 'Update of Customer for: '.$firstName.' '.$lastName.' was successful.';
        array_push($stack,$dashes,$msg);
        logSuccess($msg);
    }else{
        $msg = 'Failed Update of Customer for: '.$firstName.' '.$lastName.'.';
        array_push($stack,$dashes,$msg);
        logFailure($msg);
    }
} catch (Exception $e) {
    logFailure("Connect to CMS Server: ".$e->getMessage());
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
