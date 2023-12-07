<?php
/*-------------------------------------------------------------------------------------------
    File: sendEmail_customerTracking.php
  Author: Kevin Messina
 Created: Nov. 15, 2017
Modified: Dec. 17, 2018

©2017-2108 Creative App Solutions, LLC. - All Rights Reserved.
---------------------------------------------------------------------------------------------
NOTES:  Requires SendGrid SDK GitHub project installed on server.
        https://github.com/sendgrid/sendgrid-php

2018/12/17 - Updated to use $db-> Class.
2018/11/17 - Updated Include files.
2018/10/23 - Updated to current CMS standards.
2018/10/04 - Updated Server Paths.
2018/09/14 - Updated to SendGrid API SDK.
2018-09-11 - Updated to show extended date to user, use central email address functions.
2018-08-31 - Updated CMS field names.
2018-08-25 - Converted to CMS Logging.
2018-06-13 - Updated to current CMS settings.
-------------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://creativeapps.us/client-tools/squareframe/scripts/phpMailer/sendEmail_customerTracking.php?

https://sqframe.com/client-tools/squareframe/scripts/phpMailer/sendEmail_customerTracking.php?
orderNum=2285&
orderDate=Oct%2013%2C%202018&
email=kmessina@creativeapps.us&
name=Kevin%20Messina&
address1=48%20State%20Place&
address2=&
city=Huntington&
state=NY&
country=US&
zip=11743&
appVersion=2.12.01&
calledFromApp=Browser&
testErrorIssueEmail=0&
dontSendEmail=0&
debug=1
*/
// Alternate date if not needing conversion.
// OrderDate=2018-10-10&

$version = "2.03a";
$category = "MAILER";

/* INITIALIZE */
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/configure.php");
require_once(getScriptsPath()."funcs_mailer.php");

/* INIT TEST ERROR EMAIL */
$testErrorIssueEmail = isset($_GET["testErrorIssueEmail"]) ? (bool)$_GET["testErrorIssueEmail"] : false;
if ($testErrorIssueEmail) {
    echo "<div style='font-size:2.50em;color:white;background-color:red;'>!! testErrorIssueEmail = TRUE</div><br/><br>";
}

/* INIT DON'T SEND EMAIL FOR TEST */
$dontSendEmail = isset($_GET["dontSendEmail"]) ? (bool)$_GET["dontSendEmail"] : false;
if ($dontSendEmail) {
    echo "<div style='font-size:2.50em;color:white;background-color:red;'>!! dontSendEmail = TRUE</div><br/><br>";
}

/* GET CMS EMAIL ADDRESSES */
listEmailAddresses();

/* GET INPUT PARAMS */
$orderNum = (string)$_GET["orderNum"];
$orderDate = (string)$_GET["orderDate"];
$customer_email = (string)$_GET["email"];
$name = (string)$_GET["name"];
$address1 = (string)$_GET["address1"];
$address2 = (string)$_GET["address2"];
$city = (string)$_GET["city"];
$state = (string)$_GET["state"];
$country = (string)$_GET["country"];
$zip = (string)$_GET["zip"];

/* Convert $orderDate to YYYY-MM-DD format for backward compatibility if param not in that format */
$converted_orderDate = returnDateToFormat($dateFormat_Month_comma_Y_Day,$orderDate);

/* Build Address */
$title = "Combined Address Lines 1 & 2:";
$address = $address1;
if ($address2 != "") {
    $address .= "<br/>$address2";
}
writeMsgToStackAndConsole(__LINE__,$title,$address);

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|->orderNum: $orderNum";
$msg .= "\n|->orderDate: $orderDate (converted: $converted_orderDate)";
$msg .= "\n|->customer_email: $customer_email";
$msg .= "\n|->name: $name";
$msg .= "\n|->address1: $address1";
$msg .= "\n|->address2: $address2";
$msg .= "\n|->city: $city";
$msg .= "\n|->state: $state";
$msg .= "\n|->country: $country";
$msg .= "\n|->zip: $zip";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* Test the issue error email */
if ($testErrorIssueEmail) {
    sendIssueEmail("testErrorIssueEmail = TRUE");
}

/* INIT PARAMS */
initDefaults();
$trackingID = "";

/* FUNCTIONS */
function sendIssueEmail($error) {
    global $scriptName,$calledfromAppName,$calledfromAppVersion,$stack,
        $name,$customer_email;

    $scriptTitle = "SEND CUSTOMER TRACKING NUMBER";

    if ($scriptName == "") { $scriptName = "n/a"; }
    if ($calledfromAppName == "") { $calledfromAppName = "n/a"; }
    if ($calledfromAppVersion == "") { $calledfromAppVersion = "n/a"; }
    if ($error == "") { $error = "Unkown error occurred."; }
    $error = "Error: $error\n\nStack: ".json_encode($stack);

    if ($orderNum == "") { $orderNum = "n/a"; }
    if ($orderDate == "") { $orderDate = "n/a"; }
    $customer_name = ($name == "") ?"n/a" :$name ;
    $customer_phone = "n/a";
    if ($customer_email == "") { $customer_email = "n/a"; }

    send_MailerIssueEmail($scriptName,$error,$orderNum,$orderDate,$calledfromAppName,
        $calledfromAppVersion,$scriptTitle,$customer_name,$customer_phone,$customer_email);
}

/* PROCESSES */
try {
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);
    $records = $db->fetchRecordsWhere("orderNum='$orderNum'","");
    $found = $db->numRecords;
    $success = $db->hasRecords;

    if ($found > 0) {
        $record = $records[0];
        $trackingID = (string)$record["trackingNum"];
    }

    if ($trackingID == "") {
        $trackingID = "n/a at this time.";
    }

    writeToStackAndConsole(__LINE__,"","Order tracking# $trackingID.");
}catch(Exception $e){
    logExceptionError($e->getMessage());
}

/* EMAIL: Params */
$companyName = "Squareframe";
$subject = "Your Squareframe order #$orderNum has been shipped!";
$logoImagePath = getLogoPath()."squareframe_mailer.jpg";

/* EMAIL: Body */
$email_text = "
    <img src=\"$logoImagePath\"/><br/>
    <br/>
    Order #$orderNum placed on $converted_orderDate has shipped via U.S. Postal Service (USPS) to the below address:<br/>
    <br/>
    $name<br/>
    $address<br/>
    $city, $state $zip $country<br/>
    <br/>
    Tracking Number: $trackingID<br/>
    <br/>
    Tap the Tracking link for more information. Please note that it may take 24-hours for your tracking information to update.<br/>
    <br/>
    USPS Tracking link: https://tools.usps.com/go/TrackConfirmAction_input?origTrackNum=$trackingID<br/>
    <br/>
    We hope to see you again soon!<br/>
    <br/>
    Sincerely,
    <br/>
    <br/>
    The $companyName Team<br/>
    $emailAddress_orders<br/>
    <br/>
    <br/>
    <font color=\"darkgray\">--- End of email ---<br/>
    (v:$calledfromAppVersion, s:$version)</font></p>
";

/* EMAIL: Init */
$email = new \SendGrid\Mail\Mail();
$email->setSubject($subject);
$email->addContent($emailAttachmentType_HTML,$email_text);

/* EMAIL: Addresses */
$email->setFrom($emailAddress_orders, $companyName);
$email->addTo($customer_email,"");
$email->addBcc($emailAddress_orders,"");

/* EMAIL: Attachment(s) */

/* Log Email params */
$msg = "\nEMAIL PARAMS:";
$msg .= "\n|-> From: $emailAddress_orders";
$msg .= "\n|-> From Name: $companyName";
$msg .= "\n|-> To: $customer_email";
$msg .= "\n|-> BCC: $emailAddress_orders";
$msg .= "\n|-> Subject: $subject";
$msg .= "\n|-> Body: $email_text";
writeMsgToStackAndConsole(__LINE__,"EMAIL PARAMS:",$msg);

/* PROCESSES */
try {
    /* EMAIL: Send */
    if ($dontSendEmail == false) {
        $sendgrid = new \SendGrid($emailSendGridAPIKey);
        $retries = 0;
        do{
            $response = $sendgrid->send($email);
            $statusCode = (int)$response->statusCode();
            $success = (bool)(($statusCode >= 200) && ($statusCode < 300));
            $retries ++;
            if ($success == false) {
                writeMsgToStackAndConsole(__LINE__,"SENDGRID RETURN STATUS FAILED, RETRY # $retries:",$statusCode);
            }
        }while (($retries < 5) && ($success == false));

        /* Process Results */
        ($success) ?logSuccess($msg) :logFailure($msg);

        $successText = ($success) ?"Yes" :"No";
        $msg = "SENDGRID RETURN STATUS:";
        $msg .= "\n|-> Status Code: $statusCode";
        $msg .= "\n|-> Succeeded: $successText";
        writeMsgToStackAndConsole(__LINE__,"SENDGRID RETURN STATUS:",$msg);

        $successText = ($success) ?"Succeeded." :"Failed.";
        $msg = "Sendgrid send email for script: $scriptName $successText";
        writeToStackAndConsole(__LINE__,"SENDGRID: Send Email...",$msg);

        /* Update CMS Tracking Mailer Date */
        if ($success) {
            $db->setTableName($table_orders);
            $timestamp = getServerDateAndTime();

            $success = $db->update("mailer_trackingDate ='$timestamp'","orderNum='$orderNum'");
        }else{
            logExceptionError($msg);
            sendIssueEmail($msg);
        }
    }else{
        writeToStackAndConsole(__LINE__,"DON'T SEND EMAIL = TRUE","Skipping the send of email for test.");
        $success = true;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    logExceptionError($error);
    sendIssueEmail($error);
}

/* RETURN RESULTS */
$records = null;
$found = (int)$success;

echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
