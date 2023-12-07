<?php
/*--------------------------------------------------------------------------------------
    File: funcs_mailer.php
  Author: Kevin Messina
 Created: Sep. 10, 2018
Modified: Nov. 17, 2018

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

2018/11/17 - Updated Include files.
2018/09/14 - Added diferentiator for CAS and SF Servers.
--------------------------------------------------------------------------------------*/

$mailerScriptVersion = "2.01b";

/* INCLUDE MAILER CLASS */
require_once(getScriptsPath()."MAILER_Class.php");

/* GLOBALS */
$emailAddress_orders = "";
$emailAddress_info = "";
$emailAddress_support = "";
$emailAddress_developer = "";
$emailAddress_manager = "";
$emailAddress_fulfillment = "";
// MIME Attachment Types
$emailAttachmentType_HTML = "text/html";
$emailAttachmentType_TXT = "application/text";
$emailAttachmentType_PDF = "application/pdf";
$emailAttachmentType_JPG = "image/jpeg";
$emailAttachmentType_PNG = "image/png";
$emailAttachmentType_GIF = "image/gif";
$emailAttachmentType_BMP = "image/bmp";
$emailAttachmentType_ZIP = "application/zip";
// $emailSendGridAPIKey = "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"; // CAS
$emailSendGridAPIKey = "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"; // SF

/* FUNCTIONS */
function listEmailAddresses() {
    returnEmailInfo();
}

function getMIME_TypeFromExtension($filename) {
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $ext = trim(strtoupper($extension));
    $MIME_TYPE = "";

    $isTXT = (bool)(stripos($ext,"TXT") !== false);
    $isPDF = (bool)(stripos($ext,"PDF") !== false);
    $isJPG = (bool)(stripos($ext,"JPG") !== false);
    $isPNG = (bool)(stripos($ext,"PNG") !== false);
    $isBMP = (bool)(stripos($ext,"BMP") !== false);
    $isGIF = (bool)(stripos($ext,"GIF") !== false);
    $isZIP = (bool)(stripos($ext,"ZIP") !== false);

    if ($isTXT) { $MIME_TYPE = "application/text"; }
    elseif ($isPDF) { $MIME_TYPE = "application/pdf"; }
    elseif ($isJPG) { $MIME_TYPE = "image/jpeg"; }
    elseif ($isPNG) { $MIME_TYPE = "image/png"; }
    elseif ($isBMP) { $MIME_TYPE = "image/gif"; }
    elseif ($isGIF) { $MIME_TYPE = "image/bmp"; }
    elseif ($isZIP) { $MIME_TYPE = "application/zip"; }
    else { $MIME_TYPE = "n/a"; }

    // echo "<br>fileContentType determined as: $MIME_TYPE";

    return $MIME_TYPE;
}

function returnEmailInfo() {
    global $version,$stack,$debug;
    global $emailAddress_orders,$emailAddress_info,$emailAddress_support,
           $emailAddress_developer,$emailAddress_manager,$emailAddress_fulfillment;

    try {
        $tableName = "emailer";
        $query =
            "SELECT
                *
            FROM
                $tableName
            ;
        ";
        $records = fetchFromDB($query,$tableName);
        $found = ($records != null) ?(int)count($records) :0;
        $success = ($found > 0);

        /* CORPORATE EMAIL ADDRESSES */
        foreach ($records as $record) {
           $record_name = (string)$record["name"];
           $record_address = (string)$record["address"];

           // Parse records into categories
           if ($record_name == "ORDERS") { $emailAddress_orders = $record_address; }
           elseif ($record_name == "DEVELOPER") { $emailAddress_developer = $record_address; }
           elseif ($record_name == "MANAGER") { $emailAddress_manager = $record_address; }
           elseif ($record_name == "INFO") { $emailAddress_info = $record_address; }
           elseif ($record_name == "SUPPORT") { $emailAddress_support = $record_address; }
           elseif ($record_name == "FULFILLMENT") { $emailAddress_fulfillment = $record_address; }
        }
    }catch(Exception $e){
        logExceptionError($e->getMessage());
    }

    $title = "CMS EMAIL ADDRESS PARAMS:";
    $msg = "\n$title";
    $msg .= "<br>|-> Orders: $emailAddress_orders";
    $msg .= "<br>|-> Info: $emailAddress_info";
    $msg .= "<br>|-> Support: $emailAddress_support";
    $msg .= "<br>|-> Developer: $emailAddress_developer";
    $msg .= "<br>|-> Fulfillment: $emailAddress_fulfillment";
    $msg .= "<br>|-> Manager: $emailAddress_manager";

    if ($debug) {
        writeMsgToStackAndConsole(__LINE__,$title,str_replace("\n","<br>","$msg<br>"));
    }

    return $msg;
}

function send_MailerIssueEmail($scriptName,$errorMsg,$orderNum,$orderDate,$calledfromApp,
    $calledfromAppVersion,$scriptTitle,$customer_name,$customer_phone,$customer_email) {

    global $version,$stack,$debug;
    global $dateFormat_Ymd;
    global $emailAddress_orders,$emailAddress_info,$emailAddress_support,
           $emailAddress_developer,$emailAddress_manager,$emailAddress_fulfillment,
           $emailSendGridAPIKey,$emailAttachmentType_HTML,$mailerScriptVersion;

    $issueEmailSentToCompany = false;

    if ($errorMsg == "") { $errorMsg = "n/a"; }
    if ($scriptName == "") { $scriptName = "n/a"; }
    if ($orderNum == "") { $orderNum = "n/a"; }
    if ($orderDate == "") { $orderDate = "n/a"; }
    if ($calledfromApp == "") { $calledfromApp = "n/a"; }
    if ($calledfromAppVersion == "") { $calledfromAppVersion = "n/a"; }
    if ($scriptTitle == "") { $scriptTitle = "n/a"; }
    if ($customer_name == "") { $customer_name = "n/a"; }
    if ($customer_phone == "") { $customer_phone = "n/a"; }
    if ($customer_email == "") { $customer_email = "n/a"; }

    /* INITIALIZE EMAIL */
    $companyName = "Squareframe";
    $subject = "!! ISSUE SENDING EMAIL: $scriptTitle !!";

    /* Build table Info */
    if ($orderDate == "" || $orderDate == "n/a") {
        $orderDateConverted = "n/a";
    }else {
        $orderDateConverted = returnDateToFormat($dateFormat_Ymd,$orderDate);
    }

    $table_orderInfo = "<br/><tr><td>$orderNum</td>";
    $table_orderInfo .= "<td>$orderDateConverted</td>";
    $table_orderInfo .= "<td>$customer_name</td>";
    $table_orderInfo .= "<td>$customer_phone</td>";
    $table_orderInfo .= "<td>$customer_email</td>";
    $table_orderInfo .= "</tr>";

    $table_scriptInfo = "<br/><tr><td>$calledfromApp</td>";
    $table_scriptInfo .= "<td>$calledfromAppVersion</td>";
    $table_scriptInfo .= "<td>$scriptName</td>";
    $table_scriptInfo .= "</tr>";

    $table_errorInfo = "<br/><tr><td>$errorMsg</td>";
    $table_errorInfo .= "</tr>";

    /* EMAIL: Body */
    $body_title = "<p style=\"text-align: center; color:darkred\";>
        <b>SQUAREFRAME ORDER ISSUE NOTIFICATION</b>
        <br/>
        $scriptTitle
        <br/>
        <br/>
        <i>!! Email was NOT sent to recipient !!</i>
        <br/>
    </p>";

    $body_tableStyle = "<style>
        table,th,td { border: 1px solid black; border-collapse: collapse; padding: 5px; }
        th { text-align: left; }
    </style>";

    $body_orderInfo = "<table style=\"width:100%\">
    <tr>
        <th style=\"color:white\"; bgcolor=\"darkred\"; colspan=\"5\">ORDER INFORMATION</th>
    </tr>
    <tr>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Number</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Date</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Customer Name</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Customer Phone</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Customer Email</th>
    </tr>
    $table_orderInfo
    </table>";

    $body_scriptInfo = "<table style=\"width:100%\">
    <tr>
        <th style=\"color:white\"; bgcolor=\"darkred\"; colspan=\"3\">SCRIPT INFORMATION</th>
    </tr>
    <tr>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Calling App Name</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Calling App Version</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Script Name w/Error</th>
    </tr>
    $table_scriptInfo
    </table>";

    $body_errorInfo = "<table style=\"width:100%\">
    <tr>
        <th style=\"color:white\"; bgcolor=\"darkred\"; colspan=\"1\">ERROR INFORMATION</th>
    </tr>
    <tr>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Error Message</th>
    </tr>
    $table_errorInfo
    </table>";

    $body_version =
    "<p style=\"text-align: left;\">
        <br/>
        <font color=\"darkgray\">
            --- End of email ---<br/>
            (v:$calledfromAppVersion, s:$mailerScriptVersion)<br/>
        </font>
    </p>";

    $email_text =
        $body_title.
        $body_tableStyle.
        $body_orderInfo.
        $body_scriptInfo.
        $body_errorInfo.
        $body_version
    ;
    if ($debug) { echo $email_text; }

    $issue_email = new \SendGrid\Mail\Mail();
    $issue_email->setSubject($subject);
    $issue_email->addContent($emailAttachmentType_HTML,$email_text);

    /* EMAIL: Addresses */
    $issue_email->setFrom($emailAddress_orders, $companyName);
    $issue_email->addTo($emailAddress_support,$companyName);

    /* SEND EMAIL */
    try {
        /* EMAIL: Send */
        $sendgrid_Issue = new \SendGrid($emailSendGridAPIKey);
        $response_Issue = $sendgrid_Issue->send($issue_email);

        /* Process Results */
        $statusCode = (int)$response_Issue->statusCode();
        $success = (bool)(($statusCode >= 200) && ($statusCode < 300));
        $successText = ($success) ?"Yes" :"No";
        $msg = "SENDGRID RETURN STATUS:";
        $msg .= "<br>|-> Status Code: $statusCode";
        $msg .= "<br>|-> Succeeded: $successText";
        writeMsgToStackAndConsole(__LINE__,"** ISSUE EMAIL ** SENDGRID RETURN STATUS:",$msg);

        $successText = ($success) ?"Succeeded." :"Failed.";
        $msg = "Sendgrid send email for script: $scriptName $successText";
        if ($debug) { echo "<br>$msg<br>"; }
        ($success) ?logSuccess($msg) :logFailure($msg);

        $issueEmailSentToCompany = $success;
    } catch (Exception $e) {
        $msg = "Sendgrid Exception error occured: ".$e->getMessage();
        logFailure($msg);
        if ($debug) { echo "<br>$msg"; }
    }

    return $issueEmailSentToCompany;
}

function send_OrderIssueEmail($scriptName,$errorMsg,$orderNum,$orderDate,$calledfromApp,
    $calledfromAppVersion,$scriptTitle,$customer_name,$customer_phone,$customer_email) {

    global $version,$stack,$debug;
    global $dateFormat_Ymd;
    global $emailAddress_orders,$emailAddress_info,$emailAddress_support,
           $emailAddress_developer,$emailAddress_manager,$emailAddress_fulfillment,
           $emailSendGridAPIKey,$emailAttachmentType_HTML,$mailerScriptVersion;

    $issueEmailSentToCompany = false;

    if ($errorMsg == "") { $errorMsg = "n/a"; }
    if ($scriptName == "") { $scriptName = "n/a"; }
    if ($orderNum == "") { $orderNum = "n/a"; }
    if ($orderDate == "") { $orderDate = "n/a"; }
    if ($calledfromApp == "") { $calledfromApp = "n/a"; }
    if ($calledfromAppVersion == "") { $calledfromAppVersion = "n/a"; }
    if ($scriptTitle == "") { $scriptTitle = "n/a"; }
    if ($customer_name == "") { $customer_name = "n/a"; }
    if ($customer_phone == "") { $customer_phone = "n/a"; }
    if ($customer_email == "") { $customer_email = "n/a"; }

    /* INITIALIZE EMAIL */
    $companyName = "Squareframe";
    $subject = "!! ISSUE ORDER PLACEMENT: $scriptTitle !!";

    /* Build table Info */
    if ($orderDate == "" || $orderDate == "n/a") {
        $orderDateConverted = "n/a";
    }else {
        $orderDateConverted = returnDateToFormat($dateFormat_Ymd,$orderDate);
    }

    $table_orderInfo = "<br/><tr><td>$orderNum</td>";
    $table_orderInfo .= "<td>$orderDateConverted</td>";
    $table_orderInfo .= "<td>$customer_name</td>";
    $table_orderInfo .= "<td>$customer_phone</td>";
    $table_orderInfo .= "<td>$customer_email</td>";
    $table_orderInfo .= "</tr>";

    $table_scriptInfo = "<br/><tr><td>$calledfromApp</td>";
    $table_scriptInfo .= "<td>$calledfromAppVersion</td>";
    $table_scriptInfo .= "<td>$scriptName</td>";
    $table_scriptInfo .= "</tr>";

    $table_errorInfo = "<br/><tr><td>$errorMsg</td>";
    $table_errorInfo .= "</tr>";

    /* EMAIL: Body */
    $body_title = "<p style=\"text-align: center; color:darkred\";>
        <b>SQUAREFRAME ORDER ISSUE NOTIFICATION</b>
        <br/>
        $scriptTitle
        <br/>
        <br/>
        <i>!! Order was NOT completed !!</i>
        <br/>
    </p>";

    $body_tableStyle = "<style>
        table,th,td { border: 1px solid black; border-collapse: collapse; padding: 5px; }
        th { text-align: left; }
    </style>";

    $body_orderInfo = "<table style=\"width:100%\">
    <tr>
        <th style=\"color:white\"; bgcolor=\"darkred\"; colspan=\"5\">ORDER INFORMATION</th>
    </tr>
    <tr>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Number</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Date</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Customer Name</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Customer Phone</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Customer Email</th>
    </tr>
    $table_orderInfo
    </table>";

    $body_scriptInfo = "<table style=\"width:100%\">
    <tr>
        <th style=\"color:white\"; bgcolor=\"darkred\"; colspan=\"3\">SCRIPT INFORMATION</th>
    </tr>
    <tr>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Calling App Name</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Calling App Version</th>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Script Name w/Error</th>
    </tr>
    $table_scriptInfo
    </table>";

    $body_errorInfo = "<table style=\"width:100%\">
    <tr>
        <th style=\"color:white\"; bgcolor=\"darkred\"; colspan=\"1\">ERROR INFORMATION</th>
    </tr>
    <tr>
        <th style=\"color:black\"; bgcolor=\"lightpink\";>Error Message</th>
    </tr>
    $table_errorInfo
    </table>";

    $body_version =
    "<p style=\"text-align: left;\">
        <br/>
        <font color=\"darkgray\">
            --- End of email ---<br/>
            (v:$calledfromAppVersion, s:$mailerScriptVersion)<br/>
        </font>
    </p>";

    $email_text =
        $body_title.
        $body_tableStyle.
        $body_orderInfo.
        $body_scriptInfo.
        $body_errorInfo.
        $body_version
    ;
    if ($debug) { echo $email_text; }

    $issue_email = new \SendGrid\Mail\Mail();
    $issue_email->setSubject($subject);
    $issue_email->addContent($emailAttachmentType_HTML,$email_text);

    /* EMAIL: Addresses */
    $issue_email->setFrom($emailAddress_orders, $companyName);
    $issue_email->addTo($emailAddress_support,$companyName);

    /* SEND EMAIL */
    try {
        /* EMAIL: Send */
        $sendgrid_Issue = new \SendGrid($emailSendGridAPIKey);
        $response_Issue = $sendgrid_Issue->send($issue_email);

        /* Process Results */
        $statusCode = (int)$response_Issue->statusCode();
        $success = (bool)(($statusCode >= 200) && ($statusCode < 300));
        $successText = ($success) ?"Yes" :"No";
        $msg = "SENDGRID RETURN STATUS:";
        $msg .= "<br>|-> Status Code: $statusCode";
        $msg .= "<br>|-> Succeeded: $successText";
        writeMsgToStackAndConsole(__LINE__,"** ISSUE EMAIL ** SENDGRID RETURN STATUS:",$msg);

        $successText = ($success) ?"Succeeded." :"Failed.";
        $msg = "Sendgrid send email for script: $scriptName $successText";
        if ($debug) { echo "<br>$msg<br>"; }
        ($success) ?logSuccess($msg) :logFailure($msg);

        $issueEmailSentToCompany = $success;
    } catch (Exception $e) {
        $msg = "Sendgrid Exception error occured: ".$e->getMessage();
        logFailure($msg);
        if ($debug) { echo "<br>$msg"; }
    }

    return $issueEmailSentToCompany;
}

?>
