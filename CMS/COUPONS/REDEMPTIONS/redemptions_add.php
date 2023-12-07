<?php
/*--------------------------------------------------------------------------------------
    File: redemptions_add.php
  Author: Kevin Messina
 Created: Oct. 13, 2017
Modified: Nov. 19, 2018

©2017-2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
2018/11/10 - Updated to current CMS standards.
2018/09/03 - Converted to CMS Logging.
2018/06/29 - Migrated to CMS and updated to latest format.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/coupons/redemptions_add.php?
date=2018-11-19&
coupon_code=TEST_FREEORDER&
coupon_id=67&
coupon_value=99.99&
order_num=999999&
customer_name=Kevin%20Messina&
customer_num=90&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "COUPONS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$date = (string)$_GET["date"];
$coupon_code = (string)$_GET["coupon_code"];
$coupon_id = (int)$_GET["coupon_id"];
$coupon_value = (float)$_GET["coupon_value"];
$order_num = (string)$_GET["order_num"];
$customer_name = (string)$_GET["customer_name"];
$customer_num = (string)$_GET["customer_num"];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> date: $date";
$msg .= "\n|-> coupon_code: $coupon_code";
$msg .= "\n|-> coupon_id: $coupon_id";
$msg .= "\n|-> coupon_value: $coupon_value";
$msg .= "\n|-> order_num: $order_num";
$msg .= "\n|-> customer_name: $customer_name";
$msg .= "\n|-> customer_num: $customer_num";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
$redeemed = (int)0;
$remaining = (int)0;
$errorOccurred = false;
$success_Insert = false;
$success_Search = false;
$success_Update = false;

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $tableName = $table_redemptions;
    $query =
        "INSERT INTO
            $tableName
        VALUES (
            NULL,
            '$date',
            '$coupon_code',
            $coupon_id,
            $coupon_value,
            '$order_num',
            '$customer_name',
            '$customer_num'
        )
    ";
    $success_Insert = executeDB($query,$tableName,$dbAction_Insert);

    /* GET & UPDATE COUPON */
    if ($success_Insert) {
        // Get Coupon
        $records = fetchByIDFromDB($table_coupons,$coupon_id);
        $found = returnCountFrom($records);
        $success_Search = returnSuccessFromCount($found);

        // Update Coupon Totals
         if ($success_Search) {
            $coupon = $records[0];
            $redeemed = (int)$coupon["redeemed_qty"];
            $remaining = (int)$coupon["remaining_qty"];

            /* Adjust Coupon quantity fields */
            $redeemed = $redeemed + 1;
            $remaining = $remaining - 1;

            /* PERFORM QUERY: Update Coupon */
            $tableName = $table_coupons;
            $query =
                "UPDATE
                    $tableName
                SET
                    redeemed_qty=$redeemed,
                    remaining_qty=$remaining
                WHERE
                    id=$coupon_id
                ;
            ";
            $success_Update = executeDB($query,$tableName,$dbAction_Update);
        }else{
            logExceptionError("Error occurred searching for Coupon.");
            $errorOccurred = true;
        }
    }else{
        logExceptionError("Error Inserting Redemption entry.");
        $errorOccurred = true;
    }

    $title = "Coupon Redemption for couponID: $coupon_id ";
    $msg = $title.($errorOccurred ?"Failed." :"Succeeded.");
    writeMsgToStackAndConsole(__LINE__,$title,$msg);
    ($errorOccurred) ?logFailure($msg) :logSuccess($msg);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
$frameColors = convertArrayFromNullToEmptyArray($frameColors);
$frameStyles = convertArrayFromNullToEmptyArray($frameStyles);


echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
