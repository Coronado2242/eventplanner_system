<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/PHPMailer/PHPMailer/src/Exception.php';
require '../vendor/PHPMailer/PHPMailer/src/PHPMailer.php';
require '../vendor/PHPMailer/PHPMailer/src/SMTP.php';

$gmail_user = 'imoangeles27@gmail.com';
$gmail_password = 'wopg gabq pxbn lcwc';

function sendVerificationCode($email, $verification_code) {
    global $gmail_user, $gmail_password;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $gmail_user;
        $mail->Password = $gmail_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($gmail_user, 'Email Verification');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code';
        $mail->Body = 'Hello! Your verification code is: <b>' . $verification_code . '</b>';

        $mail->send();
        return true;
    } catch (Exception $e) { 
        return 'Mailer Error: ' . $mail->ErrorInfo;
    }
}
?>
