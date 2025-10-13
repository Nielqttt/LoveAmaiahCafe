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

    // Header rows
    fputcsv($out, ["Monthly Sales Report"]);
    fputcsv($out, ["Month", $month]);
    fputcsv($out, ["Category", $category]);
    fputcsv($out, []);

    // Summary KPIs
    $sqlSummary = "
        SELECT 
            COALESCE(SUM(od.Subtotal),0) AS revenue,
            COUNT(DISTINCT o.OrderID) AS orders,
            COALESCE(SUM(od.Quantity),0) AS items,
            COUNT(DISTINCT CASE WHEN os.CustomerID IS NOT NULL THEN os.CustomerID END) AS customers
        FROM orders o
        JOIN ordersection os ON o.OrderSID = os.OrderSID
        JOIN orderdetails od ON od.OrderID = o.OrderID
        JOIN product p ON p.ProductID = od.ProductID
        WHERE o.Status = 'Complete' 
            AND o.OrderDate BETWEEN :start AND :end
            $catFilter
    ";
    $stmt = $db->prepare($sqlSummary);
    $stmt->execute($params);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['revenue'=>0,'orders'=>0,'items'=>0,'customers'=>0];

    fputcsv($out, ["Summary"]);
    fputcsv($out, ["Total Revenue","Total Orders","Items Sold","Distinct Customers"]);
    fputcsv($out, [
        number_format((float)$summary['revenue'], 2, '.', ','),
        (int)$summary['orders'],
        (int)$summary['items'],
        (int)$summary['customers']
    ]);
    fputcsv($out, []);

    // Daily breakdown
    $sqlDaily = "
        SELECT 
            DATE(o.OrderDate) AS d,
            COUNT(DISTINCT o.OrderID) AS orders,
            COALESCE(SUM(od.Quantity),0) AS items,
            COALESCE(SUM(od.Subtotal),0) AS revenue
        FROM orders o
        JOIN ordersection os ON o.OrderSID = os.OrderSID
        JOIN orderdetails od ON od.OrderID = o.OrderID
        JOIN product p ON p.ProductID = od.ProductID
        WHERE o.Status = 'Complete'
            AND o.OrderDate BETWEEN :start AND :end
            $catFilter
        GROUP BY DATE(o.OrderDate)
        ORDER BY d ASC
    ";
    $stmt = $db->prepare($sqlDaily);
    $stmt->execute($params);
    $daily = $stmt->fetchAll(PDO::FETCH_ASSOC);

    fputcsv($out, ["Daily Breakdown"]);
    fputcsv($out, ["Date","No. of Orders","No. of Items Sold","Revenue (PHP)"]);
    foreach ($daily as $r) {
        fputcsv($out, [$r['d'], (int)$r['orders'], (int)$r['items'], number_format((float)$r['revenue'],2,'.',',')]);
    }
    fputcsv($out, []);

    // Products sold (summary)
    $sqlProducts = "
        SELECT p.ProductName AS product, COALESCE(SUM(od.Quantity),0) AS qty
        FROM orders o
        JOIN orderdetails od ON od.OrderID = o.OrderID
        JOIN product p ON p.ProductID = od.ProductID
        WHERE o.Status = 'Complete'
            AND o.OrderDate BETWEEN :start AND :end
            $catFilter
        GROUP BY p.ProductID, p.ProductName
        ORDER BY qty DESC, p.ProductName ASC
    ";
    $stmt = $db->prepare($sqlProducts);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    fputcsv($out, ["Products Sold (This Month)"]);
    fputcsv($out, ["Product","Qty"]);
    foreach ($products as $p) {
        fputcsv($out, [$p['product'], (int)$p['qty']]);
    }
    fputcsv($out, []);

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

    fputcsv($out, ["Details"]);
    fputcsv($out, ["Reference","Created At","Customer","Pickup At","Status","Payment Method","Product","Unit Price","Qty","Line Total","Order Total"]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Normalize payment method: map Walk-in to Cash as seen in receipts
        $paymentMethod = $row['PaymentMethod'] ?? '';
        if (strtolower($paymentMethod) === 'walk-in' || strtoupper($paymentMethod) === 'WALKIN') {
            $paymentMethod = 'Cash';
        }
        fputcsv($out, [
            $row['Reference'] ?? '',
            $row['OrderDate'] ?? '',
            $row['CustomerName'] ?? '',
            $row['PickupAt'] ?? '',
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
