<?php
/*--------------------------------------------------------------------------------------
    File: echoFolderPath.php
  Author: Kevin Messina
 Created: Mar. 30, 2018
Modified: Nov. 17, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/17 - Updated Include files.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/files/echoFolderPath.php?
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "FILES";

/* FUNCTIONS */
$logFilename = $_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/Logs/SF-Admin_Log.txt";
echo "LOG FILE: <br/><br/>$logFilename ".(file_exists($logFilename) ?"<br/><br/>Exists" :"<br/><br/>Does NOT Exist")." on server.";

?>
