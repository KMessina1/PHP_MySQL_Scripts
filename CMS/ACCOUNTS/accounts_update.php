<?php
/*--------------------------------------------------------------------------------------
    File: accounts_update.php
  Author: Kevin Messina
 Created: Feb. 12, 2018
Modified: Nov. 19, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
2018/09/03 - Converted to CMS Logging.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/accounts/accounts_update.php?
id=1&
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
$id = (int)$_GET["id"];
$passcode = (string)$_GET["passcode"];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> id: $id";
$msg .= "\n|-> passcode: $passcode";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $tableName = $table_accounts;
    $query =
        "UPDATE
            $tableName
        SET
            passcode='$passcode'
        WHERE
            id=$id
        ;
    ";
    $success = executeDB($query,$tableName,$dbAction_Update);
    $found = (int)$success;
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
