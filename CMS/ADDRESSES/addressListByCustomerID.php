<?php
/*--------------------------------------------------------------------------------------
    File: addressListByCustomerID.php
  Author: Kevin Messina
 Created: Apr. 28, 2018
Modified: Nov. 17, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/17 - Updated Include files.
2018/11/10 - Updated to current CMS standards.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/addresses/addressListByCustomerID.php?
customerID=90&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "ADDRESSES";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$customerID = (int)$_GET['customerID'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> customerID: $customerID";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */

/* PROCESSES */
try {
    $records = fetchOneFromDB($table_addresses,"customerID=$customerID","lastName ASC,firstName ASC");
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
