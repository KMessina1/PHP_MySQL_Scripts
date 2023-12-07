<?php
/*---------------------------------------------------------------------------------------------------------------------
    File: moveUploadedOrderFile.php
  Author: Kevin Messina
 Created: Nov. 11, 2016
Modified: Nov. 17, 2018

©2016-2018 Creative App Solutions, LLC. - All Rights Reserved.
-----------------------------------------------------------------------------------------------------------------------
NOTES:

2018/11/17 - Updated Include files.
---------------------------------------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/orders/moveUploadedOrderFile.php?
$folderName&
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "FILES";

/* Clear stored values in cache */
clearstatcache();

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$folderName = (string)$_GET["folderName"];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> folderName: $folderName";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* BUILD FILE PATH */
$uploadsDir = $_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/uploads/";
$ordersDir = $_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/Orders/";
$orders_dir = $ordersDir.$folderName;
array_push($stack,'Line#'.__LINE__.' Uploads FilePath: '.$uploads_dir);
array_push($stack,'Line#'.__LINE__.' Orders FilePath: '.$orders_dir);
logSuccess(' Uploads FilePath: '.$uploads_dir.', Orders FilePath: '.$orders_dir);

/* IF NOT EXIST, CREATE FOLDER */
$msg = ' Folder '.$folderName;
if (!file_exists($uploads_dir)) {
    array_push($stack,'Line#'.__LINE__.$msg.' does not exist, creating...');
    $success = mkdir($uploads_dir, 0777, true);
    ($success) ?logSuccess($msg.' created.') :logFailure($msg.' NOT created.');
}else{
    array_push($stack,'Line#'.__LINE__.$msg.' already exists, bypassing create.');
    logSuccess($msg);
}

/* Get Filename and filepathURL */
$uploadFile = basename($_FILES["uploadFile"]["name"]);
$orders_dir = $orders_dir.'/'.$uploadFile;
$fileTempName = $_FILES["uploadFile"]["tmp_name"];
array_push($stack,'Line#'.__LINE__.' uploadFile: '.$uploadFile);
array_push($stack,'Line#'.__LINE__.' uploads_dir: '.$uploads_dir);
array_push($stack,'Line#'.__LINE__.' fileTempName: '.$fileTempName);

/* Move completed upload files to target directory */
$success = move_uploaded_file($fileTempName, $orders_dir);
$msg = ' File: '.$fileName;
$msg = ($success) ?$msg.' Moved to '.$orders_dir :$msg.' NOT Moved to '.$orders_dir;
($success) ?logSuccess($msg) :logFailure($msg);
array_push($stack,'Line#'.__LINE__.$msg);

/* RETURN RESULTS */
$found = ($success) ?1 :0;
$records == null;
$status = ($success) ?'OK' :'Error';

$itemsToEncode = [
    "message" => $msg,
    "Status" => $status
];
echo my_json_encodeCustom($found,$success,$records,$itemsToEncode);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
