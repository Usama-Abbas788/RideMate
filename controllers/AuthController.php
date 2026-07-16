<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/twilio.php';

class AuthController {
    private $userModel;

    public function __construct($conn) {
        $this->userModel = new User($conn);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $phone    = normalizePhone(trim($_POST['phone']    ?? ''));
        $password = trim($_POST['password'] ?? '');

        if (empty($phone) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        if (!isValidPhone($phone)) {
            $_SESSION['error'] = 'Please enter a valid phone number.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        $user = $this->userModel->findByPhoneAndPassword($phone, $password);
        if (!$user) {
            $_SESSION['error'] = 'Invalid phone number or password.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        $otpCode = generateOtp();
        $otpExpires = nowDatetime();
        $otpExpires = date('Y-m-d H:i:s', strtotime($otpExpires . ' +5 minutes'));
        if (!$this->userModel->setOtp($phone, $otpCode, $otpExpires)) {
            $_SESSION['error'] = 'Unable to generate OTP. Please try again.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        $message = "Your RideMate login OTP is: {$otpCode}. It expires in 5 minutes.";
        $sent = sendSms($phone, $message);

        if (!$sent) {
            $_SESSION['error'] = 'Unable to send OTP SMS. Please try again later.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        $_SESSION['otp_phone'] = $phone;
        $_SESSION['otp_purpose'] = 'login';
        $_SESSION['otp_sent_at'] = time();
        $_SESSION['otp_resend_at'] = time() + 60;

        header('Location: /ridemate/views/auth/verify_otp.php');
        exit;
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $name     = trim($_POST['name']     ?? '');
        $phone    = normalizePhone(trim($_POST['phone']    ?? ''));
        $password = trim($_POST['password'] ?? '');
        $role     = trim($_POST['role']     ?? 'passenger');

        $errors = [];
        if (empty($name)) $errors[] = 'Name is required.';
        if (empty($phone)) $errors[] = 'Phone is required.';
        if (!isValidPhone($phone)) $errors[] = 'Invalid phone number format.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($role === 'admin' || !in_array($role, ['driver', 'passenger'])) $errors[] = 'Invalid role selected.';

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            header('Location: /ridemate/views/auth/register.php');
            exit;
        }

        if ($this->userModel->phoneExists($phone) || $this->userModel->pendingPhoneExists($phone)) {
            $_SESSION['error'] = 'Phone number is already registered or pending verification.';
            header('Location: /ridemate/views/auth/register.php');
            exit;
        }

        $otpCode = generateOtp();
        $otpExpires = nowDatetime();
        $otpExpires = date('Y-m-d H:i:s', strtotime($otpExpires . ' +5 minutes'));
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        if (!$this->userModel->registerPending($name, $phone, $hashedPassword, $role, $otpCode, $otpExpires)) {
            $_SESSION['error'] = 'Registration failed. Please try again.';
            header('Location: /ridemate/views/auth/register.php');
            exit;
        }

        $message = "Your RideMate registration OTP is: {$otpCode}. It expires in 5 minutes.";
        $sent = sendSms($phone, $message);

        if (!$sent) {
            $_SESSION['error'] = 'Registration completed, but we could not send the OTP SMS. Please try again.';
            header('Location: /ridemate/views/auth/register.php');
            exit;
        }

        $_SESSION['otp_phone'] = $phone;
        $_SESSION['otp_purpose'] = 'register';
        $_SESSION['otp_sent_at'] = time();
        $_SESSION['otp_resend_at'] = time() + 60;

        header('Location: /ridemate/views/auth/verify_otp.php');
        exit;
    }

    public function resendOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $phone = normalizePhone(trim($_POST['phone'] ?? ''));
        if (empty($phone) || !isValidPhone($phone)) {
            $_SESSION['error'] = 'Please enter a valid phone number.';
            header('Location: /ridemate/views/auth/verify_otp.php');
            exit;
        }

        $cooldown = intval($_SESSION['otp_resend_at'] ?? 0);
        if ($cooldown > time()) {
            $remaining = $cooldown - time();
            $_SESSION['error'] = 'Please wait ' . gmdate('i:s', $remaining) . ' before requesting another OTP.';
            header('Location: /ridemate/views/auth/verify_otp.php');
            exit;
        }

        $purpose = $_SESSION['otp_purpose'] ?? null;
        if (!$purpose) {
            $_SESSION['error'] = 'OTP session expired. Please start again.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        $otpCode = generateOtp();
        $otpExpires = nowDatetime();
        $otpExpires = date('Y-m-d H:i:s', strtotime($otpExpires . ' +5 minutes'));

        if ($purpose === 'register') {
            if (!$this->userModel->setOtpForPending($phone, $otpCode, $otpExpires)) {
                $_SESSION['error'] = 'Unable to generate OTP. Please try again.';
                header('Location: /ridemate/views/auth/verify_otp.php');
                exit;
            }
        } else {
            if (!$this->userModel->setOtp($phone, $otpCode, $otpExpires)) {
                $_SESSION['error'] = 'Unable to generate OTP. Please try again.';
                header('Location: /ridemate/views/auth/verify_otp.php');
                exit;
            }
        }

        $message = "Your RideMate OTP is: {$otpCode}. It expires in 5 minutes.";
        $sent = sendSms($phone, $message);

        if (!$sent) {
            $_SESSION['error'] = 'Unable to send OTP SMS. Please try again later.';
            header('Location: /ridemate/views/auth/verify_otp.php');
            exit;
        }

        $_SESSION['otp_sent_at'] = time();
        $_SESSION['otp_resend_at'] = time() + 60;
        $_SESSION['success'] = 'OTP resent. Please check your phone.';
        header('Location: /ridemate/views/auth/verify_otp.php');
        exit;
    }

    public function verifyOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $phone = normalizePhone(trim($_POST['phone'] ?? ''));
        $otp_code = trim($_POST['otp_code'] ?? '');

        if (empty($phone) || empty($otp_code)) {
            $_SESSION['error'] = 'Please enter the phone number and OTP.';
            header('Location: /ridemate/views/auth/verify_otp.php');
            exit;
        }

        if (!isValidPhone($phone)) {
            $_SESSION['error'] = 'Please enter a valid phone number.';
            header('Location: /ridemate/views/auth/verify_otp.php');
            exit;
        }

        $purpose = $_SESSION['otp_purpose'] ?? null;
        $sessionPhone = $_SESSION['otp_phone'] ?? null;
        if (!$purpose || $sessionPhone !== $phone) {
            $_SESSION['error'] = 'OTP session expired. Please start again.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        if ($purpose === 'register') {
            if ($this->userModel->verifyOtpForPending($phone, $otp_code)) {
                unset($_SESSION['otp_phone'], $_SESSION['otp_purpose'], $_SESSION['otp_sent_at'], $_SESSION['otp_resend_at']);
                $_SESSION['success'] = 'Your account has been verified. Please login.';
                header('Location: /ridemate/views/auth/login.php');
                exit;
            }

            $_SESSION['error'] = 'OTP is invalid, expired, or maximum attempts reached.';
            header('Location: /ridemate/views/auth/verify_otp.php');
            exit;
        }

        if ($purpose === 'login') {
            if ($this->userModel->verifyOtp($phone, $otp_code)) {
                $user = $this->userModel->findByPhone($phone);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                unset($_SESSION['otp_phone'], $_SESSION['otp_purpose'], $_SESSION['otp_sent_at'], $_SESSION['otp_resend_at']);

                if ($user['role'] === 'admin') {
                    header('Location: /ridemate/admin/dashboard.php');
                } else {
                    header('Location: /ridemate/index.php');
                }
                exit;
            }

            $_SESSION['error'] = 'OTP is invalid, expired, or maximum attempts reached.';
            header('Location: /ridemate/views/auth/verify_otp.php');
            exit;
        }

        if ($purpose === 'forgot_password') {
            if ($this->userModel->verifyOtp($phone, $otp_code)) {
                $_SESSION['reset_phone'] = $phone;
                unset($_SESSION['otp_purpose'], $_SESSION['otp_sent_at'], $_SESSION['otp_resend_at']);
                header('Location: /ridemate/reset-password.php');
                exit;
            }

            $_SESSION['error'] = 'OTP is invalid, expired, or maximum attempts reached.';
            header('Location: /ridemate/views/auth/verify_otp.php');
            exit;
        }

        $_SESSION['error'] = 'Unexpected verification flow. Please try again.';
        header('Location: /ridemate/views/auth/login.php');
        exit;
    }

    public function forgotPasswordRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $phone = normalizePhone(trim($_POST['phone'] ?? ''));
        if (empty($phone) || !isValidPhone($phone)) {
            $_SESSION['error'] = 'Please enter a valid phone number.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        $cooldown = intval($_SESSION['otp_resend_at'] ?? 0);
        if ($cooldown > time()) {
            $remaining = $cooldown - time();
            $_SESSION['error'] = 'Please wait ' . gmdate('i:s', $remaining) . ' before requesting another OTP.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        $user = $this->userModel->findByPhone($phone);
        if (!$user) {
            $_SESSION['error'] = 'Phone number not found.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        $otpCode = generateOtp();
        $otpExpires = nowDatetime();
        $otpExpires = date('Y-m-d H:i:s', strtotime($otpExpires . ' +5 minutes'));
        if (!$this->userModel->setOtp($phone, $otpCode, $otpExpires)) {
            $_SESSION['error'] = 'Unable to generate OTP. Please try again.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        $message = "Your RideMate password reset OTP is: {$otpCode}. It expires in 5 minutes.";
        $sent = sendSms($phone, $message);

        if (!$sent) {
            $_SESSION['error'] = 'Unable to send OTP SMS. Please try again later.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        $_SESSION['otp_phone'] = $phone;
        $_SESSION['otp_purpose'] = 'forgot_password';
        $_SESSION['otp_sent_at'] = time();
        $_SESSION['otp_resend_at'] = time() + 60;

        header('Location: /ridemate/views/auth/verify_otp.php');
        exit;
    }

    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $phone = $_SESSION['reset_phone'] ?? null;
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['password_confirm'] ?? '');

        if (empty($phone) || empty($password) || empty($confirm)) {
            $_SESSION['error'] = 'All fields are required.';
            header('Location: /ridemate/reset-password.php');
            exit;
        }

        if ($password !== $confirm) {
            $_SESSION['error'] = 'Passwords do not match.';
            header('Location: /ridemate/reset-password.php');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters.';
            header('Location: /ridemate/reset-password.php');
            exit;
        }

        if (!$phone || !isValidPhone($phone)) {
            $_SESSION['error'] = 'Reset session is invalid. Please request a new OTP.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        $user = $this->userModel->findByPhone($phone);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /ridemate/views/auth/forgot_password.php');
            exit;
        }

        if ($this->userModel->resetPassword($user['id'], $password)) {
            unset($_SESSION['reset_phone']);
            $_SESSION['success'] = 'Password changed successfully. Please login.';
            header('Location: /ridemate/views/auth/login.php');
            exit;
        }

        $_SESSION['error'] = 'Failed to update password. Please try again.';
        header('Location: /ridemate/reset-password.php');
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
