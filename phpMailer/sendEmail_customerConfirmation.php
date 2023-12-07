<?php
/*-------------------------------------------------------------------------------------------
    File: sendEmail_customerConfirmation.php
  Author: Kevin Messina
 Created: Nov. 15, 2017
Modified: Nov. 17, 2018

©2017-2108 Creative App Solutions, LLC. - All Rights Reserved.
---------------------------------------------------------------------------------------------
NOTES:  Requires SendGrid SDK GitHub project installed on server.
        https://github.com/sendgrid/sendgrid-php

2018/11/17 - Updated Include files.
2018/10/23 - Updated to current CMS standards.
2018/10/04 - Updated Server Paths.
2018/09/14 - Updated to SendGrid API SDK.
-------------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://creativeapps.us/client-tools/squareframe/scripts/phpMailer/sendEmail_customerConfirmation.php?

https://sqframe.com/client-tools/squareframe/scripts/phpMailer/sendEmail_customerConfirmation.php?
orderNum=2285&
customerEmail=kmessina%40creativeApps.us&
customerNum=90&
attachmentFileName=Order_2285.pdf&
appVersion=2.12.01&
calledFromApp=Browser&
testErrorIssueEmail=0&
dontSendEmail=0&
debug=1
*/

$version = "2.02d";
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
$orderNum = (string)$_GET['orderNum'];
$customer_email = (string)$_GET["customerEmail"];
$customer_number = (string)$_GET["customerNum"];
$attachment = (string)$_GET["attachmentFileName"];

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> orderNum: $orderNum";
$msg .= "\n|-> customer_email: $customer_email";
$msg .= "\n|-> customer_number: $customer_number";
$msg .= "\n|-> Attachment: $attachment";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* Test the issue error email */
if ($testErrorIssueEmail) {
    sendIssueEmail("testErrorIssueEmail = TRUE");
}

/* INIT PARAMS */
initDefaults();

/* FUNCTIONS */
function sendIssueEmail($error) {
    global $scriptName,$calledfromAppName,$calledfromAppVersion,$stack,
        $orderNum,$customer_number,$customer_email;

    $scriptTitle = "SEND CUSTOMER ORDER CONFIRMATION";

    if ($scriptName == "") { $scriptName = "n/a"; }
    if ($calledfromAppName == "") { $calledfromAppName = "n/a"; }
    if ($calledfromAppVersion == "") { $calledfromAppVersion = "n/a"; }
    if ($error == "") { $error = "Unkown error occurred."; }
    $error = "Error: $error\n\nStack: ".json_encode($stack);

    if ($orderNum == "") { $orderNum = "n/a"; }
    $orderDate = "n/a";
    $customer_name = ($customer_number == "") ?"n/a" :"Customer# $customer_number" ;
    $customer_phone = "n/a";
    $customer_email = "n/a";

    send_MailerIssueEmail($scriptName,$error,$orderNum,$orderDate,$calledfromAppName,
        $calledfromAppVersion,$scriptTitle,$customer_name,$customer_phone,$customer_email);
}

/* EMAIL: Params */
$companyName = "Squareframe";
$subject = "Order #$orderNum Confirmation";
$logoImagePath = getLogoPath()."squareframe_mailer.jpg";

/* EMAIL: Body */
$email_text = "
    <img src=\"$logoImagePath\"/><br/>
    <br/>
    Order #$orderNum<br/>
    Customer #$customer_number<br/>
    <br/>
    Thank you for your order!<br/>
    <br/>
    Your order is currently processing. We will notify you as soon as your order has shipped.<br/>
    <br/>
    Please see the attached PDF for your order details.<br/>
    <br/>
    Sincerely,
    <br/>
    <br/>
    The $companyName Team<br/>
    $emailAddress_orders
    <br/>
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
$filepath = getOrdersPath()."Order_$orderNum/";
$filename = $attachment;
$fileContentType = getMIME_TypeFromExtension($filename);
$file_encoded = file_get_contents($filepath.$filename);
$email->addAttachment($file_encoded,$fileContentType,$filename,"attachment");
writeToStackAndConsole(__LINE__,"File Attachment:",$filename);

/* Log Email params */
$title = "EMAIL PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> From: $emailAddress_orders";
$msg .= "\n|-> From Name: $companyName";
$msg .= "\n|-> To: $customer_email";
$msg .= "\n|-> BCC: $emailAddress_orders";
$msg .= "\n|-> Subject: $subject";
$msg .= "\n|-> Body: $email_text";
writeMsgToStackAndConsole(__LINE__,$title,$msg);

/* SEND EMAIL */
try {
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

        if ($success == false) {
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

/* Update CMS Customer Confirmation Mailer Date */
if ($success) {
    $timestamp = getServerDateAndTime();
    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);
    $success = $db->update("mailer_confDate ='$timestamp'","orderNum='$orderNum'");
} else {
    $error = "SendGrid Send Email Failed, bypassing updating CMS.";
    logExceptionError($error);
    sendIssueEmail($error);
}

/* RETURN RESULTS */
$records = null;
$found = ($attachment !=null) ?1 :0;

echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
