<?php
/*--------------------------------------------------------------------------------------
    File: coupons_search.php
  Author: Kevin Messina
 Created: Oct. 07, 2017
Modified: Nov. 1-, 2018

©2017-2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
2018/11/10 - Updated to current CMS standards.
2018/10/23 - Updated to current CMS formats.
2018/09/04 - Converted to CMS Logging.
2018/06/15 - Migrated to CMS and updated to latest format.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/coupons/coupons_search.php?
code=TEST_FREEORDER&
id=67&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "COUPONS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$code = (string)$_GET["code"];
$id = (int)$_GET["id"];

if ($id == null) { $id = 0; }

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> code: $code";
$msg .= "\n|-> id: $id";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $tableName = $table_coupons;
    $lookFor = ($id > 0) ?"id=$id" :"code='$code'";
    $query =
        "SELECT
            *
        FROM
            $tableName
        WHERE
            $lookFor
        LIMIT 1
        ;
    ";
    $records = fetchFromDB($query,$tableName);
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
