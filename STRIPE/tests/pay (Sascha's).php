<?php
require_once('vendor/autoload.php');
require_once 'generated-conf/config.php'; // This is where Propel loads the database. Look in propel.yaml for the connection info
\Stripe\Stripe::setApiKey("sk_test_VPg4Flxsq6nvrsBLXldUqPoH");
//\Stripe\Stripe::setApiKey("pk_live_v1JGFjnFgt6Y5Oc0ixlwDSWq");
\Shippo::setApiKey("shippo_test_dbba9bf66994e34039790c61d2200867edf9ddb5");
$tax_client = TaxJar\Client::withApiKey('a60873023c5f4fcf98dd2fd9641656c9');
header('Content-Type: application/json');
if($_POST["order_id"] == null){
    http_response_code(400);
    echo(json_encode(array(
        "successful" => false,
        "error_message" => "No order_id field was provided"
    )));
}
$q = new OrdersQuery();
$order = $q->findPK($_POST["order_id"]); // This finds a new order using its primary key which in this case is the ID field
if($order == null){
    http_response_code(404);
    echo(json_encode(array(
        "successful" => false,
        "error_message" => "The order could not be found"
    )));
    return;

}
if($order->getStatusid() != "Unpaid"){
    echo(json_encode(array(
        "successful" => false,
        "error_message" => "Order is in an invalid state",
        "order_number" => $order->getOrdernum() // When using Propel you access the properties by using the function get<column_name>()
    )));
    return;
}
if($order->getTotalamt() > 0){
    try {
        $charge = \Stripe\Charge::create(array(
            "amount" => floatval($order->getTotalamt()) * 100,
            "currency" => "usd",
            "description" => "Example charge",
            "source" => $_POST["token"],
        ));
        $order->setStripetransactionid($charge["id"]);
    }catch(Exception $e) {
        http_response_code(400);
        echo(json_encode(array(
            "successful" => false,
            "error_message" => $e->getMessage(),
            "order_number" => $order->getOrdernum()
        )));
        return;
    }
}else{
    if($order->getCouponid() == null){
        http_response_code(400);
        echo(json_encode(array(
            "successful" => false,
            "error_message" => "Free order without coupon!",
            "order_number" => $order->getOrdernum()
        )));
        return;
    }
}
$cq = new CustomersQuery();
$customer = $cq->findPK($order->getCustomerid());
if($customer == null){
    http_response_code(404);
    echo(json_encode(array(
        "successful" => false,
        "error_message" => "The customer for this order could not be found",
        "order_number" => $order->getOrdernum()
    )));
    return;
}

$aq = new AddressesQuery();
$a_id = $order->getShiptoid();
$address = $aq->findPk($a_id);
if($address == null){
    http_response_code(404);
    echo(json_encode(array(
        "successful" => false,
        "error_message" => "The order does not have a shipping address",
        "order_number" => $order->getOrdernum()
    )));
    return;
}
$to = $address->genShippoAddress($customer->getFirstname() . " ".  $customer->getLastname()); // This is a function on the address class. It can be found in generated-classes/Addresses.php
try {
    $from = Shippo_Address::create(array(
        "name" => "Squareframe",
        "street1" => "88 N Avondale Rd # 100",
        "city" => "Avondale Estates",
        "state" => "GA",
        "zip" => "30002",
        "country" => "US",
        "phone" => "7702959986"));
    $parcel = Shippo_Parcel::create(array(
        "length"=> "16",
        "width"=> "14",
        "height"=> "5",
        "distance_unit"=> "in",
        "weight"=> "53",
        "mass_unit"=> "oz",
    ));
    $shipment = Shippo_Shipment::create(
        array(
            "address_from" => $from,
            "address_to" => $to,
            "parcels" => $parcel,
            "async" => false
        )
    );
}catch(Exception $e) {
    http_response_code(400);
    echo(json_encode(array(
        "successful" => false,
        "error_message" => $e->getMessage(),
        "order_number" => $order->getOrdernum()
    )));
    return;
}
$order->setShippotransactionid($shipment["object_id"]); // This sets the shippo transaction id on the row in the table. The changes are queued to be saved
$billing_address = $aq->findPK($customer->getAddressid());
if($address == null){
    http_response_code(404);
    echo(json_encode(array(
        "successful" => false,
        "error_message" => "The customer's billing address could not be found",
        "order_number" => $order->getOrdernum()
    )));
    return;
}

if(floatval($order->getTaxamt()) > 0 ){
    try {
        $result = $tax_client->createOrder([
            'transaction_id' => $order->getId(),
            'transaction_date' => time(),
            'to_country' => $billing_address->getCountrycode(),
            'to_city' => $billing_address->getCity(),
            'to_zip' => $billing_address->getZip(),
            'to_state' => $billing_address->getStatecode(),
            'to_street' => $billing_address->getAddress1() . ' ' . $billing_address->getAddress2(),
            'amount' => $order->getSubtotal(),
            'shipping' => $order->getShippedamt(),
            'sales_tax' => $order->getTaxamt()
        ]);
    }catch(Exception $e){
        http_response_code(400);
        echo(json_encode(array(
            "successful" => false,
            "error_message" => $e->getMessage(),
            "order_number" => $order->getOrdernum()
        )));
        return;
    }
}
$order->setStatusid("New");
$order->save(); // This actually saves all of the queued changes to the database
echo(json_encode([
   "successful" => true,
  "order_number" => $order->getOrdernum()
]));
