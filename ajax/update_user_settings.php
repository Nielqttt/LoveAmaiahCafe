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

// For employee and customer, require a fresh OTP verification
if (in_array($userType, ['employee', 'customer'], true)) {
    if (empty($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
        echo json_encode(['success' => false, 'message' => 'OTP verification required']);
        exit;
    }
}

// Read input (JSON or form)
$input = [];
if (!empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
} else {
    $input = $_POST;
}

// Map expected fields for updateUserData
$data = [
    'username' => isset($input['username']) ? trim((string)$input['username']) : null,
    'name'     => isset($input['name']) ? trim((string)$input['name']) : null,
    'email'    => isset($input['email']) ? trim((string)$input['email']) : null,
    'phone'    => isset($input['phone']) ? trim((string)$input['phone']) : null,
];

// Password change (optional)
if (!empty($input['new_password'])) {
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
    // simple strength hint (same as client intent)
    if (strlen($data['new_password']) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }
    if (empty($data['current_password'])) {
        $errors[] = 'Current password is required to set a new password.';
    }
}
if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Ensure the verified email matches the email being updated (for employee/customer)
if (in_array($userType, ['employee', 'customer'], true) && !empty($data['email'])) {
    $verifiedEmail = isset($_SESSION['mail']) ? (string)$_SESSION['mail'] : '';
    if ($verifiedEmail === '' || strcasecmp($verifiedEmail, $data['email']) !== 0) {
        echo json_encode(['success' => false, 'message' => 'Please verify the OTP sent to the email you entered before saving.']);
        exit;
    }
}

$con = new database();
$result = $con->updateUserData($userID, $userType, $data);

// Prevent reuse of OTP after attempt
unset($_SESSION['otp_verified']);
unset($_SESSION['mail']);

echo json_encode($result);
