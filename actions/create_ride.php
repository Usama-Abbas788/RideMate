<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/RideController.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'driver') {
    $_SESSION['error'] = 'Only drivers can post rides.';
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

$rideController = new RideController($conn);
$rideController->create();
