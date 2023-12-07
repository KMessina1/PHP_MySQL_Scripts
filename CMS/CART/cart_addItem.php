<?php
/*--------------------------------------------------------------------------------------
     File: cart_addItem.php
   Author: Kevin Messina
  Created: May  10, 2018
 Modified: Dec. 01, 2018

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:

2018/12/01 - Removed incomplete cart deletion.
2018/11/19 - Updated Include files.
2018/10/10 - Converted to latest CMS syntax.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/cart/cart_addItem.php?
customerID=1619&
jsonSerializedStringOfParams=%5B%7B%22photo%22%3A%22photo_4x4na_21569293_1906948949567390_725641549362233344_n.jpg%22%2C%22frameShape%22%3A%22Square%22%2C%22frameColor%22%3A%22Natural%22%2C%22frameMaterial%22%3A%22Wood%22%2C%22frameSize%22%3A%224%5C%22x4%5C%22%22%2C%22matteColor%22%3A%22White%22%2C%22amount%22%3A99%2C%22SKU%22%3A%224x4na%22%2C%22frameStyle%22%3A%22Standard%22%2C%22price%22%3A99%2C%22customerID%22%3A%221619%22%2C%22qty%22%3A1%7D%5D&
appVersion=1.91.01&
calledFromApp=Browser&
debug=1
*/

$version = "2.02a";
$category = "CART";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$jsonSerializedStringOfParams = (string)$_GET['jsonSerializedStringOfParams'];
$customerID = (string)$_GET['customerID'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> customerID: $customerID";
$msg .= "\n|-> jsonSerializedStringOfParams: $jsonSerializedStringOfParams";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();
$cartID = -1;
$orderID = -1;
$moltinSKU = "";
$itemCount = 0;
$subtotal = (float)0.00;
$items = "";

/* DECODE JSON STRING PARAMS */
$arrayOfParams = json_decode($jsonSerializedStringOfParams, true);
if ($debug) {
    var_dump($arrayOfParams);
    printToScreen("# of array elements",count($arrayOfParams),false);
}

foreach($arrayOfParams as $item){
    $SKU = (string)$item["SKU"];
    $qty = (int)$item['qty'];
    $amount = (float)$item['amount'];

    $items = ($items == "" ?$SKU :$items.",".$SKU);
    $itemCount = ($itemCount + $qty);
    $subtotal = ($subtotal + $amount);
}

$title = "DECODED ITEMS ARRAY PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> items: $items";
$msg .= "\n|-> itemCount: $itemCount";
$msg .= "\n|-> subtotal: $subtotal";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_carts);

    // Create new cart...
    $orderID = -1;
    $values ="
        $customerID,
        $itemCount,
        '$items',
        $subtotal,
        $orderID
    ";
    $success = $db->insert($values);
    $cartID = $db->lastInsertedID;

    // INSERT NEW ITEMS WITH CART ID
    writeToStackAndConsole(__LINE__,"CART","Insert new cart ITEMS...");
    $db->setTableName($table_items);
    foreach($arrayOfParams as $item){
    // foreach($arrayOfParams as &$item){
        $customerID = (int)$item["customerID"];
        $SKU = (string)$item["SKU"];
        $frameSize = (string)$item['frameSize'];
        $frameColor = (string)$item['frameColor'];
        $frameShape = (string)$item['frameShape'];
        $frameStyle = (string)$item['frameStyle'];
        $matteColor = (string)$item['matteColor'];
        $frameMaterial = (string)$item['frameMaterial'];
        $qty = (int)$item['qty'];
        $price = (float)$item['price'];
        $amount = (float)$item['amount'];
        $photo = (string)$item['photo'];

        $title = "INPUT PARAMS:";
        $msg = "\n$title";
        $msg .= "\n|-> customerID: $customerID";
        $msg .= "\n|-> SKU: $SKU";
        $msg .= "\n|-> frameSize: $frameSize";
        $msg .= "\n|-> frameColor: $frameColor";
        $msg .= "\n|-> frameShape: $frameShape";
        $msg .= "\n|-> frameStyle: $frameStyle";
        $msg .= "\n|-> matteColor: $matteColor";
        $msg .= "\n|-> frameMaterial: $frameMaterial";
        $msg .= "\n|-> qty: $qty";
        $msg .= "\n|-> price: $price";
        $msg .= "\n|-> amount: $amount";
        $msg .= "\n|-> photo: $photo";
        writeMsgToStackAndConsole(__LINE__,$title,$msg);

        /* Insert Item */
        $orderID = -1;
        $moltinSKU = "";
        $values = "
            $cartID,
            $customerID,
            $orderID,
            '$SKU',
            '$frameSize',
            '$frameColor',
            '$frameShape',
            '$frameStyle',
            '$matteColor',
            '$frameMaterial',
            $qty,
            $price,
            $amount,
            '$photo',
            '$moltinSKU'
        ";
        $success = $db->insert($values);
        $itemID = $db->lastInsertedID;
    }

    if (is_array($arrayOfParams)) {
        $numOfItems = (int)count($arrayOfParams);
        writeToStackAndConsole(__LINE__,"$numOfItems CART/ITEM(s)...","Inserted.");
    }else{
        writeToStackAndConsole(__LINE__,"NO CART/ITEM(s)...","Nothing to add.");
    }
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
$records = null;
$found = ($cartID > 0);

$itemsToEncode = [
    "cartID" => $cartID
];
echo my_json_encodeCustom($found,$success,$records,$itemsToEncode);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
