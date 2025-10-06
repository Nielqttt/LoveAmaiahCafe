<?php
// Endpoint to update order status
// POST: order_id, status
// Response: {success: bool, message: string, order_id, status}
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['OwnerID']) && !isset($_SESSION['EmployeeID'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$orderID = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($orderID <= 0 || $status === '') {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Missing parameters']);
    exit;
}

// Map front-end display labels to canonical DB values
$map = [
    'Preparing Order' => 'Preparing',
    'Order Ready' => 'Ready',
    'Pending' => 'Pending'
];
if (isset($map[$status])) { $status = $map[$status]; }

require_once(__DIR__ . '/../classes/database.php');
$db = new database();
$result = $db->updateOrderStatus($orderID, $status);
if ($result['success']) {
    echo json_encode(['success'=>true,'message'=>'Status updated','order_id'=>$orderID,'status'=>$status]);
} else {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$result['message'],'order_id'=>$orderID]);
}
