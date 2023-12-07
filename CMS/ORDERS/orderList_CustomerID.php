<?php
/*--------------------------------------------------------------------------------------
    File: orderList_CustomerID.php
  Author: Kevin Messina
 Created: Apr. 04, 2018
Modified: Nov. 19, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/orders/orderList_CustomerID.php?
customerID=90&
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "ORDERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$id = (int)$_GET['customerID'];

/* INIT PARAMS */
$tableName = (string)"";
$shippingAddresses = null;

/* FUNCTIONS */

/* PROCESSES */
try {
    $tableName = "orders";
    $query =
        "SELECT
            *
        FROM
            $tableName
        WHERE
            customerID=$id
        ORDER BY
            orderDate DESC
        ;
    ";
    $records = fetchFromDB($query,$tableName);
    $found = ($records != null) ?(int)count($records) :0;
    $success = ($found > 0);

    /* Get Customer Shipping Addresses */
    if ($success) {
        foreach ($records as $record) {
            $shipToID = (int)$record["shipToID"];
            $orderNum = (int)$record["orderNum"];
            $shipToRecords = null;

            /* PERFORM QUERY: Shipping Addresses */
            $tableName = "addresses";
            $query =
                "SELECT
                    *
                FROM
                    $tableName
                WHERE
                    id=$shipToID
                LIMIT 1
                ;
            ";
            $addresses = fetchFromDB($query,$tableName);
            $found = ($addresses != null) ?(int)count($addresses) :0;
            $success = ($found > 0);

            if ($success > 0) {
                $shippingAddresses[] = $addresses[0];
            }
        }
    }else{
        logExceptionError("No Orders found.",$debug);
    }
} catch (Exception $e) {
    logExceptionError($e->getMessage(),$debug);
}

/* RETURN RESULTS */
$shippingAddresses = convertArrayFromNullToEmptyArray($shippingAddresses);
$found = ($shippingAddresses != null) ?(int)count($shippingAddresses) :0;
$success = ($found > 0);

if ($debug) {
    outputRecordsToConsole($shippingAddresses);
    echo (setBodyColor('black',"<br>"));
    echo (setTitleColor('rebeccapurple',"** Script finished all functions, next step is to return results. If no results follow, error has happened. **"));
    echo (setBodyColor('black',"<br>"));
    outputResultsToConsole($found,$success,$records);
}

$itemsToEncode = ["shipToRecords" => $shippingAddresses];
echo my_json_encodeCustom($found,$success,$records,$itemsToEncode);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
