<?php
/*---------------------------------------------------------------------------------------------------------------------
    File: resendEmail_companyOrderFiles.php
  Author: Kevin Messina
 Created: Oct. 21, 2016
Modified: Nov. 17, 2018

©2016-2018 Creative App Solutions, LLC. - All Rights Reserved.
-----------------------------------------------------------------------------------------------------------------------
NOTES: Requires SendGrid SDK GitHub project installed on server.
        https://github.com/sendgrid/sendgrid-php

2018-08-25 - Converted to CMS Logging.
2018-06-13 - Updated to current CMS settings.
---------------------------------------------------------------------------------------------------------------------*/

/* SAMPLE URL TO CUT & PASTE INTO BROWSER TO TEST
http://sqframe.com/client-tools/squareframe/scripts/phpMailer/resendEmail_companyOrderFiles.php?
OrderNum=2285&
OrderDate=Oct%2013%2C%202018&
CustomerEmail=kmessina@creativeapps.us&
CustomerName=Kevin%20Messina&
CustomerPhone=(631)%20923-1270&
CustomerNum=90&
attachmentFilenames=OrderConfirmation_2285.pdf,Order_2285.pdf,Photo_2285_4x6bl_1.png&
appVersion=2.12.01&
calledFromApp=Browser&
testErrorIssueEmail=0&
dontSendEmail=0&
debug=1
*/
// Alternate date if not needing conversion.
// OrderDate=2018/10/10&
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

/* GET INPUT PARAMS */
$orderNum = $_GET["OrderNum"];
$orderDate = $_GET["OrderDate"];
$appVersion = (string)$_GET["appVersion"];
$attachmentFilenamesStringArray = (string)$_GET["attachmentFilenames"];
$attachmentFilenames = explode(",", $attachmentFilenamesStringArray);
$numAttachments = count($attachmentFilenames);

/* Convert $orderDate to YYYY-MM-DD format for backward compatibility if param not in that format */
$orderDate = returnDateToFormat($dateFormat_Month_comma_Y_Day,$orderDate);

$title = "INPUT PARAMS: ORDER DETAILS";
$msg = "\n$title";
$msg = "\n• orderNum: $orderNum\n• orderDate: $orderDate";
$msg .= "\n• numAttachments: $numAttachments\n• Attachment: $attachmentFilenamesStringArray";
$msg .= "\n• appVersion: $appVersion";
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
        $orderNum,$orderDate;

    $scriptTitle = "RESEND ORDER FILES TO COMPANY";

    if ($scriptName == "") { $scriptName = "n/a"; }
    if ($calledfromAppName == "") { $calledfromAppName = "n/a"; }
    if ($calledfromAppVersion == "") { $calledfromAppVersion = "n/a"; }
    if ($error == "") { $error = "Unkown error occurred."; }
    $error = "Error: $error\n\nStack: ".json_encode($stack);

    if ($orderNum == "") { $orderNum = "n/a"; }
    if ($orderDate == "") { $orderDate = "n/a"; }
    $customer_name = "n/a";
    $customer_phone = "n/a";
    $customer_email = "n/a";

    send_MailerIssueEmail($scriptName,$error,$orderNum,$orderDate,$calledfromAppName,
        $calledfromAppVersion,$scriptTitle,$customer_name,$customer_phone,$customer_email);
}

/* BUILD ATTACHMENTS FOR HMTL Email */
$attachments = "";
for($counter=0; $counter<$numAttachments; $counter++) {
    $filename = $attachmentFilenames[$counter];
    $attachments .= ($counter + 1).") $filename<br>";
}
writeToStackAndConsole(__LINE__,"Attachments Text",$attachments);

/* FORMAT EMAIL */
$timestamp = getServerDateTime();
$email_text = "
    <p><u><b>ORDER INFORMATION</b></u><br>
    Date:  $orderDate<br>
    Number:  $orderNum<br>
    <br>
    <br>
    <u><b>ATTACHMENT INFORMATION</b></u><br>
    $attachments
    <br>
    <br>
    <br>
    <font color=\"darkgray\">--- End of email ---<br>
    (v:$calledfromAppVersion, s:$version)</font></p>
";

/* INITIALIZE EMAIL */
$companyName = "Squareframe";
$subject = "Resend of order #$orderNum files to Company";

/* EMAIL: Init */
$email = new \SendGrid\Mail\Mail();
$email->setSubject($subject);
$email->addContent($emailAttachmentType_HTML,$email_text);

/* EMAIL: Addresses */
$email->setFrom($emailAddress_orders, $companyName);
$email->addTo($emailAddress_orders,$companyName);

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

/* RETURN RESULTS */
$records = null;
$found = ($attachmentFilenames !=null) ?1 :0;

echo my_json_encode($found,$success,$records);
endScriptMsg(__LINE__,$scriptName,$calledfromApp,$category,$success);

?>
