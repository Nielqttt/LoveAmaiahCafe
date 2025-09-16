<?php
session_start();
header('Content-Type: application/json');

// This endpoint verifies an OTP stored in PHP session by ajax/send_otp.php.
// It does NOT write to the database; it simply confirms the code and marks the session as verified.

// Config
$MAX_ATTEMPTS = 5;      // max wrong attempts before temporary lockout
$LOCKOUT_MIN  = 15;     // lockout duration in minutes

// Guard: no OTP has been sent yet
if (!isset($_SESSION['otp'])) {
    echo json_encode(['success' => false, 'message' => 'No active code. Please request a new one.']);
    exit;
}

// Parse request body (JSON: { otp: "123456" })
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
$otp  = isset($data['otp']) ? preg_replace('/\D+/', '', (string)$data['otp']) : '';

$now = time();

// Lockout check
if (!empty($_SESSION['otp_locked_until']) && $now < (int)$_SESSION['otp_locked_until']) {
    $remaining = (int)$_SESSION['otp_locked_until'] - $now;
    echo json_encode([
        'success' => false,
        'message' => 'Too many attempts. Try again in ' . max(1, ceil($remaining / 60)) . ' minute(s).'
    ]);
    exit;
}

// Basic validation
if ($otp === '' || strlen($otp) !== 6) {
    echo json_encode(['success' => false, 'message' => 'Enter a valid 6-digit code.']);
    exit;
}

// Expiry check
if (empty($_SESSION['otp_expires']) || $now > (int)$_SESSION['otp_expires']) {
    echo json_encode(['success' => false, 'message' => 'The code has expired. Please request a new one.']);
    exit;
}

// Attempts tracking
$_SESSION['otp_attempts'] = isset($_SESSION['otp_attempts']) ? (int)$_SESSION['otp_attempts'] : 0;

// Verify
$isValid = hash_equals((string)$_SESSION['otp'], $otp);

if ($isValid) {
    // Mark verified and clean sensitive data
    $_SESSION['otp_verified'] = true;
    $verifiedEmail = isset($_SESSION['mail']) ? (string)$_SESSION['mail'] : '';
    unset(
        $_SESSION['otp'],
        $_SESSION['otp_expires'],
        $_SESSION['otp_attempts'],
        $_SESSION['otp_locked_until']
    );

    echo json_encode([
        'success' => true,
        'message' => 'Email verified successfully.',
        'email'   => $verifiedEmail
    ]);
    exit;
}

// Wrong code path
$_SESSION['otp_attempts']++;
if ($_SESSION['otp_attempts'] >= $MAX_ATTEMPTS) {
    $_SESSION['otp_locked_until'] = $now + ($LOCKOUT_MIN * 60);
    echo json_encode(['success' => false, 'message' => 'Too many incorrect attempts. Try again later.']);
} else {
    $remainingAttempts = $MAX_ATTEMPTS - (int)$_SESSION['otp_attempts'];
    echo json_encode(['success' => false, 'message' => 'Incorrect code. Attempts left: ' . $remainingAttempts . '.']);
}