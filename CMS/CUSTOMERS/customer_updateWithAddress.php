<?php
/*--------------------------------------------------------------------------------------
    File: customer_updateWithAddress.php
  Author: Kevin Messina
 Created: May 14, 2018
Modified: Dec. 14, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/12/14 - Updated latest standards.
--------------------------------------------------------------------------------------*/

$version = "2.01a";
$category = "CUSTOMERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$customerID = (string)$_GET['customerID'];
$firstName = (string)$_GET['firstName'];
$lastName = (string)$_GET['lastName'];
$phone = (string)$_GET['phone'];
$email = (string)$_GET['email'];
$notes = (string)$_GET['notes'];
$address1 = (string)$_GET['address1'];
$address2 = (string)$_GET['address2'];
$city = (string)$_GET['city'];
$stateCode = (string)$_GET['stateCode'];
$zip = (string)$_GET['zip'];
$countryCode = (string)$_GET['countryCode'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> firstName: $firstName";
$msg .= "\n|-> lastName: $lastName";
$msg .= "\n|-> phone: $phone";
$msg .= "\n|-> email: $email";
$msg .= "\n|-> notes: $notes";
$msg .= "\n|-> address1: $address1";
$msg .= "\n|-> address2: $address2";
$msg .= "\n|-> city: $city";
$msg .= "\n|-> stateCode: $stateCode";
$msg .= "\n|-> zip: $zip";
$msg .= "\n|-> countryCode: $countryCode";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();
$customerID = -1;
$addressID = -1;

/* FUNCTIONS */

/* PROCESSES */
try {
    /* INIT DB */
    $connection = connectToCMS();

    /* GET INPUT PARAMS */
    $customerID = (int)$_GET['customerID'];
    $firstName = (string)$_GET['firstName'];
    $lastName = (string)$_GET['lastName'];
    $email = (string)$_GET['email'];
    $phone = (string)$_GET['phone'];

    /* UPDATE CUSTOMER */
    $records = null;
    $query = "UPDATE customers SET
        firstName='$firstName',
        lastName='$lastName',
        phone='$phone',
        email='$email'
    WHERE id=$customerID;";
    array_push($stack,$dashes,'Query: '.$query);
    $results = mysqli_query($connection, $query);
    $success_customer = (bool)($results == "1");
    if ($success_customer == true) {
        array_push($stack,'Customer updated: '.$customerID);
    }else{
        array_push($stack,'Customer FAILED Update.');
    }

    /* GET INPUT PARAMS */
    $addressID = (int)$_GET['addressID'];
    $address1 = (string)$_GET['address1'];
    $address2 = (string)$_GET['address2'];
    $city = (string)$_GET['city'];
    $stateCode = (string)$_GET['stateCode'];
    $zip = (string)$_GET['zip'];
    $countryCode = (string)$_GET['countryCode'];

    // If $addressID is unknown, try getting from Customer record.
    if ($addressID < 1) {
        $records = null;
        $query = "SELECT * FROM customers WHERE id=$customerID;";
        array_push($stack,$dashes,'Query: '.$query);
        $results2 = mysqli_query($connection, $query);
        while ($r = mysqli_fetch_array($results2)) {
            $records[] = $r;
        }
        $found = (int)count($records);
        if ($found > 0) {
            $record = $records[0];
            $newAddressID = (int)$record["addressID"];
            if ($newAddressID > 0) {
                $addressID = $newAddressID;
                array_push($stack,'AddressID from Customer is now: '.$addressID);
            }else{
                array_push($stack,'AddressID from Customer is not set.');
            }
        }
    }

    // If $addressID is still unknown, try getting from address record.
    if ($addressID < 1) {
        $records = null;
        $query = "SELECT * FROM addresses
            WHERE customerID=$customerID
            AND UPPER(firstName) LIKE UPPER('%$firstName%')
            AND UPPER(lastName) LIKE UPPER('%$lastName%')
            AND UPPER(address1) LIKE UPPER('%$address1%')
            AND UPPER(city) LIKE UPPER('%$city%')
            AND stateCode='$stateCode'
        ;";
        array_push($stack,$dashes,'Query: '.$query);
        $results2 = mysqli_query($connection, $query);
        while ($r = mysqli_fetch_array($results2)) {
            $records[] = $r;
        }
        $found = (int)count($records);
        array_push($stack,'Addresses - Found: '.$found.' records.');

        if ($found > 0) {
            $record = $records[0];
            $newAddressID = (int)$record["id"];
            if ($newAddressID > 0) {
                $addressID = $newAddressID;
                array_push($stack,'AddressID from Adress matching first, last and customerID is now: '.$addressID);
            }else{
                array_push($stack,'AddressID from Address is not set.');
            }
        }
    }

    /* UPDATE CUSTOMER ADDRESS */
    if ($addressID > 0) {
        $records = null;
        $query = "UPDATE addresses SET
            firstName='$firstName',
            lastName='$lastName',
            address1='$address1',
            address2='$address2',
            city='$city',
            stateCode='$stateCode',
            zip='$zip',
            countryCode='$countryCode',
            phone='$phone',
            email='$email'
        WHERE id=$addressID;";
        array_push($stack,$dashes,'Query: '.$query);
        $results1 = mysqli_query($connection, $query);
        $success_address = (bool)($results1 == "1");
    }

    /* PROCESS RESULTS */
    $success = (bool)(($success_customer == true) && ($success_address == true));

    if ($success == true) {
        $msg = 'Updated Customer Info and Address for CustomerID: '.$customerID.'.';
        array_push($stack,$msg);
        logSuccess($msg);
    }else{
        $msg = 'Failed update of Customer Info and Address for: '.$customerID.'.';
        array_push($stack,$msg);
        logFailure($msg);
    }

    /* OUTPUT RESULTS */
    if ($records == null) { $records = array(); }
    echo json_encode([
        "version" => $version,
        "stack" => $stack,
        "found" => $found,
        "success" => $success,
        "records" => $records,
        "customerID" => $customerID,
        "addressID" => $addressID
    ]);
} catch (Exception $e) {
    logFailure("Connect to CMS Server: ".$e->getMessage());
}

?>
