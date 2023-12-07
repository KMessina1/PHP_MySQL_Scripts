<?php
/*--------------------------------------------------------------------------------------
     File: resetTestOrders.php
   Author: Kevin Messina
  Created: Mar 30, 2018
 Modified:

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:
--------------------------------------------------------------------------------------*/

$version = "1.01d";

$success = false;

try {
    /* INIT DB */
    $connection = connectToCMS();

    /* GET INPUT PARAMS */

    /* PERFORM QUERY: Does Customer Already Exist? */
    $query = "UPDATE orders SET
 statusID='Unpaid',
 paymentAuthorized=0,
 couponID=0,
 subtotal=25.00,
 taxAmt=0.00,
 shippingAmt=5.00,
 discountAmt=0.00,
 totalAmt=30.00,
 stripeTransactionID='',
 shippoTransactionID='',
 taxJarTransactionID=''
 WHERE orderNum<100;
";
    $results = mysqli_query($connection, $query);

    /* PROCESS RESULTS */
    $success = (bool)($results == "1");
} catch (Exception $e) {
    logFailure("Connect to CMS Server: ".$e->getMessage());
    $success = false;
}

/* RETURN RESULTS */
echo($success);

?>
