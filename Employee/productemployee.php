
<?php
session_start();
if (!isset($_SESSION['EmployeeID'])) {
  header('Location: ../all/login.php');
  exit();
}
require_once('../classes/database.php');
$con = new database();
$sweetAlertConfig = "";
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>Product List</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Inter', sans-serif; }
    .pagination-bar {
      position: absolute;
      bottom: 1rem;
      left: 0;
      right: 0;
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 0.5rem;
    }
  </style>
</head>
<body class="bg-[rgba(255,255,255,0.7)] min-h-screen flex">
 
<!-- Sidebar -->
<aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
    <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full mb-4" />
    <?php $current = basename($_SERVER['PHP_SELF']); ?>   

    <button title="Home" onclick="window.location.href='../Employee/employesmain.php'">
        <i class="fas fa-home text-xl <?= $current == 'employesmain.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Cart" onclick="window.location.href='../Employee/employeepage.php'">
        <i class="fas fa-shopping-cart text-xl <?= $current == 'employeepage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Transaction Records" onclick="window.location.href='../all/tranlist.php'">
        <i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Product List" onclick="window.location.href='../Employee/productemployee.php'">
        <i class="fas fa-box text-xl <?= $current == 'productemployee.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Settings" onclick="window.location.href='../all/setting.php'">
        <i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button id="logout-btn" title="Logout">
        <i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i>
    </button>
</aside>
 
<!-- Main Content -->
<main class="flex-1 p-6 relative flex flex-col">
  <header class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-[#4B2E0E] font-semibold text-xl mb-1">Product List</h1>
      <p class="text-xs text-gray-400">Browse available products</p>
    </div>
  </header>
 
  <section class="bg-white rounded-xl p-4 w-full shadow-lg flex-1 overflow-x-auto relative">
    <table id="product-table" class="w-full text-sm">
      <thead>
        <tr class="text-left text-[#4B2E0E] border-b">
          <th class="py-2 px-4 w-[5%]">#</th>
          <th class="py-2 px-4 w-[20%]">Product Name</th>
          <th class="py-2 px-4 w-[15%]">Category</th>
          <th class="py-2 px-4 w-[10%]">Status</th>
          <th class="py-2 px-4 w-[10%]">Unit Price</th>
          <th class="py-2 px-4 w-[15%]">Effective From</th>
          <th class="py-2 px-4 w-[15%]">Effective To</th>
        </tr>
      </thead>
      <tbody id="product-body">
        <?php
        $products = $con->getJoinedProductData();
        usort($products, fn($a, $b) => $a['ProductID'] <=> $b['ProductID']);
        foreach ($products as $product) {
        ?>
        <tr class="border-b hover:bg-gray-50 <?= $product['is_available'] == 0 ? 'bg-red-50 text-gray-500' : '' ?>">
          <td class="py-2 px-4"><?= htmlspecialchars($product['ProductID']) ?></td>
          <td class="py-2 px-4 font-semibold <?= $product['is_available'] == 0 ? 'line-through' : '' ?>"><?= htmlspecialchars($product['ProductName']) ?></td>
          <td class="py-2 px-4"><?= htmlspecialchars($product['ProductCategory']) ?></td>
          <td class="py-2 px-4">
            <?php if ($product['is_available'] == 1): ?>
              <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-600 bg-green-200">Available</span>
            <?php else: ?>
              <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-red-600 bg-red-200">Archived</span>
            <?php endif; ?>
          </td>
          <td class="py-2 px-4">₱<?= number_format($product['UnitPrice'], 2) ?></td>
          <td class="py-2 px-4"><?= htmlspecialchars($product['Effective_From']) ?></td>
          <td class="py-2 px-4"><?= htmlspecialchars((string)($product['Effective_To'] ?? 'N/A')) ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div id="pagination" class="pagination-bar"></div>
  </section>
 
  <?= $sweetAlertConfig ?>
</main>
 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
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
          window.location.href = "../all/logout.php";
        }
      });
    });

    
function paginateTable(containerId, paginationId, rowsPerPage = 15) {
  const tbody = document.getElementById(containerId);
  const pagination = document.getElementById(paginationId);
  const rows = Array.from(tbody.children);
  const pageCount = Math.ceil(rows.length / rowsPerPage);
  let currentPage = 1;
 
  function showPage(page) {
    rows.forEach((row, i) => {
      row.style.display = (i >= (page - 1) * rowsPerPage && i < page * rowsPerPage) ? '' : 'none';
    });
    renderPagination();
  }
 
  function renderPagination() {
    pagination.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.disabled = currentPage === 1;
    prev.onclick = () => { if (currentPage > 1) showPage(--currentPage); };
    pagination.appendChild(prev);
 
    for (let i = 1; i <= pageCount; i++) {
      const btn = document.createElement('button');
      btn.textContent = i;
      btn.className = (i === currentPage ? 'font-bold underline' : '');
      btn.onclick = () => {
        currentPage = i;
        showPage(currentPage);
      };
      pagination.appendChild(btn);
    }
 
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.disabled = currentPage === pageCount;
    next.onclick = () => { if (currentPage < pageCount) showPage(++currentPage); };
    pagination.appendChild(next);
  }
 
  if (pageCount > 1) showPage(currentPage);
}
window.addEventListener('DOMContentLoaded', () => {
  paginateTable('product-body', 'pagination');
});
</script>
 
</body>
</html>
 
