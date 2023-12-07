<?php
/*----------------------------------------------------------------------------------------
    File: ephemeral_keys.php
  Author: Sascha Wise
 Created: Mar 22, 2018
Modified: Nov. 17, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
------------------------------------------------------------------------------------------
NOTES:

2018/11/19 - Updated Include files.
2018/08/29 - Switched to LIVE keys for production.
----------------------------------------------------------------------------------------*/

$version = "2.01a";
$category = "STRIPE";

require_once('vendor/autoload.php');

/* Load Propel db info */
// This is where Propel loads the database. Look in propel.yaml for the connection info.
require_once 'generated-conf/config.php';

/* STRIPE: Secret Key */
//\Stripe\Stripe::setApiKey("sk_test_A5eTBWoS24ANyxETlElmEUyl"); // Test Key
\Stripe\Stripe::setApiKey("sk_live_XjBdnYl8erbWUpj6MPxwYdJg");  // Live Key

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
