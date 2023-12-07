<?php
/*--------------------------------------------------------------------------------------
    File: ordersList_byStatus.php
  Author: Kevin Messina
 Created: Mar. 10, 2018
Modified: Nov. 28, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/28 - Updated to latest Includes.
2018/09/04 - Converted to CMS Logging.
2018/08/01 - Added 'unpaid' to the Fulfillment 'new' status fetch.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/orders/ordersList_byStatus.php?
status=Processing&
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "ORDERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$status = (string)$_GET["status"];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> status: $status";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);

    $where = ($status == "New")
        ? "statusID='New' OR statusID='Processing' OR statusID='Shipped' OR statusID='Unpaid'"
        : "statusID='$status'";
    $records = $db->fetchRecordsWhere($where,"orderDate DESC");
    $found = $db->numRecords;
    $success = $db->hasRecords;
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
