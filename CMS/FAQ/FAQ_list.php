<?php
/*-------------------------------------------------------------------
    File: FAQ_list.php
  Author: Kevin Messina
 Created: APR 3, 2018
Modified: Nov. 17, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
---------------------------------------------------------------------
NOTES:

2018/11/17 - Updated Include files.
2018/09/26 - Updated to latest CMS syntax.
-------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/faq/FAQ_list.php?
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "FAQ";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $records = fetchAllFromDB($table_faq,"","displayOrder ASC");
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
