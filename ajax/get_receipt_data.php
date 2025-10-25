<?php
session_start();
header('Content-Type: application/json');

// Only allow owner or employee
if (!isset($_SESSION['OwnerID']) && !isset($_SESSION['EmployeeID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once(__DIR__ . '/../classes/database.php');
$db = new database();

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing order_id']);
    exit();
}

try {
    $data = $db->getOrderReceiptData($orderId);
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }
    // Normalize fields for client
    $payload = [
        'OrderID' => (int)$data['OrderID'],
        'OrderDate' => date('M d, Y H:i', strtotime($data['OrderDate'])),
        'TotalAmount' => (float)$data['TotalAmount'],
        'PaymentMethod' => $data['PaymentMethod'] ?? '',
        'ReferenceNo' => $data['ReferenceNo'] ?? '',
        'PickupAt' => !empty($data['PickupAt']) ? date('M d, Y H:i', strtotime($data['PickupAt'])) : '',
        'CustomerName' => trim(($data['CustomerFN'] ?? '') . ' ' . ($data['CustomerLN'] ?? '')),
        'Items' => array_map(function($it){
            return [
                'name' => $it['name'],
                'qty' => (int)$it['qty'],
                'unit' => (float)$it['unit'],
                'subtotal' => (float)$it['subtotal'],
            ];
        }, $data['Items'] ?? []),
        // Optional branding/footer (can be customized later)
        'brand' => 'Love Amaiah Cafe',
        'subtitle' => 'Official Receipt',
        'footer' => 'welcomehome@loveamaiahcafe.shop',
    ];
    echo json_encode(['success' => true, 'data' => $payload]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
