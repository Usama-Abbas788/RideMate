<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$auth = new AuthController($conn);
$auth->forgotPasswordRequest();

header('Location: /ridemate/views/auth/forgot_password.php');
exit;
