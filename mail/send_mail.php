<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

function sendEmail($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {

        
$smtpConfig = require __DIR__ . '/smtp_config.php';

$mail->isSMTP();

$mail->Host = $smtpConfig['host'];

$mail->SMTPAuth = true;

$mail->Username = $smtpConfig['username'];

$mail->Password = $smtpConfig['password'];

$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

$mail->Port = $smtpConfig['port'];


       $mail->setFrom(
    $smtpConfig['username'],
    'AI Placement System'
);

        $mail->addAddress($to);

        $mail->isHTML(true);

        $mail->Subject = $subject;

        $mail->Body = $body;

//         $mail->SMTPDebug = 2;
// $mail->Debugoutput = 'html';
        $mail->send();

        return true;

    } catch (Exception $e) {
    return false;
}
}
?>