<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../models/Notification.php';

/**
 * Create a notification for a given user.
 */
function createNotification(int $user_id, string $message, string $type = 'info'): bool {
    global $conn;
    $notification = new Notification($conn);
    return $notification->create($user_id, $message, $type);
}

/**
 * Sanitize a token input.
 */
function sanitizeToken(string $value): string {
    return trim(filter_var($value, FILTER_SANITIZE_STRING));
}

/**
 * Generate a secure token.
 */
function generateToken(): string {
    return bin2hex(random_bytes(50));
}

/**
 * Generate a 6-digit numeric OTP code.
 */
function generateOtp(): string {
    return str_pad((string) rand(100000, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Normalize a phone number for international use.
 * Accepts +92, 92, 0-prefixed numbers and returns +92... format.
 */
function normalizePhone(string $phone): string {
    $normalized = preg_replace('/[^0-9+]/', '', $phone);
    if ($normalized === '') {
        return '';
    }

    if ($normalized[0] === '+') {
        return $normalized;
    }

    if (strpos($normalized, '92') === 0) {
        return '+' . $normalized;
    }

    if ($normalized[0] === '0') {
        return '+92' . substr($normalized, 1);
    }

    return '+' . $normalized;
}

/**
 * Validate an international phone string.
 */
function isValidPhone(string $phone): bool {
    return (bool) preg_match('/^\+?[0-9]{10,15}$/', $phone);
}

/**
 * Validate an email address.
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize an email address.
 */
function sanitizeEmail(string $email): string {
    return trim(filter_var($email, FILTER_SANITIZE_EMAIL));
}

/**
 * Returns the current timestamp with seconds precision.
 */
function nowDatetime(): string {
    return date('Y-m-d H:i:s');
}
