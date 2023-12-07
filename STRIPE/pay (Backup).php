<?php
/*----------------------------------------------------------------------------------------
    File: pay.php
  Author: Sascha Wise
 Created: Mar 22, 2018
Modified: May 29, 2018 - Kevin Messina

©2018 Creative App Solutions, LLC. - All Rights Reserved.
------------------------------------------------------------------------------------------
2018-08-29 - Switched to LIVE keys for production.
2018-04-02 - Made the error handling a function and forced exit.
2018-03-30 - Changed error messages to be show what section name failed for task.
----------------------------------------------------------------------------------------*/

$version = "1.02f";

/* INITIALIZE: Foundation */
require_once($_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/scripts/funcs.php");
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');


/* INITIALIZE: Defaults */
require_once('vendor/autoload.php');
require_once 'generated-conf/config.php'; // This is where Propel loads the database. Look in propel.yaml for the connection info
header('Content-Type: application/json');

/* STRIPE: Secret Key */
//\Stripe\Stripe::setApiKey("sk_test_A5eTBWoS24ANyxETlElmEUyl");
\Stripe\Stripe::setApiKey("sk_live_XjBdnYl8erbWUpj6MPxwYdJg");
// \Stripe\Stripe::setApiKey("sk_test_VPg4Flxsq6nvrsBLXldUqPoH");

/* SHIPPO: API Key */
\Shippo::setApiKey("shippo_live_58400d9a4089b3888308599efe8e326da313150f");
// \Shippo::setApiKey("shippo_test_dbba9bf66994e34039790c61d2200867edf9ddb5");

/* TAXJAR: API Key */
$tax_client = TaxJar\Client::withApiKey('a60873023c5f4fcf98dd2fd9641656c9');

$orderID = "n/a";

function exitWithError($errorMsg){
    logFailure($scriptName." FAILED!! ".$errorMsg);
    echo(json_encode([
        "successful" => false,
        "error_message" => $errorMsg,
        "order_number" => $orderID
    ]));
    exit();
}

/* VALIDATE: Input Parameter order_id */
if($_POST["order_id"] == null){
    http_response_code(400);
    exitWithError("SCRIPT: No order_id field was provided");
}else{
    $orderID = $_POST["order_id"];
}

/* GET ORDER: order_id */
$q = new OrdersQuery();
$order = $q->findPK($_POST["order_id"]); // This finds a new order using its primary key which in this case is the ID field
if($order == null){
    http_response_code(404);
    exitWithError("CMS: The order could not be found");
}

/* VALIDATE: statusID = "Unpaid" */
if($order->getStatusid() != "Unpaid"){
    exitWithError("CMS: Order is in an invalid state - statusID != Unpaid");
}

/* VALIDATE: customerID of order */
$cq = new CustomersQuery();
$customer = $cq->findPK($order->getCustomerid());
if($customer == null){
    http_response_code(404);
    exitWithError("CMS: The customer for this order could not be found.");
}

/* STRIPE: totalAmt > 0 OR for FREE ORDERS, Bypass Payment Processing */
if($order->getTotalamt() > 0){
    try {
        $charge = \Stripe\Charge::create(array(
            "amount" => floatval($order->getTotalamt()) * 100,
            "currency" => "USD",
            "description" => "CMS Order#".$orderID.": ".getServerDateTime(),
            "customer" => $customer->getStripeId(),
            "source" => $_POST["token"],
        ));
        logSuccess("SUCCESS: STRIPE: ".$charge["id"]." successfully completed.");
        /* CMS: Set stripeTranscationID field */
        $order->setStripetransactionid($charge["id"]);
        $order->setPaymentauthorized(1); // This sets the stripe transaction id on the row in the table. The changes are queued to be saved
    }catch(Exception $e) {
        http_response_code(400);
        exitWithError("STRIPE: ".$e->getMessage());
    }
}else{
    /* VALIDATE: If there is a couponID which infers a FREE ORDER */
    $couponID = $order->getCouponid();
    if(($couponID == null) || ($couponID < 1)) {
        http_response_code(400);
        exitWithError("SCRIPT: Free order without coupon!");
    }
}

/* VALIDATE: shipToID of order */
$aq = new AddressesQuery();
$a_id = $order->getShiptoid();
$shipToAddress = $aq->findPK($a_id);
if($shipToAddress == null){
    http_response_code(404);
    exitWithError("CMS: The order does not have a shipping address for id:".$a_id);
}

/* SHIPPO: Create Shipping Label from shipToID of order */
$to = $shipToAddress->genShippoAddress($customer->getFirstname() . " ".  $customer->getLastname()); // This is a function on the address class. It can be found in generated-classes/Addresses.php
try {
    // From Address (CMS Fulfillment)
    $from = Shippo_Address::create(array(
        "name" => "Squareframe",
        "street1" => "88 N Avondale Rd # 100",
        "city" => "Avondale Estates",
        "state" => "GA",
        "zip" => "30002",
        "country" => "US",
        "phone" => "7702959986"
    ));

    // Parcel Information
    $parcel = Shippo_Parcel::create(array(
        "length"=> "16",
        "width"=> "14",
        "height"=> "5",
        "distance_unit"=> "in",
        "weight"=> "53",
        "mass_unit"=> "oz",
    ));

    // Create Shipment Record
    $shipment = Shippo_Shipment::create(
        array(
            "address_from" => $from,
            "address_to" => $to,
            "parcels" => $parcel,
            "async" => false
        )
    );
    logSuccess("SUCCESS: SHIPPO ".$shipment["object_id"]." successfully completed.");

    /* CMS: Set shippoTranscationID field */
    $order->setShippotransactionid($shipment["object_id"]); // This sets the shippo transaction id on the row in the table. The changes are queued to be saved
}catch(Exception $e) {
    http_response_code(400);
    exitWithError("SHIPPO: ".$e->getMessage());
}

/* TAXJAR: If salesTaxAmt > 0, Create new tax record */
if(floatval($order->getTaxamt()) > 0 ){
    // Handle test records and append date for uniqueID
    $orderNumberForTax = $order->getId();
    if (((int)$orderNumberForTax > 89) && ((int)$orderNumberForTax < 100)) {
        $timestamp = getServerDateTime();
        $orderNumberForTax = $timestamp;
    }

    try {
        $result = $tax_client->createOrder([
            'transaction_id' => $orderNumberForTax,
            'transaction_date' => time(),
            'to_country' => $shipToAddress->getCountrycode(),
            'to_city' => $shipToAddress->getCity(),
            'to_zip' => $shipToAddress->getZip(),
            'to_state' => $shipToAddress->getStatecode(),
            'to_street' => $shipToAddress->getAddress1() . ' ' . $shipToAddress->getAddress2(),
            'amount' => $order->getSubtotal(),
            'shipping' => $order->getShippingamt(),
            'sales_tax' => $order->getTaxamt()
        ]);
        logSuccess("SUCCESS: TAXJAR ".$orderNumberForTax." successfully completed.");

        /* CMS: Set taxJarTranscationID field */
        $order->setTaxjartransactionid($orderNumberForTax); // This sets the taxjar transaction id on the row in the table. The changes are queued to be saved
    }catch(Exception $e){
        http_response_code(400);
        exitWithError("TAXJAR: ".$e->getMessage());
    }
}

/* CMS: Set new statusID */
$order->setStatusid("New");

/* CMS: Save updated Order record */
$order->save(); // This actually saves all of the queued changes to the database

/* RETURN RESULTS */
logSuccess("SUCCESS: New order ".$order->getOrdernum()." successfully completed.");
echo(json_encode([
    "successful" => true,
    "error_message" => '',
    "order_number" => $order->getOrdernum()
]));

?>
