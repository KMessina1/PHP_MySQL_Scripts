<?php
/*-------------------------------------------------------------------
    File: getOrderFiles.php
  Author: Kevin Messina
 Created: Nov. 14, 2016
Modified: Nov. 28, 2018

©2016-2018 Creative App Solutions, LLC. - All Rights Reserved.
---------------------------------------------------------------------
NOTES:

2018/11/28 - Updated to latest Includes.
-------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/orders/getOrderFiles.php?
orderNum=2411&
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
$orderNum = $_GET['orderNum'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> orderNum: $orderNum";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

$dir = getOrdersPath()."Order_$orderNum/";
if (!file_exists($dir)) {
    $stack[] = "Folder does not exist";
}

array_push($stack,$dashes,"Getting all files in directory...");
$files = array_diff(scandir($dir, SCANDIR_SORT_ASCENDING), array('..', '.'));

$filesAndDates = array();
foreach($files as $file) {
    $date = date('m/d/Y',filemtime($dir . '/' . $file));
    $filesAndDates[] = ["fileName" => $file,"modifiedDate" => $date];
}

$found = (int)count($filesAndDates);
$success = (bool)($found > 0);

/* RETURN RESULTS */
$filesAndDates = convertArrayFromNullToEmptyArray($filesAndDates);

$itemsToEncode = [
    "filesAndDates" => $filesAndDates
];
echo my_json_encodeCustom($found,$success,$records,$itemsToEncode);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
