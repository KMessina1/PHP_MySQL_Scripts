<?php
/*--------------------------------------------------------------------------------------
     File: cart_create.php
   Author: Kevin Messina
  Created: May  11, 2018
 Modified: Nov. 19, 2018

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:

2018/11/19 - Updated Include files.
2018/10/11 - Updated Server Paths.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/cms/cart/cart_create.php?
appVersion=2.12.01&
cartID=&
calledFromApp=Browser&
debug=1
*/

$version = "1.02a";
$category = "CART";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");

/* GET INPUT PARAMS */
// Customer
// $moltinCustomerID = (string)$_GET['1'];
$firstName = (string)$_GET['2'];
$lastName = (string)$_GET['3'];
$phone = (string)$_GET['4'];
$email = (string)$_GET['5'];
$mailingList = (int)$_GET['6'];
$addressID = (int)$_GET['7'];
// $notes = (string)$_GET['8'];
// Customer Address
// $address_moltinID = (string)$_GET['9'];
$address_firstName = (string)$_GET['10'];
$address_lastName = (string)$_GET['11'];
$address_address1 = (string)$_GET['12'];
$address_address2 = (string)$_GET['13'];
$address_city = (string)$_GET['14'];
$address_stateCode = (string)$_GET['15'];
$address_zip = (string)$_GET['16'];
$address_countryCode = (string)$_GET['17'];
$address_phone = (string)$_GET['18'];
$address_email = (string)$_GET['19'];
// $address_notes = (string)$_GET['20'];
// ShipTo Address
// $ShipToaddress_moltinID = (string)$_GET['21'];
$ShipToaddress_firstName = (string)$_GET['22'];
$ShipToaddress_lastName = (string)$_GET['23'];
$ShipToaddress_address1 = (string)$_GET['24'];
$ShipToaddress_address2 = (string)$_GET['25'];
$ShipToaddress_city = (string)$_GET['26'];
$ShipToaddress_stateCode = (string)$_GET['27'];
$ShipToaddress_zip = (string)$_GET['28'];
$ShipToaddress_countryCode = (string)$_GET['29'];
$ShipToaddress_phone = (string)$_GET['30'];
$ShipToaddress_email = (string)$_GET['31'];
$ShipToaddress_notes = (string)$_GET['32'];
// Order
$order_ID = (string)$_GET['33'];
$order_Num = (string)$_GET['34'];
$order_Date = (string)$_GET['35'];
$order_statusID = (string)$_GET['36'];
//        $order_customerID = (int)$_GET['37'];
//        $order_customerNum = (string)$_GET['38'];
//        $order_lastName = (string)$_GET['39'];
//        $order_email = (string)$_GET['40'];
$order_cartID = (int)$_GET['41'];
$order_productCount = (int)$_GET['42'];
$order_photoCount = (int)$_GET['43'];
$order_giftMessage = (string)$_GET['44'];
$order_subTotal = (float)$_GET['45'];
$order_taxAmt = (float)$_GET['46'];
$order_shippingAmt = (float)$_GET['47'];
$order_discountAmt = (float)$_GET['48'];
$order_totalAmt = (float)$_GET['49'];
$order_couponID = (int)$_GET['50'];
$order_shipToID = (int)$_GET['51'];
$order_shipToMoltinID = (string)$_GET['52'];
$order_shippingPriority = (string)$_GET['53'];
$order_shippedVia = (string)$_GET['54'];
$order_trackingNum = (string)$_GET['55'];
$order_shippedAmt = (float)$_GET['56'];
$order_shippedDate = (string)$_GET['57'];
$order_deliveredDate = (string)$_GET['58'];
$order_mailer_confDate = (string)$_GET['59'];
$order_mailer_TrackingDate = (string)$_GET['60'];
$order_taxJarTransactionID = (string)$_GET['61'];
$order_paymentAuthorized = (int)$_GET['62'];
$order_paymentCard = (string)$_GET['63'];
$order_stripeTransactionID = (string)$_GET['64'];
$order_shippoTransactionID = (string)$_GET['65'];
$order_orderFolder = (string)$_GET['66'];
$order_orderDocs = (string)$_GET['67'];
$order_photos = (string)$_GET['68'];
$order_notes = (string)$_GET['69'];
// KM: ???????
// $order_mailer_compFilesDate = (string)$_GET['69']; // Not implemented in SF yet???

/* INIT PARAMS */
initDefaults();
$customerID = -1;
$cartID = -1;
$customerID = -1;
$addressID = -1;
$shipToAddressID = -1;
$orderID = -1;

/* FUNCTIONS */
try {
    $tableName = $table_customers;
    $query =
        "SELECT
            *
        FROM
            $tableName
        WHERE
            moltinID='$moltinCustomerID'
        ;
    ";
    $records = fetchFromDB($query,$tableName);
    $found = returnCountFrom($records);
    $success = returnSuccessFromCount($found);

    /* If not found, insert new */
    if ($found < 1) {
        array_push($stack,'--> Customer '.$moltinCustomerID.' not found, inserting as new Customer.');
        $query = "INSERT INTO customers VALUES (
            NULL,
            '$firstName',
            '$lastName',
            '$phone',
            '$email',
            '$mailingList',
            $addressID,
            '$moltinCustomerID',
            '$notes'
        );";
        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);
        $success = (bool)($results == "1");
        if ($success == true) {
            $customerID = $connection->insert_id;
            $msg = '--> moltinCustomerID: '.$moltinCustomerID.' Inserted as id: '.$customerID;
            array_push($stack,$msg);
            logSuccess($msg);
        }else{
            $msg = '--> moltinCustomerID: '.$moltinCustomerID.' Failed Insert.';
            array_push($stack,$msg);
            logFailure($msg);
        }
    }else{
        $customerID = (int)$records[0]["id"];
        $success = true;
        $msg = '--> moltinCustomerID: '.$moltinCustomerID.' Exists already.';
        array_push($stack,$msg);
        logSuccess($msg);
    }


    /* PERFORM QUERY: Does Customer Address Already Exist? */
    if ($success == true) {
        $records = null;
        $query = "SELECT * FROM addresses WHERE moltinID='$address_moltinID';";
        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);
        while ($r = mysqli_fetch_array($results)) {
            $records[] = $r;
        }

        /* Write if not already exists */
        $success = false;
        $addressID = 0;
        $found = (int)count($records);
        if ($found < 1) {
            array_push($stack,'--> Customer address '.$address_moltinID.' not found, inserting as new Address.');
            $query = "INSERT INTO addresses VALUES (
                NULL,
                $customerID,
                '$address_firstName',
                '$address_lastName',
                '$address_address1',
                '$address_address2',
                '$address_city',
                '$address_stateCode',
                '$address_zip',
                '$address_countryCode',
                '$address_phone',
                '$address_email',
                '$address_moltinID'
            );";
            array_push($stack,$dashes,'Query: '.$query);
            $results = mysqli_query($connection, $query);
            $success = (bool)($results == "1");
            if ($success == true) {
                $addressID = $connection->insert_id;
                array_push($stack,'--> addressID: '.$addressID.' Inserted.');
                array_push($stack,'--> addressID: '.$addressID.' updated in Customer record.');

                // Update Customer Record with Address ID
                $query = "UPDATE customers SET addressID='$addressID' WHERE id=$customerID;";
                array_push($stack,$dashes,'Query: '.$query);
                $results = mysqli_query($connection, $query);
                $success = (bool)($results == "1");
                if ($success == true) {
                    $msg = '--> customerID: '.$customerID.' Updated with AddressID: '.$addressID;
                    array_push($stack,$msg);
                    logSuccess($msg);
                }else{
                    $msg = '--> customerID: '.$customerID.' FAILED update with AddressID: '.$addressID;
                    array_push($stack,$msg);
                    logFailure($msg);
                }
            }else{
                $msg = '--> addressID: '.$addressID.' Failed Insert.';
                array_push($stack,$msg);
                logFailure($msg);
            }
        }else{
            $addressID = (int)$records[0]["id"];
            $success = true;
            $msg = '--> addressID '.$addressID.' Exists already.';
            array_push($stack,$msg);
            logSuccess($msg);
        }
    }

    /* PERFORM QUERY: Does ShipTo Address Already Exist? */
    if ($success == true) {
        $records = null;
        $query = "SELECT * FROM addresses WHERE moltinID='$ShipToaddress_moltinID';";
        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);
        while ($r = mysqli_fetch_array($results)) {
            $records[] = $r;
        }

        /* Write if not already exists */
        $success = false;
        $addressID = 0;
        $found = (int)count($records);
        if ($found < 1) {
            array_push($stack,'--> Shipping address '.$ShipToaddress_moltinID.' not found, inserting as new Address.');
            $query = "INSERT INTO addresses VALUES (
                NULL,
                $customerID,
                '$ShipToaddress_firstName',
                '$ShipToaddress_lastName',
                '$ShipToaddress_address1',
                '$ShipToaddress_address2',
                '$ShipToaddress_city',
                '$ShipToaddress_stateCode',
                '$ShipToaddress_zip',
                '$ShipToaddress_countryCode',
                '$ShipToaddress_phone',
                '$ShipToaddress_email',
                '$ShipToaddress_moltinID'
            );";
            array_push($stack,$dashes,'Query: '.$query);
            $results = mysqli_query($connection, $query);
            $success = (bool)($results == "1");
            if ($success == true) {
                $shipToAddressID = $connection->insert_id;
                $msg = '--> addressID: '.$shipToAddressID.' Inserted.';
                array_push($stack,$msg);
            }else{
                $msg = '--> addressID: '.$shipToAddressID.' Failed Insert.';
                array_push($stack,$msg);
                logFailure($msg);
            }
        }else{
            $shipToAddressID = (int)$records[0]["id"];
            $success = true;
            $msg = '--> shipToAddressID '.$shipToAddressID.' Exists already.';
            array_push($stack,$msg);
            logSuccess($msg);
        }
    }

    /* PERFORM QUERY: Does Order Already Exist? */
    $records = null;
    $query = "SELECT * FROM orders WHERE orderNum='$order_Num';";
    array_push($stack,$dashes,'Query: '.$query);
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) {
        $records[] = $r;
    }

    /* Write if not already exists */
    $success = false;
    $found = (int)count($records);
    if ($found < 1) {
        array_push($stack,'--> order '.$order_Num.' not found, inserting as new Order.');
        $query = "INSERT INTO orders VALUES (
            NULL,
            '$order_Num',
            '$order_Date',
            '$order_statusID',
            $customerID,
            '$moltinCustomerID',
            '$lastName',
            '$email',
            $order_cartID,
            $order_productCount,
            $order_photoCount,
            '$order_giftMessage',
            $order_subTotal,
            $order_taxAmt,
            $order_shippingAmt,
            $order_discountAmt,
            $order_totalAmt,
            $order_couponID,
            $shipToAddressID,
            '$order_shipToMoltinID',
            '$order_shippingPriority',
            '$order_shippedVia',
            '$order_trackingNum',
            $order_shippedAmt,
            '$order_shippedDate',
            '$order_deliveredDate',
            '$order_mailer_compFilesDate',
            '$order_mailer_confDate',
            '$order_mailer_TrackingDate',
            '$order_taxJarTransactionID',
            '$order_paymentAuthorized',
            '$order_paymentCard',
            '$order_stripeTransactionID',
            '$order_shippoTransactionID',
            '$order_orderFolder',
            '$order_orderDocs',
            '$order_photos',
            '$order_notes'
        );";
        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);
        $success = (bool)($results == "1");
        if ($success == true) {
            $orderID = $connection->insert_id;
            $msg = '--> orderID: '.$orderID.' Inserted.';
            array_push($stack,$msg);
        }else{
            $msg = '--> orderID: '.$orderID.' Failed Insert.';
            array_push($stack,$msg);
            logFailure($msg);
        }
    }else{
        $orderID = (int)$records[0]["id"];
        $success = true;
        $msg = '--> orderID: '.$orderID.' Exists already.';
        array_push($stack,$msg);
        logSuccess($msg);
    }

    /* PERFORM QUERY: Does Cart Already Exist? */
    $records = null;
    $query = "SELECT * FROM carts WHERE customerID='$customerID' AND orderID=$orderID;";
    array_push($stack,$dashes,'Query: '.$query);
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) {
        $records[] = $r;
    }

    $found = (int)count($records);
    if ($found > 0) { // Cart Already Exists, use it
        $cartID = (int)$records[0]["id"];
    }

    if ($cartID > 1) {
        $success = true;
    }

    if ($success == true) { // CartID found
        $msg = '---> Found existing CartID: '.$cartID;
        array_push($stack,$msg);
        logSuccess($msg);
        $success == true;
    }else { // CartID needs to be created
        array_push($stack,$dashes,'---> Existing CartID NOT FOUND, creating new CartID');

        /* PERFORM QUERY */
        $records = null;
        $cartID = 0;
        $query = "INSERT INTO carts VALUES (
            NULL,
            $customerID,
            $order_photoCount,
            '',
            0,
            $orderID
        );";
        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);
        $found = (int)count($results);

        /* PROCESS RESULTS */
        $success = (bool)($results == "1");
        $title = "CART: Add New";
        $msg = "---> Insert of CartID: $cartID for CustomerID: $customerID ".($success) ?"Succeeded." :"Failed.";
        array_push($stack,addLogEntry(__LINE__,$title,$success,$msg));
        if ($debug) { echo("<br/>$msg"); }
        ($success) ?logSuccess($msg) :logFailure($msg);

        if ($success) {
            $cartID = $connection->insert_id;
        }
    }

    /* PROCESS RESULTS */
    $success = (bool)($results == "1");
    $title = "CART: Add item";
    $msg = "---> Insert of $SKU ".($success) ?"Succeeded." :"Failed.";
    array_push($stack,addLogEntry(__LINE__,$title,$success,$msg));
    if ($debug) { echo("<br/>$msg"); }
    ($success) ?logSuccess($msg) :logFailure($msg);
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* RETURN RESULTS */
echo my_json_encode($version,$stack,$found,$success,$debug,$records);
endScriptMsg(__LINE__,$scriptName,$debug,$calledfromApp,$category,$stack,$success);

?>
