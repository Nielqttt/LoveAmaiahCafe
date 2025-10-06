<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/database.php';

// Determine logged-in user and type
$userType = '';
$userID = null;
if (isset($_SESSION['OwnerID'])) { $userType = 'owner'; $userID = (int)$_SESSION['OwnerID']; }
elseif (isset($_SESSION['EmployeeID'])) { $userType = 'employee'; $userID = (int)$_SESSION['EmployeeID']; }
elseif (isset($_SESSION['CustomerID'])) { $userType = 'customer'; $userID = (int)$_SESSION['CustomerID']; }

if (!$userType || !$userID) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Only require OTP for employee/customer when changing password (security update)
$isSecurityUpdate = !empty($_POST) ? !empty($_POST['new_password']) : false;
if (!$isSecurityUpdate) {
    // If JSON body was used, we need to check $input after parsing below
}

// Read input (JSON or form)
$input = [];
if (!empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
} else {
    $input = $_POST;
}

// Determine if this request is a security update (password change)
if (!$isSecurityUpdate) { $isSecurityUpdate = !empty($input['new_password']); }

// Respect explicit section hint from client: profile vs security
$section = isset($input['section']) ? strtolower(trim((string)$input['section'])) : '';
if ($section === 'profile') {
    // Force profile-only update: strip password fields and skip OTP
    $isSecurityUpdate = false;
    unset($input['new_password'], $input['current_password']);
}
if ($section === 'security') {
    // Ensure we treat as security update even if password key check was missed
    $isSecurityUpdate = true;
}

if ($isSecurityUpdate && in_array($userType, ['employee', 'customer'], true)) {
    if (empty($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
        echo json_encode(['success' => false, 'message' => 'OTP verification required for security changes']);
        exit;
    }
}

// Map expected fields for updateUserData
$data = [
    'username' => isset($input['username']) ? trim((string)$input['username']) : null,
    'name'     => isset($input['name']) ? trim((string)$input['name']) : null,
    'email'    => isset($input['email']) ? trim((string)$input['email']) : null,
    'phone'    => isset($input['phone']) ? trim((string)$input['phone']) : null,
];

// Password change (only when security section)
if ($isSecurityUpdate && !empty($input['new_password'])) {
    $data['new_password'] = (string)$input['new_password'];
    $data['current_password'] = isset($input['current_password']) ? (string)$input['current_password'] : '';
}

// Basic validation (server-side)
$errors = [];
if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address.';
}
if (!empty($data['phone']) && !preg_match('/^[+0-9\-\s]{7,}$/', $data['phone'])) {
    $errors[] = 'Invalid phone number format.';
}
if (!empty($data['new_password'])) {
    // Align with registration: 6+ chars with upper, lower, digit, special
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{6,}$/', $data['new_password'])) {
        $errors[] = 'New password must be 6+ characters and include upper, lower, number, and special character.';
    }
    if (empty($data['current_password'])) {
        $errors[] = 'Current password is required to set a new password.';
    }
}
if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// For profile-only updates, no OTP is required. If this is a security update, optional email match check can be enforced.
if ($isSecurityUpdate && in_array($userType, ['employee', 'customer'], true) && !empty($data['email'])) {
    $verifiedEmail = isset($_SESSION['mail']) ? (string)$_SESSION['mail'] : '';
    if ($verifiedEmail === '' || strcasecmp($verifiedEmail, $data['email']) !== 0) {
        echo json_encode(['success' => false, 'message' => 'Please verify the OTP sent to the email you entered before saving security changes.']);
        exit;
    }
}

$con = new database();
$pdo = $con->opencon();

// For security updates, verify the provided current password matches the stored hash BEFORE updating
if ($isSecurityUpdate) {
    try {
        if ($userType === 'customer') {
            $stmt = $pdo->prepare('SELECT C_Password AS pw FROM customer WHERE CustomerID = ? LIMIT 1');
        } elseif ($userType === 'employee') {
            $stmt = $pdo->prepare('SELECT E_Password AS pw FROM employee WHERE EmployeeID = ? LIMIT 1');
        } else { // owner
            $stmt = $pdo->prepare('SELECT Password AS pw FROM owner WHERE OwnerID = ? LIMIT 1');
        }
        $stmt->execute([$userID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $hash = $row['pw'] ?? '';
        if (!$hash || empty($data['current_password']) || !password_verify($data['current_password'], $hash)) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
            exit;
        }
        // Optional: if a confirm_password was provided, ensure it matches new_password
        if (!empty($input['confirm_password']) && (!isset($input['new_password']) || $input['confirm_password'] !== $input['new_password'])) {
            echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match.']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Unable to verify current password. Please try again.']);
        exit;
    }
}
// Proceed with update after validations
$result = $con->updateUserData($userID, $userType, $data);
// Prevent reuse of OTP after security update
if ($isSecurityUpdate) {
    unset($_SESSION['otp_verified']);
    unset($_SESSION['mail']);
}

echo json_encode($result);
