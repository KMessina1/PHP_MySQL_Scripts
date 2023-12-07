<?php
/*--------------------------------------------------------------------------------------
     File: customerAdd.php
   Author: Kevin Messina
  Created: Feb. 26, 2018
 Modified:

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:
--------------------------------------------------------------------------------------*/

$version = "1.02a";

/* INITIALIZE */
require_once($_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/scripts/funcs.php");
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');
$dashes = "----------------------------------------------";
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$timestamp = getServerDateTime();
$stack = array("*** Script ".$scriptName." started @ ".$timestamp." ***",$dashes);

/* INIT DEFAULTS */
$success = false;
$orderID = -1;
$found = 0;

/* FUNCTIONS */
try {
    /* INIT DB */
    $connection = connectToCMS();

    /* GET INPUT PARAMS */
    $orderNum = (int)$_GET['orderNum'];

    /* PERFORM QUERY: Does Order already exist */
    $query = "SELECT * FROM orders WHERE orderNum='$orderNum';";
    array_push($stack,$dashes,'Query: '.$query);
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) { $records[] = $r; }

    $found = (int)count($records);
    array_push($stack,'Found: '.$found.' records.');
    if ($found > 0) {
        $orderID = (int)$records[0]["id"];
        array_push($stack,'Order already exists with OrderNum: '.$orderID.' skipping Insert.');
        $success = true;
       logSuccess('Skipped insert of Order, already exists with OrderNum: '.$orderID);
    }else{
        /* GET INPUT PARAMS */
        $orderDate = (string)$_GET['orderDate'];
        $statusID = (string)$_GET['statusID'];
        $customerID = (int)$_GET['customerID'];
        $customerNum = (string)$_GET['customerNum'];
        $customer_firstName = (string)$_GET['customer_firstName'];
        $customer_lastName = (string)$_GET['customer_lastName'];
        $customer_address1 = (string)$_GET['customer_address1'];
        $customer_address2 = (string)$_GET['customer_address2'];
        $customer_city = (string)$_GET['customer_city'];
        $customer_stateCode = (string)$_GET['customer_stateCode'];
        $customer_zip = (string)$_GET['customer_zip'];
        $customer_countryCode = (string)$_GET['customer_countryCode'];
        $customer_phone = (string)$_GET['customer_phone'];
        $customer_email = (string)$_GET['customer_email'];
        $cartID = (int)$_GET['cartID'];
        $productCount = (int)$_GET['productCount'];
        $photoCount = (int)$_GET['photoCount'];
        $giftMessage = (string)$_GET['giftMessage'];
        $subtotal = (float)$_GET['subtotal'];
        $taxAmt = (float)$_GET['taxAmt'];
        $shippingAmt = (float)$_GET['shippingAmt'];
        $discountAmt = (float)$_GET['discountAmt'];
        $totalAmt = (float)$_GET['totalAmt'];
        $couponID = (int)$_GET['couponID'];
        $shipToID = (int)$_GET['shipToID'];
        $shipToMoltinID = (string)$_GET['shipToMoltinID'];
        $shipTo_firstName = (string)$_GET['shipTo_firstName'];
        $shipTo_lastName = (string)$_GET['shipTo_lastName'];
        $shipTo_address1 = (string)$_GET['shipTo_address1'];
        $shipTo_address2 = (string)$_GET['shipTo_address2'];
        $shipTo_city = (string)$_GET['shipTo_city'];
        $shipTo_stateCode = (string)$_GET['shipTo_stateCode'];
        $shipTo_zip = (string)$_GET['shipTo_zip'];
        $shipTo_countryCode = (string)$_GET['shipTo_countryCode'];
        $shipTo_phone = (string)$_GET['shipTo_phone'];
        $shipTo_email = (string)$_GET['shipTo_email'];
        $shippingPriority = (string)$_GET['shippingPriority'];
        $shippedVia = (string)$_GET['shippedVia'];
        $trackingNum = (string)$_GET['trackingNum'];
        $shippedAmt = (float)$_GET['shippedAmt'];
        $shippedDate = (string)$_GET['shippedDate'];
        $deliveredDate = (string)$_GET['deliveredDate'];
        $mailer_confDate = (string)$_GET['mailer_confDate'];
        $mailer_trackingDate = (string)$_GET['mailer_trackingDate'];
        $taxJarTransactionID = (string)$_GET['taxJarTransactionID'];
        $paymentAuthorized = (int)$_GET['paymentAuthorized'];
        $paymentCard = (string)$_GET['paymentCard'];
        $StripeTransactionID = (string)$_GET['StripeTransactionID'];
        $shippoTransactionID = (string)$_GET['shippoTransactionID'];
        $orderFolder = (string)$_GET['orderFolder'];
        $orderDocs = (string)$_GET['orderDocs'];
        $photos = (string)$_GET['photos'];
        $notes = (string)$_GET['notes'];

        /* PERFORM QUERY */
        $query = "INSERT INTO orders VALUES (
            NULL,
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
        );";
        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);
        $success = (bool)($results == "1");

        $orderID = $connection->insert_id;

        /* PROCESS RESULTS */
        if ($success == true) {
            array_push($stack,$dashes,'Insert of Order for: '.$orderID.' was successful.');
            logSuccess("Order completed.");
        }else{
            array_push($stack,$dashes,'Failed insertion of Order for: '.$orderID.'.');
            $success = false;
            logFailure("add Order failed.");
        }
    }
} catch (Exception $e) {
    logFailure("Connect to CMS Server: ".$e->getMessage());
    $success = false;
}

/* RETURN RESULTS */
if ($records == null) { $records = array(); }
echo json_encode([
    "version" => $version,
    "stack" => $stack,
    "found" => $found,
    "success" => $success,
    "records" => $records,
    "orderID" => $orderID
]);

?>
