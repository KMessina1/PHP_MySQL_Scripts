<?php
/*--------------------------------------------------------------------------------------
    File: frameProductUpdate.php
  Author: Kevin Messina
 Created: Feb. 18, 2018
Modified: Nov. 08, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/08 - Updated to current CMS standards.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/components/frameProductUpdate.php?
id=76&
SKU=TESTUPDATE&
name=TESTUPDATE&
description=TESTUPDATE&
photoSize=9x9&
frameShape=SQ&
frameColor=NA&
frameSize=9X9-8.13X8.13&
frameStyle=ST&
matteColor=WH&
frameMaterial=WD&
cost=99.99&
price=99.99&
taxable=1&
reorder=1&
qty=1&
legacy=0&
image=&
moltinID=&
moltinSKU=&
active=0&
notes=test&
calledFromApp=Browser&
debug=1
*/

$version = "1.01a";
$category = "PRODUCTS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/client-tools/squareframe/scripts/funcs.php");
ini_set("date.timezone", "America/New_York");
date_default_timezone_set("America/New_York");

/* INIT DEFAULTS */
$debug = isset($_GET["debug"]) ? (bool)$_GET["debug"] : false;
if ($debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    echo("!! DEBUGMODE = TRUE<br/><br/>");
}
$scriptName = basename($_SERVER["SCRIPT_NAME"])." v$version";
$calledfromAppVersion = (isset($_GET["appVersion"]) ? $_GET["appVersion"] : "n/a");
$calledfromApp = (isset($_GET["calledFromApp"]) ? $_GET["calledFromApp"] : "n/a")." v$calledfromAppVersion";
$stack = startScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$debug);
$success = false;
$records = null;
$found = (int)0;

/* GET INPUT PARAMS */
$id = (int)$_GET['id'];
$sku = (string)$_GET['SKU'];
$name = (string)$_GET['name'];
$description = (string)$_GET['description'];
$photoSize = (string)$_GET['photoSize'];
$frameShape = (string)$_GET['frameShape'];
$frameColor = (string)$_GET['frameColor'];
$frameSize = (string)$_GET['frameSize'];
$frameStyle = (string)$_GET['frameStyle'];
$matteColor = (string)$_GET['matteColor'];
$frameMaterial = (string)$_GET['frameMaterial'];
$cost = (float)$_GET['cost'];
$price = (float)$_GET['price'];
$taxable = (int)$_GET['taxable'];
$reorder = (int)$_GET['reorder'];
$qty = (int)$_GET['qty'];
$legacy = (int)$_GET['legacy'];
$image = (string)$_GET['image'];
$moltinID = (string)$_GET['moltinID'];
$moltinSKU = (string)$_GET['moltinSKU'];
$active = (int)$_GET['active'];
$notes = (string)$_GET['notes'];

/* INIT PARAMS */
$tableName = (string)"";

/* FUNCTIONS */

/* PROCESSES */
try {
    $tableName = "products";
    $query =
        "UPDATE
            $tableName
        SET
            SKU='$sku',
            name='$name',
            description='$description',
            photoSize='$photoSize',
            frameShape='$frameShape',
            frameColor='$frameColor',
            frameSize='$frameSize',
            frameStyle='$frameStyle',
            matteColor='$matteColor',
            frameMaterial='$frameMaterial',
            cost=$cost,
            price=$price,
            taxable=$taxable,
            reorder=$reorder,
            qty=$qty,
            legacy=$legacy,
            image='$image',
            moltinID='$moltinID',
            moltinSKU='$moltinSKU',
            active=$active,
            notes='$notes'
        WHERE
            id=$id
        ;";
    $success = executeDB($query,$stack,$debug,$tableName,$dbAction_Updte);
    $found = (int)$success;
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($version,$stack,$found,$success,$debug,$records);
endScriptMsg(__LINE__,$scriptName,$debug,$calledfromApp,$category,$stack,$success);

?>
