<?php
// Returns latest transactions as JSON for real-time updates on tranlist
// Query params:
//   since_id (int, optional): only return orders with OrderID greater than this
//   limit    (int, optional): max number of orders to return (default 20, max 50)

header('Content-Type: application/json');
// Prevent caching so polling always gets fresh data
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
session_start();

// Ensure the user is logged in as owner or employee (same check style as tranlist.php)
$userType = null; $userId = null;
if (isset($_SESSION['OwnerID'])) { $userType = 'owner'; $userId = $_SESSION['OwnerID']; }
elseif (isset($_SESSION['EmployeeID'])) { $userType = 'employee'; $userId = $_SESSION['EmployeeID']; }

if (!$userType || !$userId) {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Unauthorized']);
	exit;
}

require_once(__DIR__ . '/../classes/database.php');

try {
	$db = new database();
	$sinceId = isset($_GET['since_id']) ? max(0, (int)$_GET['since_id']) : 0;
	$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
	// If active=1 is set, return a snapshot of active (non-archived) orders instead of incremental since_id
	$active = isset($_GET['active']) ? (int)$_GET['active'] : 0;
	if ($active === 1) {
		$rows = $db->getActiveOpenTransactions(200);
		// Ensure latestId still advances so caller can keep incremental polling too
		foreach ($rows as $r) {
			$oidTmp = (int)$r['OrderID'];
			if ($oidTmp > $sinceId) { $sinceId = $oidTmp; }
		}
	} else {
		$rows = $db->getLatestTransactions($sinceId, $limit);
	}

	$data = [];
	$latestId = $sinceId;
	foreach ($rows as $r) {
		$cat = ((int)$r['UserTypeID'] === 3 && !empty($r['CustomerUsername'])) ? 'customer' : 'walkin';
		$oid = (int)$r['OrderID'];
		if ($oid > $latestId) { $latestId = $oid; }
		$data[] = [
			'OrderID' => $oid,
			'OrderDate' => $r['OrderDate'],
			'OrderDateISO' => date('c', strtotime($r['OrderDate'])),
			'TotalAmount' => (float)$r['TotalAmount'],
			'Status' => $r['Status'] ?? 'Pending',
			'UserTypeID' => (int)$r['UserTypeID'],
			'CustomerUsername' => $r['CustomerUsername'],
			'EmployeeFirstName' => $r['EmployeeFirstName'],
			'EmployeeLastName' => $r['EmployeeLastName'],
			'OwnerFirstName' => $r['OwnerFirstName'],
			'OwnerLastName' => $r['OwnerLastName'],
			'PaymentMethod' => $r['PaymentMethod'],
			'ReferenceNo' => $r['ReferenceNo'],
			'ReceiptPath' => $r['ReceiptPath'] ?? null,
			'OrderItems' => $r['OrderItems'],
			'PickupAt' => isset($r['PickupAt']) ? $r['PickupAt'] : null,
			'PickupAtISO' => !empty($r['PickupAt']) ? date('c', strtotime($r['PickupAt'])) : null,
			'category' => $cat
		];
	}

	echo json_encode([
		'success' => true,
		'count' => count($data),
		'latest_id' => $latestId,
		'data' => $data
	]);
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}

?>
