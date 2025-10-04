<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/database.php';

// Identify user
$userType = '';
$userID = null;
if (isset($_SESSION['OwnerID'])) { $userType = 'owner'; $userID = (int)$_SESSION['OwnerID']; }
elseif (isset($_SESSION['EmployeeID'])) { $userType = 'employee'; $userID = (int)$_SESSION['EmployeeID']; }
elseif (isset($_SESSION['CustomerID'])) { $userType = 'customer'; $userID = (int)$_SESSION['CustomerID']; }

if (!$userType || !$userID) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Read JSON input
$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?: [];
$current = isset($input['current_password']) ? (string)$input['current_password'] : '';
if ($current === '') {
    echo json_encode(['success' => false, 'message' => 'Current password is required.']);
    exit;
}

try {
    $db = new database();
    $pdo = $db->opencon();
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
    if (!$hash || !password_verify($current, $hash)) {
        echo json_encode(['success' => false, 'message' => 'Old password is incorrect.']);
        exit;
    }
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Could not verify password.']);
}
