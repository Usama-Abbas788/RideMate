<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$auth = new AuthController($conn);
$auth->resendVerificationAjax();
