<?php
session_start();
if (!isset($_SESSION['CustomerID'])) {
  header('Location: ../all/login');
  exit();
}
require_once('../classes/database.php');
$con = new database();
$customerID = $_SESSION['CustomerID'];
$transactions = $con->getOrdersForCustomer($customerID);
$customer = $_SESSION['CustomerFN'];
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="../images/logo.png" type="image/png"/>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Transaction Records</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    /* Fixed sidebar width consistent with owner page layout */
    .la-sidebar { width:70px; min-width:70px; flex:0 0 70px; }
    .la-sidebar img { width:48px; height:48px; }
    @media (max-width:767px){ body.nav-open { overflow:hidden; } }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
</head>
<body class="bg-[rgba(255,255,255,0.7)] min-h-screen md:h-screen flex flex-col md:flex-row md:overflow-hidden">
  <!-- Mobile Top Bar -->
  <div class="md:hidden flex items-center justify-between px-4 py-2 bg-white/90 backdrop-blur-sm shadow sticky top-0 z-30">
    <div class="flex items-center gap-2">
      <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full" />
      <span class="font-semibold text-[#4B2E0E] text-lg">Transactions</span>
    </div>
    <button id="mobile-nav-toggle" class="p-2 rounded-full border border-[#4B2E0E] text-[#4B2E0E]"><i class="fa-solid fa-bars"></i></button>
  </div>
  <!-- Mobile Slide-over Nav -->
  <div id="mobile-nav-panel" class="md:hidden fixed inset-0 z-40 hidden">
    <div class="absolute inset-0 bg-black/40" id="mobile-nav-backdrop"></div>
    <div class="absolute left-0 top-0 h-full w-60 bg-white shadow-lg p-4 flex flex-col gap-4 overflow-y-auto">
      <div class="flex justify-between items-center mb-2">
        <h2 class="text-[#4B2E0E] font-semibold">Navigation</h2>
        <button id="mobile-nav-close" class="text-gray-500 text-xl"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
      <nav class="flex flex-col gap-2 text-sm">
        <a href="../Customer/advertisement" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='advertisement.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-home"></i> Home</a>
        <a href="../Customer/customerpage" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='customerpage.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-shopping-cart"></i> Cart</a>
        <a href="../Customer/transactionrecords" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='transactionrecords.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-list"></i> Transactions</a>
        <a href="../all/setting" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='setting.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-cog"></i> Settings</a>
        <button id="logout-btn-mobile" class="flex items-center gap-2 px-3 py-2 rounded-md border border-gray-300 text-[#4B2E0E] text-left"><i class="fas fa-sign-out-alt"></i> Logout</button>
      </nav>
    </div>
  </div>
  <!-- Sidebar -->
  <aside class="hidden md:flex bg-white bg-opacity-90 backdrop-blur-sm flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
    <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
  <button title="Home" onclick="window.location='../Customer/advertisement'" class="text-xl">
  <i class="fas fa-home <?= $currentPage === 'advertisement.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
  <button title="Cart" onclick="window.location='../Customer/customerpage'" class="text-xl">
  <i class="fas fa-shopping-cart <?= $currentPage === 'customerpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
  <button title="Order List" onclick="window.location='../Customer/transactionrecords'" class="text-xl">
  <i class="fas fa-list <?= $currentPage === 'transactionrecords.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
  <button title="Settings" onclick="window.location='../all/setting'" class="text-xl">
      <i class="fas fa-cog <?= $currentPage === 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button id="logout-btn" title="Logout" class="text-xl">
      <i class="fas fa-sign-out-alt text-[#4B2E0E]"></i>
    </button>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-6 relative flex flex-col min-h-0 min-w-0 md:overflow-hidden">
    <img alt="Background image of coffee beans" aria-hidden="true" class="absolute inset-0 w-full h-full object-cover opacity-20 -z-10" height="800" src="https://storage.googleapis.com/a1aa/image/22cccae8-cc1a-4fb3-7955-287078a4f8d4.jpg" width="1200"/>
    <div class="mb-6 flex items-end justify-between gap-4 flex-wrap">
      <div>
        <h1 class="text-[#4B2E0E] text-2xl font-semibold">Your Transaction Records</h1>
        <p class="text-xs text-gray-500">Welcome back, <?= htmlspecialchars($customer) ?>.</p>
      </div>
      <div class="flex items-center gap-2"></div>
    </div>

    <!-- Summary cards -->
    <div id="summary" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
      <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-xl p-4 text-gray-800 shadow">
        <div class="text-sm text-gray-500">Total Orders</div>
        <div id="sum-orders" class="text-2xl font-bold">0</div>
      </div>
      <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-xl p-4 text-gray-800 shadow">
        <div class="text-sm text-gray-500">Total Spent</div>
        <div id="sum-amount" class="text-2xl font-bold">₱0.00</div>
      </div>
      <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-xl p-4 text-gray-800 shadow">
        <div class="text-sm text-gray-500">Last Order</div>
        <div id="sum-last" class="text-2xl font-bold">—</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-xl p-4 text-gray-800 shadow mb-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-600 mb-1">Search</label>
          <input id="f-search" type="text" placeholder="Search item names or reference no" class="w-full rounded-md border border-gray-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#C4A07A]">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">From</label>
          <input id="f-from" type="date" class="w-full rounded-md border border-gray-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#C4A07A]">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">To</label>
          <div class="flex gap-2">
            <input id="f-to" type="date" class="w-full rounded-md border border-gray-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#C4A07A]">
            <button id="f-clear" class="px-3 py-2 rounded-md border border-gray-300 text-sm hover:bg-gray-50">Clear</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white bg-opacity-90 backdrop-blur-sm rounded-xl shadow">
      <table class="min-w-full text-base text-gray-700">
        <thead>
          <tr class="bg-[#4B2E0E] text-white text-left text-base">
            <th class="p-4 cursor-pointer select-none" data-sort="date">Date</th>
            <th class="p-4">Items</th>
            <th class="p-4 cursor-pointer select-none" data-sort="amount">Total</th>
            <th class="p-4">Reference</th>
            <th class="p-4">Status</th>
            <th class="p-4">Receipt</th>
            <th class="p-4 text-center">Details</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr><td colspan="7" class="p-6 text-center text-gray-500">Loading…</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Pager -->
    <div id="pager" class="mt-4 flex justify-center flex-wrap items-center gap-2 pt-2 border-t border-gray-200"></div>
  </main>

  <script>
    // Data from PHP  
    const RAW = <?= json_encode($transactions ?? []) ?>;

    // Utilities
    const fmtMoney = n => `₱${Number(n||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})}`;
    const dateOnly = s => (s||'').slice(0,10);
    // Lifecycle status badge (Pending -> On Queue; Preparing; Ready; Complete)
    const orderStatusBadge = (status, reason='') => {
      const s = (status||'').toLowerCase();
      if (s === 'preparing') return '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Preparing</span>';
      if (s === 'ready') return '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Ready</span>';
      if (s === 'complete') return '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-800">Completed</span>';
      if (s === 'rejected') {
        const has = (reason||'').trim().length>0;
        const attrs = has? 'data-rejected="1"' : '';
        return `<button ${attrs} class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-300 hover:bg-red-200 transition">Rejected${has?' • View':''}</button>`;
      }
      return '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">On Queue</span>';
    };

    // Helper to remove trailing price e.g., " (₱125.00)"
    const stripItemPrice = (s='') => s.replace(/\s*\(₱[^)]+\)\s*$/,'').trim();

    // Normalize data (keep both raw with price and cleaned without price)
    const DATA = RAW.map(r => {
      const itemsRaw = (r.OrderItems||'').split('; ').filter(Boolean);
      return {
        id: r.OrderID,
        date: r.OrderDate,
        dateOnly: dateOnly(r.OrderDate),
        amount: parseFloat(r.TotalAmount || 0),
        itemsRaw,                      // with price
        items: itemsRaw.map(stripItemPrice), // without price (for preview/search)
        method: r.PaymentMethod || '',
        ref: r.ReferenceNo || '',
        receipt: r.ReceiptPath || '',
        status: r.Status || 'Pending',
        statusUpdatedAt: r.StatusUpdatedAt || null,
        rejectionReason: r.RejectionReason || ''
      };
    });

    // State
  let filtered = [...DATA];
  // Track last seen order lifecycle status to avoid duplicate toasts
  const orderStatusMap = {};
  DATA.forEach(o=>{ orderStatusMap[o.id] = o.status; });
  let page = 1; const pageSize = 15; let sortKey = 'date'; let sortDir = 'desc';

    function applyFilters() {
      const q = document.getElementById('f-search').value.trim().toLowerCase();
      const f = document.getElementById('f-from').value;
      const t = document.getElementById('f-to').value;
      filtered = DATA.filter(o => {
        const inSearch = !q || [o.ref.toLowerCase(), o.items.join(' ').toLowerCase()].some(v => v.includes(q));
        const inFrom = !f || o.dateOnly >= f;
        const inTo = !t || o.dateOnly <= t;
        return inSearch && inFrom && inTo;
      });
      sortData();
      page = 1;
      render();
    }

    function sortData(){
      filtered.sort((a,b)=>{
        let va, vb;
        if (sortKey==='amount'){ va=a.amount; vb=b.amount; }
        else if (sortKey==='id'){ va=a.id; vb=b.id; }
        else { va=new Date(a.date).getTime(); vb=new Date(b.date).getTime(); }
        return sortDir==='asc' ? (va>vb?1:-1) : (va<vb?1:-1);
      });
    }

    function renderSummary(){
      // Count only orders marked Complete
      const completed = filtered.filter(o => (o.status||'').toLowerCase() === 'complete');
      const total = completed.reduce((s,o)=>s+o.amount,0);
      document.getElementById('sum-orders').textContent = completed.length;
      document.getElementById('sum-amount').textContent = fmtMoney(total);
      document.getElementById('sum-last').textContent = completed[0] ? completed[0].dateOnly : '—';
    }

    function render(){
      renderSummary();
      const tbody = document.getElementById('tbody');
      if (!filtered.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="p-6 text-center text-gray-500">No transactions found.</td></tr>`;
        document.getElementById('pager').innerHTML = '';
        return;
      }
      const start = (page-1)*pageSize; const end = start + pageSize;
      const pageData = filtered.slice(start,end);
      tbody.innerHTML = pageData.map(o=>{
        const itemsPreview = o.items.slice(0,2).join('; ') + (o.items.length>2?` (+${o.items.length-2} more)`: '');
        return `
        <tr class="border-b">
          <td class="p-4 align-top">${o.date}</td>
          <td class="p-4 align-top">${itemsPreview || '—'}</td>
          <td class="p-4 align-top font-semibold">${fmtMoney(o.amount)}</td>
          <td class="p-4 align-top">
            <div class="flex items-center gap-2">
              <span>${o.ref || '—'}</span>
              ${o.ref?`<button class="text-xs px-2 py-1 border rounded hover:bg-gray-50" data-copy="${o.ref}">Copy</button>`:''}
            </div>
          </td>
          <td class="p-4 align-top" id="status-cell-${o.id}">${orderStatusBadge(o.status, o.rejectionReason)}</td>
          <td class="p-4 align-top">${o.receipt ? `<button class=\"text-xs px-2 py-1 border rounded hover:bg-gray-50\" data-receipt=\"${o.receipt}\">View</button>` : '—'}</td>
          <td class="p-4 align-top text-center">
            <button class="text-blue-600 hover:underline text-sm" data-expand="${o.id}">View</button>
          </td>
        </tr>
        <tr class="hidden" id="row-${o.id}">
          <td colspan="7" class="bg-gray-50">
            <div class="p-4">
              <div class="font-semibold mb-2">Items</div>
      <ul class="list-disc pl-6 text-sm text-gray-700">${o.itemsRaw.map(it=>`<li>${it}</li>`).join('')}</ul>
            </div>
          </td>
        </tr>`;
      }).join('');

      // Copy buttons
      tbody.querySelectorAll('[data-copy]').forEach(btn=>{
        btn.addEventListener('click', ()=>{ navigator.clipboard.writeText(btn.dataset.copy||''); btn.textContent='Copied'; setTimeout(()=>btn.textContent='Copy', 1200); });
      });
      // Expand buttons
      tbody.querySelectorAll('[data-expand]').forEach(btn=>{
        btn.addEventListener('click', ()=>{
          const id = btn.dataset.expand; const row = document.getElementById(`row-${id}`); if (!row) return; row.classList.toggle('hidden');
        });
      });

      // Receipt preview buttons
      tbody.querySelectorAll('[data-receipt]').forEach(btn=>{
        btn.addEventListener('click', () => {
          const path = btn.getAttribute('data-receipt');
          if(!path) return;
          Swal.fire({
            title: 'Payment Receipt',
            html: `<div style=\"max-height:70vh;overflow:auto\"><img src=\"../${path}\" style=\"max-width:100%;border-radius:12px;box-shadow:0 4px 18px rgba(0,0,0,0.25)\" /></div>`,
            width: 600,
            confirmButtonText: 'Close',
            confirmButtonColor: '#4B2E0E'
          });
        });
      });

      renderPager();
    }

    function renderPager(){
      const pager = document.getElementById('pager');
      const pages = Math.ceil(filtered.length / pageSize);
      if (pages <= 1) { pager.innerHTML=''; return; }
      pager.innerHTML = '';
      const mkBtn = (label, p, opts={}) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = label;
        const disabled = !!opts.disabled;
        const active = !!opts.active;
        btn.disabled = disabled;
        btn.className = 'px-3 py-1 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#4B2E0E] disabled:opacity-50';
        if (active) {
          btn.className += ' bg-[#4B2E0E] text-white cursor-default';
          btn.setAttribute('aria-current','page');
        } else {
          btn.addEventListener('click', () => { if (!disabled) { page = p; render(); } });
        }
        return btn;
      };

      // Prev
      pager.appendChild(mkBtn('Prev', Math.max(1, page-1), { disabled: page===1 }));

      // Windowed numbers with ellipsis similar to Products
      const windowSize = 2;
      const pagesSet = new Set([1, Math.max(1, pages)]);
      for (let i = page - windowSize; i <= page + windowSize; i++) {
        if (i > 1 && i < pages) pagesSet.add(i);
      }
      const list = Array.from(pagesSet).sort((a,b)=>a-b);
      for (let i = 0; i < list.length; i++) {
        const cur = list[i];
        pager.appendChild(mkBtn(String(cur), cur, { active: cur===page }));
        if (i < list.length - 1 && list[i+1] - cur > 1) {
          const span = document.createElement('span');
          span.textContent = '…';
          span.className = 'px-2 text-gray-500 select-none';
          pager.appendChild(span);
        }
      }

      // Next
      pager.appendChild(mkBtn('Next', Math.min(pages, page+1), { disabled: page===pages }));
    }

    function bindSort(){
      document.querySelectorAll('th[data-sort]').forEach(th=>{
        th.addEventListener('click', ()=>{
          const key = th.dataset.sort; sortKey = key; sortDir = (sortDir==='asc'?'desc':'asc');
          sortData(); page=1; render();
        });
      });
    }

    document.addEventListener('DOMContentLoaded', ()=>{
      // Filters
      document.getElementById('f-search').addEventListener('input', applyFilters);
      document.getElementById('f-from').addEventListener('change', applyFilters);
      document.getElementById('f-to').addEventListener('change', applyFilters);
  document.getElementById('f-clear').addEventListener('click', (e)=>{ e.preventDefault(); ['f-search','f-from','f-to'].forEach(id=>document.getElementById(id).value=''); applyFilters(); });

      bindSort();
      applyFilters();

      // Real-time status polling setup
      initStatusRealtime();
    });

    // Logout
    document.getElementById('logout-btn').addEventListener('click', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Are you sure you want to log out?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4B2E0E',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, log out',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "../all/logoutcos";
        }
      });
    });
    const mobileNavToggle = document.getElementById('mobile-nav-toggle');
    const mobileNavPanel = document.getElementById('mobile-nav-panel');
    const mobileNavClose = document.getElementById('mobile-nav-close');
    const mobileNavBackdrop = document.getElementById('mobile-nav-backdrop');
    const logoutBtnMobile = document.getElementById('logout-btn-mobile');
    function closeMobile(){ mobileNavPanel.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
    if(mobileNavToggle){ mobileNavToggle.addEventListener('click', ()=>{ mobileNavPanel.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }); }
    mobileNavClose?.addEventListener('click', closeMobile);
    mobileNavBackdrop?.addEventListener('click', closeMobile);
    if(logoutBtnMobile){ logoutBtnMobile.addEventListener('click', ()=>{ document.getElementById('logout-btn').click(); }); }

  // ================= Real-time Order Status Updates =================
  let lastStatusTs = null;
  function initStatusRealtime(){
    // Seed lastStatusTs with max StatusUpdatedAt from RAW if present
    try {
      const times = (RAW||[]).map(r=>r.StatusUpdatedAt).filter(Boolean).map(t=>new Date(t.replace(' ','T')).getTime());
      if(times.length){ lastStatusTs = new Date(Math.max(...times)); }
    } catch(e) { /* ignore */ }
    setInterval(pollStatus, 4000); // every 4s
  }

  function pollStatus(){
    const params = new URLSearchParams();
    if (lastStatusTs){
      params.append('since_ts', formatMysqlTs(lastStatusTs));
    }
    fetch(`../ajax/get_customer_status.php?${params.toString()}`, {cache:'no-store'})
      .then(r=> r.ok ? r.json() : Promise.reject())
      .then(json => {
        if(!json.success) return;
        if(!Array.isArray(json.data) || !json.data.length) return;
        let maxTs = lastStatusTs ? lastStatusTs.getTime() : 0;
        json.data.forEach(row => {
          const orderId = row.OrderID;
          const status = row.Status || 'Pending';
          const prev = orderStatusMap[orderId];
          const changed = prev !== status;
          // Only toast on actual transition AND only for non-Pending states to prevent noise
          if(changed && (status === 'Preparing' || status === 'Ready' || status === 'Complete' || status === 'Rejected')){
            showStatusToast(orderId, status);
            highlightRow(orderId, status);
          }
          if(changed){ orderStatusMap[orderId] = status; }
          if(changed){
            // Update badge cell
            const cell = document.getElementById(`status-cell-${orderId}`);
            // Update in-memory data for summary calculations
            const rec = DATA.find(o => o.id == orderId);
            if (rec) {
              rec.status = status;
              if (typeof row.RejectionReason !== 'undefined') rec.rejectionReason = row.RejectionReason || '';
            }
            const recF = filtered.find(o => o.id == orderId);
            if (recF) {
              recF.status = status;
              if (typeof row.RejectionReason !== 'undefined') recF.rejectionReason = row.RejectionReason || '';
            }
            if(cell){ cell.innerHTML = orderStatusBadge(status, rec?.rejectionReason || ''); }
            // Recompute summary cards
            renderSummary();
          }
          if(row.StatusUpdatedAt){
            const t = new Date(row.StatusUpdatedAt.replace(' ','T')).getTime();
            if(t>maxTs) maxTs = t;
          }
        });
        if(maxTs){ lastStatusTs = new Date(maxTs); }
      })
      .catch(()=>{});
  }

  function formatMysqlTs(d){
    const pad = n=> n.toString().padStart(2,'0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
  }

  function showStatusToast(orderId, status){
    if (typeof Swal === 'undefined') return;
    let icon='info', title='Order Update', text='Your order status has changed.';
    if(status==='Preparing') { 
      icon='warning';
      title='Preparing';
      text='Your order is now being prepared.';
    } else if(status==='Ready') { 
      icon='success'; 
      title='Order Ready'; 
      text='Your order is ready for pickup.'; 
    } else if(status==='Complete') {
      icon='info';
      title='Order Completed';
      text='Your order has been completed. Thank you!';
    } else if(status==='Rejected') {
      icon='error';
      title='Order Rejected';
      text='Your payment was rejected. Please check your reference or re-order.';
    } else if(status==='Pending') {
      // We normally don't toast Pending, but keep wording consistent if ever used
      icon='info';
      title='On Queue';
      text='Your order is now on queue.';
    }
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon,
      title,
      text,
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  }

  function highlightRow(orderId, status){
    const tbody = document.getElementById('tbody');
    if(!tbody) return;
    // Find the row with data-expand button
    const btn = tbody.querySelector(`[data-expand="${orderId}"]`);
    if(!btn) return;
    const tr = btn.closest('tr');
    if(!tr) return;
    tr.classList.add('ring-2','ring-offset-2');
    if(status==='Preparing') { tr.classList.add('ring-amber-500'); }
    else if(status==='Ready') { tr.classList.add('ring-green-600'); }
  else if(status==='Complete') { tr.classList.add('ring-gray-400'); }
  else if(status==='Rejected') { tr.classList.add('ring-red-500'); }
    setTimeout(()=>{
      tr.classList.remove('ring-2','ring-offset-2','ring-amber-500','ring-green-600','ring-gray-400');
    }, 3500);
  }

  // Show rejection reason when badge clicked (improved UI)
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('button[data-rejected="1"]');
    if (!btn) return;
    const td = btn.closest('td');
    const tr = td?.parentElement;
    const exp = tr?.querySelector('[data-expand]');
    const id = exp?.getAttribute('data-expand');
    const rec = DATA.find(o => String(o.id) === String(id));
    const escapeHtml = (s='') => s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const reason = escapeHtml((rec?.rejectionReason || '').trim());
    const ref = escapeHtml(rec?.ref || '—');
    const updated = escapeHtml(rec?.statusUpdatedAt || '—');
    const body = `
      <div class="text-left">
        <div class="flex items-center gap-2 mb-3">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-700">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </span>
          <span class="text-sm text-red-700 font-semibold">Payment was rejected</span>
        </div>
        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 whitespace-pre-wrap leading-relaxed">
          ${reason || '<span class="text-red-700/80">No reason provided.</span>'}
        </div>
        <div class="mt-3 text-xs text-gray-600 grid grid-cols-1 sm:grid-cols-2 gap-2">
          <div><span class="font-medium text-gray-700">Reference:</span> <span class="select-all">${ref}</span></div>
          <div><span class="font-medium text-gray-700">Updated:</span> ${updated}</div>
        </div>
        <div class="mt-3 flex items-center gap-2">
          <button id="copy-reason" type="button" class="text-xs px-2 py-1 rounded border border-gray-300 hover:bg-gray-50">Copy reason</button>
        </div>
        <div class="mt-3 text-[11px] text-gray-500">
          If you believe this is a mistake, please re-upload the correct payment or contact our staff for assistance.
        </div>
      </div>`;
    Swal.fire({
      title: 'Rejection Reason',
      html: body,
      icon: 'warning',
      confirmButtonText: 'OK',
      confirmButtonColor: '#4B2E0E',
      didOpen: () => {
        const b = document.getElementById('copy-reason');
        if (b) {
          b.addEventListener('click', () => {
            const txt = (rec?.rejectionReason || '').trim();
            if (!txt) return;
            navigator.clipboard.writeText(txt).then(()=>{
              b.textContent = 'Copied';
              setTimeout(()=> b.textContent = 'Copy reason', 1200);
            });
          });
        }
      }
    });
  });
  </script>

</body>
</html>