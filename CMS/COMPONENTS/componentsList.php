<?php
/*--------------------------------------------------------------------------------------
 File: componentsList.php
  Author: Kevin Messina
 Created: Feb. 17, 2018
Modified: Nov. 17, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/17 - Updated Include files.
2018/11/03 - Updated to current CMS standards.
-------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/components/componentsList.php?
appVersion=2.12.01&
calledFromApp=Browser&
debug=1
*/

$version = "2.01a ";
$category = "PRODUCTS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */

/* INIT PARAMS */
$tableName = (string)"";
$frameColors = null;
$frameStyles = null;
$frameSizes = null;
$frameShapes = null;
$frameMaterials = null;
$matteColors = null;
$photoSizes = null;
$products = null;

/* FUNCTIONS */

/* PROCESSES */
try {
    $query = "SELECT * FROM $table_frameColors ORDER BY CAST(code AS UNSIGNED) ASC;";
    $frameColors = fetchFromDB($query,$table_frameColors);

    $query = "SELECT * FROM $table_frameStyle ORDER BY CAST(code AS UNSIGNED) ASC;";
    $frameStyles = fetchFromDB($query,$table_frameStyle);

    $query = "SELECT * FROM $table_frameSize ORDER BY CAST(code AS UNSIGNED) ASC;";
    $frameSizes = fetchFromDB($query,$table_frameSize);

    $query = "SELECT * FROM $table_frameShapes ORDER BY CAST(code AS UNSIGNED) ASC;";
    $frameShapes = fetchFromDB($query,$table_frameShapes);

    $query = "SELECT * FROM $table_frameMaterial ORDER BY CAST(code AS UNSIGNED) ASC;";
    $frameMaterials = fetchFromDB($query,$table_frameMaterial);

    $query = "SELECT * FROM $table_matteColors ORDER BY CAST(code AS UNSIGNED) ASC;";
    $matteColors = fetchFromDB($query,$table_matteColors);

    $query = "SELECT * FROM $table_photoSize ORDER BY CAST(code AS UNSIGNED) ASC;";
    $photoSizes = fetchFromDB($query,$table_photoSize);

    $query = "SELECT * FROM $table_products ORDER BY CAST(SKU AS UNSIGNED) ASC;";
    $products = fetchFromDB($query,$table_products);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
$frameColors = convertArrayFromNullToEmptyArray($frameColors);
$frameStyles = convertArrayFromNullToEmptyArray($frameStyles);
$frameSizes = convertArrayFromNullToEmptyArray($frameSizes);
$frameShapes = convertArrayFromNullToEmptyArray($frameShapes);
$frameMaterials = convertArrayFromNullToEmptyArray($frameMaterials);
$matteColors = convertArrayFromNullToEmptyArray($matteColors);
$photoSizes = convertArrayFromNullToEmptyArray($photoSizes);
$products = convertArrayFromNullToEmptyArray($products);

$records = NULL;
$found = 1;
$success = true;

$itemsToEncode = [
    "frameColors" => $frameColors,
    "frameStyles" => $frameStyles,
    "frameSizes" => $frameSizes,
    "frameShapes" => $frameShapes,
    "matteColors" => $matteColors,
    "frameMaterials" => $frameMaterials,
    "photoSizes" => $photoSizes,
    "products" => $products
];
echo my_json_encodeCustom($found,$success,$records,$itemsToEncode);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
