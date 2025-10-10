<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../classes/database.php';

// Only Owner can query customer histories from this endpoint
if (!isset($_SESSION['OwnerID'])) {
  echo json_encode(['success'=>false,'message'=>'Unauthorized']);
  exit;
}

$cid = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
if ($cid <= 0) {
  echo json_encode(['success'=>false,'message'=>'Invalid customer ID']);
  exit;
}

try {
  $con = new database();
  $orders = $con->getOrdersForCustomer($cid);
  echo json_encode(['success'=>true,'orders'=>$orders]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Failed to load orders']);
}