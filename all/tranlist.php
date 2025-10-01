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
    header('Location: login');
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
        /* New order visual highlight */
        .new-flash { animation: flashBg 1.5s ease-out 1; }
        @keyframes flashBg { 0% { background-color: #fff9c4; } 100% { background-color: #f9fafb; } }
        /* Red dot badge for Orders icon */
        .notif-dot { position: absolute; top: -2px; right: -2px; width: 8px; height: 8px; background: #ef4444; border-radius: 9999px; box-shadow: 0 0 0 2px white; display: none; }
        .has-new .notif-dot { display: inline-block; }
    /* Full-width persistent status line styles */
    .status-line { display:block; width:100%; padding:6px 10px; border-radius:8px; font-size:0.8rem; font-weight:600; letter-spacing:.3px; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05); }
    .status-pending { background:#e0f2fe; color:#1e3a8a; }
    .status-preparing { background:#fff3e0; color:#9a3412; }
    .status-ready { background:#e6ffed; color:#065f46; }
    .status-line.fade-in { animation: statusFade .35s ease; }
    @keyframes statusFade { from { opacity:0; transform:translateY(-3px); } to { opacity:1; transform:translateY(0); } }
  </style>
</head>
<body class="min-h-screen flex">
<?php if ($loggedInUserType == 'owner'): ?>
    <aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
    <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
    <?php $current = basename($_SERVER['PHP_SELF']); ?>   
    <button title="Dashboard" onclick="window.location.href='../Owner/dashboard.php'"><i class="fas fa-chart-line text-xl <?= $current == 'dashboard.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Home" onclick="window.location.href='../Owner/mainpage.php'"><i class="fas fa-home text-xl <?= $current == 'mainpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
        <button id="orders-icon-owner" class="relative" title="Orders" onclick="window.location.href='../Owner/page.php'">
            <span class="notif-dot"></span>
            <i class="fas fa-shopping-cart text-xl <?= $current == 'page.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
    <button title="Order List" onclick="window.location.href='../all/tranlist.php'"><i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Inventory" onclick="window.location.href='../Owner/product.php'"><i class="fas fa-box text-xl <?= $current == 'product.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Users" onclick="window.location.href='../Owner/user.php'"><i class="fas fa-users text-xl <?= $current == 'user.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Settings" onclick="window.location.href='../all/setting.php'"><i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button id="logout-btn" title="Logout"><i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i></button>
</aside>
<?php elseif ($loggedInUserType == 'employee'): ?>
<aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
    <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
  <?php $current = basename($_SERVER['PHP_SELF']); ?>   
  <button title="Home" onclick="window.location.href='../Employee/employesmain.php'"><i class="fas fa-home text-xl <?= $current == 'employesmain.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button id="orders-icon-emp" class="relative" title="Cart" onclick="window.location.href='../Employee/employeepage.php'">
        <span class="notif-dot"></span>
        <i class="fas fa-shopping-cart text-xl <?= $current == 'employeepage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
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
                            <div class="flex gap-2 items-center">
                                <?php if (!empty($transaction['ReceiptPath'])): ?>
                                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150 view-receipt-btn" data-img="../<?= htmlspecialchars($transaction['ReceiptPath']) ?>" title="View Payment Proof"><i class="fas fa-image mr-1"></i>Receipt</button>
                                <?php endif; ?>
                <button class="bg-[#4B2E0E] hover:bg-[#3a240c] text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150" data-id="<?= $transaction['OrderID'] ?>" data-status="Preparing Order"><i class="fas fa-utensils mr-1"></i> Prepare</button>
                <button class="bg-green-700 hover:bg-green-800 text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150" data-id="<?= $transaction['OrderID'] ?>" data-status="Order Ready"><i class="fas fa-check-circle mr-1"></i> Ready</button>
              </div>
            </div>
            <div class="text-right text-xs text-gray-600 mt-1">Ref: <?= htmlspecialchars($transaction['ReferenceNo'] ?? 'N/A') ?></div>
            <?php
                $statusRaw = $transaction['Status'] ?? 'Pending';
                $statusLabel = 'Pending';
                $statusClass = 'status-line status-pending';
                if ($statusRaw === 'Preparing') { $statusLabel = 'Preparing Order'; $statusClass = 'status-line status-preparing'; }
                elseif ($statusRaw === 'Ready') { $statusLabel = 'Order Ready'; $statusClass = 'status-line status-ready'; }
            ?>
            <div class="mt-2" ><span id="status-<?= $transaction['OrderID'] ?>" class="<?= $statusClass ?>"><?= $statusLabel ?></span></div>
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
                        <p class="text-xs text-gray-600 mb-2">Date: <?= htmlspecialchars(date('M d, Y H:i', strtotime($transaction['OrderDate']))) ?> <span class="ml-2 inline-block bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded">Walk-in</span></p>
            <ul class="text-sm text-gray-700 list-disc list-inside mb-2"><li><?= nl2br(htmlspecialchars($transaction['OrderItems'])) ?></li></ul>
                        <div class="flex justify-between items-center mt-2">
                            <span class="font-bold text-lg text-[#4B2E0E]">₱<?= number_format($transaction['TotalAmount'], 2) ?></span>
                            <div class="flex gap-2 items-center">
                                        <span class="text-[10px] bg-gray-200 text-gray-600 px-2 py-1 rounded font-semibold tracking-wide">No Receipt (Walk-in)</span>
                <button class="bg-[#4B2E0E] hover:bg-[#3a240c] text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150" data-id="<?= $transaction['OrderID']; ?>" data-status="Preparing Order"><i class="fas fa-utensils mr-1"></i> Prepare</button>
                <button class="bg-green-700 hover:bg-green-800 text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150" data-id="<?= $transaction['OrderID']; ?>" data-status="Order Ready"><i class="fas fa-check-circle mr-1"></i> Ready</button>
              </div>
            </div>
            <div class="text-right text-xs text-gray-600 mt-1">Ref: <?= htmlspecialchars($transaction['ReferenceNo'] ?? 'N/A') ?></div>
            <?php
                $statusRaw = $transaction['Status'] ?? 'Pending';
                $statusLabel = 'Pending';
                $statusClass = 'status-line status-pending';
                if ($statusRaw === 'Preparing') { $statusLabel = 'Preparing Order'; $statusClass = 'status-line status-preparing'; }
                elseif ($statusRaw === 'Ready') { $statusLabel = 'Order Ready'; $statusClass = 'status-line status-ready'; }
            ?>
            <div class="mt-2"><span id="status-<?= $transaction['OrderID'] ?>" class="<?= $statusClass ?>"><?= $statusLabel ?></span></div>
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

    // Delegated receipt preview (works for existing + future buttons)
    document.addEventListener('click', (e)=>{
        const btn = e.target.closest('.view-receipt-btn');
        if(!btn) return;
        const img = btn.getAttribute('data-img');
        if(!img) return;
        Swal.fire({
            title: 'Payment Receipt',
            html: `<div style="max-height:70vh;overflow:auto"><img src="${img}" alt="Receipt" style="max-width:100%;border-radius:12px;box-shadow:0 4px 18px rgba(0,0,0,0.25)" /></div>`,
            width: 600,
            confirmButtonText: 'Close',
            confirmButtonColor: '#4B2E0E'
        });
    });

    document.getElementById("logout-btn").addEventListener("click", () => {
        Swal.fire({
            title: 'Are you sure?', icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#4B2E0E', cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, log out'
        }).then((result) => { if (result.isConfirmed) { window.location.href = "logout.php"; }});
    });

    // Status buttons will be bound after definition in the later script block.
    
    // Restore any previously saved statuses & disable buttons accordingly
    document.querySelectorAll('[id^="status-\"]').forEach(statusElement => {
        const orderId = statusElement.id.replace('status-','');
        const stored = sessionStorage.getItem(`orderStatus-${orderId}`);
        if (stored) {
            // stored could be HTML from older session or just code (Pending/Preparing/Ready)
            let code = 'Pending';
            if (/Ready/i.test(stored)) code = 'Ready'; else if (/Prepar/i.test(stored)) code = 'Preparing';
            applyStatusVisual(statusElement, code, false);
        }
        const card = statusElement.closest('.border');
        if (!card) return;
        const prepBtn = card.querySelector('button[data-status="Preparing Order"]');
        const readyBtn = card.querySelector('button[data-status="Order Ready"]');
        const txt = statusElement.textContent || '';
        if (txt.includes('Order Ready')) {
            [prepBtn, readyBtn].forEach(b=>{ if(b){ b.disabled=true; b.classList.add('opacity-50','cursor-not-allowed'); }});
        } else if (txt.includes('Preparing Order')) {
            if (prepBtn) { prepBtn.disabled = true; prepBtn.classList.add('opacity-50','cursor-not-allowed'); }
        }
    });
});
</script>
<script>
function applyStatusVisual(el, statusCode, animate=true){
    if(!el) return;
    el.classList.remove('status-pending','status-preparing','status-ready','fade-in');
    let label='Pending', cls='status-pending';
    if(statusCode==='Preparing'){ label='Preparing Order'; cls='status-preparing'; }
    else if(statusCode==='Ready'){ label='Order Ready'; cls='status-ready'; }
    el.textContent = label;
    el.classList.add('status-line', cls);
    if (animate) el.classList.add('fade-in');
}

// Global function to bind status buttons with AJAX calls
function bindStatusButtons(scope){
    if(!scope) scope = document;
    scope.querySelectorAll('button[data-status]')
        .forEach(btn => {
            if (btn.dataset.bound) return; // avoid double binding
            btn.dataset.bound = '1';
            btn.addEventListener('click', async () => {
                const orderId = btn.getAttribute('data-id');
                const displayStatus = btn.getAttribute('data-status'); // e.g. "Preparing Order" or "Order Ready"
                if (!orderId || !displayStatus) return;

                // Prevent rapid double clicks
                if (btn.disabled) return;
                btn.disabled = true;
                btn.classList.add('opacity-50','cursor-not-allowed');

                try {
                    const formData = new URLSearchParams();
                    formData.append('order_id', orderId);
                    formData.append('status', displayStatus);
                    const res = await fetch('../ajax/update_order_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData.toString(),
                    });
                    const json = await res.json().catch(()=>({success:false,message:'Invalid JSON'}));
                    if (!res.ok || !json.success) {
                        throw new Error(json.message || 'Update failed');
                    }
                    // Success: update UI
                    const statusEl = document.getElementById(`status-${orderId}`);
                    if (statusEl) {
                        const code = (displayStatus === 'Preparing Order') ? 'Preparing' : (displayStatus === 'Order Ready' ? 'Ready' : 'Pending');
                        applyStatusVisual(statusEl, code);
                        sessionStorage.setItem(`orderStatus-${orderId}`, code);
                    }
                    // Button disabling logic
                    const card = btn.closest('.border');
                    if (card) {
                        const prepBtn = card.querySelector('button[data-status="Preparing Order"]');
                        const readyBtn = card.querySelector('button[data-status="Order Ready"]');
                        if (displayStatus === 'Preparing Order') {
                            if (prepBtn) { prepBtn.disabled = true; prepBtn.classList.add('opacity-50','cursor-not-allowed'); }
                        } else if (displayStatus === 'Order Ready') {
                            [prepBtn, readyBtn].forEach(b=>{ if(b){ b.disabled = true; b.classList.add('opacity-50','cursor-not-allowed'); }});
                        }
                    }
                } catch (err) {
                    // Re-enable if failure so user can retry
                    btn.disabled = false;
                    btn.classList.remove('opacity-50','cursor-not-allowed');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Update Failed', text: err.message || 'Could not update status', confirmButtonColor:'#4B2E0E' });
                    } else {
                        alert('Status update failed: ' + (err.message || 'Unknown error'));
                    }
                }
            });
        });
}
// --- Real-time orders polling ---
(function(){
    const customerContainer = document.getElementById('customer-orders');
    const walkinContainer = document.getElementById('walkin-orders');
    const pagCustomerId = 'customer-pagination';
    const pagWalkinId = 'walkin-pagination';
    let latestId = 0;
    // Initialize latestId from existing DOM
    function initLatestFromDOM() {
        const ids = [];
        document.querySelectorAll('#customer-orders [id^="status-"], #walkin-orders [id^="status-"]').forEach(el => {
            const m = el.id.match(/status-(\d+)/);
            if (m) ids.push(parseInt(m[1], 10));
        });
        if (ids.length) { latestId = Math.max(...ids); }
    }
    initLatestFromDOM();

    function orderCardHTML(t){
        const dateStr = new Date(t.OrderDateISO).toLocaleString();
        const total = (Number(t.TotalAmount) || 0).toFixed(2);
        const ref = t.ReferenceNo || 'N/A';
        const itemsEsc = (t.OrderItems || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
        const cust = (t.CustomerUsername || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const headerExtra = t.category === 'customer' ? `Customer: ${cust}<br>` : '';
    // Only show receipt button for customer account orders
        const receiptBtn = (t.category === 'customer' && t.ReceiptPath)
            ? `<button class=\"bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150 view-receipt-btn\" data-img=\"../${t.ReceiptPath}\" title=\"View Payment Proof\"><i class=\"fas fa-image mr-1\"></i>Receipt</button>`
            : (t.category === 'walkin' ? `<span class=\"text-[10px] bg-gray-200 text-gray-600 px-2 py-1 rounded font-semibold tracking-wide\">No Receipt (Walk-in)</span>` : '');
    return `
    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 shadow-sm mb-4" data-oid="${t.OrderID}">
        <p class="text-sm font-semibold text-[#4B2E0E] mb-1">Order #${t.OrderID}</p>
        <p class="text-xs text-gray-600 mb-2">${headerExtra}Date: ${dateStr} ${t.category==='walkin' ? '<span class=\'ml-2 inline-block bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded\'>Walk-in</span>' : ''}</p>
            <ul class="text-sm text-gray-700 list-disc list-inside mb-2"><li>${itemsEsc}</li></ul>
            <div class="flex justify-between items-center mt-2">
              <span class="font-bold text-lg text-[#4B2E0E]">₱${total}</span>
                            <div class="flex gap-2 items-center">${receiptBtn}
                <button class="bg-[#4B2E0E] hover:bg-[#3a240c] text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150" data-id="${t.OrderID}" data-status="Preparing Order"><i class="fas fa-utensils mr-1"></i> Prepare</button>
                <button class="bg-green-700 hover:bg-green-800 text-white px-3 py-1 rounded-lg text-sm shadow transition duration-150" data-id="${t.OrderID}" data-status="Order Ready"><i class="fas fa-check-circle mr-1"></i> Ready</button>
              </div>
            </div>
            <div class="text-right text-xs text-gray-600 mt-1">Ref: ${ref}</div>
            <div class="mt-2"><span class="status-line ${(()=>{const s=t.Status||'Pending';if(s==='Preparing')return 'status-preparing';if(s==='Ready')return 'status-ready';return 'status-pending';})()}" id="status-${t.OrderID}">${(()=>{const s=t.Status||'Pending';if(s==='Preparing')return 'Preparing Order';if(s==='Ready')return 'Order Ready';return 'Pending';})()}</span></div>
        </div>`;
    }

    // Initial binding for already-rendered orders
    bindStatusButtons(document);

    function showToast(count){
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: `${count} new order${count>1?'s':''}`,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    }

    function bumpOrdersIcon(){
        const ownerBtn = document.getElementById('orders-icon-owner');
        const empBtn = document.getElementById('orders-icon-emp');
        [ownerBtn, empBtn].forEach(btn => { if (btn) { btn.classList.add('has-new'); setTimeout(()=>btn.classList.remove('has-new'), 2500); }});
    }

    async function fetchNew(){
        try {
            const res = await fetch(`../ajax/get_transactions.php?since_id=${latestId}&limit=20`, { cache: 'no-store' });
            if (!res.ok) return;
            const json = await res.json();
            if (!json.success) return;
            const items = Array.isArray(json.data) ? json.data : [];
            if (!items.length) return;

            // Newest first (endpoint returns DESC), append to top in order of DESC to keep visual newest first
            items.forEach(t => {
                const html = orderCardHTML(t);
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                const card = wrapper.firstElementChild;
                card.classList.add('new-flash');
                if (t.category === 'customer') {
                    customerContainer.insertBefore(card, customerContainer.firstChild);
                    bindStatusButtons(card);
                } else {
                    walkinContainer.insertBefore(card, walkinContainer.firstChild);
                    bindStatusButtons(card);
                }
                // Disable buttons based on status
                const statusEl = card.querySelector(`#status-${t.OrderID}`);
                if (statusEl) {
                    const text = statusEl.textContent;
                    const prepBtn = card.querySelector('button[data-status="Preparing Order"]');
                    const readyBtn = card.querySelector('button[data-status="Order Ready"]');
                    if (text.includes('Preparing Order')) { if (prepBtn) { prepBtn.disabled = true; prepBtn.classList.add('opacity-50','cursor-not-allowed'); } }
                    if (text.includes('Order Ready')) { [prepBtn, readyBtn].forEach(b=>{ if(b){ b.disabled=true; b.classList.add('opacity-50','cursor-not-allowed'); }}); }
                }
            });

            // Update latestId
            if (json.latest_id && json.latest_id > latestId) latestId = json.latest_id;

            // Re-run pagination to account for new items (keeping current page as 1 effectively)
            paginate('customer-orders', pagCustomerId, 5);
            paginate('walkin-orders', pagWalkinId, 5);

            // Notify user
            showToast(items.length);
            bumpOrdersIcon();
        } catch (e) {
            // Silently ignore to avoid noisy UI; could log if needed
        }
    }

    // Poll every 3 seconds
    setInterval(fetchNew, 3000);
})();
</script>
</body>
</html>