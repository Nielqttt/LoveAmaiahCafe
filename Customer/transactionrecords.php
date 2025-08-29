<?php
session_start();
if (!isset($_SESSION['CustomerID'])) {
  header('Location: ../all/login.php');
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Transaction Records</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex bg-cover bg-center bg-no-repeat" style="background-image: url('../images/LAbg.png');">

  <!-- Sidebar -->
  <aside class="w-16 bg-white bg-opacity-90 backdrop-blur-sm flex flex-col items-center py-6 space-y-8 shadow-lg">
    <img src="../images/logo.png" alt="Logo" class="w-14 h-14 rounded-full mb-6" />
    <button title="Home" onclick="window.location='../Customer/advertisement.php'" class="text-xl">
      <i class="fas fa-home <?= $currentPage === 'advertisement.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Cart" onclick="window.location='../Customer/customerpage.php'" class="text-xl">
      <i class="fas fa-shopping-cart <?= $currentPage === 'customerpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Order List" onclick="window.location='../Customer/transactionrecords.php'" class="text-xl">
      <i class="fas fa-list <?= $currentPage === 'transactionrecords.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Settings" onclick="window.location='../all/setting.php'" class="text-xl">
      <i class="fas fa-cog <?= $currentPage === 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button id="logout-btn" title="Logout" class="text-xl">
      <i class="fas fa-sign-out-alt text-[#4B2E0E]"></i>
    </button>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-6 sm:p-10 text-white bg-black bg-opacity-40 backdrop-blur-sm">
    <div class="mb-6 flex items-end justify-between gap-4 flex-wrap">
      <div>
        <h1 class="text-3xl font-semibold">Your Transaction Records</h1>
        <p class="text-sm text-white/80">Welcome back, <?= htmlspecialchars($customer) ?>.</p>
      </div>
  <div class="flex items-center gap-2"></div>
    </div>

    <!-- Summary cards -->
    <div id="summary" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="bg-white/90 rounded-xl p-4 text-gray-800 shadow">
        <div class="text-sm text-gray-500">Total Orders</div>
        <div id="sum-orders" class="text-2xl font-bold">0</div>
      </div>
      <div class="bg-white/90 rounded-xl p-4 text-gray-800 shadow">
        <div class="text-sm text-gray-500">Total Spent</div>
        <div id="sum-amount" class="text-2xl font-bold">₱0.00</div>
      </div>
      <div class="bg-white/90 rounded-xl p-4 text-gray-800 shadow">
        <div class="text-sm text-gray-500">Last Order</div>
        <div id="sum-last" class="text-2xl font-bold">—</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white/90 rounded-xl p-4 text-gray-800 shadow mb-4">
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
    <div class="overflow-x-auto bg-white/90 rounded-xl shadow">
      <table class="min-w-full text-base text-gray-700">
        <thead>
          <tr class="bg-[#4B2E0E] text-white text-left text-base">
            <th class="p-4 cursor-pointer select-none" data-sort="date">Date</th>
            <th class="p-4">Items</th>
            <th class="p-4 cursor-pointer select-none" data-sort="amount">Total</th>
            <th class="p-4">Reference</th>
            <th class="p-4">Status</th>
            <th class="p-4 text-center">Details</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr><td colspan="6" class="p-6 text-center text-gray-500">Loading…</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Pager -->
    <div id="pager" class="mt-4 flex justify-center items-center gap-2"></div>
  </main>

  <script>
    // Data from PHP  
    const RAW = <?= json_encode($transactions ?? []) ?>;

    // Utilities
    const fmtMoney = n => `₱${Number(n||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})}`;
    const dateOnly = s => (s||'').slice(0,10);
    const statusBadge = (pm, ref) => {
      const paid = !!(pm || ref);
      const color = paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
      const label = paid ? 'Paid' : 'Pending';
      return `<span class="px-2 py-1 rounded-full text-xs font-semibold ${color}">${label}</span>`;
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
        ref: r.ReferenceNo || ''
      };
    });

    // State
    let filtered = [...DATA];
    let page = 1; const pageSize = 8; let sortKey = 'date'; let sortDir = 'desc';

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
      const total = filtered.reduce((s,o)=>s+o.amount,0);
      document.getElementById('sum-orders').textContent = filtered.length;
      document.getElementById('sum-amount').textContent = fmtMoney(total);
      document.getElementById('sum-last').textContent = filtered[0] ? filtered[0].dateOnly : '—';
    }

    function render(){
      renderSummary();
      const tbody = document.getElementById('tbody');
      if (!filtered.length) {
  tbody.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-gray-500">No transactions found.</td></tr>`;
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
          <td class="p-4 align-top">${statusBadge(o.method, o.ref)}</td>
          <td class="p-4 align-top text-center">
            <button class="text-blue-600 hover:underline text-sm" data-expand="${o.id}">View</button>
          </td>
        </tr>
    <tr class="hidden" id="row-${o.id}">
          <td colspan="6" class="bg-gray-50">
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

      renderPager();
    }

    function renderPager(){
      const pager = document.getElementById('pager');
      const pages = Math.ceil(filtered.length / pageSize);
      if (pages <= 1) { pager.innerHTML=''; return; }
      let html = '';
  const btn = (label,p,disabled=false,active=false)=>`<button ${disabled?'disabled':''} data-page="${p}" class="px-4 py-2 rounded border text-base ${active?'bg-[#C4A07A] text-white border-[#C4A07A]':'hover:bg-gray-50'} ${disabled?'opacity-50 cursor-not-allowed':''}">${label}</button>`;
      html += btn('« Prev', Math.max(1,page-1), page===1);
      for(let i=1;i<=pages;i++){ html += btn(i, i, false, i===page); }
      html += btn('Next »', Math.min(pages,page+1), page===pages);
      pager.innerHTML = html;
      pager.querySelectorAll('button[data-page]').forEach(b=>b.addEventListener('click', ()=>{ const p = parseInt(b.dataset.page,10); if (!isNaN(p)) { page = p; render(); } }));
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
          window.location.href = "../all/logoutcos.php";
        }
      });
    });
  </script>

</body>
</html>