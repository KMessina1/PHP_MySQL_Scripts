<?php
/*--------------------------------------------------------------------------------------
    File: customer_addWithAddress.php
  Author: Kevin Messina
 Created: May 11, 2018
Modified: Dec. 14, 2018

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/12/14 - Updated latest standards.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/customers/customer_addWithAddress.php?
firstName=JOHNNY&
lastName=APPLESEED&
phone=9999999999&
email=kmessina@creativeapps.us&
notes=TEST&
address1=123%20MAIN%20STREET&
address2=&
city=ANYTOWN&
stateCode=AK&
zip=99999&
countryCode=US&
appVersion=99.99.99&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "CUSTOMERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");


/* GET INPUT PARAMS */
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
$mailingList = 1;
$addressID = -1;
$moltinID = "";
$stripeID = "";

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_customers);

    // Insert Customer
    $values ="
        '$firstName',
        '$lastName',
        '$phone',
        '$email',
        $mailingList,
        $addressID,
        '$moltinID',
        '$stripeID',
        '$notes'
    ";
    $success = $db->insert($values);
    $customerID = $db->lastInsertedID;

    // Insert Address
    if ($success) {
        $db->setTableName($table_addresses);
        $values ="
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
        ";
        $success = $db->insert($values);
        $addressID = $db->lastInsertedID;

        // Update Customer with AddressID
        if ($success) {
            $db->setTableName($table_customers);
            $db->updateID($customerID,"addressID=$addressID");
        }
    }
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
$success = (bool)(($customerID > 0) && ($addressID > 0));
$found = (int)$success;

$itemsToEncode = [
    "customerID" => $customerID,
    "addressID" => $addressID
];

echo my_json_encodeCustom($found,$success,$records,$itemsToEncode);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
