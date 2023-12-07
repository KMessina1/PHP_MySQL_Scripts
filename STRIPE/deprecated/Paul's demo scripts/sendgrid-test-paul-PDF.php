
<?php
require '../../vendor/autoload.php';

$email = new \SendGrid\Mail\Mail();
$email->setFrom("orders@sqframe.com", "Squareframe");
$email->setSubject("SendGrid Example");
$email->addTo("kmessina@sqframe.com", "Kevin");
$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
$email->addContent(
    "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
);

$file_encoded = file_get_contents('Order_2285.pdf');
$email->addAttachment(
    $file_encoded,
    "application/pdf",
    "Order_2285.pdf",
    "attachment"
);

$sendgrid = new \SendGrid('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');
try {
    $response = $sendgrid->send($email);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: '.  $e->getMessage(). "\n";
}