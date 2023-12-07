<?php
/*--------------------------------------------------------------------------------------
    File: customer_updateMailingList.php
  Author: Kevin Messina
 Created: Feb. 27, 2018
Modified: Dec. 14, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/12/14 - Updated latest standards.
2018/11/17 - Updated Include files.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/customers/customer_updateMailingList.php?
id=90&
mailingList=0&;
appVersion=99.99.99&
calledFromApp=Browser&
debug=1
*/

$version = "2.01b";
$category = "CUSTOMERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$id = (int)$_GET['id'];
$mailingList = (int)$_GET['mailingList'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> id: $id";
$msg .= "\n|-> mailingList: $mailingList";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_customers);

    $success = $db->updateID($id,"mailingList=$mailingList");
    $found = (int)$success;
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
