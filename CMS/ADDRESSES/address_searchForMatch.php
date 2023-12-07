<?php
/*--------------------------------------------------------------------------------------
    File: address_searchForMatch.php
  Author: Kevin Messina
 Created: Feb. 28, 2018
Modified: Nov. 19, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
2018/11/08 - Updated to current CMS standards.
2018/10/10 - Updated to latest CMS Standards.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/addresses/address_searchForMatch.php?
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
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "ADDRESSES";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$customerID = (int)$_GET['customerID'];
$firstName = trim((string)$_GET['firstName']);
$lastName = trim((string)$_GET['lastName']);
$address1 = trim((string)$_GET['address1']);
$address2 = trim((string)$_GET['address2']);
$city = trim((string)$_GET['city']);
$stateCode = trim((string)$_GET['stateCode']);
$zip = trim((string)$_GET['zip']);
$countryCode = trim((string)$_GET['countryCode']);
$phone = trim((string)$_GET['phone']);
$email = trim((string)$_GET['email']);

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
    $where =
        "customerID=$customerID
        AND UPPER(firstName) LIKE UPPER('%$firstName%')
        AND UPPER(lastName) LIKE UPPER('%$lastName%')
        AND UPPER(address1) LIKE UPPER('%$address1%')
        AND UPPER(city) LIKE UPPER('%$city%')
        AND stateCode='$stateCode'
        ";
    $records = fetchAllFromDB($tableName,"",$where);
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);

    /* Address Found? If not, insert as new */
    if ($success) {
        $record = $records[0];
        $addressID = (int)$record["id"];
    }else{
        $moltinID = "";
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
$success = ($addressID > 0);
$found = (int)$success;

echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
