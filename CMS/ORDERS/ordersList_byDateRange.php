<?php
/*--------------------------------------------------------------------------------------
     File: ordersList_byDateRange.php
   Author: Kevin Messina
  Created: Mar. 10, 2018
 Modified: Jun. 10, 2018

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:
--------------------------------------------------------------------------------------*/

$version = "2.01A";
$category = "ORDERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$date = (string)$_GET['fromDate'];
$month = $date[0].$date[1];
$day = $date[3].$date[4];
$year = $date[6].$date[7].$date[8].$date[9];
$fromDate = $year."/".$month."/".$day;

$date = (string)$_GET['toDate'];
$month = $date[0].$date[1];
$day = $date[3].$date[4];
$year = $date[6].$date[7].$date[8].$date[9];
$toDate = $year."/".$month."/".$day;

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> fromDate: $fromDate";
$msg .= "\n|-> toDate: $toDate";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);

    $records = $db->fetchRecordsWhere(
        "orderNum>=100 AND (CAST(orderDate AS DATE) BETWEEN CAST('$fromDate' AS DATE) AND CAST('$toDate' AS DATE))",
        "statusID ASC"
    );

    $found = $db->numRecords;
    $success = $db->hasRecords;
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
