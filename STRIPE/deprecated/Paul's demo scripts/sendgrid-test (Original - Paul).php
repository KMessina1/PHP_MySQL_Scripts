<?php

require $_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php";
$email = new \SendGrid\Mail\Mail();

// don't touch anything above this

// This page is currently hard-coded with the information inline, however the data could be passed to this page as variables and emails delivered based on those variables.

// 1. this is the from email that the end user will see.
$email->setFrom("KMessina@creativeapps.us", "Creative Apps");

// 2. add as many addTos as you require by copying and changing the line below.
$email->addTo("support@sqframe.com", "Squareframe");

// 3. add as many addCcs as you require by copying and changing the line below. If ccs aren't required, comment out the line below.
$email->addCc("KMessina@creativeapps.us", "Kevin Messina");

// 4. add as many addBccs as you require by copying and changing the line below. If bccs aren't required, comment out the line below.
// $email->addBcc("hello@jetblackyak.com", "Jet Black Yak");

// 5. set the Subject of the email here.
$email->setSubject("Sent Using Sendgrid from Creative Apps");

// 6. change the body of the email here. Basic HTML and CSS can be used to format the content.
$email->addContent(
    "text/html", "<strong style='color: red;'>This email was sent using Sendgrid, simply by accessing the file at https://creativeapps.us/sendgrid-test.php</strong>

    <br /><br />

    Adapt this file to change the to, cc, bcc, heading, attachments and message fields.

    <br /><br />

    Text can be formatted using HTML and CSS and attachments can added by manipulating the path of the attachment in the PHP.

    <br /><br />"
);

// 7. Add attachments here. my-file.txt lives in the root of your directory structure but can be placed anywhere and addressed in the file_get_contents() line below.
$file_encoded = base64_encode(file_get_contents('my-file.txt'));
$email->addAttachment(
    $file_encoded,
    "application/text",
    "my-file.txt",
    "attachment"
);

// don't touch anything below this

$sendgrid = new \SendGrid('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');

// send the email, get the response and the error status if applicable.
try {
    $response = $sendgrid->send($email);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: '. $e->getMessage() ."\n";
}

?>
