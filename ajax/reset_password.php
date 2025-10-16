<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../classes/database.php';

// Only allow POST JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method not allowed']);
  exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$email = isset($data['email']) ? trim((string)$data['email']) : '';
$newPassword = isset($data['new_password']) ? (string)$data['new_password'] : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid email']);
  exit;
}
if (strlen($newPassword) < 6) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
  exit;
}

// Require that an OTP verification has occurred recently for this session & email
if (empty($_SESSION['otp_verified']) || empty($_SESSION['mail']) || strcasecmp($_SESSION['mail'], $email) !== 0) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Please verify your email first.']);
  exit;
}

try {
  $db = new database();
  $pdo = $db->opencon();

  $hash = password_hash($newPassword, PASSWORD_DEFAULT);

  // Find which accounts exist for this email across all user types
  $exists = [ 'customer' => false, 'employee' => false, 'owner' => false ];
  $stmt = $pdo->prepare("SELECT 1 FROM customer WHERE C_Email = ? LIMIT 1");
  $stmt->execute([$email]);
  $exists['customer'] = (bool)$stmt->fetchColumn();
  $stmt = $pdo->prepare("SELECT 1 FROM employee WHERE E_Email = ? LIMIT 1");
  $stmt->execute([$email]);
  $exists['employee'] = (bool)$stmt->fetchColumn();
  $stmt = $pdo->prepare("SELECT 1 FROM owner WHERE O_Email = ? LIMIT 1");
  $stmt->execute([$email]);
  $exists['owner'] = (bool)$stmt->fetchColumn();

  if (!$exists['customer'] && !$exists['employee'] && !$exists['owner']) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No account found for that email.']);
    exit;
  }

  // Update ALL matching accounts to keep credentials consistent for the email owner
  if ($exists['customer']) {
    $stmt = $pdo->prepare("UPDATE customer SET C_Password = ? WHERE C_Email = ?");
    $stmt->execute([$hash, $email]);
  }
  if ($exists['employee']) {
    $stmt = $pdo->prepare("UPDATE employee SET E_Password = ? WHERE E_Email = ?");
    $stmt->execute([$hash, $email]);
  }
  if ($exists['owner']) {
    $stmt = $pdo->prepare("UPDATE owner SET Password = ? WHERE O_Email = ?");
    $stmt->execute([$hash, $email]);
  }

  // Clear verification so the token can't be reused
  unset($_SESSION['otp_verified']);
  echo json_encode(['success' => true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Server error.']);
}
