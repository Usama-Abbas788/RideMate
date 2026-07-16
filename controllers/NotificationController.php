<?php
require_once __DIR__ . '/../models/Notification.php';

class NotificationController {
    private $notificationModel;

    public function __construct($conn) {
        $this->notificationModel = new Notification($conn);
    }

    public function fetchForUser(int $user_id): array {
        return $this->notificationModel->getByUser($user_id);
    }

    public function markRead(int $notification_id, int $user_id): bool {
        return $this->notificationModel->markAsRead($notification_id, $user_id);
    }

    public function markAllRead(int $user_id): bool {
        return $this->notificationModel->markAllAsRead($user_id);
    }

    public function countUnread(int $user_id): int {
        return $this->notificationModel->countUnread($user_id);
    }
}
