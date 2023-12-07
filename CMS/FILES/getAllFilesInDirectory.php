<?php
/*-------------------------------------------------------------------
    File: getAllFilesInDirectory.php
  Author: Kevin Messina
 Created: Nov. 14, 2016
Modified: Nov. 28, 2018

©2016-2018 Creative App Solutions, LLC. - All Rights Reserved.
---------------------------------------------------------------------
NOTES:

2018/11/28 - Updated to latest Includes.
2018/10/23 - Updated to current CMS standards.
-------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/files/getAllFilesInDirectory.php?
folderAndfileName=assets/frameGallery&
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
$folderAndfileName = (string)$_GET['folderAndfileName'];
if (!isset($folderAndfileName)) { $folderAndfileName = "Orders/"; }

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> folderAndfileName: $folderAndfileName";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* BUILD FILE PATH */
$dir = $_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/".$folderAndfileName;
writeToStackAndConsole(__LINE__,"FOLDER:",$dir);

$success = file_exists($dir);

if ($success == false) {
    writeToStackAndConsole(__LINE__,"FOLDER:","Folder does not exist: ".$dir);
}else{
    /* Get all files in folder */
    writeToStackAndConsole(__LINE__,"FOLDER:","--> Getting all files in folder...");
    $files = array_diff(scandir($dir, SCANDIR_SORT_ASCENDING), array('..', '.'));
    $filesAndDates = array();

    foreach($files as $file) { // NOTES: filectime = creation, filemtime = modified
        $date = date('Y-m-d H:i:s e',filemtime($dir . '/' . $file)); // MODIFIED DATE
        // $date = date('Y/m/d',filemtime($dir . '/' . $file)); // CREATED DATE
        $filesAndDates[] = ["fileName" => $file,"modifiedDate" => $date];
        writeToStackAndConsole(__LINE__,"FOLDER:","--> --> FILE IN FOLDER: $file dated: $date");
    }

    $found = returnCountFrom($filesAndDates);
    $success = returnSuccessFromCount($found);
    ($success) ?logSuccess('Found: '.$found.' files.') :logFailure('FAILED!! Found: '.$found.' files.');
}

/* RETURN RESULTS */
$files = convertArrayFromNullToEmptyArray($files);
$filesAndDates = convertArrayFromNullToEmptyArray($filesAndDates);

$records = NULL;
$success = (int)($files != null && $filesAndDates != null);
$found = (int)$success;

$itemsToEncode = [
    "files" => $files,
    "filesAndDates" => $filesAndDates
];
echo my_json_encodeCustom($found,$success,$records,$itemsToEncode);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
