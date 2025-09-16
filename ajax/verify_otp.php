<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']); exit;
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
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']); exit;
}

// Validate OTP session state
if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expires']) || !isset($_SESSION['mail'])) {
    echo json_encode(['success' => false, 'message' => 'No OTP session found. Please request a new code.']); exit;
}

// Rate limit failed attempts
$now = time();
if (!empty($_SESSION['otp_locked_until']) && $now < (int)$_SESSION['otp_locked_until']) {
    $remain = (int)$_SESSION['otp_locked_until'] - $now;
    echo json_encode(['success' => false, 'message' => "Too many attempts. Try again in {$remain}s.", 'cooldown' => $remain]); exit;
}

// Check expiration and email match
if ($email !== $_SESSION['mail']) {
    echo json_encode(['success' => false, 'message' => 'Email mismatch. Please request a new code for this email.']); exit;
}
if ($now > (int)$_SESSION['otp_expires']) {
    echo json_encode(['success' => false, 'message' => 'The code has expired. Please request a new one.']); exit;
}

// Compare code
if ($otp !== (string)$_SESSION['otp']) {
    $_SESSION['otp_attempts'] = (int)($_SESSION['otp_attempts'] ?? 0) + 1;
    if ((int)$_SESSION['otp_attempts'] >= 5) {
        $_SESSION['otp_locked_until'] = $now + 60; // lock 60s after 5 failed attempts
    }
    echo json_encode(['success' => false, 'message' => 'Invalid code. Please try again.']); exit;
}

// Passed OTP; proceed to register
$db = new database();

// Server-side uniqueness checks
if ($db->isEmailExists($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is already registered.']); exit;
}
if ($db->isUsernameExists($username)) {
    echo json_encode(['success' => false, 'message' => 'Username is already taken.']); exit;
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT);
$userID = $db->signupCustomer($firstname, $lastname, $phonenum, $email, $username, $passwordHash);

if ($userID) {
    // Clear OTP session values
    unset($_SESSION['otp'], $_SESSION['otp_expires'], $_SESSION['otp_attempts'], $_SESSION['otp_locked_until']);
    // Keep mail in session only if needed; otherwise clear
    // unset($_SESSION['mail']);
    echo json_encode(['success' => true, 'message' => 'Registration successful.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again later.']);
}
?><?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../admin/database/db_connect.php';

// Require that an OTP flow and pending registration are in progress
if (!isset($_SESSION['pending_registration']) || !isset($_SESSION['otp'])) {
    echo json_encode(['success' => false, 'message' => 'No pending registration found.']);
    exit;
}

// Config
$MAX_ATTEMPTS = 5;
$LOCKOUT_MIN = 15;

// Parse request
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$otp = isset($data['otp']) ? preg_replace('/\D+/', '', $data['otp']) : '';

// Validation
$errors = [];
$now = time();

// Lockout check
if (!empty($_SESSION['otp_locked_until']) && $now < $_SESSION['otp_locked_until']) {
    $remaining = $_SESSION['otp_locked_until'] - $now;
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Try again in ' . ceil($remaining / 60) . ' minute(s).']);
    exit;
}

// Basic validation
if ($otp === '' || strlen($otp) < 6 || strlen($otp) > 6) {
    echo json_encode(['success' => false, 'message' => 'Enter a valid 6-digit code.']);
    exit;
}

// Check expiry
if (empty($_SESSION['otp_expires'])) {
    echo json_encode(['success' => false, 'message' => 'No active code. Please request a new one.']);
    exit;
} elseif ($now > (int)$_SESSION['otp_expires']) {
    echo json_encode(['success' => false, 'message' => 'The code has expired. Please request a new one.']);
    exit;
}

// Attempts tracking
$_SESSION['otp_attempts'] = $_SESSION['otp_attempts'] ?? 0;

// Verify OTP
$verified = hash_equals((string)$_SESSION['otp'], $otp);

if ($verified) {
    // Success! Now register the user
    try {
        $db = new Database();
        $pdo = $db->opencon();
        
        // Insert the user from pending registration
        $stmt = $pdo->prepare("INSERT INTO users (user_FN, user_LN, user_email, user_password) VALUES (?, ?, ?, ?)");
        $inserted = $stmt->execute([
            $_SESSION['pending_registration']['user_FN'],
            $_SESSION['pending_registration']['user_LN'],
            $_SESSION['pending_registration']['user_email'],
            $_SESSION['pending_registration']['user_password']
        ]);
        
        if ($inserted) {
            $userId = $pdo->lastInsertId();
            
            // Set user session
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $_SESSION['pending_registration']['user_email'];
            $_SESSION['user_name'] = $_SESSION['pending_registration']['user_FN'] . ' ' . $_SESSION['pending_registration']['user_LN'];
            
            // Clean up OTP and pending registration
            unset(
                $_SESSION['otp'],
                $_SESSION['otp_expires'],
                $_SESSION['otp_attempts'],
                $_SESSION['otp_locked_until'],
                $_SESSION['pending_registration']
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful! You are now logged in.',
                'redirect' => 'index.php' // Adjust as needed
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create account. Please try again.']);
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred during registration.']);
    }
} else {
    // Failed verification
    $_SESSION['otp_attempts']++;
    
    if ($_SESSION['otp_attempts'] >= $MAX_ATTEMPTS) {
        $_SESSION['otp_locked_until'] = $now + ($LOCKOUT_MIN * 60);
        echo json_encode(['success' => false, 'message' => 'Too many incorrect attempts. Try again later.']);
    } else {
        $remainingAttempts = $MAX_ATTEMPTS - $_SESSION['otp_attempts'];
        echo json_encode(['success' => false, 'message' => 'Incorrect code. Attempts left: ' . $remainingAttempts . '.']);
    }
}