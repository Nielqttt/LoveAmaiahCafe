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

  // Try to update across possible user types; only one should match the email.
  $hash = password_hash($newPassword, PASSWORD_DEFAULT);
  $updated = false;

  // Customer
  $stmt = $pdo->prepare("UPDATE customer SET C_Password = ? WHERE C_Email = ?");
  $stmt->execute([$hash, $email]);
  if ($stmt->rowCount() > 0) { $updated = true; }

  // Employee
  if (!$updated) {
    $stmt = $pdo->prepare("UPDATE employee SET E_Password = ? WHERE E_Email = ?");
    $stmt->execute([$hash, $email]);
    if ($stmt->rowCount() > 0) { $updated = true; }
  }

  // Owner
  if (!$updated) {
    $stmt = $pdo->prepare("UPDATE owner SET Password = ? WHERE O_Email = ?");
    $stmt->execute([$hash, $email]);
    if ($stmt->rowCount() > 0) { $updated = true; }
  }

  if ($updated) {
    // Clear verification so the token can't be reused
    unset($_SESSION['otp_verified']);
    echo json_encode(['success' => true]);
  } else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No account found for that email.']);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Server error.']);
}
