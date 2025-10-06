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
    body { font-family: 'Inter', sans-serif; background: url('../images/LAbg.png') no-repeat center center/cover; }
    .main-content { flex-grow: 1; padding: 1rem; position: relative; display: flex; flex-direction: column; }
    .main-content .bg-image { position: absolute; inset: 0; width:100%; height:100%; object-fit:cover; opacity:.2; z-index:-10; }
    /* Redesigned layout overrides */
    .order-section { height: auto; backdrop-filter: blur(4px); }
    .order-list-wrapper { max-height:60vh; overflow-y:auto; }
    .thin-scroll::-webkit-scrollbar { width: 6px; }
    .thin-scroll::-webkit-scrollbar-track { background: transparent; }
    .thin-scroll::-webkit-scrollbar-thumb { background: #c19a6b55; border-radius: 9999px; }
    .thin-scroll::-webkit-scrollbar-thumb:hover { background: #c19a6b; }
    /* Status line */
    .status-line { display:block; width:100%; padding:6px 10px; border-radius:8px; font-size:0.75rem; font-weight:600; letter-spacing:.3px; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05); }
    .status-pending { background:#e0f2fe; color:#1e3a8a; }
    .status-preparing { background:#fff3e0; color:#9a3412; }
    .status-ready { background:#e6ffed; color:#065f46; }
    .status-line.fade-in { animation: statusFade .35s ease; }
    @keyframes statusFade { from { opacity:0; transform:translateY(-3px);} to { opacity:1; transform:translateY(0);} }
    /* New order highlight */
    .new-flash { animation: flashBg 1.5s ease-out 1; }
    @keyframes flashBg { 0% { background-color:#fff9c4;} 100% { background-color:#f9fafb;} }
    /* Cards */
    .order-card { transition: box-shadow .18s, transform .12s, background-color .25s; }
    .order-card:hover { box-shadow:0 6px 20px -4px rgba(0,0,0,0.15); background:#fff; }
    .order-card:active { transform: scale(.985); }
    .notif-dot { position:absolute; top:-2px; right:-2px; width:8px; height:8px; background:#ef4444; border-radius:9999px; box-shadow:0 0 0 2px white; display:none; }
    .has-new .notif-dot { display:inline-block; }
  </style>
</head>
<body class="min-h-screen flex">
<?php if ($loggedInUserType == 'owner'): ?>
    <aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
      <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
      <?php $current = basename($_SERVER['PHP_SELF']); ?>   
      <button title="Dashboard" onclick="window.location.href='../Owner/dashboard.php'"><i class="fas fa-chart-line text-xl <?= $current == 'dashboard.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button title="Home" onclick="window.location.href='../Owner/mainpage.php'"><i class="fas fa-home text-xl <?= $current == 'mainpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button id="orders-icon-owner" class="relative" title="Orders" onclick="window.location.href='../Owner/page.php'"><span class="notif-dot"></span><i class="fas fa-shopping-cart text-xl <?= $current == 'page.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
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
      <button id="orders-icon-emp" class="relative" title="Cart" onclick="window.location.href='../Employee/employeepage.php'"><span class="notif-dot"></span><i class="fas fa-shopping-cart text-xl <?= $current == 'employeepage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button title="Transaction Records" onclick="window.location.href='../all/tranlist.php'"><i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button title="Box" onclick="window.location.href='../Employee/productemployee.php'"><i class="fas fa-box text-xl <?= $current == 'productemployee.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button title="Settings" onclick="window.location.href='../all/setting.php'"><i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button id="logout-btn" title="Logout"><i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i></button>
    </aside>
<?php endif; ?>

<div class="main-content">
  <img src="../images/LAbg.png" alt="Background image" class="bg-image" />
  <div class="bg-white/90 w-full rounded-2xl shadow-lg p-5 sm:p-8 flex flex-col gap-6 relative z-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-200 pb-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#4B2E0E]">Transaction Records</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">Monitor, search, and update live order statuses in real time.</p>
      </div>
      <div class="flex flex-col sm:flex-row gap-3 sm:items-center w-full md:w-auto">
        <div class="relative w-full sm:w-72">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#4B2E0E]/60"><i class="fas fa-search"></i></span>
          <input id="orderSearch" type="text" autocomplete="off" class="w-full pl-9 pr-9 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#c19a6b]/60 bg-white placeholder:text-gray-400 text-sm" placeholder="Search (ID, customer, items, ref, status)" />
          <button id="clearSearch" class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#4B2E0E] transition" title="Clear search"><i class="fas fa-times-circle"></i></button>
        </div>
        <div class="text-[11px] sm:text-xs text-gray-500">Live filter across both lists</div>
      </div>
    </div>

    <div class="grid xl:grid-cols-2 gap-6">
      <!-- Customer Orders -->
      <section class="order-section bg-white/80 border border-gray-200 rounded-xl p-4 sm:p-6 flex flex-col gap-4 shadow-sm">
        <div class="flex items-center justify-between gap-2">
          <h2 class="text-lg font-bold text-[#4B2E0E] flex items-center gap-2"><i class="fas fa-user-check"></i> Customer Account Orders</h2>
          <span id="customer-count-badge" class="text-xs px-2 py-1 rounded-full bg-[#4B2E0E]/10 text-[#4B2E0E] font-medium"></span>
        </div>
        <div id="customer-orders" class="order-list-wrapper thin-scroll">
          <?php foreach ($customerAccountOrders as $transaction): ?>
          <div class="order-card border border-gray-200 rounded-lg p-4 bg-gray-50 shadow-sm mb-4" data-oid="<?= htmlspecialchars($transaction['OrderID']) ?>">
            <div class="flex justify-between items-start gap-4">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-[#4B2E0E] mb-1">Order #<?= htmlspecialchars($transaction['OrderID']) ?></p>
                <p class="text-xs text-gray-600 mb-2">Customer: <?= htmlspecialchars($transaction['CustomerUsername']) ?><br>Date: <?= htmlspecialchars(date('M d, Y H:i', strtotime($transaction['OrderDate']))) ?></p>
                <ul class="text-sm text-gray-700 list-disc list-inside mb-2"><li><?= nl2br(htmlspecialchars($transaction['OrderItems'])) ?></li></ul>
              </div>
              <div class="flex flex-col items-end gap-2">
                <span class="font-bold text-lg text-[#4B2E0E] whitespace-nowrap">₱<?= number_format($transaction['TotalAmount'], 2) ?></span>
                <div class="flex gap-2 flex-wrap justify-end">
                  <?php if (!empty($transaction['ReceiptPath'])): ?>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg text-xs shadow transition view-receipt-btn" data-img="../<?= htmlspecialchars($transaction['ReceiptPath']) ?>" title="View Payment Proof"><i class="fas fa-image mr-1"></i>Receipt</button>
                  <?php endif; ?>
                  <button class="bg-[#4B2E0E] hover:bg-[#3a240c] text-white px-3 py-1 rounded-lg text-xs shadow transition" data-id="<?= $transaction['OrderID'] ?>" data-status="Preparing Order"><i class="fas fa-utensils mr-1"></i>Prepare</button>
                  <button class="bg-green-700 hover:bg-green-800 text-white px-3 py-1 rounded-lg text-xs shadow transition" data-id="<?= $transaction['OrderID'] ?>" data-status="Order Ready"><i class="fas fa-check-circle mr-1"></i>Ready</button>
                </div>
              </div>
            </div>
            <div class="text-right text-[11px] text-gray-500 mt-1">Ref: <?= htmlspecialchars($transaction['ReferenceNo'] ?? 'N/A') ?></div>
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
        <div id="customer-pagination" class="flex flex-wrap items-center justify-center gap-2 mt-1"></div>
        <div id="customer-empty" class="hidden text-center text-sm text-gray-500">No matching orders</div>
      </section>

      <!-- Walk-in Orders -->
      <section class="order-section bg-white/80 border border-gray-200 rounded-xl p-4 sm:p-6 flex flex-col gap-4 shadow-sm">
        <div class="flex items-center justify-between gap-2">
          <h2 class="text-lg font-bold text-[#4B2E0E] flex items-center gap-2"><i class="fas fa-walking"></i> Walk-in / Staff-Assisted Orders</h2>
          <span id="walkin-count-badge" class="text-xs px-2 py-1 rounded-full bg-[#4B2E0E]/10 text-[#4B2E0E] font-medium"></span>
        </div>
        <div id="walkin-orders" class="order-list-wrapper thin-scroll">
          <?php foreach ($walkinStaffOrders as $transaction): ?>
          <div class="order-card border border-gray-200 rounded-lg p-4 bg-gray-50 shadow-sm mb-4" data-oid="<?= htmlspecialchars($transaction['OrderID']) ?>">
            <div class="flex justify-between items-start gap-4">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-[#4B2E0E] mb-1">Order #<?= htmlspecialchars($transaction['OrderID']) ?></p>
                <p class="text-xs text-gray-600 mb-2">Date: <?= htmlspecialchars(date('M d, Y H:i', strtotime($transaction['OrderDate']))) ?> <span class="ml-2 inline-block bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded">Walk-in</span></p>
                <ul class="text-sm text-gray-700 list-disc list-inside mb-2"><li><?= nl2br(htmlspecialchars($transaction['OrderItems'])) ?></li></ul>
              </div>
              <div class="flex flex-col items-end gap-2">
                <span class="font-bold text-lg text-[#4B2E0E] whitespace-nowrap">₱<?= number_format($transaction['TotalAmount'], 2) ?></span>
                <div class="flex gap-2 flex-wrap justify-end">
                  <span class="text-[10px] bg-gray-200 text-gray-600 px-2 py-1 rounded font-semibold tracking-wide">No Receipt</span>
                  <button class="bg-[#4B2E0E] hover:bg-[#3a240c] text-white px-3 py-1 rounded-lg text-xs shadow transition" data-id="<?= $transaction['OrderID']; ?>" data-status="Preparing Order"><i class="fas fa-utensils mr-1"></i>Prepare</button>
                  <button class="bg-green-700 hover:bg-green-800 text-white px-3 py-1 rounded-lg text-xs shadow transition" data-id="<?= $transaction['OrderID']; ?>" data-status="Order Ready"><i class="fas fa-check-circle mr-1"></i>Ready</button>
                </div>
              </div>
            </div>
            <div class="text-right text-[11px] text-gray-500 mt-1">Ref: <?= htmlspecialchars($transaction['ReferenceNo'] ?? 'N/A') ?></div>
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
        <div id="walkin-pagination" class="flex flex-wrap items-center justify-center gap-2 mt-1"></div>
        <div id="walkin-empty" class="hidden text-center text-sm text-gray-500">No matching orders</div>
      </section>
    </div>
  </div>
</div>

<script>
function updateCounts(){
  const sections = [
    {listId:'customer-orders', badgeId:'customer-count-badge', empty:'customer-empty'},
    {listId:'walkin-orders', badgeId:'walkin-count-badge', empty:'walkin-empty'}
  ];
  sections.forEach(sec=>{
    const list = document.getElementById(sec.listId);
    const badge = document.getElementById(sec.badgeId);
    const emptyBox = document.getElementById(sec.empty);
    if(!list) return;
    const visible = Array.from(list.children).filter(c=> c.classList.contains('order-card') && c.getAttribute('data-match') !== 'false');
    if (badge) badge.textContent = visible.length ? visible.length + (visible.length === 1 ? ' order' : ' orders') : '0';
    if (emptyBox) emptyBox.classList.toggle('hidden', visible.length !== 0);
  });
}

function paginate(containerId, paginationId, itemsPerPage = 10) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const pagination = document.getElementById(paginationId);
    if (!pagination) return;
    const items = Array.from(container.children).filter(el => el.classList.contains('order-card') && el.getAttribute('data-match') !== 'false');
    const totalPages = Math.ceil(items.length / itemsPerPage) || 1;
    let currentPage = 1;

    function showPage(page) {
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        currentPage = page;
        items.forEach((item, i) => {
            const show = (i >= (page - 1) * itemsPerPage && i < page * itemsPerPage);
            item.style.display = show ? '' : 'none';
        });
        renderPagination();
        updateCounts();
    }

    function makeBtn(label, onClick, disabled=false, active=false, ariaLabel='') {
        const b = document.createElement('button');
        b.type = 'button';
        b.textContent = label;
        b.className = [
            'px-3 py-1 rounded-md text-sm font-medium transition select-none',
            active ? 'bg-[#4B2E0E] text-white shadow' : 'bg-white text-[#4B2E0E] border border-[#4B2E0E]/30 hover:bg-[#4B2E0E] hover:text-white',
            disabled ? 'opacity-40 cursor-not-allowed hover:bg-white hover:text-[#4B2E0E]' : ''
        ].join(' ');
        if (disabled) b.disabled = true;
        if (ariaLabel) b.setAttribute('aria-label', ariaLabel);
        if (!disabled) b.addEventListener('click', onClick);
        return b;
    }

    function renderPagination() {
        pagination.innerHTML = '';
        if (totalPages <= 1) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-center gap-1 flex-wrap';
        wrapper.appendChild(makeBtn('Prev', () => showPage(currentPage - 1), currentPage === 1, false, 'Previous page'));

        const windowSize = 5;
        let start = Math.max(1, currentPage - Math.floor(windowSize/2));
        let end = start + windowSize - 1;
        if (end > totalPages) { end = totalPages; start = Math.max(1, end - windowSize + 1); }

        if (start > 1) {
            wrapper.appendChild(makeBtn('1', () => showPage(1), false, currentPage===1));
            if (start > 2) { const dotsL = document.createElement('span'); dotsL.textContent='…'; dotsL.className='px-2 text-[#4B2E0E]'; wrapper.appendChild(dotsL); }
        }
        for (let i=start;i<=end;i++) wrapper.appendChild(makeBtn(String(i), () => showPage(i), false, i===currentPage));
        if (end < totalPages) {
            if (end < totalPages - 1) { const dotsR = document.createElement('span'); dotsR.textContent='…'; dotsR.className='px-2 text-[#4B2E0E]'; wrapper.appendChild(dotsR); }
            wrapper.appendChild(makeBtn(String(totalPages), () => showPage(totalPages), false, currentPage===totalPages));
        }
        wrapper.appendChild(makeBtn('Next', () => showPage(currentPage + 1), currentPage === totalPages, false, 'Next page'));
        pagination.appendChild(wrapper);
    }

    showPage(currentPage);
}

// Global search
window.currentSearchQuery = '';
window.applySearch = function(){
  const qInput = document.getElementById('orderSearch');
  if (!qInput) return;
  const query = (qInput.value || '').trim().toLowerCase();
  window.currentSearchQuery = query;
  const clearBtn = document.getElementById('clearSearch');
  if (clearBtn) clearBtn.classList.toggle('hidden', query.length === 0);
  const sections = [
    { listId: 'customer-orders', pagId: 'customer-pagination', emptyId: 'customer-empty' },
    { listId: 'walkin-orders', pagId: 'walkin-pagination', emptyId: 'walkin-empty' }
  ];
  sections.forEach(sec => {
    const list = document.getElementById(sec.listId);
    if (!list) return;
    Array.from(list.children).forEach(card => {
      if (!card.classList.contains('order-card')) return;
      if (!query) {
        card.removeAttribute('data-match');
        return;
      }
      const text = card.innerText.toLowerCase();
      if (text.includes(query)) {
        card.removeAttribute('data-match');
      } else {
        card.setAttribute('data-match','false');
        card.style.display = 'none';
      }
    });
    paginate(sec.listId, sec.pagId, 5);
  });
  updateCounts();
};

document.addEventListener('DOMContentLoaded', () => {
    paginate('customer-orders', 'customer-pagination', 5);
    paginate('walkin-orders', 'walkin-pagination', 5);
    updateCounts();

    // Search listeners
    const searchInput = document.getElementById('orderSearch');
    const clearBtn = document.getElementById('clearSearch');
    let searchDebounce;
    if (searchInput) {
        searchInput.addEventListener('input', () => { clearTimeout(searchDebounce); searchDebounce = setTimeout(()=>window.applySearch(), 200); });
    }
    if (clearBtn) { clearBtn.addEventListener('click', () => { searchInput.value=''; window.applySearch(); searchInput.focus(); }); }

    // Delegated receipt preview
    document.addEventListener('click', (e)=>{
        const btn = e.target.closest('.view-receipt-btn');
        if(!btn) return; const img = btn.getAttribute('data-img'); if(!img) return;
        Swal.fire({ title: 'Payment Receipt', html: `<div style="max-height:70vh;overflow:auto"><img src="${img}" alt="Receipt" style="max-width:100%;border-radius:12px;box-shadow:0 4px 18px rgba(0,0,0,0.25)" /></div>`, width: 600, confirmButtonText: 'Close', confirmButtonColor: '#4B2E0E' });
    });

    document.getElementById("logout-btn")?.addEventListener("click", () => {
        Swal.fire({ title: 'Are you sure?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#4B2E0E', cancelButtonColor: '#d33', confirmButtonText: 'Yes, log out' }).then((r)=>{ if(r.isConfirmed){ window.location.href = "logout.php"; }});
    });

    // Restore statuses
    document.querySelectorAll('[id^="status-"]').forEach(statusElement => {
        const orderId = statusElement.id.replace('status-','');
        const stored = sessionStorage.getItem(`orderStatus-${orderId}`);
        if (stored) {
            let code = 'Pending';
            if (/Ready/i.test(stored)) code = 'Ready'; else if (/Prepar/i.test(stored)) code = 'Preparing';
            applyStatusVisual(statusElement, code, false);
        }
        const card = statusElement.closest('.order-card');
        if (!card) return;
        const prepBtn = card.querySelector('button[data-status="Preparing Order"]');
        const readyBtn = card.querySelector('button[data-status="Order Ready"]');
        const txt = statusElement.textContent || '';
        if (txt.includes('Order Ready')) { [prepBtn, readyBtn].forEach(b=>{ if(b){ b.disabled=true; b.classList.add('opacity-50','cursor-not-allowed'); }}); }
        else if (txt.includes('Preparing Order')) { if (prepBtn) { prepBtn.disabled = true; prepBtn.classList.add('opacity-50','cursor-not-allowed'); } }
    });
});

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

function bindStatusButtons(scope){
    if(!scope) scope = document;
    scope.querySelectorAll('button[data-status]')
        .forEach(btn => {
            if (btn.dataset.bound) return;
            btn.dataset.bound = '1';
            btn.addEventListener('click', async () => {
                const orderId = btn.getAttribute('data-id');
                const displayStatus = btn.getAttribute('data-status');
                if (!orderId || !displayStatus) return;
                if (btn.disabled) return;
                btn.disabled = true; btn.classList.add('opacity-50','cursor-not-allowed');
                try {
                    const formData = new URLSearchParams();
                    formData.append('order_id', orderId);
                    formData.append('status', displayStatus);
                    const res = await fetch('../ajax/update_order_status.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: formData.toString() });
                    const json = await res.json().catch(()=>({success:false,message:'Invalid JSON'}));
                    if (!res.ok || !json.success) throw new Error(json.message || 'Update failed');
                    const statusEl = document.getElementById(`status-${orderId}`);
                    if (statusEl) {
                        const code = (displayStatus === 'Preparing Order') ? 'Preparing' : (displayStatus === 'Order Ready' ? 'Ready' : 'Pending');
                        applyStatusVisual(statusEl, code);
                        sessionStorage.setItem(`orderStatus-${orderId}`, code);
                    }
                    const card = btn.closest('.order-card');
                    if