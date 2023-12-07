<?php
/*-------------------------------------------------------------------
    File: getFrameGalleryInfo.php
  Author: Kevin Messina
 Created: Apr. 06, 2018
Modified: Nov. 17, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
---------------------------------------------------------------------
NOTES:

2018/11/17 - Updated Include files.
-------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/frameGallery/getFrameGalleryInfo.php?
appVersion=2.11.02&
calledFromApp=SF-Admin&
debug=1
*/

$version = "2.01a";
$category = "PRODUCTS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $records = fetchAllFromDB($table_sfFrameGallery);
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
