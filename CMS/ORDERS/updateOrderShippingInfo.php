<?php
/*--------------------------------------------------------------------------------------
    File: updateOrderShippingInfo.php
  Author: Kevin Messina
 Created: Aug. 20, 2018
Modified: Dec. 17, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/12/17 - Update to latest Includes.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/orders/updateOrderShippingInfo.php?
orderID=2475&
statusID=Shipped&
postage=8.10&
shipDate=2018-12-17&
trackingNum=1234567890&
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
$postage = (float)$_GET['postage'];
$shipDate = (string)$_GET['shipDate'];
$trackingNum = (string)$_GET['trackingNum'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> orderID: $orderID";
$msg .= "\n|-> statusID: $statusID";
$msg .= "\n|-> postage: $postage";
$msg .= "\n|-> shipDate: $shipDate";
$msg .= "\n|-> trackingNum: $trackingNum";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);
    $success = $db->updateID($orderID,"statusID='$statusID',trackingNum='$trackingNum',shippedAmt=$postage,shippedDate='$shipDate'");
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
