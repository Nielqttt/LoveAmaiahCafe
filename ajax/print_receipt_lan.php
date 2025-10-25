<?php
session_start();
header('Content-Type: application/json');

// Only allow owner or employee to trigger server-side printing
if (!isset($_SESSION['OwnerID']) && !isset($_SESSION['EmployeeID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once(__DIR__ . '/../classes/database.php');
require_once(__DIR__ . '/../classes/escpos_lan_printer.php');

$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : (isset($_GET['order_id']) ? intval($_GET['order_id']) : 0);
if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing order_id']);
    exit();
}

try {
    $db = new database();
    $data = $db->getOrderReceiptData($orderId);
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }
    // Normalize for builder
    $payload = [
        'OrderID' => (int)$data['OrderID'],
        'OrderDate' => date('M d, Y H:i', strtotime($data['OrderDate'])),
        'TotalAmount' => (float)$data['TotalAmount'],
        'PaymentMethod' => $data['PaymentMethod'] ?? '',
        'ReferenceNo' => $data['ReferenceNo'] ?? '',
        'Items' => array_map(function($it){
            return [
                'name' => (string)$it['name'],
                'qty' => (int)$it['qty'],
                'unit' => (float)$it['unit'],
                'subtotal' => (float)$it['subtotal'],
            ];
        }, $data['Items'] ?? []),
        'brand' => 'Love Amaiah Cafe',
        'subtitle' => 'Official Receipt',
        'footer' => 'welcomehome@loveamaiahcafe.shop',
    ];

    $text = la_build_text_receipt($payload);

    $p = new EscposLanPrinter();
    $p->connect(3);
    // Basic print sequence
    $p->align('left');
    $p->text($text);
    $p->lf(3);
    // Optional: open cash drawer (safe to ignore if not connected)
    try { $p->drawerKick(); } catch (Exception $e) { /* ignore */ }
    $p->cut(true);
    $p->close();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('LAN print error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Print failed']);
}

?>
