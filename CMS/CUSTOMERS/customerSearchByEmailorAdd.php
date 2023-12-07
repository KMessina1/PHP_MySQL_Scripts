<?php
/*--------------------------------------------------------------------------------------
    File: customerSearchByEmailorAdd.php
  Author: Kevin Messina
 Created: Apr. 04, 2018
Modified: Dec. 14, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/12/14 - Updated latest standards.
2018/11/17 - Updated Include files.
2018/11/08 - Updated to current CMS standards.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/customers/customerSearchByEmailorAdd.php?
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
notes=&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "CUSTOMERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
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
$notes = trim((string)$_GET['notes']);

$title = "INPUT PARAMS:";
$msg = "\n$title";
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
$msg .= "\n|-> notes: $notes";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();
$customerID = (int)-1;
$addressID = (int)-1;
$address = null;
$customer = null;

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_customers);
    $customer = $db->fetchRecordsWhere("email='$email'");

    /* Customer Found? If not, insert as new */
    if ($db->hasRecords) {
        $customer = $customer[0];
        $customerID = (int)$customer["id"];
        $addressID = (int)$customer["addressID"];

        /* Get Customer Address */
        if (getAddressByID() == false){
            if (insertAddress()) {
                if (updateCustomerAddressID()) {
                    $success = getAddressByID();
                    $success = getCustomerByID();
                }
            }
        }
    }else{
        if (insertCustomer()) {
            if (insertAddress()) {
                if (updateCustomerAddressID()) {
                    $success = getAddressByID($addressID);
                    $success = getCustomerByID($customerID);
                }
            }
        }
    }
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
$records = null;
$success = (bool)(($customer != null) && ($address != null));
$found = (int)$success;

$customer = convertArrayFromNullToEmptyArray($customer);
$address = convertArrayFromNullToEmptyArray($address);

$itemsToEncode = [
    "customer" => $customer,
    "address" => $address
];
echo my_json_encodeCustom($found,$success,$records,$itemsToEncode);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

exit();


/* FUNCTIONS */
function getAddressByID(){
    global $db,$address,$table_addresses,$addressID;

    $db->setTableName($table_addresses);
    $db->fetchRecordID($addressID);
    if ($db->hasRecords) {
        $address = $db->records[0];
    }
    return $db->hasRecords;
}

function getCustomerByID() {
    global $db,$customer,$table_customers,$customerID;

    $db->setTableName($table_customers);
    $db->fetchRecordID($customerID);
    if ($db->hasRecords) {
        $customer = $db->records[0];
    }
    return $db->hasRecords;
}

function insertAddress() {
    global $db,$addressID,$table_addresses,$customerID,$firstName,$lastName,$address1,$address2,$city,
            $stateCode,$zip,$countryCode,$phone,$email;

    $moltinID = "";
    $values = "
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

    $db->setTableName($table_addresses);
    $success = $db->insert($values);
    $addressID = $db->lastInsertedID;

    return ($addressID > 0);
}

function updateCustomerAddressID() {
    global $db,$addressID,$customerID,$table_customers;

    $db->setTableName($table_customers);
    $success = $db->updateID($customerID,"addressID=$addressID");

    return $success;
}

function insertCustomer() {
    global $db,$customerID,$table_customers,$firstName,$lastName,$phone,$email;

    $addressID = -1;
    $mailingList = (int)1;
    $moltinID = "";
    $stripeID = "";
    $notes = "Created on ".getTodaysDateTimeConverted();

    $values = "
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

    $db->setTableName($table_customers);
    $db->insert($values);
    $customerID = $db->lastInsertedID;

    return ($customerID > 0);
}

?>
