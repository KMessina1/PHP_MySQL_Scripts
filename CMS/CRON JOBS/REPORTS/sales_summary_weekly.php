<?php
/*----------------------------------------------------------------------------------------
    File: sales_summary_weekly.php
  Author: Kevin Messina
 Created: Mar. 29, 2018
Modified: Jun. 22, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
------------------------------------------------------------------------------------------
NOTES:

2018_06_22 - Updated to latest settings.
----------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/cronJobs/sales_summary_weekly.php
*/

$version = "1.02d";

/* INITIALIZE: Libraries */
require_once($_SERVER['DOCUMENT_ROOT'].'/mail23/class.phpmailer.php');
require_once($_SERVER['DOCUMENT_ROOT']."/client-tools/squareframe/scripts/funcs.php");

/* INITIALIZE: Script */
ini_set('date.timezone', 'America/New_York');
date_default_timezone_set('America/New_York');
setlocale(LC_MONETARY, 'en_US');

/* INIT DEFAULTS */
$scriptName = basename($_SERVER['SCRIPT_NAME']);
$stack = array('Line#'.__LINE__.' Func: '.__FUNCTION__.' 📝'."*** Script ".$scriptName." started @ ".$timestamp." ***",$dashes);
$success = false;
$records = null;
$found = 0;

/* FUNCTIONS */
try {
    /* INIT DB */
    $connection = connectToCMS();

    /* GET INPUT PARAMS */
    $todaysDate = getServerDate();
    $weekStartDate = getWeekStartDate();
    $todaysDateConverted = getTodaysDateConverted();
    $weekStartDateConverted = get30DaysStartDate();

    /* PERFORM QUERY */
    $records = null;
    $result = mysqli_query($connection, "SELECT * FROM emailer");
    while ($r = mysqli_fetch_array($result)) { $records[] = $r; }
    $found = (int)count($records);
    $success = (bool)($found > 0);
    $msg = 'Found: '.$found.' records.';
    ($success == true) ?logSuccess($msg) : logFailure($msg);

    /* RESULTS */
    if ($success == false) {
        echo false;
        die("CMS Mailer email addresses not found.");
    }

    // Corporate Email Addresses
    $email_from = 'orders@sqframe.com';
    $email_developer = 'kmessina@creativeapps.us';
    $email_manager = 'ssmith@sqframe.com';
    $email_fulfillment = 'info@edwinjarvis.com';

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

    /* PERFORM QUERY: New Orders */
    $newOrders = null;
    // Ignore order numbers < 100 as tests.
    $query = "SELECT * FROM orders WHERE orderDate>='$weekStartDate' AND orderDate<='$todaysDate' AND orderNum>=100 ORDER BY customer_lastName ASC;";
    array_push($stack,$dashes,'Query: '.$query);
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) { $newOrders[] = $r; }
    $foundNew = (int)count($newOrders);
    array_push($stack,'--> Found: '.$foundNew.' NEW orders.');

    $newList = null;
    $newTotal_Amt = 0.00;
    $newTotal_Shipping = 0.00;
    $newTotal_Shipped = 0.00;
    $newTotal_Discount = 0.00;
    $newTotal_Tax = 0.00;
    $newTotal_Items = 0;
    for($counter=0; $counter<$foundNew; $counter++) {
        $order = $newOrders[$counter];
        $orderNum = (string)$order["orderNum"];
        $orderDate = (string)$order["orderDate"];
        $orderStatus = (string)$order["statusID"];
        $lastName = (string)$order["customer_lastName"];
        $totalAmt = (float)$order["totalAmt"];
        $totalShipping = (float)$order["shippingAmt"];
        $totalShipped = (float)$order["shippedAmt"];
        $totalDiscount = (float)$order["discountAmt"];
        $totalTax = (float)$order["taxAmt"];
        $totalItems = (int)$order["photoCount"];

        $newTotal_Amt = $newTotal_Amt + $totalAmt;
        $newTotal_Shipping = $newTotal_Shipping + $totalShipping;
        $newTotal_Shipped = $newTotal_Shipped + $totalShipped;
        $newTotal_Discount = $newTotal_Discount + $totalDiscount;
        $newTotal_Tax = $newTotal_Tax + $totalTax;
        $newTotal_Items = $newTotal_Items + $totalItems;

        $newList .= '
        <tr>
            <td align="left";>'.$orderNum.'</td>
            <td align="left";>'.$orderDate.'</td>
            <td align="left";>'.$orderStatus.'</td>
            <td align="left";>'.$lastName.'</td>
            <td align="right";>'.$totalItems.'</td>
            <td align="right";>'.money_format('%(#10n', $totalShipping).'</td>
            <td align="right";>'.money_format('%(#10n', $totalShipped).'</td>
            <td align="right";>'.money_format('%(#10n', $totalDiscount).'</td>
            <td align="right";>'.money_format('%(#10n', $totalTax).'</td>
            <td align="right";>'.money_format('%(#10n', $totalAmt).'</td>
        </tr>';
    }

    if ($newList == null) { $newList .= '<tr><td colspan="10">'.'n/a'.'</td></tr>'; }

    $newTotals = '
    <tr bgcolor="AliceBlue">
        <td align="right"; colspan="4"><b>Totals</b></td>
        <td align="right"><b>'.$newTotal_Items.'</b></td>
        <td align="right"><b>'.money_format('%(#10n', $newTotal_Shipping).'</b></td>
        <td align="right"><b>'.money_format('%(#10n', $newTotal_Shipped).'</b></td>
        <td align="right"><b>'.money_format('%(#10n', $newTotal_Discount).'</b></td>
        <td align="right"><b>'.money_format('%(#10n', $newTotal_Tax).'</b></td>
        <td align="right"><b>'.money_format('%(#10n', $newTotal_Amt).'</b></td>
    </tr>';


    /* PROCESS RESULTS */
    $success = (bool)(($foundNew > 0) && ($foundShipped > 0) && ($foundDelivered > 0) && ($foundIssues > 0));
    $msg = 'Found: '.$found.' orders and Found: '.$foundIssues.'issues.';
    array_push($stack,$dashes,$msg);
    ($success == true) ?logSuccess($msg) :logFailure($msg);

    /* FORMAT EMAIL */
    $timestamp = getServerDateTimeConverted();
    $email_text =
    '<style>
        table,th,td { border: 1px solid black; border-collapse: collapse; padding: 5px; }
        th { text-align: left; }
    </style>
    <p style="text-align: center;">
        <b>SQUAREFRAME WEEKLY SALES SUMMARY FOR PAST 30-DAYS</b><br>
        '.$weekStartDateConverted.' thru '.$todaysDateConverted.'<style="text-align: left;">
    </p>
    <br>
    <table style="width:100%">
        <tr>
            <th style="color:white"; bgcolor="LightSlateGray"; colspan="10">'.$foundNew.' NEW ORDERS</th>
        </tr>
        <tr>
            <th style="text-align:left; color:black"; bgcolor="silver";>Order Number</th>
            <th style="text-align:left; color:black"; bgcolor="silver";>Order Date</th>
            <th style="text-align:left; color:black"; bgcolor="silver";>Order Status</th>
            <th style="text-align:left; color:black"; bgcolor="silver";>Last Name</th>
            <th style="text-align:right; color:black"; bgcolor="silver";>Items</th>
            <th style="text-align:right; color:black"; bgcolor="silver";>Shipping</th>
            <th style="text-align:right; color:black"; bgcolor="silver";>Shipped</th>
            <th style="text-align:right; color:black"; bgcolor="silver";>Discount</th>
            <th style="text-align:right; color:black"; bgcolor="silver";>Tax</th>
            <th style="text-align:right; color:black"; bgcolor="silver";>Total</th>
      </tr>
      '.$newList.'
      '.$newTotals.'
    </table>
    <p style="text-align: center;">
        <br>s'.$version.'<br><br>
        <b>--- End of email ---</b><br>
    </p>
    ';

    /* INITIALIZE EMAIL */
    error_reporting(E_ALL ^ E_DEPRECATED);
    $page = 'the mailout page';
    $email = new PHPMailer();
    $email->IsHTML(true);
    $email->From = $email_from;
    $email->FromName = 'Squareframe';
    $email->Subject = 'Weekly Sales Summary ('.$weekStartDate.' thru '.$todaysDate.')';
    $email->Body = $email_text;
    $email->AddAddress( $email_developer );
    $email->AddAddress( $email_manager );

    $success = $email->send();
    $msg = 'Weekly Sales Summary email to Manager for period: '.$weekStartDate.' thru '.$todaysDate;
    ($success ?logSuccess($msg.' Succeeded.') :logFailure($msg.' Failed.'));
} catch (Exception $e) {
    logFailure("Connect to CMS Server: ".$e->getMessage());
}

// echo $email_text;

echo $success;

?>
