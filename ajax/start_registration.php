<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../classes/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Accept form-url-encoded or JSON
$input = [];
if (!empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
} else {
    $input = $_POST;
}

$firstname = trim((string)($input['firstname'] ?? ''));
$lastname  = trim((string)($input['lastname'] ?? ''));
$email     = trim((string)($input['email'] ?? ''));
$username  = trim((string)($input['username'] ?? ''));
$phonenum  = trim((string)($input['phonenum'] ?? ''));
$password  = (string)($input['password'] ?? '');

// Basic validation
$errors = [];
if ($firstname === '') $errors['firstname'] = 'First name is required';
if ($lastname === '')  $errors['lastname']  = 'Last name is required';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
if (!preg_match('/^09\d{9}$/', $phonenum)) $errors['phonenum'] = 'Valid phone starts with 09 and has 11 digits';
// Reasonable password check on server side as well
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/', $password)) {
    $errors['password'] = 'Password must be 6+ chars with upper, number, special';
}

if ($errors) {
    echo json_encode(['success' => false, 'message' => 'Fix validation errors', 'errors' => $errors]);
    exit;
}

$con = new database();
// Uniqueness checks
if ($con->isUsernameExists($username)) {
    echo json_encode(['success' => false, 'message' => 'Username is already taken', 'field' => 'username']);
    exit;
}
if ($con->isEmailExists($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is already in use', 'field' => 'email']);
    exit;
}

// Save pending registration in session; hash password here
$_SESSION['pending_registration'] = [
    'firstname' => $firstname,
    'lastname'  => $lastname,
    'phonenum'  => $phonenum,
    'email'     => $email,
    'username'  => $username,
    'password'  => password_hash($password, PASSWORD_BCRYPT),
];
// Mark OTP as not verified yet
$_SESSION['otp_verified'] = false;
// Also prepare default email for OTP send endpoint
$_SESSION['mail'] = $email;

// Clear any previous OTP state
unset($_SESSION['otp'], $_SESSION['otp_expires'], $_SESSION['otp_attempts'], $_SESSION['otp_locked_until']);

echo json_encode(['success' => true, 'message' => 'Pending registration started', 'email' => $email]);
