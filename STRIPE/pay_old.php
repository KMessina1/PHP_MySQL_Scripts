<?php
/*----------------------------------------------------------------------------------------
    File: pay.php
  Author: Sascha Wise
 Created: Mar 22, 2018
Modified: Jul 26, 2018 - Kevin Messina

©2018 Creative App Solutions, LLC. - All Rights Reserved.
------------------------------------------------------------------------------------------
2018-07-26 - Changed TaxJar so that a force write can be done from test record ID.
2018-07-20 - Changed TaxJar to use SALES TAX, SHIIPPING and TOTAL amt for GA also,
             it is taxable there.
2018-06-20 - Changed Shippo structure to use Fulfillment info.
2018-06-13 - Added Stack logging for major all functions.
             Added Minor failures in addition to Major Failures.
             Added PhPMailer to send company order issues.
             Changed logic flow so that test orders don't write TaxJar records.
             Changed logic flow so that Minor Errors no longer return false success.
2018-05-29 - Switched to LIVE keys for production.
2018-04-02 - Made the error handling a function and forced exit.
2018-03-30 - Changed error messages to be show what section name failed for task.
----------------------------------------------------------------------------------------*/

$version = "1.04h";

/* INITIALIZE: Foundation */
require_once($_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/scripts/funcs.php");
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$stack = array('(Line#'.__LINE__.' Func: '.__FUNCTION__.') '."*** Script ".$scriptName." started @ ".$timestamp." ***",$dashes);

/* INIT DEFAULTS */
$records = null;
$success = (bool)false;
$found = (int)0;
$orderID = (string)"n/a";
$errorMsg = (string)"";
$error_CMS = (bool)false;
$error_Stripe = (bool)false;
$error_Shippo = (bool)false;
$error_TaxJar = (bool)false;
$errorCount = (int)0;

/* INITIALIZE: Libraries */
require_once($_SERVER['DOCUMENT_ROOT'].'/mail23/class.phpmailer.php');
require_once('vendor/autoload.php');
require_once 'generated-conf/config.php'; // This is where Propel loads the database. Look in propel.yaml for the connection info
header('Content-Type: application/json');

/* USE Test or Live API Keys */
$testOrder = (bool)$_POST["testOrder"];
if ($testOrder == null) { $testOrder = false; }
$forceWriteTaxRecord = (bool)$_POST["forceWriteTaxRecord"];
if ($forceWriteTaxRecord == null) { $forceWriteTaxRecord = false; }

array_push($stack,($testOrder ?"testOrder = True" :"testOrder = False"));
$stripeKey = ($testOrder == false) ?'sk_live_' :'sk_test_';
$shippoKey = ($testOrder == false) ?'shippo_live_' :'shippo_test_';
$taxJarKey = '';

\Stripe\Stripe::setApiKey($stripeKey); // STRIPE: Secret Key
\Shippo::setApiKey($shippoKey); // SHIPPO: API Key
\Shippo::setApiVersion("2018-02-08");
$tax_client = TaxJar\Client::withApiKey($taxJarKey); //TAXJAR: API Key

/* FUNCTIONS */
function exitWithError($errMsg){
    global $version,$scriptName,$dashes,$timestamp,$stack,$records,$success,$found,
        $orderID,$errorMsg,$error_CMS,$error_Stripe,$error_Shippo,$error_TaxJar,$errorCount;

    ++$errorCount;
    $errorMsg = $errMsg;
    $success = false;
    logFailure($scriptName." FAILED!! ".$errMsg);
    array_push($stack,$dashes,'Failure Exit: '.$errMsg);
    returnResults();
}

function continueWithMinorError($errMsg){
    global $version,$scriptName,$dashes,$timestamp,$stack,$records,$success,$found,
        $orderID,$errorMsg,$error_CMS,$error_Stripe,$error_Shippo,$error_TaxJar,$errorCount;

    ++$errorCount;
    $errorMsg = $errorMsg.','.$errMsg;
    logFailure($scriptName.''.$orderID.'encountered minor Failure: '.$errMsg);
    array_push($stack,$dashes,'Minor Failure: '.$errMsg);
}

function returnResults() {
    global $version,$scriptName,$dashes,$timestamp,$stack,$records,$success,$found,
        $orderID,$errorMsg,$error_CMS,$error_Stripe,$error_Shippo,$error_TaxJar,$errorCount;

    array_push($stack,$dashes,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'*** Script '.$scriptName.' Finished @ '.getServerDateTime().' ***',$dashes);

    array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'*** SCRIPT RESULTS ***');
    array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'Number of Errors in Script: '.$errorCount);
    $msg = 'Script completed with ';
    ($success == true) ?$msg.' Success.' : $msg.' Failure.';
    ($success == true) ?logSuccess($msg) :logFailure($msg);

    array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'Script Major Errors: '.($success ?'0' :'1'));
    array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'Script Minor Errors: '.$errorCount);

    if (($success == false) || ($errorCount > 0)){
        array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'Errors occurred, sending issues email to Company.');
        sendErrorEmailToCompany();
    }

    if ($records == null) { $records = array(); }
    echo json_encode([
        "version" => $version,
        "stack" => $stack,
        "found" => $found,
        "success" => $success,
        "records" => $records,
        "error_CMS" => $error_CMS,
        "error_Stripe" => $error_Stripe,
        "error_Shippo" => $error_Shippo,
        "error_TaxJar" => $error_TaxJar,
        "error_count" => $errorCount,
        "successful" => $success,
        "error_message" => $errorMsg,
        "order_number" => $orderID
    ]);

    exit();
}

function sendErrorEmailToCompany() {
    global $version,$scriptName,$dashes,$timestamp,$stack,$records,$success,$found,
        $orderID,$errorMsg,$error_CMS,$error_Stripe,$error_Shippo,$error_TaxJar,$errorCount;

    $email_from = 'orders@sqframe.com';
    $email_developer = 'kmessina@creativeapps.us';
    $email_manager = '';
    $email_fulfillment = '';

    /* Get Company Email Addresses */
    $connection = connectToCMS();

    /* PERFORM QUERY: Corporate Emails */
    $records = null;
    $result = mysqli_query($connection, "SELECT * FROM emailer");
    while ($r = mysqli_fetch_array($result)) { $records[] = $r; }
    $found = (int)count($records);
    $success = (bool)($found > 0);
    $msg = 'Found: '.$found.' records.';
    ($success == true) ?logSuccess($msg) : logFailure($msg);
    array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.($success == true
        ?'CMS: Found Company Emails Addresses.'
        :'CMS: No Company Email Addresses Found.'));

    /* BUILD EMAIL ADDRESSES */
    if ($success == true) {
        foreach ($records as $record) {
            $record_name = (string)$record["name"];
            $record_address = (string)$record["address"];
            if ($record_name == "FROM") { $email_from = $record_address; }
            elseif ($record_name == "DEVELOPER") { $email_developer = $record_address; }
            elseif ($record_name == "MANAGER") { $email_manager = $record_address; }
            elseif ($record_name == "FULFILLMENT") { $email_fulfillment = $record_address; }
        }
    }

    $email_text = '*** Squareframe CMS System ***<br><br>';
    $email_text = $email_text.'Scriptname: '.$scriptName.' Version: '.$version.'<br>';
    $dateTimeFormatted = getTodaysDateTimeConverted();
    $email_text = $email_text.'Date/Time: '.$dateTimeFormatted.'<br><br>';

    if ($orderID == null) { $orderID = 'n/a'; }
    $email_text = $email_text.'Order #: '.$orderID.'<br><br>';

    if ($error_CMS == true) {
        $email_text = $email_text.'CMS Error occurred.<br>';
        array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'CMS Error: true.');
    }
    if ($error_Stripe == true) {
        $email_text = $email_text.'Stripe Error occurred.<br>';
        array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'Stripe Error: true.');
    }
    if ($error_Shippo == true) {
        $email_text = $email_text.'Shippo Error occurred.<br>';
        array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'Shippo Error: true.');
    }
    if ($error_TaxJar == true) {
        $email_text = $email_text.'TaxJar Error occurred.<br>';
        array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'TaxJar Error: true.');
    }

    $errorList = explode(',', $errorMsg);
    $email_text = $email_text.'<br><br>Error Messages:<br>';
    foreach ($errorList as $errDetail) {
        $email_text = $email_text.$errDetail.'<br>';
    }

    array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'Email Text: '.$email_text);

    $email_text = $email_text.'<br><br>Script Stack:<br>';
    foreach ($stack as $stackDetail) {
        $email_text = $email_text.$stackDetail.'<br>';
    }

    error_reporting(E_ALL ^ E_DEPRECATED);
    $page = 'the mailout page';
    $email = new PHPMailer();
    $email->IsHTML(true);
    $email->From = $email_from;
    $email->FromName = 'Squareframe';
    $email->Subject = '!! ISSUES for Order #'.$orderID;
    $email->Body = $email_text;
    $email->AddAddress( $email_from );
    $email->AddBCC( $email_developer );

    $success = $email->send();
    $msg = ($success ?'ISSUES EMAIL: Order issues email sent to Company for order#: '.$orderID :' ISSUES EMAIL: Order issues email FAILED sending to Company for order#: '.$orderID);
    ($success ?logSuccess($msg) :logFailure($msg));
    array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.$msg);
}

// ****** THE FOLLOWING ARE ALL CRITICAL FAILURE FUNCTIONS ******

/* VALIDATE: Input Parameter order_id */
if($_POST["order_id"] == null){
    http_response_code(400);
    $msg = 'SCRIPT: No order_id field was provided.';
    array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.$msg);
    $error_CMS = true;
    exitWithError($msg);
}else{
    $orderID = $_POST["order_id"];
    array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'OrderID valid: '.$orderID);
}

/* GET ORDER: order_id */
$q = new OrdersQuery();
$order = $q->findPK($_POST["order_id"]); // This finds a new order using its primary key which in this case is the ID field
if($order == null){
    http_response_code(404);
    $msg = 'CMS: The order could not be found.';
    array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.$msg);
    $error_CMS = true;
    exitWithError($msg);
}

/* VALIDATE: statusID = "Unpaid" */
if($order->getStatusid() != "Unpaid"){
    $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'CMS: Order is in an invalid state, statusID != Unpaid for OrderID:'.$orderID;
    array_push($stack,$msg);
    $error_CMS = true;
    exitWithError($msg);
}

/* VALIDATE: customerID of order */
$cq = new CustomersQuery();
$customer = $cq->findPK($order->getCustomerid());
if($customer == null){
    http_response_code(404);
    $msg ='(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'CMS: The customer for this order could not be found.';
    array_push($stack,$msg);
    $error_CMS = true;
    exitWithError($msg);
}

/* STRIPE: totalAmt > 0 OR for FREE ORDERS, Bypass Payment Processing */
if($order->getTotalamt() > 0){
    array_push($stack,'Stripe: Attempting Charge.');
    try {
        $charge = \Stripe\Charge::create(array(
            "amount" => floatval($order->getTotalamt()) * 100,
            "currency" => "USD",
            "description" => "CMS Order#".$orderID.": ".getServerDateTime(),
            "customer" => $customer->getStripeId(),
            "source" => $_POST["token"],
        ));
        /* CMS: Set stripeTranscationID field */
        $order->setStripetransactionid($charge["id"]);
        $order->setPaymentauthorized(1); // This sets the stripe transaction id on the row in the table. The changes are queued to be saved
        $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'STRIPE: '.$charge["id"].' successfully completed.';
        logSuccess($msg);
        array_push($stack,$msg);
    }catch(Exception $e) {
        http_response_code(400);
        $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'STRIPE: '.$e->getMessage();
        array_push($stack,$msg);
        $error_Stripe = true;
        exitWithError($msg);
    }
}else{
    /* VALIDATE: If there is a couponID which infers a FREE ORDER */
    $couponID = $order->getCouponid();
    if(($couponID == null) || ($couponID < 1)) {
        http_response_code(400);
        $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SCRIPT: Free order without couponID.';
        array_push($stack,$msg);
        $error_Stripe = true;
        exitWithError($msg);
    }
}

// ****** THE FOLLOWING ARE ALL NON-CRITICAL FAILURE FUNCTIONS, REQUIRE MANUAL HANDLING ******

/* VALIDATE: shipToID of order */
/// FIX ME: Should look for addressID as a get or if not found, insert
$aq = new AddressesQuery();
$a_id = $order->getShiptoid();
$shipToAddress = $aq->findPK($a_id);
if($shipToAddress == null){
    http_response_code(400);
    $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'CMS: Order# '.$orderID.' shipping address not found for addressID:'.$a_id;
    array_push($stack,$msg);
    $error_CMS = true;
    continueWithMinorError($msg);
}else{
    array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'CMS: Shipping Address found.');
}

if ($shipToAddress != null) {
    /* Set default shipping return information */
    $shipper_Name = 'Squareframe';
    $shipper_Address1 = '88 N Avondale Rd # 100';
    $shipper_Address2 = '';
    $shipper_City = 'Avondale Estates';
    $shipper_StateCode = 'GA';
    $shipper_Zip = '30002';
    $shipper_CountryCode = 'US';
    $shipper_Phone = '7702959986';
    $shipper_Email = 'info@edwinjarvis.com';

    /* Get CMS Fulfillment information for return address for Shippo Labels */
    try {
        /* Get Company Email Addresses */
        $connection = connectToCMS();

        /* PERFORM QUERY: Corporate Emails */
        $result = mysqli_query($connection, "SELECT * FROM fulfillment LIMIT 1;");
        while ($r = mysqli_fetch_array($result)) { $records[] = $r; }
        $found = (int)count($records);
        $success = (bool)($found > 0);
        $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'Found: '.$found.' records.';
        ($success == true) ?logSuccess($msg) : logFailure($msg);
        array_push($stack,($success == true)
            ? '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'CMS: Found Company Fulfillment Information.'
            : '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'CMS: No Company Fulfillment Information Found.');

        if ($success) {
            $record = $records[0];
            $shipper_Name = (string)$record["company"];
            $shipper_Address1 = (string)$record["street1"];
            $shipper_Address2 = (string)$record["street2"];
            $shipper_City = (string)$record["city"];
            $shipper_StateCode = (string)$record["stateCode"];
            $shipper_Zip = (string)$record["zip"];
            $shipper_CountryCode = (string)$record["countryCode"];
            $shipper_Phone = (string)$record["phone"];
            $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SCRIPT: Using CMS fulfillment settings.';
        }else{
            $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SCRIPT: Using default script fulfillment settings.';
            array_push($stack,$msg);
            $error_CMS = true;
            continueWithMinorError($msg);
        }

        ($success == true) ?logSuccess($msg) : logFailure($msg);
    }catch(Exception $e) {
        $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'CMS: Fulfillment Information Failed with error: '.$e->getMessage();
        array_push($stack,$msg);
        array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SCRIPT: Using default script fulfillment settings.');
        $error_CMS = true;
        continueWithMinorError($msg);
    }

    /* SHIPPO: Create Shipping Label from shipToID of order */
    try {
        $toAddress = Shippo_Address::create(array(
            'name' => $shipToAddress->getFirstname().' '.$shipToAddress->getLastname(),
            'street1' => $shipToAddress->getAddress1(),
            'street2' => $shipToAddress->getAddress2(),
            'city' => $shipToAddress->getCity(),
            'state' => $shipToAddress->getStatecode(),
            'zip' => $shipToAddress->getZip(),
            'country' => $shipToAddress->getCountrycode(),
            'phone' => $shipToAddress->getPhone(),
            'email' => $shipToAddress->getEmail()
        ));

        array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SHIPPO: to: '.$toAddress);

        $fromAdress = Shippo_Address::create(array(
            'company' => $shipper_Name,
            'street1' => $shipper_Address1,
            'street2' => $shipper_Address2,
            'city' => $shipper_City,
            'state' => $shipper_StateCode,
            'zip' => $shipper_Zip,
            'country' => $shipper_CountryCode,
            'phone' => $shipper_Phone,
            'email' => $shipper_Email
        ));

        array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SHIPPO: From: '.$fromAdress);

        // Parcel Information
        $parcel = Shippo_Parcel::create(array(
            'length' => '16',
            'width' => '14',
            'height' => '5',
            'distance_unit' => 'in',
            'weight' => '53',
            'mass_unit' => 'oz',
        ));

        array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SHIPPO: Parcel: '.$parcel);

        // Create Shipment Record
        $shipment = Shippo_Shipment::create(array(
                'address_from' => $fromAdress,
                'address_to' => $toAddress,
                'parcels' => array($parcel),
                'async' => false
            )
        );

        array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SHIPPO: Shipment: '.$shipment);
        $shipmentTransactionID = $shipment["object_id"];

        /* CMS: Set shippoTranscationID field */
        $order->setShippotransactionid($shipmentTransactionID); // This sets the shippo transaction id on the row in the table. The changes are queued to be saved
        $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SHIPPO: '.$shipmentTransactionID.' successfully completed.';
        logSuccess($msg);
    }catch(Exception $e) {
        $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SHIPPO: Failed with error: '.$e->getMessage();
        array_push($stack,$msg);
        $error_shippo = true;
        continueWithMinorError($msg);
    }

    /* TAXJAR: If salesTaxAmt > 0, Create new tax record */
    $isNotTestOrderNumber = ($orderID >= 100);
    if ($forceWriteTaxRecord) { $isNotTestOrderNumber = true; } // Ignore test record if force write
    $hasTaxAmt = (bool)(floatval($order->getTaxamt()) > 0 );

    array_push($stack,'(Line#'.__LINE__.'Is a test order: '.($isNotTestOrderNumber) ?'true' :'false');
    array_push($stack,'(Line#'.__LINE__.'Force Write Tax: '.($TaxableAmount) ?'true' :'false');

    // Pass in to TaxJar the pre=tax dollar amount, Tax Jar auto-calculates.
    $subTotalAmount = floatval($order->getSubtotal());
    $shippingAmount = floatval($order->getShippingamt());
    $preTaxableAmount = floatval(($subTotalAmount + $shippingAmount));
    $discountAmount = floatval($order->getDiscountamt());
    $TaxableAmount = floatval(($preTaxableAmount - $discountAmount));
    array_push($stack,'(Line#'.__LINE__.'subTotalAmount'.$subTotalAmount);
    array_push($stack,'(Line#'.__LINE__.'shippingAmount'.$shippingAmount);
    array_push($stack,'(Line#'.__LINE__.'preTaxableAmount'.$preTaxableAmount);
    array_push($stack,'(Line#'.__LINE__.'discountAmount'.$discountAmount);
    array_push($stack,'(Line#'.__LINE__.'TaxableAmount'.$TaxableAmount);

    if ($isNotTestOrderNumber) {
        if ($hasTaxAmt) {
            array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'TAXJAR: This order has sales tax.');
            $timestamp = getServerDateTime();

            try {
                $result = $tax_client->createOrder([
                    'transaction_id' => $orderID,
                    'transaction_date' => $timestamp,
                    'to_country' => $shipToAddress->getCountrycode(),
                    'to_city' => $shipToAddress->getCity(),
                    'to_zip' => $shipToAddress->getZip(),
                    'to_state' => $shipToAddress->getStatecode(),
                    'to_street' => $shipToAddress->getAddress1() . ' ' . $shipToAddress->getAddress2(),
                    'amount' => $TaxableAmount,
                    // 'amount' => floatval($order->getTotalamt()),
                    'shipping' => $order->getShippingamt(),
                    'sales_tax' => $order->getTaxamt()
                ]);

                /* CMS: Set taxJarTranscationID field */
                $order->setTaxjartransactionid($orderID); // This sets the taxjar transaction id on the row in the table. The changes are queued to be saved

                logSuccess('(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SUCCESS: TAXJAR '.$orderNumberForTax.' successfully completed.');
            }catch(Exception $e){
                $msg = '(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'TAXJAR: Failed with error: '.$e->getMessage();
                array_push($stack,$msg);
                $error_taxJar = true;
                continueWithMinorError($msg);
            }
        }else{
            array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'TAXJAR: This order does not have sales tax.');
        }
    }else{
        array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'TAXJAR: This order is a test, will not write TaxJar record.');
    }
}

/* CMS: Set new statusID */
$order->setStatusid("New");

/* CMS: Save updated Order record */
$order->save(); // This actually saves all of the queued changes to the database
array_push($stack,'(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'CMS: StatusID Changed to NEW and Order transactionID(s) udpated.');

/* RETURN RESULTS */
logSuccess('(Line#'.__LINE__.' Func: '.__FUNCTION__.') '.'SUCCESS: New order '.$order->getOrdernum().' successfully completed.');
$success = true;
returnResults();

?>
