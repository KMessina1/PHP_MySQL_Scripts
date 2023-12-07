<?php
/*--------------------------------------------------------------------------------------
     File: convertResetOrders.php
   Author: Kevin Messina
  Created: Apr 13, 2018
 Modified:

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:
--------------------------------------------------------------------------------------*/

$version = "1.02a";

require_once($_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/scripts/funcs.php");

$successCustomers = false;
$successAddresses = false;
$successCarts = false;
$successOrders = false;

try {
    /* INIT DB */
    $connection = connectToCMS();

    /* GET INPUT PARAMS */

    /* Delete Customers */
    $query = "DELETE FROM customers WHERE id>99;";
    $results = mysqli_query($connection, $query);
    $successCustomers = (bool)($results == "1");
    if ($successCustomers == true) {
        logSuccess("All Customers Deleted");
    }else{
        logFailure("FAILED! All Customers Not Deleted");
    }

    /* Delete Addresses */
    $query = "DELETE FROM addresses WHERE id>99;";
    $results = mysqli_query($connection, $query);
    $successAddresses = (bool)($results == "1");
    if ($successAddresses == true) {
        logSuccess("All Addresses Deleted");
    }else{
        logFailure("FAILED! All Addresses Not Deleted");
    }

    /* Delete Carts */
    $query = "DELETE FROM carts WHERE id>99;";
    $results = mysqli_query($connection, $query);
    $successCarts = (bool)($results == "1");
    if ($successCarts == true) {
        logSuccess("All Carts Deleted");
    }else{
        logFailure("FAILED! All Carts Not Deleted");
    }

    /* Delete Orders */
    $query = "DELETE FROM orders WHERE orderNum>99;";
    $results = mysqli_query($connection, $query);
    $success = (bool)($results == "1");
    $successOrders = (bool)($results == "1");
    if ($successOrders == true) {
        logSuccess("All Orders Deleted");
    }else{
        logFailure("FAILED! All Orders Not Deleted");
    }

    /* PROCESS RESULTS */
    $success = (bool)($results == "1");
} catch (Exception $e) {
    logFailure("Connect to CMS Server: ".$e->getMessage());
    $success = false;
}

/* RETURN RESULTS */
echo($success);

?>
