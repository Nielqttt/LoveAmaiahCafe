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
    <link rel="icon" href="../images/logo.png" type="image/png"/>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>Order Receipt</title>
  <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <style>
    body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .receipt-card { box-shadow: none !important; border: 1px solid #e5e7eb; }
        }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center relative">
    <img src="../images/LAbg.png" alt="Background" class="absolute inset-0 w-full h-full object-cover opacity-30 -z-10" />

    <div class="receipt-card bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-6 sm:p-8 w-full max-w-2xl">
        <!-- Brand header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full" />
                <div>
                    <h2 class="text-2xl font-extrabold text-[#4B2E0E] leading-tight">Order Receipt</h2>
                    <p class="text-xs text-gray-500">Thank you for your purchase</p>
                </div>
            </div>
            <?php 
                $pm = trim((string)($order['PaymentMethod'] ?? ''));
                $pmIcon = 'fa-peso-sign'; $pmBG = 'bg-amber-100 text-amber-700';
                if (stripos($pm,'gcash') !== false) { $pmIcon = 'fa-wallet'; $pmBG = 'bg-blue-100 text-blue-600'; }
            ?>
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold <?= $pmBG ?>">
                <i class="fa-solid <?= $pmIcon ?>"></i>
                <?= htmlspecialchars($pm) ?>
            </span>
        </div>

        <!-- Meta -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5 border rounded-xl p-4 border-gray-200">
            <p class="text-sm text-gray-600">Order ID: <span class="font-semibold text-[#4B2E0E]"><?= htmlspecialchars($order['OrderID']) ?></span></p>
            <p class="text-sm text-gray-600">Reference No: 
                <span id="ref-text" class="font-semibold text-[#4B2E0E]"><?= htmlspecialchars($order['ReferenceNo']) ?></span>
                <button class="no-print ml-2 text-xs text-blue-600 hover:underline" onclick="copyRef()">Copy</button>
            </p>
            <p class="text-sm text-gray-600">Date: <span class="font-semibold text-[#4B2E0E]"><?= date('M d, Y \a\t h:i A', strtotime($order['OrderDate'])) ?></span></p>
            <p class="text-sm text-gray-600">Served By: <span class="font-semibold text-[#4B2E0E]">LoveAmaiah</span></p>
        </div>

        <!-- Items -->
        <div class="mb-4">
            <h3 class="font-semibold text-[#4B2E0E] mb-3">Items</h3>
            <div class="overflow-hidden rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr class="text-left">
                            <th class="py-2 px-4">Item</th>
                            <th class="py-2 px-4">Qty</th>
                            <th class="py-2 px-4">Unit</th>
                            <th class="py-2 px-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <?php 
                        $items = $order['Details'] ?? [];
                        if (!empty($items)):
                            foreach ($items as $it):
                                $qty = (int)($it['Quantity'] ?? 0);
                                $sub = (float)($it['Subtotal'] ?? 0);
                                $unit = $qty > 0 ? ($sub / max($qty,1)) : $sub;
                    ?>
                        <tr>
                            <td class="py-2 px-4 text-gray-800"><?= htmlspecialchars($it['ProductName']) ?></td>
                            <td class="py-2 px-4 text-gray-600"><?= htmlspecialchars($qty) ?></td>
                            <td class="py-2 px-4 text-gray-600">₱<?= number_format($unit, 2) ?></td>
                            <td class="py-2 px-4 text-right font-semibold">₱<?= number_format($sub, 2) ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4" class="py-3 px-4 text-gray-500">No items found for this order.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals -->
        <?php 
            $computedSubtotal = 0.0; 
            if (!empty($items)) { foreach ($items as $it) { $computedSubtotal += (float)($it['Subtotal'] ?? 0); } }
            $grandTotal = (float)($order['TotalAmount'] ?? $computedSubtotal);
        ?>
        <div class="mt-4 pt-4 border-t border-dashed border-gray-300">
            <div class="flex justify-between items-center text-sm text-gray-700">
                <span>Subtotal</span>
                <span>₱<?= number_format($computedSubtotal, 2) ?></span>
            </div>
            <div class="flex justify-between items-center text-lg font-extrabold text-[#4B2E0E] mt-2">
                <span>Total</span>
                <span>₱<?= number_format($grandTotal, 2) ?></span>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex flex-wrap gap-2 justify-between no-print">
            <div class="flex gap-2">
                <button onclick="window.print()" class="bg-[#4B2E0E] text-white rounded-lg px-4 py-2 font-semibold hover:bg-[#6b3e14] transition">
                    <i class="fa-solid fa-print mr-2"></i> Print
                </button>
                <button onclick="copyRef()" class="bg-white border border-gray-300 text-[#4B2E0E] rounded-lg px-4 py-2 font-semibold hover:bg-[#f7f5f3] transition">
                    <i class="fa-solid fa-copy mr-2"></i> Copy Ref No
                </button>
            </div>
            <div>
                <?php if ($loggedInUserType == 'owner'): ?>
                    <button onclick="window.location.href='../Owner/mainpage.php'" class="bg-emerald-600 text-white rounded-lg px-4 py-2 font-semibold hover:bg-emerald-700 transition">Back to Owner Menu</button>
                <?php elseif ($loggedInUserType == 'employee'): ?>
                    <button onclick="window.location.href='../Employee/employesmain.php'" class="bg-emerald-600 text-white rounded-lg px-4 py-2 font-semibold hover:bg-emerald-700 transition">Back to Employee Menu</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function copyRef(){
            const t = document.getElementById('ref-text')?.textContent || '';
            if(!t) return;
            navigator.clipboard?.writeText(t).then(()=>{
                // Optional toast
            }).catch(()=>{
                const ta = document.createElement('textarea');
                ta.value = t; document.body.appendChild(ta); ta.select(); try{ document.execCommand('copy'); }catch(e){} document.body.removeChild(ta);
            });
        }
    </script>
</body>
</html>