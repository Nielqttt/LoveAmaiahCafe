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

$allOrders = $con->getOrdersForOwnerOrEmployee($loggedInID, $loggedInUserType);

$customerAccountOrders = [];
$walkinStaffOrders = [];

foreach ($allOrders as $transaction) {
    if ($transaction['UserTypeID'] == 3 && !empty($transaction['CustomerUsername'])) {
        $customerAccountOrders[] = $transaction;
    } else {
        $walkinStaffOrders[] = $transaction;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Transaction Records</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
        font-family: 'Inter', sans-serif;
        background: url('../images/LAbg.png') no-repeat center center/cover;
    }
    .main-content {
        flex-grow: 1; padding: 1rem 1rem 0 1rem; position: relative; display: flex; flex-direction: column;
        align-items: stretch; justify-content: flex-start; width: 100%;
    }
    .main-content .bg-image {
        position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.2; z-index: -10;
    }
    .flex-wrapper {
        flex-grow: 1; display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem; padding: 1rem 1rem 0 1rem;
    }
    .order-section {
        position: relative; display: flex; flex-direction: column; background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px); border-radius: 1rem; padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); height: calc(100vh - 100px); overflow: hidden;
    }
    .order-list-wrapper { overflow-y: auto; flex-grow: 1; margin-bottom: 3.5rem; }
    .pagination-bar { position: absolute; bottom: 1rem; left: 0; right: 0; }
  </style>
</head>
<body class="min-h-screen flex">
<?php if ($loggedInUserType == 'owner'): ?>
<aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
    <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full mb-4" />
    <?php $current = basename($_SERVER['PHP_SELF']); ?>   
    <button title="Dashboard" onclick="window.location.href='../Owner/dashboard.php'"><i class="fas fa-chart-line text-xl <?= $current == 'dashboard.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Home" onclick="window.location.href='../Owner/mainpage.php'"><i class="fas fa-home text-xl <?= $current == 'mainpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Orders" onclick="window.location.href='../Owner/page.php'"><i class="fas fa-shopping-cart text-xl <?= $current == 'page.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Order List" onclick="window.location.href='../all/tranlist.php'"><i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Inventory" onclick="window.location.href='../Owner/product.php'"><i class="fas fa-box text-xl <?= $current == 'product.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Users" onclick="window.location.href='../Owner/user.php'"><i class="fas fa-users text-xl <?= $current == 'user.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Settings" onclick="window.location.href='../all/setting.php'"><i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button id="logout-btn" title="Logout"><i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i></button>
</aside>
<?php elseif ($loggedInUserType == 'employee'): ?>
<aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
  <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full mb-4" />
  <?php $current = basename($_SERVER['PHP_SELF']); ?>   
  <button title="Home" onclick="window.location.href='../Employee/employesmain.php'"><i class="fas fa-home text-xl <?= $current == 'employesmain.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
  <button title="Cart" onclick="window.location.href='../Employee/employeepage.php'"><i class="fas fa-shopping-cart text-xl <?= $current == 'employeepage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
  <button title="Transaction Records" onclick="window.location.href='../all/tranlist.php'"><i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
  <button title="Box" onclick="window.location.href='../Employee/productemployee.php'"><i class="fas fa-box text-xl <?= $current == 'productemployee.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
  <button title="Settings" onclick="window.location.href='../all/setting.php'"><i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
  <button id="logout-btn" title="Logout"><i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i></button>
</aside>
<?php endif; ?>

<div class="main-content">
  <img src="../images/Labg.png" alt="Background image" class="bg-image" />
  <div class="flex-wrapper relative z-10">
    <div class="order-section">
      <h1 class="text-xl font-bold text-[#4B2E0E] mb-4 flex items-center gap-2"><i class="fas fa-user-check"></i> Customer Account Orders</h1>
      <div id="customer-orders" class="order-list-wrapper">
        <?php foreach ($customerAccountOrders as $transaction): ?>
          <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 shadow-sm mb-4">
            <p class="text-sm font-semibold text-[#4B2E0E] mb-1">Order #<?= htmlspecialchars($transaction['OrderID']) ?></p>
            <p class="text-xs text-gray-600 mb-2">Customer: <?= htmlspecialchars($transaction['CustomerUsername']) ?><br>Date: <?= htmlspecialchars(date('M d, Y H:i', strtotime($transaction['OrderDate']))) ?></p>
            <ul class="text-sm text-gray-700 list-disc list-inside mb-2"><li><?= nl2br(htmlspecialchars($transaction['OrderItems'])) ?></li></ul>
            <div class="flex justify-between items-center mt-2">
              <span class="font-bold text-lg text-[#4B2E0E]">₱<?= number_format($transaction['TotalAmount'], 2) ?></span>
              <div class="flex gap-2">
                <button class="bg-[#4B2E0E] hover:bg-[#3a240c] text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150" data-id="<?= $transaction['OrderID'] ?>" data-status="Preparing Order"><i class="fas fa-utensils mr-1"></i> Prepare</button>
                <button class="bg-green-700 hover:bg-green-800 text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150" data-id="<?= $transaction['OrderID'] ?>" data-status="Order Ready"><i class="fas fa-check-circle mr-1"></i> Ready</button>
              </div>
            </div>
            <div class="text-right text-xs text-gray-600 mt-1">Ref: <?= htmlspecialchars($transaction['ReferenceNo'] ?? 'N/A') ?></div>
            <div class="text-sm mt-2 text-gray-800 font-medium" id="status-<?= $transaction['OrderID'] ?>"><span class="text-blue-700">Pending</span></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="customer-pagination" class="pagination-bar d-flex justify-content-center flex-wrap gap-2"></div>
    </div>
    <div class="order-section">
      <h1 class="text-xl font-bold text-[#4B2E0E] mb-4 flex items-center gap-2"><i class="fas fa-walking"></i> Walk-in / Staff-Assisted Orders</h1>
      <div id="walkin-orders" class="order-list-wrapper">
        <?php foreach ($walkinStaffOrders as $transaction): ?>
          <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 shadow-sm mb-4">
            <p class="text-sm font-semibold text-[#4B2E0E] mb-1">Order #<?= htmlspecialchars($transaction['OrderID']) ?></p>
            <p class="text-xs text-gray-600 mb-2">Date: <?= htmlspecialchars(date('M d, Y H:i', strtotime($transaction['OrderDate']))) ?></p>
            <ul class="text-sm text-gray-700 list-disc list-inside mb-2"><li><?= nl2br(htmlspecialchars($transaction['OrderItems'])) ?></li></ul>
            <div class="flex justify-between items-center mt-2">
              <span class="font-bold text-lg text-[#4B2E0E]">₱<?= number_format($transaction['TotalAmount'], 2) ?></span>
              <div class="flex gap-2">
                <button class="bg-[#4B2E0E] hover:bg-[#3a240c] text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150" data-id="<?= $transaction['OrderID']; ?>" data-status="Preparing Order"><i class="fas fa-utensils mr-1"></i> Prepare</button>
                <button class="bg-green-700 hover:bg-green-800 text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150" data-id="<?= $transaction['OrderID']; ?>" data-status="Order Ready"><i class="fas fa-check-circle mr-1"></i> Ready</button>
              </div>
            </div>
            <div class="text-right text-xs text-gray-600 mt-1">Ref: <?= htmlspecialchars($transaction['ReferenceNo'] ?? 'N/A') ?></div>
            <div class="text-sm mt-2 text-gray-800 font-medium" id="status-<?= $transaction['OrderID'] ?>"><span class="text-blue-700">Pending</span></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="walkin-pagination" class="pagination-bar d-flex justify-content-center flex-wrap gap-2"></div>
    </div>
  </div>
</div>

<script>
function paginate(containerId, paginationId, itemsPerPage = 10) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const pagination = document.getElementById(paginationId);
    const items = Array.from(container.children);
    const totalPages = Math.ceil(items.length / itemsPerPage);
    let currentPage = 1;

    function showPage(page) {
        items.forEach((item, i) => {
            item.style.display = (i >= (page - 1) * itemsPerPage && i < page * itemsPerPage) ? '' : 'none';
        });
        if (totalPages > 1) renderPagination();
    }

    function renderPagination() {
        pagination.innerHTML = '';
        const prev = document.createElement('button');
        prev.textContent = 'Prev';
        prev.className = 'btn btn-outline-secondary btn-sm';
        prev.disabled = currentPage === 1;
        prev.onclick = () => { if (currentPage > 1) { currentPage--; showPage(currentPage); } };
        pagination.appendChild(prev);

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = `btn btn-sm mx-1 ${i === currentPage ? 'btn-dark' : 'btn-outline-secondary'}`;
            btn.onclick = () => { currentPage = i; showPage(currentPage); };
            pagination.appendChild(btn);
        }

        const next = document.createElement('button');
        next.textContent = 'Next';
        next.className = 'btn btn-outline-secondary btn-sm';
        next.disabled = currentPage === totalPages;
        next.onclick = () => { if (currentPage < totalPages) { currentPage++; showPage(currentPage); } };
        pagination.appendChild(next);
    }
    showPage(currentPage);
}

document.addEventListener('DOMContentLoaded', () => {
    paginate('customer-orders', 'customer-pagination', 5);
    paginate('walkin-orders', 'walkin-pagination', 5);

    document.getElementById("logout-btn").addEventListener("click", () => {
        Swal.fire({
            title: 'Are you sure?', icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#4B2E0E', cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, log out'
        }).then((result) => { if (result.isConfirmed) { window.location.href = "logout.php"; }});
    });

    document.querySelectorAll('button[data-status]').forEach(button => {
        button.addEventListener('click', () => {
            const orderId = button.getAttribute('data-id');
            const status = button.getAttribute('data-status');
            const statusElement = document.getElementById(`status-${orderId}`);
            
            if (statusElement) {
                const newStatusHTML = status === "Preparing Order"
                    ? `<span class="text-orange-500 font-semibold">${status}</span>`
                    : `<span class="text-green-700 font-semibold">${status}</span>`;
                statusElement.innerHTML = newStatusHTML;
                sessionStorage.setItem(`orderStatus-${orderId}`, newStatusHTML);
            }

            const parentDiv = button.closest('.flex.gap-2');
            if (status === "Preparing Order") {
                button.disabled = true;
                button.classList.add('opacity-50', 'cursor-not-allowed');
            } else if (status === "Order Ready") {
                if(parentDiv) {
                    parentDiv.querySelectorAll('button[data-status]').forEach(btn => {
                        btn.disabled = true;
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                    });
                }
            }
        });
    });
    
    Object.keys(sessionStorage).forEach(key => {
        if (key.startsWith("orderStatus-")) {
            const orderId = key.replace("orderStatus-", "");
            const statusElement = document.getElementById(`status-${orderId}`);
            if (statusElement) {
                // normalize any previously-saved values that included 'Status:'
                const saved = sessionStorage.getItem(key) || '';
                const normalized = saved.replace(/\bStatus:\s*/i, '');
                statusElement.innerHTML = normalized;
                const card = statusElement.closest('.border');
                if (card) {
                    const statusText = statusElement.textContent;
                    const readyButton = card.querySelector('button[data-status="Order Ready"]');
                    const prepareButton = card.querySelector('button[data-status="Preparing Order"]');

                    if (statusText.includes("Order Ready")) {
                        if(readyButton) {
                            readyButton.disabled = true;
                            readyButton.classList.add('opacity-50', 'cursor-not-allowed');
                        }
                        if(prepareButton) {
                            prepareButton.disabled = true;
                            prepareButton.classList.add('opacity-50', 'cursor-not-allowed');
                        }
                    } else if (statusText.includes("Preparing Order")) {
                         if(prepareButton) {
                            prepareButton.disabled = true;
                            prepareButton.classList.add('opacity-50', 'cursor-not-allowed');
                        }
                    }
                }
            }
        }
    });
});
</script>
</body>
</html>