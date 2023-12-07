<?php
/*-------------------------------------------------------------------
    File: folderAndfileNameExists.php
  Author: Kevin Messina
 Created: Apr. 5, 2018
Modified: Nov. 17, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
---------------------------------------------------------------------
NOTES:

2018/11/17 - Updated Include files.
-------------------------------------------------------------------*/

/* URL Test
http://sqframe.com/client-tools/squareframe/scripts/cms/files/folderAndfileNameExists.php?
folderAndfileName=assets/appImages/homeScreen.jpg&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "FILES";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* INIT DEFAULTS */
clearstatcache();

/* INIT PARAMS */
initDefaults();

/* GET INPUT PARAMS */
$folderAndfileName = (string)$_GET['folderAndfileName'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> folderAndfileName: $folderAndfileName";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* FILE EXIST? */
$dir = $_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/".$folderAndfileName;
$success = file_exists($dir);
$found = (int)$success;

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
