<?php
/*----------------------------------------------------------------------------------------
    File: ephemeral_keys.php
  Author: Sascha Wise
 Created: Mar 22, 2018
Modified: May 29, 2018 - Kevin Messina

©2018 Creative App Solutions, LLC. - All Rights Reserved.
------------------------------------------------------------------------------------------
2018-08-29 - Switched to LIVE keys for production.
----------------------------------------------------------------------------------------*/

$version = "1.01b";

require_once('vendor/autoload.php');
require_once 'generated-conf/config.php'; // This is where Propel loads the database. Look in propel.yaml for the connection info

/* STRIPE: Secret Key */
//\Stripe\Stripe::setApiKey("sk_test_A5eTBWoS24ANyxETlElmEUyl");
\Stripe\Stripe::setApiKey("sk_live_XjBdnYl8erbWUpj6MPxwYdJg");

$q = new CustomersQuery();
$customer = $q->findPK($_POST["customer_id"]);
$stripe_customer_id = $customer->getStripeid();
if($stripe_customer_id == null) {
    $stripe_customer = \Stripe\Customer::create();
    $stripe_customer_id = $stripe_customer["id"];
    $customer->setStripeid($stripe_customer_id);
    $customer->save();
}

if (!isset($_POST['api_version'])){
    exit(http_response_code(400));
}

try {
    $key = \Stripe\EphemeralKey::create(
        array("customer" => $stripe_customer_id),
        array("stripe_version" => $_POST['api_version'])
    );
    header('Content-Type: application/json');
    exit(json_encode($key));
} catch (Exception $e) {
    exit(http_response_code(500));
}

?>
