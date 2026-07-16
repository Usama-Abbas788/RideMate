<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

$notification_id = intval($_POST['notification_id'] ?? 0);
$notificationController = new NotificationController($conn);
$notificationController->markRead($notification_id, $_SESSION['user_id']);

header('Location: /ridemate/views/notifications.php');
exit;
