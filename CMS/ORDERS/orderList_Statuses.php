<?php
/*--------------------------------------------------------------------------------------
    File: orderList_Statuses.php
  Author: Kevin Messina
 Created: Mar. 21, 2018
Modified: Nov. 28, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/28 - Updated to latest Includes.
2018/09/04 - Converted to CMS Logging.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/orders/orderList_Statuses.php?
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "ORDERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $query = "SELECT statusID,COUNT(*) as count FROM orders GROUP BY statusID ORDER BY statusID ASC;";
    $records = fetchFromDB($query,$table_orders);
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
$records = convertArrayFromNullToEmptyArray($records);
echo json_encode([
    "version" => $version,
    "stack" => $stack,
    "found" => $found,
    "success" => $success,
    "debug" => $debug,
    "records" => $records
]);

endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
