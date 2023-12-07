<?php
/*--------------------------------------------------------------------------------------
 File: frameProductInStock.php
  Author: Kevin Messina
 Created: Apr. 16, 2018
Modified: Nov. 17, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/17 - Updated Include files.
2018/11/07 - Updated to current CMS standards.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/products/frameProductInStock.php?
SKU=A-4x4-5.8x5.8-NA-SQ-SL-WH-WD&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "PRODUCTS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$SKU = (string)$_GET['SKU'];

/* INIT PARAMS */
$tableName = (string)"";

/* FUNCTIONS */

/* PROCESSES */
try {
    $tableName = $table_products;
    $query =
        "SELECT
            *
        FROM
            $tableName
        WHERE
            sku='$SKU'
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
