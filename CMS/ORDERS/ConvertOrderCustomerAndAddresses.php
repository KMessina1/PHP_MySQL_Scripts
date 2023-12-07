<?php
/*--------------------------------------------------------------------------------------
     File: ConvertOrderCustomerAndAddresses.php
   Author: Kevin Messina
  Created: Mar. 21, 2018
 Modified:

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:
--------------------------------------------------------------------------------------*/

$version = "1.11a";


/* INITIALIZE */
require_once($_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/scripts/funcs.php");
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$timestamp = getServerDateTime();


/* FUNCTIONS */
function doQuery() {
    global $version,$scriptName,$timestamp;

    /* INIT DEFAULTS */
    $dashes = "----------------------------------------------";
    $scriptName = basename($_SERVER['SCRIPT_NAME']);
    $timestamp = getServerDateTime();
    $stack = array("*** Script ".$scriptName." started @ ".$timestamp." ***",$dashes);

    $success = false;
    $customerID = 0;
    $found = 0;

    try {
        $records = null;

        /* INIT DB */
        $connection = connectToCMS();

        /* GET INPUT PARAMS */
        // Customer
        $moltinCustomerID = (string)$_GET['1'];
        $firstName = (string)$_GET['2'];
        $lastName = (string)$_GET['3'];
        $phone = (string)$_GET['4'];
        $email = (string)$_GET['5'];
        $mailingList = (string)$_GET['6'];
        $addressID = (int)$_GET['7'];
        $notes = (string)$_GET['8'];
        // Customer Address
        $address_moltinID = (string)$_GET['9'];
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
//        $address_notes = (string)$_GET['20'];
        // ShipTo Address
        $ShipToaddress_moltinID = (string)$_GET['21'];
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
        // $ShipToaddress_notes = (string)$_GET['32'];
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

        /* PERFORM QUERY: Does Customer Already Exist? */
        $records = null;
        $query = "SELECT * FROM customers WHERE moltinID='$moltinCustomerID';";
        array_push($stack,$dashes,'Query: '.$query);
        $results = mysqli_query($connection, $query);
        while ($r = mysqli_fetch_array($results)) {
            $records[] = $r;
        }

        $success = false;
        $customerID = 0;
        $found = (int)count($records);
        $addressID = 0;
        $shipToAddressID = 0;
        $orderID = 0;

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
            $msg = '--> moltinCustomerID: '.$moltinCustomerID.' Exists already, skipping Insert.';
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
                    array_push($stack,'----> addressID: '.$addressID.' updated in Customer record.');

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
                $msg = '--> addressID '.$addressID.' Exists already, skipping Insert.';
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
                $msg = '--> shipToAddressID '.$shipToAddressID.' Exists already, skipping Insert.';
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

            $query = "INSERT INTO orders (
                id,orderNum,orderDate,statusID,customerID,customerNum,
                customer_firstName,customer_lastName,customer_address1,customer_address2,customer_city,
                customer_stateCode,customer_zip,customer_countryCode,customer_phone,customer_email,
                cartID,productCount,photoCount,giftMessage,
                subtotal,taxAmt,shippingAmt,discountAmt,totalAmt,couponID,
                shipToID,shipToMoltinID,shipTo_firstName,shipTo_lastName,shipTo_address1,shipTo_address2,shipTo_city,
                shipTo_stateCode,shipTo_zip,shipTo_countryCode,shipTo_phone,shipTo_email,
                shippingPriority,shippedVia,trackingNum,shippedAmt,shippedDate,deliveredDate,
                mailer_confDate,mailer_trackingDate,
                taxJarTransactionID,paymentAuthorized,paymentCard,StripeTransactionID,shippoTransactionID,
                orderFolder,orderDocs,photos,notes
            ) VALUES(
                NULL,
                '$order_Num',
                '$order_Date',
                '$order_statusID',
                $customerID,
                '$moltinCustomerID',

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
                '$ShipToaddress_moltinID',
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

                '$order_shippingPriority',
                '$order_shippedVia',
                '$order_trackingNum',

                $order_shippedAmt,
                '$order_shippedDate',
                '$order_deliveredDate',

                '$order_mailer_confDate',
                '$order_mailer_TrackingDate',

                '$order_taxJarTransactionID',
                $order_paymentAuthorized,
                '$order_paymentCard',
                '$order_stripeTransactionID',
                '$order_shippoTransactionID',

                '$order_orderFolder',
                '$order_orderDocs',
                '$order_photos',
                '$order_notes'
            );";

// -- $query = 'INSERT INTO orders VALUES(
// -- NULL,
// -- `$order_Num`,
// -- `$order_Date`,
// -- `$order_statusID`,
// -- `(int)$customerID`,
// -- `$moltinCustomerID`,
// -- `$address_firstName`,
// -- `$address_lastName`,
// -- `$address_address1`,
// -- `$address_address2`,
// -- `$address_city`,
// -- `$address_stateCode`,
// -- `$address_zip`,
// -- `$address_countryCode`,
// -- `$address_phone`,
// -- `$address_email`,
// -- `(int)$order_cartID`,
// -- `(int)$order_productCount`,
// -- `(int)$order_photoCount`,
// -- `$order_giftMessage`,
// -- `(decimal)$order_subTotal`,
// -- `(decimal)$order_taxAmt`,
// -- `(decimal)$order_shippingAmt`,
// -- `(decimal)$order_discountAmt`,
// -- `(decimal)$order_totalAmt`,
// -- `(int)$order_couponID`,
// -- `(int)$shipToAddressID`,
// -- `$order_shipToMoltinID`,
// -- `$ShipToaddress_firstName`,
// -- `$ShipToaddress_lastName`,
// -- `$ShipToaddress_address1`,
// -- `$ShipToaddress_address2`,
// -- `$ShipToaddress_city`,
// -- `$ShipToaddress_stateCode`,
// -- `$ShipToaddress_zip`,
// -- `$ShipToaddress_countryCode`,
// -- `$ShipToaddress_phone`,
// -- `$ShipToaddress_email`,
// -- `$order_shippingPriority`,
// -- `$order_shippedVia`,
// -- `$order_trackingNum`,
// -- `(decimal)$order_shippedAmt`,
// -- `$order_shippedDate`,
// -- `$order_deliveredDate`,
// -- `$order_mailer_confDate`,
// -- `$order_mailer_TrackingDate`,
// -- `$order_taxJarTransactionID`,
// -- `(int)$order_paymentAuthorized`,
// -- `$order_paymentCard`,
// -- `$order_stripeTransactionID`,
// -- `$order_shippoTransactionID`,
// -- `$order_orderFolder`,
// -- `$order_orderDocs`,
// -- `$order_photos`,
// -- `$order_notes`
// -- );';

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
            $msg = '--> orderID: '.$orderID.' Exists already, skipping Insert.';
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

        $cartID = (int)$records[0]["id"];
        if ($cartID > 0) { // Cart Already Exists, use it
            $msg = '---> Found existing CartID: '.$cartID;
            array_push($stack,$msg);
            logSuccess($msg);
            $success == true;
        }else { // CartID needs to be created
            array_push($stack,'---> Existing CartID NOT FOUND, creating new CartID');

            /* PERFORM QUERY */
            $records = null;
            $cartID = 0;
            $query = "INSERT INTO carts VALUES (
                NULL,
                $customerID,
                $order_photoCount,
                '',
                $order_subTotal,
                $orderID
            );";
            array_push($stack,$dashes,'Query: '.$query);
            $results = mysqli_query($connection, $query);
            $found = (int)count($results);

            /* PROCESS RESULTS */
            $success = (bool)($results == "1");
            if ($success == true) {
                $cartID = $connection->insert_id;
                $msg = '---> Insert of CartID: '.$cartID.' for CustomerID: '.$customerID.' was successful.';
                array_push($stack,$msg);
                logSuccess($msg);
                $success == true;
            }else{
                $msg = '---> Insert of CartID: '.$cartID.' for CustomerID: '.$customerID.' FAILED.';
                array_push($stack,$msg);
                logFailure($msg);
                $success == false;
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
        "records" => $records
    ]);
}


/* RUN SCRIPT */
doQuery();

?>
