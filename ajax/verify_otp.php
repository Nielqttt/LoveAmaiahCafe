<?php
session_start();
header('Content-Type: application/json');
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
ob_start();

require_once __DIR__ . '/../classes/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $resp = json_encode(['success' => false, 'message' => 'Method not allowed.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?? [];

$otp = isset($data['otp']) ? trim((string)$data['otp']) : '';
if ($otp === '' || !preg_match('/^\d{6}$/', $otp)) {
    $resp = json_encode(['success' => false, 'message' => 'Enter a valid 6-digit code.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

// Ensure pending registration exists
if (!isset($_SESSION['pending_registration']) || !isset($_SESSION['otp'])) {
    $resp = json_encode(['success' => false, 'message' => 'No pending registration found.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

$now = time();
if (!empty($_SESSION['otp_locked_until']) && $now < (int)$_SESSION['otp_locked_until']) {
    $remain = (int)$_SESSION['otp_locked_until'] - $now;
    $resp = json_encode(['success' => false, 'message' => 'Too many attempts. Try again in ' . $remain . 's.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

if (empty($_SESSION['otp_expires']) || $now > (int)$_SESSION['otp_expires']) {
    $resp = json_encode(['success' => false, 'message' => 'The code has expired. Please request a new one.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

if (!hash_equals((string)$_SESSION['otp'], $otp)) {
    $_SESSION['otp_attempts'] = (int)($_SESSION['otp_attempts'] ?? 0) + 1;
    if ((int)$_SESSION['otp_attempts'] >= 5) { $_SESSION['otp_locked_until'] = $now + 60; }
    $resp = json_encode(['success' => false, 'message' => 'Invalid code. Please try again.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

// Create account from pending registration
$p = $_SESSION['pending_registration'];
$db = new database();
// Recheck uniqueness
if ($db->isEmailExists($p['email'])) { $resp = json_encode(['success' => false, 'message' => 'Email is already registered.']); if (ob_get_length()) { ob_clean(); } echo $resp; exit; }
if ($db->isUsernameExists($p['username'])) { $resp = json_encode(['success' => false, 'message' => 'Username is already taken.']); if (ob_get_length()) { ob_clean(); } echo $resp; exit; }

$userID = $db->signupCustomer($p['firstname'], $p['lastname'], $p['phonenum'], $p['email'], $p['username'], $p['password']);
if ($userID) {
    unset($_SESSION['otp'], $_SESSION['otp_expires'], $_SESSION['otp_attempts'], $_SESSION['otp_locked_until'], $_SESSION['pending_registration']);
    $resp = json_encode(['success' => true, 'message' => 'Registration successful.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
} else {
    $resp = json_encode(['success' => false, 'message' => 'Registration failed. Please try again later.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}