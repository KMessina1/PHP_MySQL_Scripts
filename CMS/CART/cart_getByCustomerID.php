<?php
/*--------------------------------------------------------------------------------------
    File: cart_getByCustomerID.php
  Author: Kevin Messina
 Created: Apr 15, 2018
Modified: Nov. 19, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/cart/cart_getByCustomerID.php?
customerID=90&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "CART";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$customerID = (int)$_GET['customerID'];

/* INIT PARAMS */
initDefaults();
$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> customerID: $customerID";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_carts);
    $records = $db->fetchRecordsWhere("customerID=$customerID AND OrderID<1");
    $found = $db->numRecords();
    $success = $db->hasRecords();
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
