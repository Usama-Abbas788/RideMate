<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Mail settings: update these values for your XAMPP / SMTP environment.
define('MAIL_FROM_ADDRESS', 'no-reply@ridemate.local');
define('MAIL_FROM_NAME', 'RideMate');
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_SMTP_AUTH', false);
define('MAIL_USERNAME', 'your-smtp-user');
define('MAIL_PASSWORD', 'your-smtp-password');
define('MAIL_SMTP_SECURE', 'tls');
define('MAIL_PORT', 587);

define('MAIL_USE_SMTP', false);

define('MAIL_REPLY_TO', 'support@ridemate.local');

define('MAIL_REPLY_NAME', 'RideMate Support');

define('BASE_URL', 'http://localhost/ridemate');

/**
 * Send an email using PHPMailer.
 *
 * @param string $to
 * @param string $toName
 * @param string $subject
 * @param string $body
 * @return bool
 */
function sendMail(string $to, string $toName, string $subject, string $body): bool {
    $mail = new PHPMailer(true);

    try {
        if (MAIL_USE_SMTP) {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = MAIL_SMTP_AUTH;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_SMTP_SECURE;
            $mail->Port       = MAIL_PORT;
        } else {
            $mail->isMail();
        }

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addReplyTo(MAIL_REPLY_TO, MAIL_REPLY_NAME);
        $mail->addAddress($to, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

        return $mail->send();
    } catch (Exception $e) {
        error_log('Mail error: ' . $e->getMessage());
        return false;
    }
}
