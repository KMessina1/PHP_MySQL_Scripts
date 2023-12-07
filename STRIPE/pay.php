<?php
/*----------------------------------------------------------------------------------------
    File: pay.php
  Author: Sascha Wise
 Created: Mar 22, 2018
Modified: Dec. 07, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/12/07 - Updated to use Debug.
2018/11/19 - Updated Include files.
2018/07/26 - Changed TaxJar so that a force write can be done from test record ID.
2018/07/20 - Changed TaxJar to use SALES TAX, SHIIPPING and TOTAL amt for GA also,
             it is taxable there.
2018/06/20 - Changed Shippo structure to use Fulfillment info.
2018/06/13 - Added Stack logging for major all functions.
             Added Minor failures in addition to Major Failures.
             Added PhPMailer to send company order issues.
             Changed logic flow so that test orders don't write TaxJar records.
             Changed logic flow so that Minor Errors no longer return false success.
2018/05/29 - Switched to LIVE keys for production.
2018/04/02 - Made the error handling a function and forced exit.
2018/03/30 - Changed error messages to be show what section name failed for task.
----------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://sqframe.com/client-tools/squareframe/scripts/stripe/pay.php?
order_id=2449&
token=abc&
calledFromApp=Browser&
dontSendEmail=1&
testOrder=1&
forceWriteTaxRecord=0&
debug=1
*/

$version = "3.02a";
$category = "PAYMENT";

/* INCLUDES */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");
// CAS Mailer Functions
require_once(getScriptsPath()."funcs_mailer.php");
// TaxJar, Shippo, Stripe, Yaml Libraries
require_once('vendor/autoload.php');
// This is where Propel loads the database. Look in propel.yaml for the connection info
require_once 'generated-conf/config.php';


/* INITIALIZE */
if (!$debug) {
    header('Content-Type: application/json');
}


/* INIT PARAMS */
initDefaults();
$errorMsg = (string)"";
$error_CMS = (bool)false;
$error_Stripe = (bool)false;
$error_Shippo = (bool)false;
$error_TaxJar = (bool)false;
$errorCount = (int)0;
$customer_email = (string)"";
$customer_phone = (string)"";
$customer_name = (string)"";
$from_street = (string)"";
$from_city = (string)"";
$from_stateCode = (string)"";
$from_zip = (string)"";
$from_countryCode = (string)"";
$from_company = (string)"";
$from_phone = (string)"";
$from_email = (string)"";

$to_street1 = (string)"";
$to_street2 = (string)"";
$to_street = (string)"";
$to_city = (string)"";
$to_stateCode = (string)"";
$to_zip = (string)"";
$to_countryCode = (string)"";

/* GET INPUT PARAMS */
if ($debug) {
    $testOrder = isset($_GET["testOrder"]) ? (bool)$_GET["testOrder"] : false;
    $dontSendEmail = isset($_GET["dontSendEmail"]) ? (bool)$_GET["dontSendEmail"] : false;
    $forceWriteTaxRecord = isset($_GET["forceWriteTaxRecord"]) ? (bool)$_GET["forceWriteTaxRecord"] : false;
    $orderID = isset($_GET["order_id"]) ? (int)$_GET["order_id"] : null;
    $ccToken = isset($_GET["token"]) ? (string)$_GET["token"] : null;
}else{
    $testOrder = isset($_POST["testOrder"]) ? (bool)$_POST["testOrder"] : false;
    $dontSendEmail = isset($_POST["dontSendEmail"]) ? (bool)$_POST["dontSendEmail"] : false;
    $forceWriteTaxRecord = isset($_POST["forceWriteTaxRecord"]) ? (bool)$_POST["forceWriteTaxRecord"] : false;
    $orderID = isset($_POST["order_id"]) ? (int)$_POST["order_id"] : null;
    $ccToken = isset($_POST["token"]) ? (string)$_POST["token"] : null;
}

$title = "DEBUG PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> debug: ".returnTextFromBool($debug);
$msg .= "\n|-> dontSendEmail: ".returnTextFromBool($dontSendEmail);
$msg .= "\n|-> testOrder: ".returnTextFromBool($testOrder);
$msg .= "\n|-> forceWriteTaxRecord: ".returnTextFromBool($forceWriteTaxRecord);
writeMsgToStackAndConsole(__LINE__,$title,$msg);

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> order_id: $orderID";
$msg .= "\n|-> token: $ccToken";
writeMsgToStackAndConsole(__LINE__,$title,$msg);


/* VALIDATE INPT PARAMS */
validateInputParam($orderID,"order_id");
validateInputParam($ccToken,"token");

/* Get CMS Data */
getOrderInfo();
validateOrderStatus();
getCustomerInfo();
getshipFromAddress();
getShipToAddress();

/* Process Order */
processPayment();
processSalesTax();
processShippingLabel();

/* Update CMS Order */
updateOrderInfo();
returnResults();
exit();


/* FUNCTIONS */
function updateOrderInfo() {
    global $order,$orderID,$success;

    /* CMS: Change statusID from UnPaid -> New */
    $newStatusID = "New";
    $order->setStatusid($newStatusID);

    /* CMS: Save updated Order record */
    $order->save(); // This actually saves all of the queued changes to the database

    writeToStackAndConsole(__LINE__,"CMS:","StatusID: $newStatusID for Order# $orderID completed.");

    $success = true;
}

function processShippingLabel(){
    global $testOrder,$error_Shippo,$orderID,$order,
            $from_countryCode,$from_zip,$from_stateCode,$from_city,$from_street,$from_company,
            $from_phone,$from_email,
            $to_countryCode,$to_zip,$to_stateCode,$to_city,$to_street1,$to_street2,$to_name,$to_phone,
            $to_email;

    $shippoKey = ($testOrder)
        ?"shippo_test_"
        :"shippo_live_";

    \Shippo::setApiKey($shippoKey); // SHIPPO: API Key
    \Shippo::setApiVersion("2018-02-08");

    $fromAddressParams = array(
        'company' => $from_company,
        'street1' => $from_street,
        'street2' => "",
        'city' => $from_city,
        'state' => $from_stateCode,
        'zip' => $from_zip,
        'country' => $from_countryCode,
        'phone' => $from_phone,
        'email' => $from_email
    );

    $title = "SHIP FROM (Fulfillment):";
    $msg = "\n$title";
    $msg .= "\n|-> company: $from_company";
    $msg .= "\n|-> street1: $from_street";
    $msg .= "\n|-> street2: ";
    $msg .= "\n|-> city: $from_city";
    $msg .= "\n|-> state: $from_stateCode";
    $msg .= "\n|-> zip: $from_zip";
    $msg .= "\n|-> country: $from_countryCode";
    $msg .= "\n|-> company: $from_company";
    $msg .= "\n|-> phone: $from_phone";
    $msg .= "\n|-> email: $from_email";
    writeMsgToStackAndConsole(__LINE__,$title,$msg);

    $toAddressParams = array(
        'name' => $to_name,
        'street1' => $to_street1,
        'street2' => $to_street2,
        'city' => $to_city,
        'state' => $to_stateCode,
        'zip' => $to_zip,
        'country' => $to_countryCode,
        'phone' => $to_phone,
        'email' => $to_email
    );

    $title = "SHIP TO:";
    $msg = "\n$title";
    $msg .= "\n|-> name: $to_name";
    $msg .= "\n|-> street1: $to_street1";
    $msg .= "\n|-> street2: $to_street2";
    $msg .= "\n|-> city: $to_city";
    $msg .= "\n|-> state: $to_stateCode";
    $msg .= "\n|-> zip: $to_zip";
    $msg .= "\n|-> country: $to_countryCode";
    $msg .= "\n|-> phone: $from_phone";
    $msg .= "\n|-> email: $from_email";
    writeMsgToStackAndConsole(__LINE__,$title,$msg);

    $parcel_length = '16';
    $parcel_width = '14';
    $parcel_height = '5';
    $parcel_distance_unit = 'in';
    $parcel_weight = '53';
    $parcel_mass_unit = 'oz';

    $parcelParams = array(
        'length' => $parcel_length,
        'width' => $parcel_width,
        'height' => $parcel_height,
        'distance_unit' => $parcel_distance_unit,
        'weight' => $parcel_weight,
        'mass_unit' => $parcel_mass_unit,
    );

    $title = "PARCEL INFO:";
    $msg = "\n$title";
    $msg .= "\n|-> length: $parcel_length";
    $msg .= "\n|-> width: $parcel_width";
    $msg .= "\n|-> height: $parcel_height";
    $msg .= "\n|-> distance_unit: $parcel_distance_unit";
    $msg .= "\n|-> weight: $parcel_weight";
    $msg .= "\n|-> mass_unit: $parcel_mass_unit";
    writeMsgToStackAndConsole(__LINE__,$title,$msg);


    try {
        $fromAdress = Shippo_Address::create($fromAddressParams);
        $toAddress = Shippo_Address::create($toAddressParams);
        $parcel = Shippo_Parcel::create($parcelParams);

        $shipmentParams = array(
            'address_from' => $fromAdress,
            'address_to' => $toAddress,
            'parcels' => array($parcel),
            'async' => false
        );

        // Create Shipment Record
        $shipment = Shippo_Shipment::create($shipmentParams);
        $shipmentTransactionID = $shipment["object_id"];

        writeToStackAndConsole(__LINE__,"SHIPPO:",$shipment);

        /* CMS: Set shippoTranscationID field */
        $order->setShippotransactionid($shipmentTransactionID); // Queued change
        writeToStackAndConsole(__LINE__,"SHIPPO:","Succesfully created shipping label entry ($shipmentTransactionID) for orderID: $orderID.");
        logSuccess($msg);
    }catch(Exception $e) {
        $error_Shippo = true;
        $msg = $e->getMessage();
        logExceptionError($msg);
        continueWithMinorError($msg);
    }
}

function validateOrderStatus(){
    global $error_CMS,$order,$orderID;

    $statusID = $order->getStatusid();
    if ($statusID == "Unpaid") {
        writeToStackAndConsole(__LINE__,"CMS:","StatusID is $statusID, continuing to process...");
    }else{
        $error_CMS = true;
        http_response_code(404);
        exitWithError("CMS: Order is in an invalid state, statusID:$statusID != or Unpaid for OrderID: $orderID");
    }
}

function getOrderInfo() {
    global $order,$orderID;

    $orderQuery = new OrdersQuery();
    $order = $orderQuery->findPK($orderID); // Finds order
    validateQueryResult($order,"Order with ID: $orderID");
}

function getCustomerInfo(){
    global $order,$customer,$customerID,$customer_email,$customer_phone,$customer_name;

    $customerQuery = new CustomersQuery();
    $customerID = $order->getCustomerid();
    $customer = $customerQuery->findPK($customerID);
    validateQueryResult($customer,"Customer with ID: $customerID");

    $customer_email = $customer->getEmail();
    $customer_phone = $customer->getPhone();
    $customer_Firstname = $customer->getFirstname();
    $customer_Lastname = $customer->getLastname();
    $customer_name = "$customer_Firstname $customer_Lastname";
}

function processPayment() {
    global $order,$customer,$error_Stripe,$testOrder;

    $stripeKey = ($testOrder)
        ?"sk_test_"
        :"sk_live_";

    \Stripe\Stripe::setApiKey($stripeKey); // STRIPE: Secret Key

    $orderTotal = $order->getTotalamt();
    $amtToCharge = floatval($orderTotal) * 100;
    if($orderTotal > 0){ // Process Payment
        writeToStackAndConsole(__LINE__,"STRIPE:","Attempting to charge credit card...");

        try {
            $stripeID = $customer->getStripeId(); // No StripeID = No CC on file.
            $currencyCode = "USD";
            $description = "CMS Order# $orderID: ".getServerDateAndTimeAMPM();

            $title = "STRIPE PARAMS:";
            $msg = "\n$title";
            $msg .= "\n|-> amtToCharge: $$amtToCharge";
            $msg .= "\n|-> currencyCode: $currencyCode";
            $msg .= "\n|-> description: $description";
            $msg .= "\n|-> stripeID: $stripeID";
            writeMsgToStackAndConsole(__LINE__,$title,$msg);

            $chargeParams = array(
                "amount" => $amtToCharge,
                "currency" => $currencyCode,
                "description" => $description,
                "customer" => $stripeID,
                "source" => $ccToken
            );

            $charge = \Stripe\Charge::create($chargeParams);

            /* CMS: Set stripeTranscationID field */ //The changes are queued to be saved
            $chargeID = $charge["id"];
            $order->setStripetransactionid($chargeID); //Queued for change
            $order->setPaymentauthorized((int)1); //Queued for change

            $msg = "Successfully charged $$amtToCharge$currencyCode for Order# $orderID with transactionID: $chargeID.";
            logSuccess($msg);
            writeToStackAndConsole(__LINE__,"STRIPE:",$msg);
        }catch(Exception $e) {
            $error_Stripe = true;
            $msg = $e->getMessage();
            logExceptionError($msg);
            exitWithError($msg);
        }
    }else{ // Validate CouponID exists which is assumed to be a FREE ORDER since $orderTotal = 0.
        writeToStackAndConsole(__LINE__,"STRIPE:","Order Total was $$amtToCharge, looking for Free Order CouponID...");
        $couponID = $order->getCouponid();
        $hasCouponID = (($couponID == null) || ($couponID > 0));

        $title = "COUPON PARAMS:";
        $msg = "\n$title";
        $msg .= "\n|-> hasCouponID: ".returnTextFromBool($hasCouponID);
        $msg .= "\n|-> couponID: $couponID";
        writeMsgToStackAndConsole(__LINE__,$title,$msg);

        if ($hasCouponID) {
            $order->setStripetransactionid("Bypassed with Free Order CouponID: $couponID."); //Queued for change
            $order->setPaymentauthorized((int)1); //Queued for change
            writeToStackAndConsole(__LINE__,"STRIPE:","FREE ORDER with CouponID:$couponID, bypassed STRIPE processing.");
        }else{
            $error_Stripe = true;
            http_response_code(400);
            exitWithError("STRIPE: Free order without couponID cannot be processed.");
        }
    }
}

function getshipFromAddress() {
    global $db,$table_fulfillment,
            $from_countryCode,$from_zip,$from_stateCode,$from_city,$from_street,$from_company,
            $from_phone,$from_email;

    listEmailAddresses();

    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_fulfillment);

    $records = $db->fetchRecordsWhere();
    if ($records != null) {
        writeToStackAndConsole(__LINE__,"CMS:","Found Ship From (Fulfillment) Address...");
        $fulfillment = $db->firstRecord();
        $from_street1 = $fulfillment["street1"];
        $from_street2 = $fulfillment["street2"];
        $from_street = "$from_street1 $from_street2";
        $from_city = $fulfillment["city"];
        $from_stateCode = $fulfillment["stateCode"];
        $from_zip = $fulfillment["zip"];
        $from_countryCode = $fulfillment["countryCode"];
        $from_company = $fulfillment["company"];
        $from_phone = $fulfillment["phone"];
        $from_email = $fulfillment["email"];

        $title = "SHIP FROM (Fulfillment):";
        $msg = "\n$title";
        $msg .= "\n|-> street: $from_street";
        $msg .= "\n|-> city: $from_city";
        $msg .= "\n|-> from_stateCode: $from_stateCode";
        $msg .= "\n|-> from_zip: $from_zip";
        $msg .= "\n|-> from_countryCode: $from_countryCode";
        $msg .= "\n|-> from_company: $from_company";
        $msg .= "\n|-> from_phone: $from_phone";
        $msg .= "\n|-> from_email: $from_email";
        writeMsgToStackAndConsole(__LINE__,$title,$msg);
    }
}

function getShipToAddress() {
    global $db,$table_orders,$orderID,
            $to_countryCode,$to_zip,$to_stateCode,$to_city,$to_street1,$to_street2,$to_name,$to_phone,
            $to_email;

    $db->setTableName($table_orders);
    $db->fetchRecordID($orderID);
    $shippingAddressFound = $db->hasRecords;
    if ($shippingAddressFound) {
        writeToStackAndConsole(__LINE__,"CMS:","Found Ship To Address...");
        $shipTo = $db->firstRecord();
        $to_firstName = $shipTo["shipTo_firstName"];
        $to_lastName = $shipTo["shipTo_lastName"];
        $to_name = "$to_firstName $to_lastName";
        $to_street1 = $shipTo["shipTo_address1"];
        $to_street2 = $shipTo["shipTo_address2"];
        $to_street = "$to_street1 $to_street2";
        $to_city = $shipTo["shipTo_city"];
        $to_stateCode = $shipTo["shipTo_stateCode"];
        $to_zip = $shipTo["shipTo_zip"];
        $to_countryCode = $shipTo["shipTo_countryCode"];
        $to_phone = $shipTo["shipTo_phone"];
        $to_email = $shipTo["shipTo_email"];

        $title = "SHIP TO:";
        $msg = "\n$title";
        $msg .= "\n|-> street: $to_street";
        $msg .= "\n|-> city: $to_city";
        $msg .= "\n|-> from_stateCode: $to_stateCode";
        $msg .= "\n|-> from_zip: $to_zip";
        $msg .= "\n|-> from_countryCode: $to_countryCode";
        writeMsgToStackAndConsole(__LINE__,$title,$msg);
    }else{
        writeMsgToStackAndConsole(__LINE__,$title,"Address NOT found.");
    }
}

function processSalesTax() {
    global $forceWriteTaxRecord,$error_taxJar,$order,$TaxJarClient,$customerID,$orderID,
            $from_street,$from_city,$from_stateCode,$from_zip,$from_countryCode,
            $to_countryCode,$to_zip,$to_stateCode,$to_city,$to_street;

    $taxAmt = floatval($order->getTaxamt());
    $subTotalAmount = floatval($order->getSubtotal());
    $shippingAmount = floatval($order->getShippingamt());
    $preTaxableAmount = floatval(($subTotalAmount + $shippingAmount));
    $discountAmount = floatval($order->getDiscountamt());
    $taxableAmount = floatval(($preTaxableAmount - $discountAmount));

    $hasTaxAmt = (bool)($taxAmt > 0 );
    $isNotTestOrderNumber = ($orderID >= 100);
    if ($forceWriteTaxRecord) { $isNotTestOrderNumber = true; } // Ignore test record if force write

    $title = "TAX JAR:";
    $msg = "\n$title";
    $msg .= "\n|-> isNotTestOrderNumber: ".returnTextFromBool($isNotTestOrderNumber);
    $msg .= "\n|-> forceWriteTaxRecord: ".returnTextFromBool($forceWriteTaxRecord);
    $msg .= "\n|-> taxAmt: $$taxAmt";
    $msg .= "\n|-> subTotalAmount: $$subTotalAmount";
    $msg .= "\n|-> shippingAmount: $$shippingAmount";
    $msg .= "\n|-> preTaxableAmount: $$preTaxableAmount";
    $msg .= "\n|-> discountAmount: $$discountAmount";
    $msg .= "\n|-> TaxableAmount: $$taxableAmount";
    writeMsgToStackAndConsole(__LINE__,$title,$msg);

    if ($forceWriteTaxRecord) {
        $TaxableAmount = 25.00;
        $shippingAmount = 5.00;
        $taxAmt = 0.08;
        $to_city = "Atlanta";
        $to_stateCode = "GA";
        $to_zip = "30303";
    }

    if ($isNotTestOrderNumber) {
        if ($hasTaxAmt || $forceWriteTaxRecord) {
            writeToStackAndConsole(__LINE__,"TAXJAR:","Attempting to create entry for $$TaxableAmount...");

            $dateTime = getServerDateTime();
            $taxJarparams = [
                // 'transaction_id' => $orderID.'e', // Use tghe suffix to test when order# already exists.
                'transaction_id' => $orderID,
                'customer_id' => $customerID,
                'from_street' => $from_street,
                'from_city' => $from_city,
                'from_state' => $from_stateCode,
                'from_zip' => $from_zip,
                'from_country' => $from_countryCode,
                'transaction_date' => $dateTime,
                'to_country' => $to_countryCode,
                'to_zip' => $to_zip,
                'to_state' => $to_stateCode,
                'to_city' => $to_city,
                'to_street' => $to_street,
                'amount' => $taxableAmount,
                'shipping' => $shippingAmount,
                'sales_tax' => $taxAmt
            ];

            $taxJarKey = '';

            try {
                $TaxJarClient = TaxJar\Client::withApiKey($taxJarKey);
                $result = $TaxJarClient->createOrder($taxJarparams);

                /* CMS: Set taxJarTranscationID field */
                $order->setTaxjartransactionid($orderID); // Queued change
                writeToStackAndConsole(__LINE__,"TAXJAR:","Succesfully created sales-tax entry ($$taxAmt) for orderID: $orderID.");
            }catch(Exception $e){
                $error_taxJar = true;
                $msg = $e->getMessage();
                $alreadyExists = (bool)(stripos($msg,"422") !== false);
                if ($alreadyExists) {
                    writeToStackAndConsole(__LINE__,"TAXJAR:","Error 422: Entry may already exist orderID: $orderID.");
                }else{
                    logExceptionError($msg);
                    continueWithMinorError($msg);
                }
            }
        }else{
            writeToStackAndConsole(__LINE__,"TAXJAR:","This order does not have sales tax.");
        }
    }else{
        writeToStackAndConsole(__LINE__,"TAXJAR:","This order is a test, will not write TaxJar record.");
    }
}

function validateInputParam($param,$paramName) {
    $isValid = isset($param);

    if ($isValid) {
        writeToStackAndConsole(__LINE__,"CMS:","Input param $paramName:$param is valid.");
    }else{
        $error_CMS = true;
        http_response_code(400);
        exitWithError("CMS: No $paramName input parameter was provided.");
    }
}

function validateQueryResult($queryResult,$queryName) {
    if($queryResult == null){
        $error_CMS = true;
        http_response_code(404);
        exitWithError("CMS: $queryName not found.");
    }else{
        writeToStackAndConsole(__LINE__,"CMS:","$queryName found.");
    }
}


function exitWithError($errMsg){
    global $success,$errorCount;

    writeToStackAndConsole(__LINE__,"FAILURE FORCED EXIT:",$errMsg);
    logExceptionError($errMsg);

    ++$errorCount;
    $success = false;
    returnResults();
}

function continueWithMinorError($errMsg){
    global $errorMsg,$errorCount;

    $title = "MINOR ERROR: Continuing...";
    writeToStackAndConsole(__LINE__,$title,$errMsg);
    logExceptionError($title.$errMsg);

    ++$errorCount;
    $errorMsg = $errorMsg.','.$errMsg; // Build on minor errors list.
}

function returnResults() {
    global $scriptName,$records,$success,$found,$calledfromApp,$category,
        $errorMsg,$error_CMS,$error_Stripe,$error_Shippo,$error_TaxJar,$errorCount,
        $orderID;

    $majorErrors = (!$success);
    $majorErrorsCount = ($majorErrors ?1 :0);
    $minorErrors = ($success && ($errorCount > 0));
    $minorErrorsCount = ($minorErrors ?$errorCount :0);

    $title = "SCRIPT RESULTS:";
    $msg = "\n$title";
    $msg .= "\n|-> Script Success: ".returnTextFromBool($success);
    if (!$success) {
        $msg .= "\n|------> Total Errors: $errorCount";
        $msg .= "\n|------> Major Errors: ".returnTextFromBool($majorErrors).", $majorErrorsCount error(s)";
        $msg .= "\n|------> Minor Errors: ".returnTextFromBool($minorErrors).", $minorErrorsCount error(s)";
    }
    writeMsgToStackAndConsole(__LINE__,$title,$msg);

    if (!$success || ($errorCount > 0)){
        writeToStackAndConsole(__LINE__,"","Errors occurred, sending issues email to Company.");
        sendErrorEmailToCompany();
    }

    ($success ?logSuccess($msg) :logFailure($msg));

    /* RETURN RESULTS */
    $itemsToEncode = [
        "error_CMS" => (int)$error_CMS,
        "error_Stripe" => (int)$error_Stripe,
        "error_Shippo" => (int)$error_Shippo,
        "error_TaxJar" => (int)$error_TaxJar,
        "error_count" => (int)$errorCount,
        "successful" => (int)$success,
        "error_message" => (string)$errorMsg,
        "order_number" => (string)$orderID
    ];
    echo my_json_encodeCustom($found,$success,$records,$itemsToEncode);
    endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

    exit();
}

function sendErrorEmailToCompany() {
    global $dontSendEmail,$scriptName,$version,$stack,
        $errorMsg,$error_CMS,$error_Stripe,$error_Shippo,$error_TaxJar,$errorCount,
        $customer_email,$customer_phone,$customer_name,
        $orderID;

    if ($dontSendEmail) { return; }

    $orderDate = getServerDateTime();

    if (($customer_name == null) || ($customer_name == "")) { $customer_name = "n/a"; }
    if (($customer_email == null) || ($customer_email == "")) { $customer_email = "n/a"; }
    if (($customer_phone == null) || ($customer_phone == "")) { $customer_phone = "n/a"; }

    $calledfromAppName = $scriptName;
    $calledfromAppVersion = "Script v$version";

    $errorList = explode(',', $errorMsg);
    $errorMsg_Text = "";
    foreach ($errorList as $errDetail) {
        $errorMsg_Text = $errorMsg_Text.$errDetail."<br>";
    }

    $errorMsg_Text = $errorMsg_Text.'<br>Script Stack:<br>';
    foreach ($stack as $stackDetail) {
        $errorMsg_Text = $errorMsg_Text.$stackDetail.'<br>';
    }

    $error = "errorMsg: $errorMsg_Text<br>";
    $error .= "error_CMS: ".returnTextFromBool($error_CMS)."<br>";
    $error .= "error_Stripe: ".returnTextFromBool($error_Stripe)."<br>";
    $error .= "error_Shippo: ".returnTextFromBool($error_Shippo)."<br>";
    $error .= "error_TaxJar: ".returnTextFromBool($error_TaxJar)."<br>";
    $error .= "errorCount: $errorCount";

    $emailSent = send_OrderIssueEmail(
        $scriptName,
        $error,
        $orderID,
        $orderDate,
        $calledfromAppName,
        $calledfromAppVersion,
        "PROCESS ORDER PAYMENT",
        $customer_name,
        $customer_phone,
        $customer_email
    );
}

?>
