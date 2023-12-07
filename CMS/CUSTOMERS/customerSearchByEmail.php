<?php
/*--------------------------------------------------------------------------------------
    File: customerSearchByEmail.php
  Author: Kevin Messina
 Created: Apr. 4, 2018
Modified: Dec. 14, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/12/14 - Updated latest standards.
2018/11/17 - Updated Include files.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/customers/customerSearchByEmail.php?
email=kmessina@creativeapps.us&
appVersion=2.01&
calledFromApp=Browser&
debug=1
*/

$version = "2.01b";
$category = "CUSTOMERS";


/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");


/* GET INPUT PARAMS */
$email = trim((string)$_GET['email']);

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> email: $email";
writeMsgToStackAndConsole(__LINE__,$title,$msg);


/* INIT PARAMS */
initDefaults();
$customerID = (int)-1;
$addressID = (int)-1;
$address = null;
$customer = null;

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_customers);
    $records = $db->fetchRecordsWhere("email='$email'");

    /* Customer Found? If yes, get Customers Address */
    if ($db->hasRecords) {
        $customer = $records[0];
        $addressID = (int)$customer["addressID"];

        /* PERFORM QUERY: Get Address */
        $db->setTableName($table_addresses);
        if ($db->fetchRecordID($addressID)) {
            $address = $db->records[0];
        }
    }
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
$records == null;
$success = (bool)(($customer != null) && ($address != null));
$found = (int)($success);

$customer = convertArrayFromNullToEmptyArray($customer);
$address = convertArrayFromNullToEmptyArray($address);

$itemsToEncode = [
    "address" => $address,
    "customer" => $customer
];
echo my_json_encodeCustom($found,$success,$records,$itemsToEncode);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
