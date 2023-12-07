<?php
/*--------------------------------------------------------------------------------------
 File: frameProductList.php
  Author: Kevin Messina
 Created: Apr 03, 2018
Modified: Nov. 07, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/07 - Updated to current CMS standards.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/products/frameProductList.php?
SKU=A-4x4-5.8x5.8-NA-SQ-SL-WH-WD&
calledFromApp=Browser&
debug=1
*/

$version = "1.04a";
$category = "PRODUCTS";

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
$tableName = (string)"";

/* GET INPUT PARAMS */
$sku = (string)$_GET['SKU'];

/* INIT PARAMS */

/* FUNCTIONS */

/* PROCESSES */
try {
    /* PERFORM QUERY */
    $tableName = (string)"products";
    $query = "SELECT * FROM $tableName WHERE legacy=0 ORDER BY CAST(SKU AS UNSIGNED) ASC;";
    $records = fetchFromDB($query,$stack,$debug,$tableName);
    $found = count($records);
    $success = ($found > 0);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($version,$stack,$found,$success,$debug,$records);
endScriptMsg(__LINE__,$scriptName,$debug,$calledfromApp,$category,$stack,$success);

?>
