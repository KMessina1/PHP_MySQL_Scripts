<?php
/*--------------------------------------------------------------------------------------
    File: mailingList_list.php
  Author: Kevin Messina
 Created: Mar. 08, 2018
Modified: Nov. 23, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/23 - Updated to current CMS standards.
2018/09/04 - Converted to CMS Logging.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/mailingList/mailingList_list.php?
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "1.02a";
$category = "CUSTOMERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_customers);

    $records = $db->fetchRecordsWhere("mailingList=1","lastName ASC,FirstName ASC");
    $found = $db->numRecords;
    $success = $db->hasRecords;
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
