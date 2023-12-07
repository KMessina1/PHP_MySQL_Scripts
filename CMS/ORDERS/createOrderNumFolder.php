<?php
/*-------------------------------------------------------------------
    File: createOrderNumFolder.php
  Author: Kevin Messina
 Created: Oct. 21, 2016
Modified: Jun. 28, 2018

©2016-2018 Creative App Solutions, LLC. - All Rights Reserved.
---------------------------------------------------------------------
NOTES:
-------------------------------------------------------------------*/

/* URL Test
http://sqframe.com/client-tools/squareframe/scripts/cms/orders/createOrderNumFolder.php?
calledFromApp=SF-App&
orderNum=1
*/

$version = "1.02a";

/* INITIALIZE */
require_once($_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/scripts/funcs.php");
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');

/* INIT DEFAULTS */
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$timestamp = getServerDateTime();
$stack = array('Line#'.__LINE__.' *** Script '.$scriptName.' started @ '.$timestamp.' ***',$dashes);
$success = false;
$records = null;
$found = (int)0;

/* GET INPUT PARAMS */
$orderNum = (string)$_GET['orderNum'];

/* BUILD FILE PATH */
$dir = $_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/Orders/";
$fileName = 'Order_'.$orderNum;
$filePath = $dir.$fileName;
array_push($stack,'Line#'.__LINE__.' FilePath: '.$filePath);

/* IF NOT EXIST, CREATE FOLDER */
$msg = ' Folder '.$folderName;
if (!file_exists($filePath)) {
    array_push($stack,'Line#'.__LINE__.$msg.' does not exist, creating...');

    $success = (mkdir($filePath, 0777, true));
    ($success)?logSuccess($msg.' created.') :logFailure($msg.' NOT created.');
}else{
    array_push($stack,'Line#'.__LINE__.$msg.' already exists, bypassing create.');
    logSuccess($msg);
}

/* PROCESS RESULTS */
$found = ($success) ?1 :0;
$records == null;

if ($records == null) { $records = array(); }
echo json_encode([
    "version" => $version,
    "stack" => $stack,
    "found" => $found,
    "success" => $success,
    "records" => $records
]);

?>
