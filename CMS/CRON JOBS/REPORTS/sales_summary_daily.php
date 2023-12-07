<?php
/*--------------------------------------------------------------------------------------
    File: sales_summary_daily.php
  Author: Kevin Messina
 Created: Mar. 28, 2018
Modified: Jun. 22, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
--------------------------------------------------------------------------------------------------------------
NOTES:

2018_06_22 - Updated to latest settings.
------------------------------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/cronJobs/sales_summary_daily.php
*/

$version = "1.09c";

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
    $todaysExtendedate = getServerExtendedDate();

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
    $query = "SELECT * FROM orders WHERE orderDate='$todaysDate' AND orderNum>=100 ORDER BY customer_lastName ASC;";
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) { $newOrders[] = $r; }
    $foundNew = (int)count($newOrders);

    $newList = null;
    for($counter=0; $counter<$foundNew; $counter++) {
        $order = $newOrders[$counter];
        $orderNum = (string)$order["orderNum"];
        $lastName = (string)$order["customer_lastName"];
        $trackingNum = (string)$order["trackingNum"];
        $newList .= '<br><tr><td>'.($counter + 1).') '.$orderNum.'</td><td>'.$lastName.'</td><td>'.$trackingNum.'</td></tr>';
    }

    if ($newList == null) { $newList = '<tr><td colspan="3">'.'n/a'.'</td></tr>'; }

    /* PERFORM QUERY: Shipped Orders */
    $shippedOrders = null;
    $query = "SELECT * FROM orders WHERE shippedDate='$todaysDate' ORDER BY customer_lastName ASC;";
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) { $shippedOrders[] = $r; }
    $foundShipped = (int)count($shippedOrders);

    $shippedList = null;
    for($counter=0; $counter<$foundShipped; $counter++) {
        $order = $shippedOrders[$counter];
        $orderNum = (string)$order["orderNum"];
        $lastName = (string)$order["customer_lastName"];
        $trackingNum = (string)$order["trackingNum"];
        $shippedList .= '<br><tr><td>'.($counter + 1).') '.$orderNum.'</td><td>'.$lastName.'</td><td>'.$trackingNum.'</td></tr>';
    }

    if ($shippedList == null) { $shippedList = '<tr><td colspan="3">'.'n/a'.'</td></tr>'; }

    /* PERFORM QUERY: Delivered Orders */
    $deliveredOrders = null;
    $query = "SELECT * FROM orders WHERE deliveredDate='$todaysDate' ORDER BY customer_lastName ASC;";
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) { $deliveredOrders[] = $r; }
    $foundDelivered = (int)count($deliveredOrders);

    $deliveredList = null;
    for($counter=0; $counter<$foundDelivered; $counter++) {
        $order = $deliveredOrders[$counter];
        $orderNum = (string)$order["orderNum"];
        $lastName = (string)$order["customer_lastName"];
        $trackingNum = (string)$order["trackingNum"];
        $deliveredList .= '<br><tr><td>'.($counter + 1).') '.$orderNum.'</td><td>'.$lastName.'</td><td>'.$trackingNum.'</td></tr>';
    }

    if ($deliveredList == null) { $deliveredList = '<tr><td colspan="3">'.'n/a'.'</td></tr>'; }

    /* PERFORM QUERY: Issue Orders */
    $issueOrders = '';
    $query = "SELECT * FROM issues WHERE status='Open' ORDER BY id DESC;";
    $results = mysqli_query($connection, $query);
    while ($r = mysqli_fetch_array($results)) { $issueOrders[] = $r; }
    $foundIssues = (int)count($issueOrders);

    $issueList = null;
    for($counter=0; $counter<$foundIssues; $counter++) {
        $order = $issueOrders[$counter];
        $orderNum = (string)$order["orderID"];
        $issue = (string)$order["issue"];
        $issueList .= '<br><tr><td>'.($counter + 1).') '.$orderNum.'</td><td>'.$issue.'</td></tr>';
    }

    if ($issueList == null) { $issueList = '<tr><td colspan="2">'.'n/a'.'</td></tr>'; }

    /* PROCESS RESULTS */
    $success = (bool)(($foundNew > 0) && ($foundShipped > 0) && ($foundDelivered > 0) && ($foundIssues > 0));
    $msg = 'Found NEW: '.$foundNew.', SHIPPED: '.$foundShipped.' DELIVERED: '.$foundDelivered.' ISSUES: '.$foundIssues;

    if ($success == false) {
        echo false;
        die("No new orders.");
    }

    /* FORMAT EMAIL */
    $timestamp = getServerDateTimeConverted();
    $email_text =
    '<style>
        table,th,td { border: 1px solid black; border-collapse: collapse; padding: 5px; }
        th { text-align: left; }
    </style>
    <p style="text-align: center;">
        <b>SQUAREFRAME DAILY SALES SUMMARY FOR </b><br>
        '.$todaysExtendedate.'<style="text-align: left;">
    </p>
    <br>
    <table style="width:100%">
        <tr>
        <th style="color:white"; bgcolor="LightSlateGray"; colspan="3">'.$foundNew.' NEW ORDERS</th>
      </tr>
      <tr>
        <th style="color:black"; bgcolor="silver";>Order Number</th>
        <th style="color:black"; bgcolor="silver";>Last Name</th>
        <th style="color:black"; bgcolor="silver";>Tracking Number</th>
      </tr>
      '.$newList.'
    </table>
    <br>
    <table style="width:100%">
        <tr>
            <th style="color:white"; bgcolor="LightSlateGray"; colspan="4">'.$foundShipped.' SHIPPED ORDERS</th>
        </tr>
        <tr>
            <th style="color:black"; bgcolor="silver";>Order Number</th>
            <th style="color:black"; bgcolor="silver";>Last Name</th>
            <th style="color:black"; bgcolor="silver";>Tracking Number</th>
        </tr>
        '.$shippedList.'
    </table>
    <br>
    <table style="width:100%">
        <tr>
            <th style="color:white"; bgcolor="LightSlateGray"; colspan="4">'.$foundDelivered.' DELIVERED ORDERS</th>
        </tr>
        <tr>
            <th style="color:black"; bgcolor="silver";>Order Number</th>
            <th style="color:black"; bgcolor="silver";>Last Name</th>
            <th style="color:black"; bgcolor="silver";>Tracking Number</th>
        </tr>
        '.$deliveredList.'
    </table>
    <table style="width:100%"><tr><th style="color:white"; bgcolor="LightSlateGray"; colspan="2">'.$foundIssues.' ORDERS WITH ISSUES</th></tr>
        <tr><th style="color:black"; bgcolor="silver";>Order Number</th><th style="color:black"; bgcolor="silver";>Issue</th></tr>'.$issueList.'
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
    $email->Subject = 'Daily Sales Summary ('.$todaysDate.')';
    $email->Body = $email_text;
    $email->AddAddress( $email_developer );
    $email->AddAddress( $email_manager );

    $success = $email->send();
    $msg = 'Daily Sales Summary email to Manager for today: '.$todaysDate;
    ($success ?logSuccess($msg.' Succeeded.') :logFailure($msg.' Failed.'));
} catch (Exception $e) {
    logFailure("Connect to CMS Server: ".$e->getMessage());
}

// echo $email_text;

echo $success;

?>
