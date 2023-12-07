<?php
/*-------------------------------------------------------------------
    File: getAppImagesInfo.php
  Author: Kevin Messina
 Created: Apr. 06, 2018
Modified: Nov. 23, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
---------------------------------------------------------------------
NOTES:

2018/11/23 - Updated to current CMS standards.
2018/10/10 - Updated to latest CMS Standards.
-------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/appImages/getAppImagesInfo.php?
appVersion=2.11.02&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "APP IMAGES";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $records = fetchOneFromDB($table_sfAppImages);
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
