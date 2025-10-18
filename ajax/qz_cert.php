<?php
session_start();
// Restrict to staff
if (!isset($_SESSION['OwnerID']) && !isset($_SESSION['EmployeeID'])) {
    http_response_code(403);
    header('Content-Type: text/plain');
    echo 'Forbidden';
    exit();
}

require_once(__DIR__ . '/../classes/qz_config.php');
$path = QZConfig::publicCertPath();
if (!is_file($path)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo 'Certificate not found';
    exit();
}
header('Content-Type: text/plain');
readfile($path);
?>