<?php
/*--------------------------------------------------------------------------------------
    File: updateOrderStatus.php
  Author: Kevin Messina
 Created: Mar. 10, 2018
Modified: Nov. 26, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/26 - Updated to latest Includes.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/orders/updateOrderStatus.php?
status=Cancelled&
orderNums=2385&
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "ORDERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$status = (string)$_GET['status'];
$orderNums = (string)$_GET['orderNums'];
$orderIDs = explode(',',$orderNums);

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> status: $status";
$msg .= "\n|-> orderNums: $orderNums";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();
$errOccurred = false;

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);

    /* PERFORM QUERY */
    foreach($orderIDs as $orderID) {
        $success = $db->updateID($orderID,"statusID='$status'");

        if ($success == false) {
            $errOccurred = true;
        }

        writeToStackAndConsole(__LINE__,"Order: ".$orderID." change to statusID: ".$status,($success ?"Succeeded." :"Failed"));
    }
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
$found = is_array($orderIDs) ?count($orderIDs) :0;
$success = (int)($errOccurred == false);

echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
