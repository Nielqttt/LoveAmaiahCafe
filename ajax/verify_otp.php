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
$data = json_decode($raw, true);

$otp       = isset($data['otp']) ? trim((string)$data['otp']) : '';
$firstname = isset($data['firstname']) ? trim((string)$data['firstname']) : '';
$lastname  = isset($data['lastname']) ? trim((string)$data['lastname']) : '';
$email     = isset($data['email']) ? trim((string)$data['email']) : '';
$username  = isset($data['username']) ? trim((string)$data['username']) : '';
$phonenum  = isset($data['phonenum']) ? trim((string)$data['phonenum']) : '';
$password  = isset($data['password']) ? (string)$data['password'] : '';

if ($otp === '' || $email === '' || $username === '' || $password === '' || $firstname === '' || $lastname === '' || $phonenum === '') {
    $resp = json_encode(['success' => false, 'message' => 'Missing required fields.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

// Validate OTP session state
if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expires']) || !isset($_SESSION['mail'])) {
    $resp = json_encode(['success' => false, 'message' => 'No OTP session found. Please request a new code.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

// Rate limit failed attempts
$now = time();
if (!empty($_SESSION['otp_locked_until']) && $now < (int)$_SESSION['otp_locked_until']) {
    $remain = (int)$_SESSION['otp_locked_until'] - $now;
    $resp = json_encode(['success' => false, 'message' => "Too many attempts. Try again in {$remain}s.", 'cooldown' => $remain]);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

// Check expiration and email match
if ($email !== $_SESSION['mail']) {
    $resp = json_encode(['success' => false, 'message' => 'Email mismatch. Please request a new code for this email.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}
if ($now > (int)$_SESSION['otp_expires']) {
    $resp = json_encode(['success' => false, 'message' => 'The code has expired. Please request a new one.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

// Compare code
if ($otp !== (string)$_SESSION['otp']) {
    $_SESSION['otp_attempts'] = (int)($_SESSION['otp_attempts'] ?? 0) + 1;
    if ((int)$_SESSION['otp_attempts'] >= 5) {
        $_SESSION['otp_locked_until'] = $now + 60; // lock 60s after 5 failed attempts
    }
    $resp = json_encode(['success' => false, 'message' => 'Invalid code. Please try again.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

// Passed OTP; proceed to register
$db = new database();

// Server-side uniqueness checks
if ($db->isEmailExists($email)) {
    $resp = json_encode(['success' => false, 'message' => 'Email is already registered.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}
if ($db->isUsernameExists($username)) {
    $resp = json_encode(['success' => false, 'message' => 'Username is already taken.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT);
$userID = $db->signupCustomer($firstname, $lastname, $phonenum, $email, $username, $passwordHash);

if ($userID) {
    // Clear OTP session values
    unset($_SESSION['otp'], $_SESSION['otp_expires'], $_SESSION['otp_attempts'], $_SESSION['otp_locked_until']);
    // Keep mail in session only if needed; otherwise clear
    // unset($_SESSION['mail']);
    $resp = json_encode(['success' => true, 'message' => 'Registration successful.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
} else {
    $resp = json_encode(['success' => false, 'message' => 'Registration failed. Please try again later.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}
?>