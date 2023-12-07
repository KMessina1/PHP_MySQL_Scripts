<?php
/*-------------------------------------------------------------------
    File: services_updateActive.php
  Author: Kevin Messina
 Created: Jun. 15, 2018
Modified: Nov. 17, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
---------------------------------------------------------------------
NOTES:

2018/11/17 - Updated Include files.
2018/09_27 - Updated to latest CMS syntax.
2018/09/03 - Converted to CMS Logging.
2018/07/17 - Added message parameters to update.
-------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/services/services_updateActive.php?
active=0&
msg_eng=English&
msg_esp=Spanish&
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "CMS SERVICES";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$active = (int)$_GET["active"];
$msg_eng = (string)$_GET["msg_eng"];
$msg_esp = (string)$_GET["msg_esp"];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> active: $active";
$msg .= "\n|-> msg_eng: $msg_eng";
$msg .= "\n|-> msg_esp: $msg_esp";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $tableName = $table_servicesAvailable;
    $query =
        "UPDATE
            $tableName
        SET
            active=$active,
            unavailableMsg_eng='$msg_eng',
            unavailableMsg_esp='$msg_esp'
        WHERE
            id=1
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
