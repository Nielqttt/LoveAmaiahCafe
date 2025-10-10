<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../classes/database.php';

// Only Owner can query customer histories from this endpoint
if (!isset($_SESSION['OwnerID'])) {
  http_response_code(403);
  echo json_encode(['success'=>false,'message'=>'Unauthorized']);
  exit;
}

// Accept both GET and POST for flexibility
$cid = 0;
if (isset($_GET['customer_id'])) {
  $cid = (int)$_GET['customer_id'];
} elseif (isset($_POST['customer_id'])) {
  $cid = (int)$_POST['customer_id'];
}

if ($cid <= 0) {
  http_response_code(400);
  echo json_encode(['success'=>false,'message'=>'Invalid customer ID']);
  exit;
}

try {
  $con = new database();
  // Ensure order status columns exist before querying (public wrapper)
  if (method_exists($con, 'ensureOrderStatus')) {
    $con->ensureOrderStatus();
  }
  $orders = $con->getOrdersForCustomer($cid);
  echo json_encode(['success'=>true,'orders'=>$orders]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Failed to load orders']);
}
