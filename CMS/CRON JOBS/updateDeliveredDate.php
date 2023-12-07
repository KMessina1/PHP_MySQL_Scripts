<?php
/*--------------------------------------------------------------------------------------
     File: updateDeliveredDate.php
   Author: Kevin Messina
  Created: Oct. 17, 2018
 Modified: Nov. 17, 2018

 ©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
 NOTES:

2018/11/17 - Updated Include files.
2018/11/13 - Andy Stein added SHIPPO SDK Location.
            - Updated with Debug info.
--------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/cms/cronJobs/updateDeliveredDate.php?
calledFromApp=Browser&
debug=1
*/

$version = "2.01a";
$category = "ORDERS";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");
/* INCLUDE MAILER */
require_once(getScriptsPath()."funcs_mailer.php");

/* GET INPUT PARAMS */

/* INIT PARAMS */
initDefaults();
$ordersUpdated = array();

/* FUNCTIONS */

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);
    $records = $db->fetchRecordsWhere("statusID='Shipped'");

    /* Loop through orders and check Shippo Delivered field */
    if ($db->hasRecords) {
        $title = "CHECK FOR DELIVERY DATE & UPDATE";
        $msg = "There are $db->numRecords orders with SHIPPED status, starting SHIPPO lookups...";
        writeToStackAndConsole(__LINE__,$title,$msg);

        /* INITIALIZE SHIPPO */
        \Shippo::setApiKey("shippo_live_80c2563f92b1bc7ac2c80164489abfcf10841cc5");
        writeToStackAndConsole(__LINE__,"SHIPPO","Initializing...");
        writeToStackAndConsole(__LINE__,"============= SHIPPO: UPDATE DELIVERED DATES: ".getServerDateAndTimeAMPM()." =============\n","");

        foreach($records as $record) {
            $orderID = (int)$record["id"];
            $trackingNum = (string)$record["trackingNum"];
            $existingNotes = (string)$record["notes"];

            $customerID = (int)$record["customerID"];
            $customer_firstName = (string)$record["customer_firstName"];
            $customer_lastName = (string)$record["customer_lastName"];
            $customer_name = "$customer_lastName, $customer_firstName";

            $shipTo_city = (string)$record["shipTo_city"];
            $shipTo_state = (string)$record["shipTo_stateCode"];
            $location = "$shipTo_city, $shipTo_state";

            /* Set SHIPPO params */
            $status_params = array(
                'id' => $trackingNum,
                'carrier' => 'usps'
            );
            writeToStackAndConsole(__LINE__,$title,"Shippo Tracking Params (id= $trackingNum, carrier= 'usps').");

            $status = Shippo_Track::get_status($status_params);
            // outputArrayToConsole($status); // Debug

            $tracking_status = $status['tracking_status'];
            // outputArrayToConsole($tracking_status); // Debug
                $status = $tracking_status["status"];
                $status_details = $tracking_status["status_details"];
                $status_date = $tracking_status["status_date"];

                $substatus = $tracking_status["substatus"];
                    $substatus_text = $substatus["text"];
                    $substatus_action_required = $substatus["action_required"];

                $loc = $tracking_status["location"];
                    $location_city = $loc["city"];
                    $location_state = $loc["state"];
                    $location_zip = $loc["zip"];
                    $location_country = $loc["country"];

            $deliveryDate = returnDateToFormat($dateFormat_Month_Day_comma_Year_at_hi_A_T,$status_date);
            $deliveredDate = returnDateToFormat($dateFormat_Ymd_His,$status_date);
            writeToStackAndConsole(__LINE__,"Date Delivered",$deliveryDate);

            $title = "SHIPPO RECORD:";
            $msg = "$title<br>";
            $msg .= "|-> DELIVERED:<br>";
            $msg .= "|->-> status: $status<br>";
            $msg .= "|->-> status_details: $status_details<br>";
            $msg .= "|->-> status_date: $status_date<br>";
            $msg .= "|->-> converted date: $deliveryDate<br>";
            $msg .= "|->-> SUBSTATUS:<br>";
            $msg .= "|->->-> text: $substatus_text<br>";
            $msg .= "|->->-> action_required: $substatus_action_required<br>";
            $msg .= "|->-> LOCATION:<br>";
            $msg .= "|->->-> city: $location_city<br>";
            $msg .= "|->->-> stateCode: $location_state<br>";
            $msg .= "|->->-> zip: $location_zip<br>";
            $msg .= "|->->-> countryCode: $location_country<br>";
            writeMsgToStackAndConsole(__LINE__,$title,$msg);

            $title = "SHIPPO RESULTS:";
            $msg = "Order# $orderID with tracking_status = $tracking_status was delivered on $deliveryDate.";
            writeToStackAndConsole(__LINE__,$title,$msg);

            /* Display Info for script output to logfile on server. */
            if (strtoupper($status) == "DELIVERED") {
                $title = "SHIPPO DELIVERY INFO:";

                $notes = "Delivery Details: $status_details";
                $newMsg = "";
                if ($substatus_action_required != "") {
                    $newMsg = "❌ 📦Order# $orderID, ‼️Action required: $substatus_action_required.\n";
                    $notes .= "<br/><br/>Action Required: $substatus_action_required";
                }else{
                    $newMsg = "✅ 📦Order# $orderID, 🚛$deliveryDate (📝 $status_details).\n";
                }
                appendLogFile("CRONJOB_".$scriptNameWithoutVersion.".txt",getServerDateAndTimeAMPM().": $newMsg");

                if ($existingNotes != "") {
                    $existingNotes .= "<br/><br/>";
                }
                $existingNotes .= $notes;

                $ordersUpdated[] = [
                    "id" => $orderID,
                    "delivered" => $deliveryDate,
                    "notes" => $status_details,
                    "action" => $substatus_action_required,
                    "customerID" => $customerID,
                    "name" => $customer_name,
                    "location" => $location
                ];

                /* UPDATE CART information */
                writeToStackAndConsole(__LINE__,"CMS:  Update Orders","Updating Orders to Delivered...");
                $db->setTableName($table_orders);
                $set = "
                    statusID='Delivered',
                    deliveredDate='$deliveredDate',
                    notes='$existingNotes'
                ";
                $success = $db->updateID($orderID,$set);
                $msg = "Order# $orderID was ".($success ?"Successfully" :"NOT")." updated.";
                writeMsgToStackAndConsole(__LINE__,$title,$msg);
            }
        }
    }else{
        $msg = "No orders with SHIPPED status found.";
        writeToStackAndConsole(__LINE__,"CMS:",$msg);
        appendLogFile("CRONJOB_".$scriptNameWithoutVersion.".txt",getServerDateAndTimeAMPM().": $msg\n");
    }
} catch (Exception $e) {
    logExceptionError($e->getMessage());
}

/* If any shipped -> delivered orders, Send email for Delivered orders */
 $numOrdersChanged = count((array)$ordersUpdated);
if ($numOrdersChanged > 0) {
    writeToStackAndConsole(__LINE__,"MAILER: ","Preparing to send summary email...");

    $table_changes = "<br/>";
    foreach($ordersUpdated as $item) {
        $table_changes .= "<tr>";
        $table_changes .= "<td>".$item["id"]."</td>";
        $table_changes .= "<td>".$item["customerID"]."</td>";
        $table_changes .= "<td>".$item["name"]."</td>";
        $table_changes .= "<td>".$item["location"]."</td>";
        $table_changes .= "<td>".$item["delivered"]."</td>";
        $table_changes .= "<td>".$item["notes"]."</td>";
        $table_changes .= "<td>".$item["action"]."</td>";
        $table_changes .= "</tr>";
    }

    $body_title = "<p style=\"text-align: center;\">
        <b>CHANGE ORDER STATUS SUMMARY (Shipped->Delivered)</b>
        <br/>
    </p>";

    $body_tableStyle = "<style>
        table,th,td { border: 1px solid black; border-collapse: collapse; padding: 5px; }
        th { text-align: left; }
    </style>";

    $body_changes = "<table style=\"width:100%\">
    <tr>
        <th style=\"color:white\"; bgcolor=\"LightSlateGray\"; colspan=\"7\">CMS: ORDER CHANGE (SHIPPED->DELIVERED) DETAIL(s)</th>
    </tr>
    <tr>
        <th style=\"color:black\"; bgcolor=\"silver\";>Order#</th>
        <th style=\"color:black\"; bgcolor=\"silver\";>Cust#</th>
        <th style=\"color:black\"; bgcolor=\"silver\";>Customer Name</th>
        <th style=\"color:black\"; bgcolor=\"silver\";>Delivery Location</th>
        <th style=\"color:black\"; bgcolor=\"silver\";>Delivered</th>
        <th style=\"color:black\"; bgcolor=\"silver\";>Notes</th>
        <th style=\"color:black\"; bgcolor=\"silver\";>Action Required</th>
    </tr>
    $table_changes
    </table>";

    $body_version =
    "<p style=\"text-align: left;\">
        <br/>
        <font color=\"darkgray\">
            --- End of email ---<br/>
            (v:$calledfromAppVersion, s:$version)<br/>
        </font>
    </p>";

    $email_text = $body_title.$body_tableStyle.$body_changes.$body_version;

    $mailer = new MAILER();
    $mailer->init();
    $mailer->setCompanyName("Squareframe");
    $mailer->setSubject("Order Status Change (Shipped->Delivered) Summary");
    if ($debug) {
        $mailer->setFromAddress($mailer->emailAddress_developer);
        $mailer->setToAddress($mailer->emailAddress_developer);
    }else{
        $mailer->setFromAddress($mailer->emailAddress_orders);
        $mailer->setToAddress($mailer->emailAddress_orders);
    }
    $mailer->setBody($email_text);
    $success = $mailer->sendEmail();
}

/* RETURN RESULTS */
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
