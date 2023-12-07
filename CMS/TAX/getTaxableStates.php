<?php
/*--------------------------------------------------------------------------------------
    File: getTaxableStates.php
  Author: Kevin Messina
 Created: Apr. 18, 2018
Modified: Nov. 19, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
2018/11/17 - Updated to current CMS standards.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/tax/getTaxableStates.php?
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.02a";
$category = "TAXES";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */

/* INIT PARAMS */

/* FUNCTIONS */

/* PROCESSES */
try {
    $records = fetchAllFromDB($table_taxableStates);
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
