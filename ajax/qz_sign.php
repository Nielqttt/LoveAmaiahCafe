<?php
session_start();
header('Content-Type: text/plain');

// Restrict to staff
if (!isset($_SESSION['OwnerID']) && !isset($_SESSION['EmployeeID'])) {
    http_response_code(403);
    echo 'Forbidden';
    exit();
}

require_once(__DIR__ . '/../classes/qz_config.php');

// Read input (supports JSON {toSign:"..."} or form-encoded toSign=...)
$raw = file_get_contents('php://input') ?: '';
$toSign = null;
if ($raw) {
    $json = json_decode($raw, true);
    if (is_array($json) && isset($json['toSign'])) {
        $toSign = (string)$json['toSign'];
    }
}
if ($toSign === null && isset($_POST['toSign'])) {
    $toSign = (string)$_POST['toSign'];
}
if ($toSign === null) {
    http_response_code(400);
    echo 'Missing toSign';
    exit();
}

$keyPath = QZConfig::privateKeyPath();
if (!is_file($keyPath)) {
    http_response_code(500);
    echo 'Private key not found';
    exit();
}

$pass = QZConfig::privateKeyPassphrase();
$pkey = @openssl_pkey_get_private(file_get_contents($keyPath), $pass ?: '');
if (!$pkey) {
    http_response_code(500);
    echo 'Invalid private key';
    exit();
}

$signature = '';
$ok = @openssl_sign($toSign, $signature, $pkey, OPENSSL_ALGO_SHA256);
@openssl_pkey_free($pkey);
if (!$ok) {
    http_response_code(500);
    echo 'Sign failed';
    exit();
}

// Return Base64 signature string
echo base64_encode($signature);
?>