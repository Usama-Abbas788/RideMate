<?php
require_once __DIR__ . '/../config/database.php';

class Notification {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    private function prepare(string $query) {
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log('Notification SQL prepare failed: ' . $this->conn->error);
            return false;
        }
        return $stmt;
    }

    /**
     * Create a new notification entry.
     */
    public function create(int $user_id, string $message, string $type = 'info'): bool {
        $stmt = $this->prepare(
            "INSERT INTO notifications (user_id, message, type, is_read) VALUES (?, ?, ?, 0)"
        );
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('iss', $user_id, $message, $type);
        return $stmt->execute();
    }

    /**
     * Get notifications for a user.
     */
    public function getByUser(int $user_id, int $limit = 50): array {
        $stmt = $this->prepare(
            "SELECT id, message, type, is_read, created_at FROM notifications
             WHERE user_id = ? ORDER BY created_at DESC LIMIT ?"
        );
        if ($stmt === false) {
            return [];
        }
        $stmt->bind_param('ii', $user_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Count unread notifications for a user.
     */
    public function countUnread(int $user_id): int {
        $stmt = $this->prepare(
            "SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0"
        );
        if ($stmt === false) {
            return 0;
        }
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return intval($result['total'] ?? 0);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(int $id, int $user_id): bool {
        $stmt = $this->prepare(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?"
        );
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('ii', $id, $user_id);
        return $stmt->execute();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $user_id): bool {
        $stmt = $this->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ?"
        );
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('i', $user_id);
        return $stmt->execute();
    }
}
