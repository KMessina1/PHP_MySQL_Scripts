<?php
/*--------------------------------------------------------------------------------------
    File: ordersList_byOrderNum.php
  Author: Kevin Messina
 Created: Mar. 10, 2018
Modified: NOV. 26, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/26 - Updated to latest Includes.
2018/09/28 - Updated to latest CMS syntax.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/orders/ordersList_byOrderNum.php?
orderNum=2269&
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01A";
$category = "ORDERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$orderNum = (string)$_GET['orderNum'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> orderNum: $orderNum";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);

    $records = $db->fetchRecordsWhere("orderNum='$orderNum'");
    $found = $db->numRecords;
    $success = $db->hasRecords;
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
