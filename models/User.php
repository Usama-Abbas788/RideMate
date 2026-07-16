<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function register($name, $phone, $password, $role) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare(
            "INSERT INTO users (name, phone, password, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $phone, $hashed, $role);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function registerPending($name, $phone, $password, $role, $otp_code, $otp_expires) {
        $stmt = $this->conn->prepare(
            "REPLACE INTO pending_registrations (name, phone, password, role, otp_code, otp_expires) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss", $name, $phone, $password, $role, $otp_code, $otp_expires);
        return $stmt->execute();
    }

    public function findPendingByPhone($phone) {
        $stmt = $this->conn->prepare(
            "SELECT id, name, phone, password, role, otp_code, otp_expires, otp_attempts, created_at FROM pending_registrations WHERE phone = ?"
        );
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function deletePendingRegistration(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM pending_registrations WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function createVerifiedUserFromPending(array $pending): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO users (name, phone, password, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $pending['name'], $pending['phone'], $pending['password'], $pending['role']);
        return $stmt->execute();
    }

    public function findByPhone($phone) {
        $stmt = $this->conn->prepare(
            "SELECT id, name, phone, password, role, otp_code, otp_expires, otp_attempts, created_at FROM users WHERE phone = ?"
        );
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($id) {
        $stmt = $this->conn->prepare(
            "SELECT id, name, phone, password, role, otp_code, otp_expires, otp_attempts, created_at FROM users WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllUsers() {
        $result = $this->conn->query(
            "SELECT id, name, phone, role, created_at FROM users ORDER BY created_at DESC"
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
            "SELECT id, name, phone FROM users WHERE role = ?"
        );
        $stmt->bind_param("s", $role);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function phoneExists($phone) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function pendingPhoneExists($phone) {
        $stmt = $this->conn->prepare("SELECT id FROM pending_registrations WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function setOtpForPending($phone, $otp_code, $otp_expires): bool {
        $stmt = $this->conn->prepare(
            "UPDATE pending_registrations SET otp_code = ?, otp_expires = ?, otp_attempts = 0 WHERE phone = ?"
        );
        $stmt->bind_param("sss", $otp_code, $otp_expires, $phone);
        return $stmt->execute();
    }

    public function setOtp($phone, $otp_code, $otp_expires): bool {
        $stmt = $this->conn->prepare(
            "UPDATE users SET otp_code = ?, otp_expires = ?, otp_attempts = 0 WHERE phone = ?"
        );
        $stmt->bind_param("sss", $otp_code, $otp_expires, $phone);
        return $stmt->execute();
    }

    public function verifyOtpForPending(string $phone, string $otp_code): bool {
        $pending = $this->findPendingByPhone($phone);
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

    public function verifyOtp($phone, $otp_code): bool {
        $user = $this->findByPhone($phone);
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

    public function clearOtp($phone): bool {
        $stmt = $this->conn->prepare(
            "UPDATE users SET otp_code = NULL, otp_expires = NULL, otp_attempts = 0 WHERE phone = ?"
        );
        $stmt->bind_param("s", $phone);
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

    public function findByPhoneAndPassword($phone, $password) {
        $user = $this->findByPhone($phone);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['password']) ? $user : false;
    }

    public function resetPassword(int $user_id, string $newPassword): bool {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare(
            "UPDATE users SET password = ? WHERE id = ?"
        );
        $stmt->bind_param("si", $hashed, $user_id);
        return $stmt->execute();
    }

    public function deleteUser(int $user_id): bool {
        $stmt = $this->conn->prepare(
            "DELETE FROM users WHERE id = ? AND role != 'admin'"
        );
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
}
