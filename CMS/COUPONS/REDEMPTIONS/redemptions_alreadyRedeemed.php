<?php
/*--------------------------------------------------------------------------------------
    File: redemptions_alreadyRedeemed.php
  Author: Kevin Messina
 Created: Oct. 1, 2017
Modified: Nov. 19, 2018

©2017-2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
2018/11/10 - Updated to current CMS standards.
2018/10/23 - Updated to current CMS formats.
2018/09/03 - Converted to CMS Logging.
2018/06/15 - Migrated to CMS and updated to latest format.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/coupons/redemptions_alreadyRedeemed.php?
couponID=67&
customerID=90&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "COUPONS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$couponID = (string)$_GET["couponID"];
$customerID = (string)$_GET["customerID"];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> couponID: $couponID";
$msg .= "\n|-> customerID: $customerID";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $tableName = $table_redemptions;
    $query =
        "SELECT
            *
        FROM
            $tableName
        WHERE
            coupon_id=$couponID
            AND
            customer_num='$customerID'
        ;
    ";
    $records = fetchFromDB($query,$tableName);
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
