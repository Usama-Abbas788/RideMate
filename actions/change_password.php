<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

$auth = new AuthController($conn);
$auth->changePassword();

header('Location: /ridemate/views/auth/change_password.php');
exit;
