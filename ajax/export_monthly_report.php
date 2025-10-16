<?php
session_start();
require_once(__DIR__ . '/../classes/database.php');
$con = new database();

// Only allow Owners (dashboard is owner-only)
if (!isset($_SESSION['OwnerID'])) {
    http_response_code(403);
    echo "Access denied";
    exit;
}

// Validate inputs
$month = isset($_GET['month']) ? trim($_GET['month']) : date('Y-m');
$category = isset($_GET['category']) ? trim($_GET['category']) : 'All';
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    // fallback to current month
    $month = date('Y-m');
}

// Compute start and end
$start = new DateTime($month . '-01 00:00:00');
$end = (clone $start)->modify('last day of this month')->setTime(23,59,59);
$startStr = $start->format('Y-m-d H:i:s');
$endStr = $end->format('Y-m-d H:i:s');

$db = $con->opencon();
$con->ensureOrderStatus();

$params = [':start' => $startStr, ':end' => $endStr];
$catFilter = '';
if ($category !== '' && strtolower($category) !== 'all') {
    $catFilter = ' AND p.ProductCategory = :cat ';
    $params[':cat'] = $category;
}

try {
    // We'll output a CSV of per-order-per-item rows. Columns similar to dashboard sample:
    // Reference, Created At, Customer, Pickup Location, Status, Payment Method, Product, Unit Price, Qty, Line Total, Order Total

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="monthly_report_' . $month . '.csv"');

    $out = fopen('php://output', 'w');
    // BOM for Excel compatibility
    fwrite($out, "\xEF\xBB\xBF");

    // Output simple header for details-only CSV (one row per order item)
    fputcsv($out, ["MONTHLY SALES REPORT"]);

    // Detailed per-order-per-item rows
    // We join orders -> orderdetails -> product -> productprices -> payment -> ordersection -> customer/employee/owner
    $sqlDetails = "
        SELECT o.OrderID, pmt.ReferenceNo AS Reference, o.OrderDate, 
            COALESCE(c.C_Username, CONCAT(e.EmployeeFN, ' ', e.EmployeeLN), CONCAT(ow.OwnerFN, ' ', ow.OwnerLN), 'Guest') AS CustomerName,
            o.PickupAt, o.Status, pmt.PaymentMethod, prod.ProductName, pp.UnitPrice, od.Quantity, od.Subtotal, o.TotalAmount, os.CustomerID
        FROM orders o
        JOIN orderdetails od ON od.OrderID = o.OrderID
        JOIN product prod ON prod.ProductID = od.ProductID
        JOIN productprices pp ON pp.PriceID = od.PriceID
        LEFT JOIN ordersection os ON o.OrderSID = os.OrderSID
        LEFT JOIN customer c ON os.CustomerID = c.CustomerID
        LEFT JOIN employee e ON os.EmployeeID = e.EmployeeID
        LEFT JOIN owner ow ON os.OwnerID = ow.OwnerID
        LEFT JOIN payment pmt ON o.OrderID = pmt.OrderID
        WHERE o.Status = 'Complete' AND o.OrderDate BETWEEN :start AND :end
        $catFilter
        ORDER BY o.OrderDate ASC, o.OrderID ASC
    ";
    $stmt = $db->prepare($sqlDetails);
    $stmt->execute($params);

    
    fputcsv($out, ["Reference","Created At","Customer","Status","Payment Method","Product","Unit Price","Qty","Line Total","Order Total"]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Normalize payment method: map Walk-in to Cash as seen in receipts
        $paymentMethod = $row['PaymentMethod'] ?? '';
        if (strtolower($paymentMethod) === 'walk-in' || strtoupper($paymentMethod) === 'WALKIN') {
            $paymentMethod = 'Cash';
        }
            // Created At: only the day number (e.g., 27)
            $createdAt = $row['OrderDate'] ?? '';
            $createdDay = '';
            if (!empty($createdAt)) {
                $ts = strtotime($createdAt);
                if ($ts !== false) {
                    $createdDay = date('j', $ts); // day without leading zeros
                }
            }
            fputcsv($out, [
                $row['Reference'] ?? '',
                $createdDay,
                $row['CustomerName'] ?? '',
                $row['Status'] ?? '',
                $paymentMethod,
                $row['ProductName'] ?? $row['Product'] ?? '',
                number_format((float)($row['UnitPrice'] ?? 0), 2, '.', ','),
                (int)($row['Quantity'] ?? 0),
                number_format((float)($row['Subtotal'] ?? 0), 2, '.', ','),
                number_format((float)($row['TotalAmount'] ?? 0), 2, '.', ',')
            ]);
    }

    fclose($out);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo "Server error";
    exit;
}

?>
