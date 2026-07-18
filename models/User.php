<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function register($name, $email, $password, $role, $phone = null) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare(
            "INSERT INTO users (name, email, phone, password, role, email_verified) VALUES (?, ?, ?, ?, ?, 0)"
        );
        $stmt->bind_param("sssss", $name, $email, $phone, $hashed, $role);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function registerPending($name, $email, $password, $role, $verification_token, $verification_expires, $phone = null) {
        $stmt = $this->conn->prepare(
            "REPLACE INTO pending_registrations (name, email, phone, password, role, verification_token, verification_expires, otp_code, otp_expires, otp_attempts) VALUES (?, ?, ?, ?, ?, ?, ?, NULL, NULL, 0)"
        );
        $stmt->bind_param("sssssss", $name, $email, $phone, $password, $role, $verification_token, $verification_expires);
        return $stmt->execute();
    }

    public function findPendingByEmail($email) {
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, phone, password, role, verification_token, verification_expires, otp_code, otp_expires, otp_attempts, created_at FROM pending_registrations WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updatePendingVerificationToken(string $email, string $verification_token, string $verification_expires): bool {
        $stmt = $this->conn->prepare(
            "UPDATE pending_registrations SET verification_token = ?, verification_expires = ? WHERE email = ?"
        );
        $stmt->bind_param("sss", $verification_token, $verification_expires, $email);
        return $stmt->execute();
    }

    public function deletePendingRegistration(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM pending_registrations WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function createVerifiedUserFromPending(array $pending): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO users (name, email, phone, password, role, email_verified) VALUES (?, ?, ?, ?, ?, 1)"
        );
        $stmt->bind_param("sssss", $pending['name'], $pending['email'], $pending['phone'], $pending['password'], $pending['role']);
        return $stmt->execute();
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, phone, password, role, email_verified, otp_code, otp_expires, otp_attempts, reset_token, reset_expires, created_at FROM users WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($id) {
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, phone, password, role, email_verified, otp_code, otp_expires, otp_attempts, reset_token, reset_expires, created_at FROM users WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllUsers() {
        $result = $this->conn->query(
            "SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countByRole($role) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE role = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['cnt'];
    }

    public function getUsersByRole(string $role): array {
        $stmt = $this->conn->prepare(
            "SELECT id, name, email FROM users WHERE role = ?"
        );
        $stmt->bind_param("s", $role);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function pendingEmailExists($email) {
        $stmt = $this->conn->prepare("SELECT id FROM pending_registrations WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function phoneExists($phone) {
        if (empty($phone)) {
            return false;
        }
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function pendingPhoneExists($phone) {
        if (empty($phone)) {
            return false;
        }
        $stmt = $this->conn->prepare("SELECT id FROM pending_registrations WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function setOtpForPending($email, $otp_code, $otp_expires): bool {
        $stmt = $this->conn->prepare(
            "UPDATE pending_registrations SET otp_code = ?, otp_expires = ?, otp_attempts = 0 WHERE email = ?"
        );
        $stmt->bind_param("sss", $otp_code, $otp_expires, $email);
        return $stmt->execute();
    }

    public function setOtp($email, $otp_code, $otp_expires): bool {
        $stmt = $this->conn->prepare(
            "UPDATE users SET otp_code = ?, otp_expires = ?, otp_attempts = 0 WHERE email = ?"
        );
        $stmt->bind_param("sss", $otp_code, $otp_expires, $email);
        return $stmt->execute();
    }

    public function verifyOtpForPending(string $email, string $otp_code): bool {
        $pending = $this->findPendingByEmail($email);
        if (!$pending) {
            return false;
        }

        if ($pending['otp_attempts'] >= 5) {
            return false;
        }

        if ($pending['otp_code'] !== $otp_code || strtotime($pending['otp_expires']) < time()) {
            $this->incrementOtpAttemptsPending($pending['id']);
            return false;
        }

        if ($this->createVerifiedUserFromPending($pending)) {
            return $this->deletePendingRegistration($pending['id']);
        }

        return false;
    }

    public function verifyOtp($email, $otp_code): bool {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }

        if ($user['otp_attempts'] >= 5) {
            return false;
        }

        if ($user['otp_code'] !== $otp_code || strtotime($user['otp_expires']) < time()) {
            $this->incrementOtpAttempts($user['id']);
            return false;
        }

        return $this->clearOtpById($user['id']);
    }

    public function clearOtp($email): bool {
        $stmt = $this->conn->prepare(
            "UPDATE users SET otp_code = NULL, otp_expires = NULL, otp_attempts = 0 WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        return $stmt->execute();
    }

    public function clearOtpById(int $user_id): bool {
        $stmt = $this->conn->prepare(
            "UPDATE users SET otp_code = NULL, otp_expires = NULL, otp_attempts = 0 WHERE id = ?"
        );
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    public function incrementOtpAttempts(int $user_id): bool {
        $stmt = $this->conn->prepare(
            "UPDATE users SET otp_attempts = otp_attempts + 1 WHERE id = ?"
        );
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    public function incrementOtpAttemptsPending(int $pending_id): bool {
        $stmt = $this->conn->prepare(
            "UPDATE pending_registrations SET otp_attempts = otp_attempts + 1 WHERE id = ?"
        );
        $stmt->bind_param("i", $pending_id);
        return $stmt->execute();
    }

    public function findByEmailAndPassword($email, $password) {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['password']) ? $user : false;
    }

    public function requestPasswordReset(string $email, string $reset_token, string $reset_expires): bool {
        $stmt = $this->conn->prepare(
            "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ? AND email_verified = 1"
        );
        $stmt->bind_param("sss", $reset_token, $reset_expires, $email);
        return $stmt->execute();
    }

    public function findByResetToken(string $token) {
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, phone, role, reset_token, reset_expires FROM users WHERE reset_token = ? AND reset_expires >= NOW()"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function resetPasswordByToken(string $token, string $newPassword): bool {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare(
            "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?"
        );
        $stmt->bind_param("ss", $hashed, $token);
        return $stmt->execute();
    }

    public function deleteUser(int $user_id): bool {
        $stmt = $this->conn->prepare(
            "DELETE FROM users WHERE id = ? AND role != 'admin'"
        );
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    public function verifyEmail(string $token): bool {
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, phone, password, role, verification_token, verification_expires FROM pending_registrations WHERE verification_token = ?"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_assoc();

        if (!$pending) {
            return false;
        }

        if (strtotime($pending['verification_expires']) < time()) {
            return false;
        }

        if ($this->createVerifiedUserFromPending($pending)) {
            return $this->deletePendingRegistration($pending['id']);
        }

        return false;
    }
}
