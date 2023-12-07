<?php
/*--------------------------------------------------------------------------------------
    File: addressUpdate.php
  Author: Kevin Messina
 Created: Feb. 28, 2018
Modified: Nov. 10, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/10 - Updated to current CMS standards.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/addresses/addressUpdate.php?
id=2346&
customerID=90&
firstName=TESTER&
lastName=TESTER&
address1=TESTER&
address2=TESTER&
city=TESTER&
stateCode=NY&
zip=99999&
countryCode=US&
phone=9999999999&
email=TESTER@TESTER.COM&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "ADDRESSES";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$id = (int)$_GET['id'];
$customerID = (string)$_GET['customerID'];
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
$msg .= "\n|-> id: $id";
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

/* FUNCTIONS */

/* PROCESSES */
try {
    $set =
        "customerID='$customerID',
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
    ";
    $success = updateDB($table_addresses,$set,$id);
    $found = (int)$success;
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
