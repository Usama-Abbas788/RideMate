<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AdminController.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = 'Unauthorized action.';
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id'] ?? 0);
    
    $adminController = new AdminController($conn);
    if ($adminController->deleteUser($userId)) {
        $_SESSION['success'] = 'User deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete user or user is an admin.';
    }
}

header('Location: /ridemate/admin/dashboard.php#users');
exit;
