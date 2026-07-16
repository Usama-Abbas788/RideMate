<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$auth = new AuthController($conn);
$auth->login();

// Fallback redirect if not POST
header('Location: /ridemate/views/auth/login.php');
exit;
