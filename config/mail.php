<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Mail settings: update these values for Gmail SMTP.
define('MAIL_FROM_ADDRESS', 'danishriasat792@gmail.com'); // replace with your Gmail address
define('MAIL_FROM_NAME', 'RideMate');
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_SMTP_AUTH', true);
define('MAIL_USERNAME', 'danishriasat792@gmail.com'); // replace with your Gmail address
define('MAIL_PASSWORD', 'lauz xvwa kvtt upjo'); // replace with your Gmail app password
define('MAIL_SMTP_SECURE', 'tls');
define('MAIL_PORT', 587);

define('MAIL_USE_SMTP', true);

define('MAIL_REPLY_TO', 'support@ridemate.local');
define('MAIL_REPLY_NAME', 'RideMate Support');

define('BASE_URL', 'http://localhost/ridemate'); // update if your local app URL differs

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
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addReplyTo(MAIL_REPLY_TO, MAIL_REPLY_NAME);
        $mail->addAddress($to, $toName);

        if (MAIL_USE_SMTP) {
            if (empty(MAIL_HOST) || empty(MAIL_PORT)) {
                error_log('Mail error: SMTP configuration is incomplete.');
                return false;
            }
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = MAIL_SMTP_AUTH;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_SMTP_SECURE;
            $mail->Port       = MAIL_PORT;
            $mail->SMTPAutoTLS = MAIL_SMTP_SECURE !== '';
        } else {
            $mail->isMail();
        }

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
