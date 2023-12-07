<?php
/*--------------------------------------------------------------------------------------
     File: configure.php
   Author: Kevin Messina
  Created: Nov. 17, 2018
 Modified: Nov. 19, 2018

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Added funcs_DB.php include.
--------------------------------------------------------------------------------------*/

$configScriptVersion = "1.01a";

/* INCLUDES */
// CAS Functions
require_once($_SERVER["DOCUMENT_ROOT"]."/client-tools/squareframe/scripts/funcs.php");
// CAS Database Functions
require_once($_SERVER["DOCUMENT_ROOT"]."/client-tools/squareframe/scripts/funcs_DB.php");
// CMS Class
require_once($_SERVER["DOCUMENT_ROOT"]."/client-tools/squareframe/scripts/CMS_Class.php");
// CAS Third Party Libraries
require_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

/* TEST debug MODE */
$debug = isset($_GET["debug"]) ? (bool)$_GET["debug"] : false;
if ($debug) {
    echo "<div style='font-size:2.50em;color:white;background-color:red;'>!! debug = TRUE</div><br/><br>";
}

/* CONFIGURE RUNTIME ENVIRONMENT */
ini_set("date.timezone", "America/New_York");
date_default_timezone_set("America/New_York");
if ($debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

/* INIT DEFAULTS */
$scriptNameWithoutVersion = basename($_SERVER["SCRIPT_NAME"]);
$scriptName = basename($_SERVER["SCRIPT_NAME"])." v$version";
$calledfromAppVersion = (isset($_GET["appVersion"]) ? $_GET["appVersion"] : "n/a");
$calledfromAppName = (isset($_GET["calledFromApp"]) ? $_GET["calledFromApp"] : "n/a");
$calledfromApp = "$calledfromAppName v$calledfromAppVersion";
$stack = startScriptMsg(__LINE__,$scriptName,$calledfromApp,$category);
$success = false;
$records = null;
$found = (int)0;
$tableName = (string)"";

function initDefaults(){
    global $success,$records,$found,$tableName;

    $success = false;
    $records = null;
    $found = 0;
    $tableName = "";
}
