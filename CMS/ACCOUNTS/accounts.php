<?php
/*--------------------------------------------------------------------------------------
    File: accounts.php
  Author: Kevin Messina
 Created: Feb. 16, 2018
Modified: Nov. 19, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
2018/09/03 - Converted to CMS Logging.
--------------------------------------------------------------------------------------*/

/*
http://sqframe.com/client-tools/squareframe/scripts/cms/accounts/accounts.php?
name=ADMIN PANEL&
passcode=abc&
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "ACCOUNTS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$name = (string)$_GET["name"];
$passcode = (string)$_GET["passcode"];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> name: $name";
$msg .= "\n|-> passcode: $passcode";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $records = fetchAllFromDB($table_accounts,"name='$name' AND passcode=BINARY'$passcode'");
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
