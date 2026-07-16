<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/BookingController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

$bookingController = new BookingController($conn);
$bookingController->cancel();
