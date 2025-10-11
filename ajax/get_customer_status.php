<?php
// Returns customer orders with status changes since a given timestamp or after a given order id
// Query params:
//   since_ts (ISO or 'YYYY-MM-DD HH:MM:SS') optional
//   since_id (int) optional
// Response: { success: true, data: [ {OrderID, Status, StatusUpdatedAt, ReferenceNo} ], server_time }

header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['CustomerID'])) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

require_once(__DIR__ . '/../classes/database.php');
$db = new database();
$customerID = $_SESSION['CustomerID'];
$sinceTs = isset($_GET['since_ts']) ? trim($_GET['since_ts']) : '';
$sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;

$con = $db->opencon();
$db->ensureOrderStatus(); // ensure columns exist

$params = [ $customerID ];
$wheres = ['os.CustomerID = ?'];
if ($sinceTs !== '') {
    // Basic validation
    if (strtotime($sinceTs) !== false) {
        $wheres[] = 'o.StatusUpdatedAt IS NOT NULL AND o.StatusUpdatedAt > ?';
        $params[] = $sinceTs;
    }
}
if ($sinceId > 0) {
    $wheres[] = 'o.OrderID > ?';
    $params[] = $sinceId;
}
$whereSql = implode(' AND ', $wheres);
$sql = "SELECT o.OrderID, o.Status, o.StatusUpdatedAt, o.RejectionReason, p.ReferenceNo FROM orders o JOIN ordersection os ON o.OrderSID = os.OrderSID LEFT JOIN payment p ON o.OrderID = p.OrderID WHERE $whereSql ORDER BY o.OrderID DESC LIMIT 100";

try {
    $stmt = $con->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'data'=>$rows,'server_time'=>date('Y-m-d H:i:s')]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error','error'=>$e->getMessage()]);
}
