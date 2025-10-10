<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../classes/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (empty($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Email not verified']);
    exit;
}

$pending = $_SESSION['pending_registration'] ?? null;
if (!$pending) {
    echo json_encode(['success' => false, 'message' => 'No pending registration found']);
    exit;
}

$con = new database();
$userID = $con->signupCustomer(
    $pending['firstname'],
    $pending['lastname'],
    $pending['phonenum'],
    $pending['email'],
    $pending['username'],
    $pending['password']
);

if ($userID) {
    // Persist verified state
    try {
        $con->ensureCustomerEmailVerified();
        $con->markCustomerEmailVerified((int)$userID);
    } catch (Throwable $e) { /* ignore */ }
    // Cleanup after success
    unset($_SESSION['pending_registration']);
    // We keep otp_verified as true for this session only if needed; safe to unset too.
    unset($_SESSION['otp_verified']);
    echo json_encode(['success' => true, 'message' => 'Registration complete', 'userID' => $userID]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create account. Please try again.']);
}
