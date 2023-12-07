<?php
/*--------------------------------------------------------------------------------------
     File: coupon_exists.php
   Author: Kevin Messina
  Created: Oct. 02, 2017
 Modified: Nov. 10, 2018

©2017-2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/10 - Updated to current CMS standards.
2018/10/23 - Updated to current CMS formats.
2018/09/04 - Converted to CMS Logging.
2018/06/15 - Migrated to CMS and updated to latest format.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/coupons/coupon_exists.php?
code=TEST_FREEORDER&
calledFromApp=Browser&
debug=1
*/

$version = "1.06b";
$category = "COUPONS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/client-tools/squareframe/scripts/funcs.php");
ini_set("date.timezone", "America/New_York");
date_default_timezone_set("America/New_York");

/* INIT DEFAULTS */
$debug = isset($_GET["debug"]) ? (bool)$_GET["debug"] : false;
if ($debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    echo("!! DEBUGMODE = TRUE<br/><br/>");
}
$scriptName = basename($_SERVER["SCRIPT_NAME"])." v$version";
$calledfromAppVersion = (isset($_GET["appVersion"]) ? $_GET["appVersion"] : "n/a");
$calledfromApp = (isset($_GET["calledFromApp"]) ? $_GET["calledFromApp"] : "n/a")." v$calledfromAppVersion";
$stack = startScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$debug);
$success = false;
$records = null;
$found = (int)0;

/* GET INPUT PARAMS */
$code = (string)$_GET["code"];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> code: $code";
writeMsgToStackAndConsole(__LINE__,$title,$stack,$msg,$debug);

/* INIT PARAMS */
$tableName = (string)"";

/* FUNCTIONS */

/* PROCESSES */
try {
    $tableName = "coupons";
    $query =
        "SELECT
            *
        FROM
            $tableName
        WHERE
            code='$code'
        ;
    ";
    $records = fetchFromDB($query,$stack,$debug,$tableName);
    $found = ($records != null) ?(int)count($records) :0;
    $success = ($found > 0);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($version,$stack,$found,$success,$debug,$records);
endScriptMsg(__LINE__,$scriptName,$debug,$calledfromApp,$category,$stack,$success);

?>
