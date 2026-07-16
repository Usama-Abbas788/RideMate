<?php

// Twilio SMS configuration using environment variables.
// Set these in your environment or Apache/PHP config for production use.

define('TWILIO_ACCOUNT_SID', getenv('TWILIO_ACCOUNT_SID') ?: '');
define('TWILIO_AUTH_TOKEN', getenv('TWILIO_AUTH_TOKEN') ?: '');
define('TWILIO_FROM_PHONE', getenv('TWILIO_FROM_PHONE') ?: '');

define('TWILIO_API_URL', 'https://api.twilio.com/2010-04-01/Accounts/' . urlencode(TWILIO_ACCOUNT_SID) . '/Messages.json');

function sendSms(string $to, string $message): bool {
    if (empty(TWILIO_ACCOUNT_SID) || empty(TWILIO_AUTH_TOKEN) || empty(TWILIO_FROM_PHONE)) {
        error_log('Twilio SMS failed: missing Twilio configuration.');
        return false;
    }

    $payload = http_build_query([
        'From' => TWILIO_FROM_PHONE,
        'To' => $to,
        'Body' => $message,
    ]);

    $ch = curl_init(TWILIO_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN,
    ]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $status < 200 || $status >= 300) {
        error_log("Twilio SMS failed ({$status}): {$curlError} Response: {$response}");
        return false;
    }

    return true;
}
