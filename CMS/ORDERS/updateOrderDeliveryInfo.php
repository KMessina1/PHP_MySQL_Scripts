<?php
/*--------------------------------------------------------------------------------------
    File: updateOrderDeliveryInfo.php
  Author: Kevin Messina
 Created: Aug. 21, 2018
Modified: Jan 01, 2019

©2018-2019 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/orders/updateOrderDeliveryInfo.php?
orderID=2475&
statusID=Delivered&
deliveredDate=2019-01-01&
appVersion=99.99.99&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "ORDER";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$orderID = (int)$_GET['orderID'];
$statusID = (string)$_GET['statusID'];
$deliveredDate = (string)$_GET['deliveredDate'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> orderID: $orderID";
$msg .= "\n|-> statusID: $statusID";
$msg .= "\n|-> deliveredDate: $deliveredDate";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);
    $success = $db->updateID($orderID,"statusID='$statusID',deliveredDate='$deliveredDate'");
    $found = int($success);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
