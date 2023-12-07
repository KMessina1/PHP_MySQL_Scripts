<?php
/*--------------------------------------------------------------------------------------
     File: coupon_update.php
   Author: Kevin Messina
  Created: Oct. 2, 2017
 Modified: Nov. 19, 2018

©2017-2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
2018/11/10 - Updated to current CMS standards.
2018/10/23 - Updated to current CMS formats.
2018/09/04 - Converted to CMS Logging.
2018/07/16 - Updated to PhP variable assignments to input parameters.
           - Added the return of the updated record.
2018/06/15 - Migrated to CMS and updated to latest format.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/coupons/coupon_update.php?
id=115&
created_date=2018-10-31&
code=ABC&
name=TESTER&
description=TESTER&
limit_qty=100&
redeemed_qty=0&
remaining_qty=100&
effective_date=2018-09-03&
expiration_date=2019-09-03&
discount=100&
type=free&
scope=entire%20order&
limit_one_per=0&
status=1&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "COUPONS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$id = (int)$_GET["id"];
$code = (string)$_GET["code"];
$name = (string)$_GET["name"];
$description = (string)$_GET["description"];
$limit_qty = (int)$_GET["limit_qty"];
$redeemed_qty = (int)$_GET["redeemed_qty"];
$remaining_qty = (int)$_GET["remaining_qty"];
$effective_date = (string)$_GET["effective_date"];
$expiration_date = (string)$_GET["expiration_date"];
$discount = (int)$_GET["discount"];
$type = (string)$_GET["type"];
$scope = (string)$_GET["scope"];
$limit_one_per = (int)$_GET["limit_one_per"];
$status = (int)$_GET["status"];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> id: $id";
$msg .= "\n|-> code: $code";
$msg .= "\n|-> name: $name";
$msg .= "\n|-> description: $description";
$msg .= "\n|-> limit_qty: $limit_qty";
$msg .= "\n|-> redeemed_qty: $redeemed_qty";
$msg .= "\n|-> remaining_qty: $remaining_qty";
$msg .= "\n|-> effective_date: $effective_date";
$msg .= "\n|-> expiration_date: $expiration_date";
$msg .= "\n|-> discount: $discount";
$msg .= "\n|-> type: $type";
$msg .= "\n|-> scope: $scope";
$msg .= "\n|-> limit_one_per: $limit_one_per";
$msg .= "\n|-> status: $status";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $tableName = $table_coupons;
    $query =
        "UPDATE
            $tableName
        SET
            code = '$code',
            name = '$name',
            description = '$description',
            limit_qty = $limit_qty,
            redeemed_qty = $redeemed_qty,
            remaining_qty = $remaining_qty,
            effective_date = '$effective_date',
            expiration_date = '$expiration_date',
            discount = $discount,
            type = '$type',
            scope = '$scope',
            limit_one_per = $limit_one_per,
            status = $status
        WHERE
            id=$id
        ;
    ";
    $success = executeDB($query,$tableName,$dbAction_Update);
    $found = (int)$success;
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
