<?php
/*--------------------------------------------------------------------------------------
    File: customerAdd.php
  Author: Kevin Messina
 Created: Feb. 26, 2018
Modified: Dec. 14, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/12/07 - Updated to latest values.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/custoemrs/customerAdd.php?
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "CUSTOMER";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$firstName = (string)$_GET['firstName'];
$lastName = (string)$_GET['lastName'];
$phone = (string)$_GET['phone'];
$email = (string)$_GET['email'];
$mailingList = (int)$_GET['mailingList'];
$addressID = (int)$_GET['addressID'];
$moltinID = (string)$_GET['moltinID'];
$stripeID = (string)$_GET['stripeID'];
$notes = (string)$_GET['notes'];

$firstName = (string)$_GET['firstName'];
$lastName = (string)$_GET['lastName'];
$phone = (string)$_GET['phone'];
$email = (string)$_GET['email'];
$mailingList = (int)$_GET['mailingList'];
$addressID = (int)$_GET['addressID'];
$moltinID = (string)$_GET['moltinID'];
$stripeID = (string)$_GET['stripeID'];
$notes = (string)$_GET['notes'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> jsonSerializedStringOfParams: $jsonSerializedStringOfParams";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();
$customerID = -1;
$moltinID = (string)"";

/* FUNCTIONS */

/* PROCESSES */
try {
    /* INIT DB */
    $connection = connectToCMS();

    /* GET INPUT PARAMS */

    // printLineToScreen();
    // printToScreen("moltinID",$moltinID,false);
    // printLineToScreen();

    /* PERFORM QUERY: Does Customer already exist */
    $query = "SELECT * FROM customers WHERE moltinID='$moltinID';";
    array_push($stack,$dashes,'Query: '.$query);
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) { $records[] = $r; }

    $found = (int)count($records);
    array_push($stack,'Found: '.$found.' records.');
    if ($found > 0) {
        $customerID = (int)$records[0]["id"];
        array_push($stack,'Customer already exists with MoltinID: '.$moltinID.' skipping Insert.');
        $success = true;
       logSuccess('Skipped insert of Customer, already exists with MoltinID: '.$moltinID);
    }else{
        /* PERFORM QUERY: Insert */
        $query = "INSERT INTO customers VALUES (
            NULL,
            '$firstName',
            '$lastName',
            '$phone',
            '$email',
            '$mailingList',
            $addressID,
            '$moltinID',
            '$stripeID',
            '$notes'
        );";
        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);
        $success = (bool)($results == "1");

        $customerID = $connection->insert_id;

        /* PROCESS RESULTS */
        if ($success == true) {
            array_push($stack,$dashes,'Insert of Customer for: '.$firstName.' '.$lastName.' was successful.');
            logSuccess("add Customer completed.");
        }else{
            array_push($stack,$dashes,'Failed insertion of Customer for: '.$firstName.' '.$lastName.'.');
            logFailure('add Customer FAILED for: '.$firstName.' '.$lastName.'.');
        }
    }
} catch (Exception $e) {
    array_push($stack,$dashes,'Failed insertion of Customer for: '.$firstName.' '.$lastName.'.');
    array_push($stack,"Connect to CMS Server: ".$e->getMessage());
    $success = false;
    logFailure("Connect to CMS Server: ".$e->getMessage());
}

/* RETURN RESULTS */
if ($records == null) { $records = array(); }
echo json_encode([
    "version" => $version,
    "stack" => $stack,
    "found" => $found,
    "success" => $success,
    "records" => $records,
    "customerID" => $customerID
]);

?>
