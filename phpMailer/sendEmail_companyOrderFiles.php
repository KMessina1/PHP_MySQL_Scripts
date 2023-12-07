<?php
/*-----------------------------------------------------------------------------------------------------------------------
    File: sendEmail_companyOrderFiles.php
  Author: Kevin Messina
 Created: Oct. 21, 2016
Modified: Nov. 17, 2018

©2016-2018 Creative App Solutions, LLC. - All Rights Reserved.
-----------------------------------------------------------------------------------------------------------------------
NOTES:  Requires SendGrid SDK GitHub project installed on server.
        https://github.com/sendgrid/sendgrid-php

2018/11/17 - Updated Include files.
2018/10/23 - Updated to current CMS standards.
2018/10/06 - Updated Server Paths.
2018/09/14 - Updated to SendGrid API SDK.
2018/08/25 - Converted to CMS Logging.
2018/06/13 - Updated to current CMS settings.
-----------------------------------------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
https://creativeapps.us/client-tools/squareframe/scripts/phpMailer/sendEmail_companyOrderFiles.php?

https://sqframe.com/client-tools/squareframe/scripts/phpMailer/sendEmail_companyOrderFiles.php?
OrderNum=2285&
OrderDate=2018-11-17&
CustomerEmail=kmessina@creativeapps.us&
CustomerPhone=(631)%20923-1270&
CustomerName=Kevin%20Messina&
CustomerNum=90&
attachmentFilenames=OrderConfirmation_2285.pdf,Order_2285.pdf,Photo_2285_4x6bl_1.png&
appVersion=2.12.01&
calledFromApp=Browser&
testErrorIssueEmail=0&
dontSendEmail=0&
debug=1
*/
// Alternate date if not needing conversion.
// OrderDate=2018-10-10&
// OrderDate=Oct%2013%2C%202018&

$version = "2.01a";
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

/* PARAMETERS TO PASS IN */
$orderNum = (int)$_GET["OrderNum"];
$orderDate = (string)$_GET["OrderDate"];
$customer_email = (string)$_GET["CustomerEmail"];
$customer_phone = (string)$_GET["CustomerPhone"];
$customer_name = (string)$_GET["CustomerName"];
$customer_number = (int)$_GET["CustomerNum"];
$attachmentFilenamesStringArray = (string)$_GET["attachmentFilenames"];

/* Convert $orderDate to YYYY-MM-DD format for backward compatibility if param not in that format */
$orderDate = returnDateToFormat($dateFormat_Month_comma_Y_Day,$orderDate);

/* Build Attachments */
$attachmentFilenames = explode(",", $attachmentFilenamesStringArray);
$numAttachments = count($attachmentFilenames);

/* Build Attachment Text for Email Body */
$attachmentsText = "";
for($counter=0; $counter<$numAttachments; $counter++) {
    $filename = $attachmentFilenames[$counter];
    $attachmentsText .= ($counter + 1).") ".$filename."<br/>";
}
writeToStackAndConsole(__LINE__,"Attachment Text",$attachmentsText);

$title = "INPUT PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> orderNum: $orderNum";
$msg .= "\n|-> orderDate: $orderDate";
$msg .= "\n|-> customer_email: $customer_email";
$msg .= "\n|-> customer_phone: $customer_phone";
$msg .= "\n|-> customer_name: $customer_name";
$msg .= "\n|-> customer_number: $customer_number";
$msg .= "\n|-> attachmentFilenames: $attachmentFilenamesStringArray";
$msg .= "\n\n** CALCULATED PARAMS **:";
$msg .= "\n|-> number of Attachments: $numAttachments";
$msg .= "\n|-> attachment Filenames Exploded: $attachmentsText";
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
        $orderNum,$orderDate,$customer_name,$customer_email,$customer_phone;

    $scriptTitle = "SEND COMPANY ORDER FILES";

    if ($scriptName == "") { $scriptName = "n/a"; }
    if ($calledfromAppName == "") { $calledfromAppName = "n/a"; }
    if ($calledfromAppVersion == "") { $calledfromAppVersion = "n/a"; }
    if ($error == "") { $error = "Unkown error occurred."; }
    $error = "Error: $error\n\nStack: ".json_encode($stack);

    if ($orderNum == "") { $orderNum = "n/a"; }
    if ($orderDate == "") { $orderDate = "n/a"; }
    if ($customer_name == "") { $customer_name = "n/a"; }
    if ($customer_phone == "") { $customer_phone = "n/a"; }
    if ($customer_email == "") { $customer_email = "n/a"; }

    send_MailerIssueEmail($scriptName,$error,$orderNum,$orderDate,$calledfromAppName,
        $calledfromAppVersion,$scriptTitle,$customer_name,$customer_phone,$customer_email);
}

/* Build table Info */
$table_orderInfo = "<br/><tr><td>$orderNum</td>";
$table_orderInfo .= "<td>$orderDate</td>";
$table_orderInfo .= "</tr>";

$table_customerInfo = "<br/><tr><td>$customer_name</td>";
$table_customerInfo .= "<td>$customer_number</td>";
$table_customerInfo .= "<td>$customer_phone</td>";
$table_customerInfo .= "<td>$customer_email</td>";
$table_customerInfo .= "</tr>";

$table_attachmentInfo = "<br/><tr><td>";
for($counter=0; $counter<$numAttachments; $counter++) {
    $filename = $attachmentFilenames[$counter];
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $ext = trim(strtoupper($extension));
    $isPNG = (bool)(stripos($ext,"PNG") !== false);
    if ($isPNG) {
        $table_attachmentInfo .= ($counter + 1).") $attachmentFilenames[$counter] -> .jpg<br/>";
    }else{
        $table_attachmentInfo .= ($counter + 1).") $attachmentFilenames[$counter]<br/>";
    }
}
$table_attachmentInfo .= "</td></tr>";

/* EMAIL: Params */
$companyName = "Squareframe";
$subject = "New Order Received (#$orderNum)";

/* EMAIL: Body */
$body_title = "<p style=\"text-align: center;\">
    <b>SQUAREFRAME NEW ORDER RECEIVED</b>
    <br/>
</p>";

$body_tableStyle = "<style>
    table,th,td { border: 1px solid black; border-collapse: collapse; padding: 5px; }
    th { text-align: left; }
</style>";

$body_orderInfo = "<table style=\"width:100%\">
<tr>
    <th style=\"color:white\"; bgcolor=\"LightSlateGray\"; colspan=\"2\">ORDER INFORMATION</th>
</tr>
<tr>
    <th style=\"color:black\"; bgcolor=\"silver\";>Number</th>
    <th style=\"color:black\"; bgcolor=\"silver\";>Date</th>
</tr>
$table_orderInfo
</table>";

$body_customerInfo =
"<table style=\"width:100%\">
<tr>
    <th style=\"color:white\"; bgcolor=\"LightSlateGray\"; colspan=\"4\">CUSTOMER INFORMATION</th>
</tr>
<tr>
    <th style=\"color:black\"; bgcolor=\"silver\";>Name</th>
    <th style=\"color:black\"; bgcolor=\"silver\";>ID</th>
    <th style=\"color:black\"; bgcolor=\"silver\";>Phone</th>
    <th style=\"color:black\"; bgcolor=\"silver\";>Email</th>
</tr>
$table_customerInfo
</table>";

$body_attachmentInfo =
"<table style=\"width:100%\">
<tr>
    <th style=\"color:white\"; bgcolor=\"LightSlateGray\"; colspan=\"1\">ATTACHMENT INFORMATION</th>
</tr>
<tr>
    <th style=\"color:black\"; bgcolor=\"silver\";>$numAttachments attached files...</th>
</tr>
$table_attachmentInfo
</table>";

$body_version =
"<p style=\"text-align: left;\">
    <br/>
    <font color=\"darkgray\">
        --- End of email ---<br/>
        (v:$calledfromAppVersion, s:$version)<br/>
    </font>
</p>";

$email_text =
    $body_title.
    $body_tableStyle.
    $body_orderInfo.
    $body_customerInfo.
    $body_attachmentInfo.
    $body_version
;

/* INITIALIZE EMAIL */
$companyName = "Squareframe";
$subject = "Order #$orderNum company confirmation";

/* EMAIL: Init */
$email = new \SendGrid\Mail\Mail();
$email->setSubject($subject);
$email->addContent($emailAttachmentType_HTML,$email_text);

/* EMAIL: Addresses */
$email->setFrom($emailAddress_orders, $companyName);
$email->addTo($emailAddress_orders,$companyName);

/* ADD ATTACHMENT(S) */
$filepath = getOrdersPath()."Order_$orderNum/";
for($counter=0; $counter<$numAttachments; $counter++) {
    $filename = $attachmentFilenames[$counter];
    $fileContentType = getMIME_TypeFromExtension($filename);
    $fullFilePath = $filepath.$filename;

    if ($fileContentType == "image/png") {
        try {
            $newFile = str_replace(".png", ".jpg", $filename);
            $fullNewFilePath = getOrdersPath()."Order_$orderNum/$newFile";
            $compressionPercentage = 70; // 1 to 100%
	        ini_set('memory_limit', '-1');
            $tempImage = imagecreatefrompng($fullFilePath);
            if ($debug) {
                if ($tempImage == null) { echo ("<br/>Memory Image is null !!"); }
                else {echo ("<br/>Created Memory Image from PNG...");}
            }
            imagejpeg($tempImage, $fullNewFilePath, $compressionPercentage);
            if ($debug) { echo ("<br/>Save & Compress Memory Image to JPG Image..."); }
            imagedestroy($tempImage);
            if ($debug) { echo ("<br/>Destroy Memory Image..."); }

            $filename = $newFile;
            $fullFilePath = $fullNewFilePath;
            $fileContentType = getMIME_TypeFromExtension($newFile);
        } catch (Exception $e) {
            logExceptionError($e->getMessage());
        }
    }

    $title = "ATTACHMENT FILENAMES:";
    $msg = "\n$title";
    $msg .= "\n|-> filename: $filename";
    $msg .= "\n|-> fullFilePath: $fullFilePath";
    $msg .= "\n|-> fileContentType: $fileContentType";
    writeMsgToStackAndConsole(__LINE__,"",$msg);

    $file_encoded = file_get_contents($fullFilePath);
    $email->addAttachment($file_encoded,$fileContentType,$filename,"attachment");
    writeToStackAndConsole(__LINE__,$title,$filename);
}

/* Log Email params */
$title = "EMAIL PARAMS:";
$msg = "\n$title";
$msg .= "\n|-> From: $emailAddress_orders";
$msg .= "\n|-> From Name: $companyName";
$msg .= "\n|-> To: $emailAddress_orders";
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

/* Update CMS Company Order Files Mailer Date */
if ($success) {
    $timestamp = getServerDateAndTime();

    $db = new CMS();
    $db->connectToAndinitDB();
    $db->setTableName($table_orders);
    $success = $db->update("mailer_compFilesDate ='$timestamp'","orderNum='$orderNum'");
} else {
    logExceptionError("SendGrid Send Email Failed, bypassing updating CMS.");
}

/* RETURN RESULTS */
$records = null;
$found = ($attachmentFilenames !=null) ?1 :0;

echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
