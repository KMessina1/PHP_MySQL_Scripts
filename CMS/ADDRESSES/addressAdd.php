<?php
/*--------------------------------------------------------------------------------------
    File: addressAdd.php
  Author: Kevin Messina
 Created: Feb. 28, 2018
Modified: Nov. 10, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/10 - Updated to current CMS standards.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/addresses/addressAdd.php?
customerID=90&
firstName=TEST&
lastName=TEST&
address1=TEST&
address2=TEST&
city=TEST&
stateCode=NY&
zip=99999&
countryCode=US&
phone=9999999999&
email=TEST@TEST.COM&
moltinID=&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "ADDRESSES";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$customerID = (int)$_GET['customerID'];
$firstName = (string)$_GET['firstName'];
$lastName = (string)$_GET['lastName'];
$address1 = (string)$_GET['address1'];
$address2 = (string)$_GET['address2'];
$city = (string)$_GET['city'];
$stateCode = (string)$_GET['stateCode'];
$zip = (string)$_GET['zip'];
$countryCode = (string)$_GET['countryCode'];
$phone = (string)$_GET['phone'];
$email = (string)$_GET['email'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> customerID: $customerID";
$msg .= "\n|-> firstName: $firstName";
$msg .= "\n|-> lastName: $lastName";
$msg .= "\n|-> address1: $address1";
$msg .= "\n|-> address2: $address2";
$msg .= "\n|-> city: $city";
$msg .= "\n|-> stateCode: $stateCode";
$msg .= "\n|-> zip: $zip";
$msg .= "\n|-> countryCode: $countryCode";
$msg .= "\n|-> phone: $phone";
$msg .= "\n|-> email: $email";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();
$addressID = (int)-1;

/* FUNCTIONS */

/* PROCESSES */
try {
    $tableName = $table_addresses;
    $where = "
        firstName='$firstName'
        AND lastName='$lastName'
        AND address1='$address1'
        AND city='$city'
        AND stateCode='$stateCode'
        AND zip='$zip'
        ;
    ";
    $records = fetchFromDB($tableName,"",$where);
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);

    /* Insert if record doesn't already exist */
    if ($success) {
        $addressID = (int)$records[0]["id"];
        writeMsgToStackAndConsole(__LINE__,"RECORD EXISTS","Skipping Insert.");
    }else{
        $moltinID = "";
        $tableName = $table_addresses;
        $query =
            "INSERT INTO
                $tableName
            VALUES (
                NULL,
                $customerID,
                '$firstName',
                '$lastName',
                '$address1',
                '$address2',
                '$city',
                '$stateCode',
                '$zip',
                '$countryCode',
                '$phone',
                '$email',
                '$moltinID'
            )
            ;
        ";
        $addressID = insertDBreturnID($query,$tableName);
    }
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
$found = $addressID;

echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
