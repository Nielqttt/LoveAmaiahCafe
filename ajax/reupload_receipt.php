<?php
// Allow customers to re-upload a payment receipt for an order that was rejected due to incomplete/invalid proof.
// POST multipart/form-data: order_id, file 'receipt'
// Response: { success: bool, message: string, order_id, path }

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['CustomerID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$customerID = (int)$_SESSION['CustomerID'];
$orderID = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

if ($orderID <= 0 || !isset($_FILES['receipt'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$file = $_FILES['receipt'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Upload failed']);
    exit;
}

// Basic validation: only allow common image types
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/jpg' => 'jpg', 'image/gif' => 'gif'];
$finfo = @finfo_open(FILEINFO_MIME_TYPE);
$mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
if ($finfo) { finfo_close($finfo); }
if (!$mime || !isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

// Limit size ~5MB
if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
    exit;
}

// Ensure destination directory exists
$baseDir = dirname(__DIR__);
$destDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'receipts';
if (!is_dir($destDir)) {
    @mkdir($destDir, 0775, true);
}

// Generate unique filename
$ext = $allowed[$mime];
$fname = 'receipt_' . $orderID . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$destPath = $destDir . DIRECTORY_SEPARATOR . $fname;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save file']);
    exit;
}

// Store relative path from web root (../ uploads/...)
$relativePath = 'uploads/receipts/' . $fname;

require_once(__DIR__ . '/../classes/database.php');
$db = new database();

$ok = $db->customerReuploadReceipt($customerID, $orderID, $relativePath);
if ($ok['success']) {
    echo json_encode(['success' => true, 'message' => 'Receipt uploaded', 'order_id' => $orderID, 'path' => $relativePath]);
} else {
    // On failure, try to delete file to avoid orphan
    @unlink($destPath);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $ok['message'] ?? 'Update failed']);
}

?>
