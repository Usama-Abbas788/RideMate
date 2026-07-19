<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/mail.php';

class AuthController {
    private $userModel;

    public function __construct($conn) {
        $this->userModel = new User($conn);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $email = sanitizeEmail(trim($_POST['email'] ?? ''));
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        if (!isValidEmail($email)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Invalid email or password.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        if (!$user['email_verified']) {
            $_SESSION['error'] = 'Please verify your email address before logging in.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: /ridemate/admin/dashboard.php');
        } else {
            header('Location: /ridemate/index.php');
        }
        exit;
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $name     = trim($_POST['name']         ?? '');
        $email    = sanitizeEmail(trim($_POST['email'] ?? ''));
        $phoneRaw = trim($_POST['phone']        ?? '');
        $phone    = normalizePhone($phoneRaw);
        $password = trim($_POST['password']     ?? '');
        $role     = trim($_POST['role']         ?? 'passenger');

        $errors = [];
        if (empty($name)) $errors[] = 'Name is required.';
        if (empty($email)) $errors[] = 'Email is required.';
        if (!isValidEmail($email)) $errors[] = 'Invalid email address.';
        if (empty($phoneRaw)) $errors[] = 'Phone number is required.';
        if (!isValidPhone($phone)) $errors[] = 'Invalid phone number format.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($role === 'admin' || !in_array($role, ['driver', 'passenger'])) $errors[] = 'Invalid role selected.';

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            header('Location: /ridemate/views/auth/register.php');
            exit;
        }

        if ($this->userModel->emailExists($email) || $this->userModel->pendingEmailExists($email)) {
            $_SESSION['error'] = 'Email is already registered or pending verification.';
            header('Location: /ridemate/views/auth/register.php');
            exit;
        }

        if ($this->userModel->phoneExists($phone) || $this->userModel->pendingPhoneExists($phone)) {
            $_SESSION['error'] = 'Phone number is already registered or pending verification.';
            header('Location: /ridemate/views/auth/register.php');
            exit;
        }

        $verificationToken = generateToken();
        $verificationExpires = nowDatetime();
        $verificationExpires = date('Y-m-d H:i:s', strtotime($verificationExpires . ' +15 minutes'));
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        if (!$this->userModel->registerPending($name, $email, $hashedPassword, $role, $verificationToken, $verificationExpires, $phone)) {
            $_SESSION['error'] = 'Registration failed. Please try again.';
            header('Location: /ridemate/views/auth/register.php');
            exit;
        }

        $verificationLink = BASE_URL . '/verify.php?token=' . urlencode($verificationToken);
        $subject = 'Verify your RideMate email';
        $body = "<p>Hi " . htmlspecialchars($name) . ",</p>" .
                "<p>Thank you for registering at RideMate. Please click the link below to verify your email address and activate your account:</p>" .
                "<p><a href='" . $verificationLink . "'>Verify my email</a></p>" .
                "<p>If the button does not work, paste this URL into your browser:</p>" .
                "<p>" . htmlspecialchars($verificationLink) . "</p>" .
                "<p>This link expires in 15 minutes.</p>";

        $sent = sendMail($email, $name, $subject, $body);
        if (!$sent) {
            $_SESSION['error'] = 'Registration created, but we could not send the verification email. Please try again later.';
            header('Location: /ridemate/views/auth/register.php');
            exit;
        }

        $_SESSION['verification_email'] = $email;
        $_SESSION['verification_resend_at'] = time() + 120;
        $_SESSION['success'] = 'A verification email has been sent. Please check your inbox.';

        header('Location: /ridemate/views/auth/verify_status.php');
        exit;
    }

    public function resendVerification() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $email = sanitizeEmail(trim($_POST['email'] ?? ''));
        if (empty($email) || !isValidEmail($email)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            header('Location: /ridemate/views/auth/resend_verification.php');
            exit;
        }

        $pending = $this->userModel->findPendingByEmail($email);
        if (!$pending) {
            $_SESSION['error'] = 'No pending registration found for that email.';
            header('Location: /ridemate/views/auth/resend_verification.php');
            exit;
        }

        $verificationToken = generateToken();
        $verificationExpires = nowDatetime();
        $verificationExpires = date('Y-m-d H:i:s', strtotime($verificationExpires . ' +15 minutes'));

        if (!$this->userModel->updatePendingVerificationToken($email, $verificationToken, $verificationExpires)) {
            $_SESSION['error'] = 'Could not refresh verification link. Please try again.';
            header('Location: /ridemate/views/auth/resend_verification.php');
            exit;
        }

        $verificationLink = BASE_URL . '/verify.php?token=' . urlencode($verificationToken);
        $subject = 'Your RideMate verification link';
        $body = "<p>Hi " . htmlspecialchars($pending['name']) . ",</p>" .
                "<p>Use the link below to verify your RideMate email address:</p>" .
                "<p><a href='" . $verificationLink . "'>Verify my email</a></p>" .
                "<p>This link expires in 15 minutes.</p>";

        $sent = sendMail($email, $pending['name'], $subject, $body);
        if (!$sent) {
            $_SESSION['error'] = 'Unable to send verification email. Please try again later.';
            header('Location: /ridemate/views/auth/resend_verification.php');
            exit;
        }

        $_SESSION['verification_email'] = $email;
        $_SESSION['verification_resend_at'] = time() + 120;
        $_SESSION['success'] = 'Verification email resent. Please check your inbox.';
        header('Location: /ridemate/views/auth/resend_verification.php');
        exit;
    }

    public function forgotPasswordRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $email = sanitizeEmail(trim($_POST['email'] ?? ''));
        if (empty($email) || !isValidEmail($email)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $_SESSION['error'] = 'Email address not found.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        if (!$user['email_verified']) {
            $_SESSION['error'] = 'Please verify your email before requesting a password reset.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        $resetToken = generateToken();
        $resetExpires = nowDatetime();
        $resetExpires = date('Y-m-d H:i:s', strtotime($resetExpires . ' +15 minutes'));
        if (!$this->userModel->requestPasswordReset($email, $resetToken, $resetExpires)) {
            $_SESSION['error'] = 'Unable to create password reset request. Please try again.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        $resetLink = BASE_URL . '/reset-password.php?token=' . urlencode($resetToken);
        $subject = 'RideMate password reset request';
        $body = "<p>Hi " . htmlspecialchars($user['name']) . ",</p>" .
                "<p>We received a request to reset your RideMate password. Click the button below to set a new password:</p>" .
                "<p><a href='" . $resetLink . "'>Reset my password</a></p>" .
                "<p>If you did not request this, you can ignore this email. The link expires in 15 minutes.</p>";

        $sent = sendMail($email, $user['name'], $subject, $body);
        if (!$sent) {
            $_SESSION['error'] = 'Unable to send reset email. Please try again later.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        $_SESSION['reset_email'] = $email;
        $_SESSION['password_reset_resend_at'] = time() + 120;
        $_SESSION['success'] = 'Password reset email has been sent. Please check your inbox.';
        header('Location: /ridemate/views/auth/forgot_password_status.php');
        exit;
    }

    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $token = trim($_POST['reset_token'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm = trim($_POST['password_confirm'] ?? '');

        if (empty($token) || empty($password) || empty($confirm)) {
            $_SESSION['error'] = 'All fields are required.';
            header('Location: /ridemate/reset-password.php?token=' . urlencode($token));
            exit;
        }

        if ($password !== $confirm) {
            $_SESSION['error'] = 'Passwords do not match.';
            header('Location: /ridemate/reset-password.php?token=' . urlencode($token));
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters.';
            header('Location: /ridemate/reset-password.php?token=' . urlencode($token));
            exit;
        }

        $resetRequest = $this->userModel->findByResetToken($token);
        if (!$resetRequest) {
            $_SESSION['error'] = 'Reset link is invalid or expired. Please request a new one.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        if ($this->userModel->resetPasswordByToken($token, $password)) {
            unset($_SESSION['reset_email']);
            $_SESSION['success'] = 'Password updated successfully. Please login.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        $_SESSION['error'] = 'Failed to update password. Please try again.';
        header('Location: /ridemate/reset-password.php?token=' . urlencode($token));
        exit;
    }

    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please login first.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        $current = trim($_POST['current_password'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm = trim($_POST['password_confirm'] ?? '');

        if (empty($current) || empty($password) || empty($confirm)) {
            $_SESSION['error'] = 'All fields are required.';
            header('Location: /ridemate/change-password.php');
            exit;
        }

        if ($password !== $confirm) {
            $_SESSION['error'] = 'Passwords do not match.';
            header('Location: /ridemate/change-password.php');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters.';
            header('Location: /ridemate/change-password.php');
            exit;
        }

        $user = $this->userModel->findById($_SESSION['user_id']);
        if (!$user || !password_verify($current, $user['password'])) {
            $_SESSION['error'] = 'Current password is incorrect.';
            header('Location: /ridemate/change-password.php');
            exit;
        }

        if ($this->userModel->resetPassword($user['id'], $password)) {
            $_SESSION['success'] = 'Password changed successfully.';
            header('Location: /ridemate/change-password.php');
            exit;
        }

        $_SESSION['error'] = 'Failed to change password. Please try again.';
        header('Location: /ridemate/change-password.php');
        exit;
    }

    public function logout() {
        session_destroy();
        header('Location: /ridemate/index.php');
        exit;
    }
}
