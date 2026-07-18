<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

$notificationController = new NotificationController($conn);
$notificationController->deleteAll($_SESSION['user_id']);

header('Location: /ridemate/views/notifications.php');
exit;
