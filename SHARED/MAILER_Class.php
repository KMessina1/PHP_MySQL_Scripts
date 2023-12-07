<?php
/*--------------------------------------------------------------------------------------
    File: CMS_Class.php
  Author: Kevin Messina
 Created: Nov. 23, 2018
Modified:

©2018 Creative App Solutions, LLC. - All Rights Reserved.
----------------------------------------------------------------------------------------
NOTES:

--------------------------------------------------------------------------------------*/

$MAILER_ClassVersion = "1.01a";

/* CMS Class */
class MAILER{
    const SendGridAPIKey_CAS = "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";
    const SendGridAPIKey_SF = "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";

    public $emailAddress_orders;
    public $emailAddress_info;
    public $emailAddress_support;
    public $emailAddress_developer;
    public $emailAddress_manager;
    public $emailAddress_fulfillment;
    public $companyName;
    public $subject;
    public $body;
    public $fromAddress;
    public $toAddress;
    public $logoImagePath;

/* Setters */
    public function setEmailAddress_orders($info){ $this->emailAddress_orders = $info; }
    public function setEmailAddress_info($info){ $this->emailAddress_info = $info; }
    public function setEmailAddress_support($info){ $this->emailAddress_support = $info; }
    public function setEmailAddress_developer($info){ $this->emailAddress_developer = $info; }
    public function setEmailAddress_manager($info){ $this->emailAddress_manager = $info; }
    public function setEmailAddress_fulfillment($info){ $this->emailAddress_fulfillment = $info; }
    public function setCompanyName($info){ $this->companyName = $info; }
    public function setSubject($info){ $this->subject = $info; }
    public function setBody($info){ $this->body = $info; }
    public function setFromAddress($info){ $this->fromAddress = $info; }
    public function setToAddress($info){ $this->toAddress = $info; }

// Init
    function init() {
        $this->emailAddress_orders = "";
        $this->emailAddress_info = "";
        $this->emailAddress_support = "";
        $this->emailAddress_developer = "";
        $this->emailAddress_manager = "";
        $this->emailAddress_fulfillment = "";

        $this->companyName = "";
        $this->subject = "";
        $this->body = "";
        $this->fromAddress = "";
        $this->toAddress = "";
        $this->logoImagePath = "";

        self::fetchEmailAddresses();
    }

// sendEmail() returns $success
    function sendEmail() {
        global $emailAttachmentType_HTML,$scriptName;

        $title = "EMAIL PARAMS:";
        $msg = "\n$title";
        $msg .= "\n|-> From: $this->fromAddress";
        $msg .= "\n|-> From Name: $this->companyName";
        $msg .= "\n|-> To: $this->toAddress";
        $msg .= "\n|-> Subject: $this->subject";
        $msg .= "\n|-> Body: $this->body";
        writeMsgToStackAndConsole(__LINE__,$title,$msg);

        try {
            $sendgrid = new \SendGrid(self::SendGridAPIKey_SF);
            $email = new \SendGrid\Mail\Mail();

            $email->setFrom($this->emailAddress_orders, $this->companyName);
            $email->addTo($this->emailAddress_orders,$this->companyName);
            $email->setSubject($this->subject);
            $email->addContent($emailAttachmentType_HTML,$this->body);

            $response = $sendgrid->send($email);

            /* Process Results */
            $statusCode = (int)$response->statusCode();
            $success = (bool)(($statusCode >= 200) && ($statusCode < 300));
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
        } catch (Exception $e) {
            $error = $e->getMessage();
            logExceptionError($error);
            sendIssueEmail($error);
        }

        return $success;
    }

    // fetchEmailAddresses()
    public function fetchEmailAddresses(){
        global $table_emailer;

        $this->emailAddress_orders = "";
        $this->emailAddress_info = "";
        $this->emailAddress_support = "";
        $this->emailAddress_developer = "";
        $this->emailAddress_manager = "";
        $this->emailAddress_fulfillment = "";

        try {
            $db = new CMS();
            $db->connectToAndinitDB();
            $db->setTableName($table_emailer);
            $records = $db->fetchRecordsWhere();
        }catch(Exception $e){
            logExceptionError($e->getMessage());
        }

        // Parse records into categories
        foreach ($records as $record) {
            $record_name = (string)$record["name"];
            $record_address = (string)$record["address"];

            if ($record_name == "ORDERS") {
               $this->emailAddress_orders = $record_address;
            } elseif ($record_name == "DEVELOPER") {
               $this->emailAddress_developer = $record_address;
            } elseif ($record_name == "MANAGER") {
               $this->emailAddress_manager = $record_address;
            } elseif ($record_name == "INFO") {
               $this->emailAddress_info = $record_address;
            } elseif ($record_name == "SUPPORT") {
               $this->emailAddress_support = $record_address;
            } elseif ($record_name == "FULFILLMENT") {
               $this->emailAddress_fulfillment = $record_address;
            }
        }

        $title = "CMS EMAIL ADDRESS PARAMS:";
        $msg = "\n$title";
        $msg .= "<br>|-> Orders: $this->emailAddress_orders";
        $msg .= "<br>|-> Info: $this->emailAddress_info";
        $msg .= "<br>|-> Support: $this->emailAddress_support";
        $msg .= "<br>|-> Developer: $this->emailAddress_developer";
        $msg .= "<br>|-> Fulfillment: $this->emailAddress_fulfillment";
        $msg .= "<br>|-> Manager: $this->emailAddress_manager";
        writeMsgToStackAndConsole(__LINE__,$title,str_replace("\n","<br>","$msg<br>"));
    }
}
