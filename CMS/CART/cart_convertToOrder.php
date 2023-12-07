<?php
/*--------------------------------------------------------------------------------------
    File: cart_convertToOrder.php
  Author: Kevin Messina
 Created: May  22, 2018
Modified: Nov. 19, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
2018-10-10 - Converted to latest CMS syntax.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/cart/cart_convertToOrder.php?
jsonSerializedStringOfParams=%7B%22shipTo_lastName%22%3A%22Smith%22%2C%22paymentCard%22%3A%22%22%2C%22shipTo_zip%22%3A%2230002%22%2C%22shippoTransactionID%22%3A%22%22%2C%22cartID%22%3A%221246%22%2C%22StripeTransactionID%22%3A%22%22%2C%22customerID%22%3A%221619%22%2C%22customer_stateCode%22%3A%22GA%22%2C%22shipToMoltinID%22%3A%22%22%2C%22customer_zip%22%3A%2230002%22%2C%22shipTo_email%22%3A%22smithstp%40gmail.com%22%2C%22shipToID%22%3A2093%2C%22statusID%22%3A%22Unpaid%22%2C%22deliveredDate%22%3A%22%22%2C%22photos%22%3A%22%22%2C%22totalAmt%22%3A0%2C%22orderDate%22%3A%222018-10-11%22%2C%22shipTo_address1%22%3A%2288%20N.%20Avondale%20Rd%22%2C%22photoCount%22%3A1%2C%22orderNum%22%3A%22-1%22%2C%22giftMessage%22%3A%22%22%2C%22orderDocs%22%3A%22%22%2C%22customer_countryCode%22%3A%22US%22%2C%22customer_firstName%22%3A%22Stephen%22%2C%22shipTo_stateCode%22%3A%22GA%22%2C%22customer_city%22%3A%22Avondale%20Estates%22%2C%22shippingPriority%22%3A%22Standard%22%2C%22shipTo_countryCode%22%3A%22US%22%2C%22customer_phone%22%3A%227702959986%22%2C%22customer_lastName%22%3A%22Smith%22%2C%22customerNum%22%3A%221619%22%2C%22subtotal%22%3A25%2C%22customer_email%22%3A%22smithstp%40gmail.com%22%2C%22customer_address2%22%3A%22%23100%22%2C%22shipTo_address2%22%3A%22%23100%22%2C%22mailer_compFilesDate%22%3A%22%22%2C%22mailer_confDate%22%3A%22%22%2C%22mailer_trackingDate%22%3A%22%22%2C%22orderFolder%22%3A%22http%3A%5C/%5C/sqframe.com%5C/client-tools%5C/squareframe%5C/Orders%22%2C%22notes%22%3A%22SF-App%20v.1.91.01.%22%2C%22taxJarTransactionID%22%3A%22%22%2C%22shipTo_city%22%3A%22Avondale%20Estates%22%2C%22trackingNum%22%3A%22%22%2C%22discountAmt%22%3A30%2C%22shippedVia%22%3A%22US%20Postal%20Service%22%2C%22shippingAmt%22%3A5%2C%22taxAmt%22%3A0%2C%22customer_address1%22%3A%2288%20N.%20Avondale%20Rd%22%2C%22shippedAmt%22%3A0%2C%22shippedDate%22%3A%22%22%2C%22id%22%3A%22-1%22%2C%22shipTo_phone%22%3A%227702959986%22%2C%22shipTo_firstName%22%3A%22Stephen%22%2C%22couponID%22%3A67%2C%22productCount%22%3A1%2C%22paymentAuthorized%22%3A%22%22%7D&
appVersion=1.91.01&
calledFromApp=Browser&
debug=1
*/

$version = '2.01a';
$category = "CART";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
$jsonSerializedStringOfParams = (string)$_GET['jsonSerializedStringOfParams'];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> jsonSerializedStringOfParams: $jsonSerializedStringOfParams";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* INIT PARAMS */
initDefaults();
$orderID = (int)-1;

/* DECODE JSON STRING PARAMS */
$item = json_decode($jsonSerializedStringOfParams, true);
$orderNum = (int)$item["orderNum"];
$orderDate = (string)$item["orderDate"];
$statusID = (string)$item["statusID"];
$customerID = (int)$item["customerID"];
$customerNum = (string)$item["customerNum"];
$customer_firstName = (string)$item["customer_firstName"];
$customer_lastName = (string)$item["customer_lastName"];
$customer_address1 = (string)$item["customer_address1"];
$customer_address2 = (string)$item["customer_address2"];
$customer_city = (string)$item["customer_city"];
$customer_stateCode = (string)$item["customer_stateCode"];
$customer_zip = (string)$item["customer_zip"];
$customer_countryCode = (string)$item["customer_countryCode"];
$customer_phone = (string)$item["customer_phone"];
$customer_email = (string)$item["customer_email"];
$cartID = (int)$item["cartID"];
$productCount = (int)$item["productCount"];
$photoCount = (int)$item["photoCount"];
$giftMessage = (string)$item["giftMessage"];
$subtotal = (float)$item["subtotal"];
$taxAmt = (float)$item["taxAmt"];
$shippingAmt = (float)$item["shippingAmt"];
$discountAmt = (float)$item["discountAmt"];
$totalAmt = (float)$item["totalAmt"];
$couponID = (int)$item["couponID"];
$shipToID = (int)$item["shipToID"];
$shipToMoltinID = (string)$item["shipToMoltinID"];
$shipTo_firstName = (string)$item["shipTo_firstName"];
$shipTo_lastName = (string)$item["shipTo_lastName"];
$shipTo_address1 = (string)$item["shipTo_address1"];
$shipTo_address2 = (string)$item["shipTo_address2"];
$shipTo_city = (string)$item["shipTo_city"];
$shipTo_stateCode = (string)$item["shipTo_stateCode"];
$shipTo_zip = (string)$item["shipTo_zip"];
$shipTo_countryCode = (string)$item["shipTo_countryCode"];
$shipTo_phone = (string)$item["shipTo_phone"];
$shipTo_email = (string)$item["shipTo_email"];
$shippingPriority = (string)$item["shippingPriority"];
$shippedVia = (string)$item["shippedVia"];
$trackingNum = (string)$item["trackingNum"];
$shippedAmt = (float)$item["shippedAmt"];
$shippedDate = (string)$item["shippedDate"];
$deliveredDate = (string)$item["deliveredDate"];
$mailer_compFilesDate = (string)$item["mailer_compFilesDate"];
$mailer_confDate = (string)$item["mailer_confDate"];
$mailer_trackingDate = (string)$item["mailer_trackingDate"];
$taxJarTransactionID = (string)$item["taxJarTransactionID"];
$paymentAuthorized = (int)$item["paymentAuthorized"];
$paymentCard = (string)$item["paymentCard"];
$StripeTransactionID = (string)$item["StripeTransactionID"];
$shippoTransactionID = (string)$item["shippoTransactionID"];
$orderFolder = (string)$item["orderFolder"];
$orderDocs = (string)$item["orderDocs"];
$photos = (string)$item["photos"];
$notes = (string)$item["notes"];

$title = "DECODED ITEMS ARRAY PARAMS:";
$msg = "\n$title";
$msg .= "\n|->orderNum :$orderNum ";
$msg .= "\n|->orderDate :$orderDate ";
$msg .= "\n|->statusID :$statusID ";
$msg .= "\n|->customerID :$customerID ";
$msg .= "\n|->customerNum :$customerNum ";
$msg .= "\n|->customer_firstName :$customer_firstName ";
$msg .= "\n|->customer_lastName :$customer_lastName ";
$msg .= "\n|->customer_address1 :$customer_address1 ";
$msg .= "\n|->customer_address2 :$customer_address2 ";
$msg .= "\n|->customer_city :$customer_city ";
$msg .= "\n|->customer_stateCode :$customer_stateCode ";
$msg .= "\n|->customer_zip :$customer_zip ";
$msg .= "\n|->customer_countryCode :$customer_countryCode ";
$msg .= "\n|->customer_phone :$customer_phone ";
$msg .= "\n|->customer_email :$customer_email ";
$msg .= "\n|->cartID :$cartID ";
$msg .= "\n|->productCount :$productCount ";
$msg .= "\n|->photoCount :$photoCount ";
$msg .= "\n|->customer_email :$customer_email ";
$msg .= "\n|->subtotal :$subtotal ";
$msg .= "\n|->taxAmt :$taxAmt ";
$msg .= "\n|->shippingAmt :$shippingAmt ";
$msg .= "\n|->discountAmt :$discountAmt ";
$msg .= "\n|->totalAmt :$totalAmt ";
$msg .= "\n|->couponID :$couponID ";
$msg .= "\n|->shipToID :$shipToID ";
$msg .= "\n|->shipToMoltinID :$shipToMoltinID ";
$msg .= "\n|->shipTo_firstName :$shipTo_firstName ";
$msg .= "\n|->shipTo_lastName :$shipTo_lastName ";
$msg .= "\n|->shipTo_address1 :$shipTo_address1 ";
$msg .= "\n|->shipTo_address2 :$shipTo_address2 ";
$msg .= "\n|->shipTo_city :$shipTo_city ";
$msg .= "\n|->shipTo_stateCode :$shipTo_stateCode ";
$msg .= "\n|->shipTo_zip :$shipTo_zip ";
$msg .= "\n|->shipTo_countryCode :$shipTo_countryCode ";
$msg .= "\n|->shipTo_phone :$shipTo_phone ";
$msg .= "\n|->shipTo_email :$shipTo_email ";
$msg .= "\n|->shippingPriority :$shippingPriority ";
$msg .= "\n|->shippedVia :$shippedVia ";
$msg .= "\n|->trackingNum :$trackingNum ";
$msg .= "\n|->shippedAmt :$shippedAmt ";
$msg .= "\n|->shippedDate :$shippedDate ";
$msg .= "\n|->deliveredDate :$deliveredDate ";
$msg .= "\n|->mailer_compFilesDate :$mailer_compFilesDate ";
$msg .= "\n|->mailer_confDate :$mailer_confDate ";
$msg .= "\n|->mailer_trackingDate :$mailer_trackingDate ";
$msg .= "\n|->taxJarTransactionID :$taxJarTransactionID ";
$msg .= "\n|->paymentAuthorized :$paymentAuthorized ";
$msg .= "\n|->paymentCard :$paymentCard ";
$msg .= "\n|->StripeTransactionID :$StripeTransactionID ";
$msg .= "\n|->shippoTransactionID :$shippoTransactionID ";
$msg .= "\n|->orderFolder :$orderFolder ";
$msg .= "\n|->orderDocs :$orderDocs ";
$msg .= "\n|->photos :$photos ";
$msg .= "\n|->notes :$notes ";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);

    $values = "
        '$orderNum',
        '$orderDate',
        '$statusID',
        $customerID,
        '$customerNum',
        '$customer_firstName',
        '$customer_lastName',
        '$customer_address1',
        '$customer_address2',
        '$customer_city',
        '$customer_stateCode',
        '$customer_zip',
        '$customer_countryCode',
        '$customer_phone',
        '$customer_email',
        $cartID,
        $productCount,
        $photoCount,
        '$giftMessage',
        $subtotal,
        $taxAmt,
        $shippingAmt,
        $discountAmt,
        $totalAmt,
        $couponID,
        $shipToID,
        '$shipToMoltinID',
        '$shipTo_firstName',
        '$shipTo_lastName',
        '$shipTo_address1',
        '$shipTo_address2',
        '$shipTo_city',
        '$shipTo_stateCode',
        '$shipTo_zip',
        '$shipTo_countryCode',
        '$shipTo_phone',
        '$shipTo_email',
        '$shippingPriority',
        '$shippedVia',
        '$trackingNum',
        $shippedAmt,
        '$shippedDate',
        '$deliveredDate',
        '$mailer_compFilesDate',
        '$mailer_confDate',
        '$mailer_trackingDate',
        '$taxJarTransactionID',
        $paymentAuthorized,
        '$paymentCard',
        '$StripeTransactionID',
        '$shippoTransactionID',
        '$orderFolder',
        '$orderDocs',
        '$photos',
        '$notes'
    ";
    $success = $db->insert($values);

    /* Update Order Num & Docs info */
    if ($success) {
        $orderID = $db->lastInsertedID;
        $orderFolder = $orderFolder.'/Order_'.$orderID;
        $pdf_Order = 'Order_'.$orderID.'.pdf';
        $pdf_OrderConf = 'OrderConfirmation_'.$orderID.'.pdf';
        $orderDocs = $pdf_Order.','.$pdf_OrderConf;

        $set = "
            orderNum='$orderID',
            orderFolder='$orderFolder',
            orderDocs='$orderDocs'
        ";
        $db->setTableName($table_orders);
        $success = $db->updateID($orderID,$set);
    }else{
        $errNo = mysqli_connect_errno();
        $errText = mysqli_connect_error();
        $msg = "---> Insert new order Failed for cartID: $cartID with error#: $errNo, $errText";
        logExceptionError($msg);
    }

    /* Update Cart with new OrderID */
    if ($orderID > 0) {
        $db->setTableName($table_carts);
        $success = $db->updateID($cartID,"orderID=$orderID");

        // Update Items with CartID
        $db->setTableName($table_items);
        $success = $db->update("orderID=$orderID","cartID=$cartID");
    }

    /* See if shipping address exists already */
    $shippingAddressExists = false;
    $db->setTableName($table_addresses);
    if ($shipToID > 0) {
        writeToStackAndConsole(__LINE__,"Ship To Address:","Exists, fetching record.");
        $shippingAddressExists = $db->fetchRecordID($shipToID);
    }

    /* shipping address found? otherwise insert new shipping address */
    if (!$shippingAddressExists) {
        $values = "
            $customerID,
            '$shipTo_firstName',
            '$shipTo_lastName',
            '$shipTo_address1',
            '$shipTo_address2',
            '$shipTo_city',
            '$shipTo_stateCode',
            '$shipTo_zip',
            '$shipTo_countryCode',
            '$shipTo_phone',
            '$shipTo_email',
            '$shipToMoltinID'
        ";
        $success = $db->insert($values);
    }
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
$records = null;
$success = ($orderID > 0);
$found = (int)$success;

$itemsToEncode = [
    "orderID" => $orderID
];
echo my_json_encodeCustom($found,$success,$records,$itemsToEncode);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
