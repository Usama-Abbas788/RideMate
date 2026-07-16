<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/BookingController.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to book a ride.';
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

$bookingController = new BookingController($conn);
$bookingController->book();
