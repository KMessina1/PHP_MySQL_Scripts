<?php
/*--------------------------------------------------------------------------------------
    File: ordersList.php
  Author: Kevin Messina
 Created: Mar. 10, 2018
Modified: Nov. 28, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/28 - Update to latest Includes.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/orders/ordersList.php?
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01A";
$category = "ORDERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);
    $records = $db->fetchRecordsWhere("","orderDate DESC");
    $found = $db->numRecords;
    $success = $db->hasRecords;
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
