<?php
/*--------------------------------------------------------------------------------------
       File: coupon_delete.php
     Author: Kevin Messina
    Created: Sep. 30, 2017
ModModified: Nov. 19, 2018

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
http://sqframe.com/client-tools/squareframe/scripts/cms/coupons/coupon_delete.php?
id=115&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "COUPONS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$id = (int)$_GET["id"];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> id: $id";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $success = deleteIDfromDB($table_coupons,$id);
    $found = (int)$success;
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
