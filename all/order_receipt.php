<?php
session_start();

$loggedInUserType = null;
$loggedInID = null;


if (isset($_SESSION['OwnerID'])) {
    $loggedInUserType = 'owner';
    $loggedInID = $_SESSION['OwnerID'];
} elseif (isset($_SESSION['EmployeeID'])) {
    $loggedInUserType = 'employee';
    $loggedInID = $_SESSION['EmployeeID'];
} else {
   
    header('Location: login.php'); 
    exit();
}

require_once('../classes/database.php'); 
$con = new database();

$orderID = $_GET['order_id'] ?? null;
$referenceNo = $_GET['ref_no'] ?? null;

$order = false;
if ($orderID && $referenceNo) {
    $order = $con->getFullOrderDetails($orderID, $referenceNo);
}

// Access control
$hasPermission = false;
if ($order) {
    if ($loggedInUserType == 'owner' && $order['OwnerID'] == $loggedInID) {
        $hasPermission = true;
    } elseif ($loggedInUserType == 'employee') {
        
        $employeeOwnerID = $con->getEmployeeOwnerID($loggedInID);
        if ($employeeOwnerID !== null) {
            if (
                ($order['EmployeeID'] !== null && $order['EmployeeID'] == $loggedInID) || 
                ($order['OwnerID'] !== null && $order['OwnerID'] == $employeeOwnerID && $order['UserTypeID'] == 3) || 
                ($order['OwnerID'] !== null && $order['OwnerID'] == $employeeOwnerID && $order['UserTypeID'] == 1) 
            ) {
                $hasPermission = true;
            }
        }
    }
}

if (!$order || !$hasPermission) {
   
    if ($loggedInUserType == 'owner') {
        header('Location: ../Owner/page.php?error=unauthorized_or_order_not_found'); 
    } elseif ($loggedInUserType == 'employee') {
        header('Location: ../Employee/employeepage.php?error=unauthorized_or_order_not_found'); 
    } else {
        header('Location: login.php'); 
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <body style="background: url('../images/LAbg.png') no-repeat center center/cover;" class="min-h-screen flex flex-col items-center justify-center"></body>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>Order Receipt</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-[rgba(255,255,255,0.7)] min-h-screen flex items-center justify-center">

<div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-xl shadow-lg p-8 w-full max-w-md">
    <h2 class="text-3xl font-extrabold text-[#4B2E0E] text-center mb-6">Order Confirmation</h2>

    <div class="mb-6 border-b pb-4">
        <p class="text-sm text-gray-600 mb-1">Order ID: <span class="font-semibold text-[#4B2E0E]"><?= htmlspecialchars($order['OrderID']) ?></span></p>
        <p class="text-sm text-gray-600 mb-1">Reference No: <span class="font-semibold text-[#4B2E0E]"><?= htmlspecialchars($order['ReferenceNo']) ?></span></p>
        <p class="text-sm text-gray-600 mb-1">Date: <span class="font-semibold text-[#4B2E0E]"><?= date('M d, Y H:i', strtotime($order['OrderDate'])) ?></span></p>
        <p class="text-sm text-gray-600">Payment Method: <span class="font-semibold text-[#4B2E0E]"><?= htmlspecialchars($order['PaymentMethod']) ?></span></p>
    </div>

    <div class="mb-6">
        <h3 class="font-semibold text-[#4B2E0E] mb-3">Items Ordered:</h3>
        <ul class="space-y-2">
            <?php if (!empty($order['Details'])): ?>
                <?php foreach ($order['Details'] as $item): ?>
                    <li class="flex justify-between items-center text-sm text-gray-700">
                        <span><?= htmlspecialchars($item['ProductName']) ?> x <?= htmlspecialchars($item['Quantity']) ?></span>
                        <span class="font-semibold">₱<?= htmlspecialchars(number_format($item['Subtotal'], 2)) ?></span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-sm text-gray-500">No items found for this order.</p>
            <?php endif; ?>
        </ul>
    </div>

    <div class="mt-6 pt-4 border-t border-dashed border-gray-300">
        <div class="flex justify-between items-center text-lg font-bold text-[#4B2E0E]">
            <span>Total:</span>
            <span>₱<?= htmlspecialchars(number_format($order['TotalAmount'], 2)) ?></span>
        </div>
    </div>

    <div class="mt-8 text-center">
        <?php if ($loggedInUserType == 'owner'): ?>
            <button onclick="window.location.href='../Owner/mainpage.php'" class="bg-[#4B2E0E] text-white rounded-full py-2 px-6 font-semibold hover:bg-[#6b3e14] transition">
                Back to Owner Menu
            </button>
        <?php elseif ($loggedInUserType == 'employee'): ?>
            <button onclick="window.location.href='../Employee/employesmain.php'" class="bg-[#4B2E0E] text-white rounded-full py-2 px-6 font-semibold hover:bg-[#6b3e14] transition">
                Back to Employee Menu
            </button>
        <?php endif; ?>
    </div>
</div>

</body>
</html>