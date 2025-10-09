
<?php
session_start();
  if (!isset($_SESSION['EmployeeID'])) {
    header('Location: ../all/login');
  exit();
}
require_once('../classes/database.php');
$con = new database();
$sweetAlertConfig = "";
// For image thumbnails (read-only)
$webUploadDir = '../uploads/';
$placeholderImage = 'placeholder.png';
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="../images/logo.png" type="image/png"/>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>Product List</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Inter', sans-serif; }
    /* Fixed sidebar width consistent with other pages */
    .la-sidebar { width:70px; min-width:70px; flex:0 0 70px; }
    .la-sidebar img { width:48px; height:48px; }
    /* Pagination container (static, accessible) */
    .pagination-bar {
      position: static;
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-top: 1rem;
      padding-top: .5rem;
      border-top: 1px solid #e5e7eb;
    }
  .product-img-thumb { width: 64px; height: 64px; object-fit: cover; border-radius: 6px; }
  </style>
</head>
<body class="bg-[rgba(255,255,255,0.7)] min-h-screen md:h-screen flex flex-col md:flex-row md:overflow-hidden">
  <!-- Mobile Top Bar -->
  <div class="md:hidden flex items-center justify-between px-4 py-2 bg-white shadow sticky top-0 z-30">
    <div class="flex items-center gap-2">
      <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full" />
      <span class="font-semibold text-[#4B2E0E] text-lg">Products</span>
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
      <?php $current = basename($_SERVER['PHP_SELF']); ?>
      <nav class="flex flex-col gap-2 text-sm">
        <a href="../Employee/employesmain" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='employesmain.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-home"></i> Home</a>
        <a href="../Employee/employeepage" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='employeepage.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-shopping-cart"></i> Cart</a>
        <a href="../all/tranlist" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='tranlist.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-list"></i> Orders</a>
        <a href="../Employee/productemployee" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='productemployee.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-box"></i> Products</a>
        <a href="../all/setting" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='setting.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-cog"></i> Settings</a>
        <button id="logout-btn-mobile" class="flex items-center gap-2 px-3 py-2 rounded-md border border-gray-300 text-[#4B2E0E] text-left"><i class="fas fa-sign-out-alt"></i> Logout</button>
      </nav>
    </div>
  </div>

<!-- Sidebar -->
<aside class="hidden md:flex bg-white flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
  <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
    <?php $current = basename($_SERVER['PHP_SELF']); ?>   

  <button title="Home" onclick="window.location.href='../Employee/employesmain'">
        <i class="fas fa-home text-xl <?= $current == 'employesmain.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
  <button title="Cart" onclick="window.location.href='../Employee/employeepage'">
        <i class="fas fa-shopping-cart text-xl <?= $current == 'employeepage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
  <button title="Transaction Records" onclick="window.location.href='../all/tranlist'">
        <i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
  <button title="Product List" onclick="window.location.href='../Employee/productemployee'">
        <i class="fas fa-box text-xl <?= $current == 'productemployee.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
  <button title="Settings" onclick="window.location.href='../all/setting'">
        <i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button id="logout-btn" title="Logout">
        <i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i>
    </button>
</aside>
 
<!-- Main Content -->
<main class="flex-1 p-6 relative flex flex-col min-w-0">
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
          <th class="py-2 px-4 w-[15%]">Image</th>
          <th class="py-2 px-4 w-[18%]">Product Name</th>
          <th class="py-2 px-4 w-[13%]">Category</th>
          <th class="py-2 px-4 w-[15%]">Allergens</th>
          <th class="py-2 px-4 w-[10%]">Status</th>
          <th class="py-2 px-4 w-[10%]">Unit Price</th>
          <th class="py-2 px-4 w-[10%]">Effective From</th>
          <th class="py-2 px-4 w-[10%]">Effective To</th>
        </tr>
      </thead>
      <tbody id="product-body">
        <?php
        $products = $con->getJoinedProductData();
        usort($products, fn($a, $b) => $a['ProductID'] <=> $b['ProductID']);
        foreach ($products as $product) {
          // fetch ImagePath for this product (joined data doesn't include ImagePath)
          $imagePath = $placeholderImage;
          try {
            $db = $con->opencon();
            $stmt = $db->prepare("SELECT ImagePath FROM product WHERE ProductID = ?");
            $stmt->execute([$product['ProductID']]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($r && !empty($r['ImagePath'])) $imagePath = $r['ImagePath'];
          } catch (Exception $e) {
            $imagePath = $placeholderImage;
          }
        ?>
        <tr class="border-b hover:bg-gray-50 <?= $product['is_available'] == 0 ? 'bg-red-50 text-gray-500' : '' ?>">
          <td class="py-2 px-4"><?= htmlspecialchars($product['ProductID']) ?></td>
          <td class="py-2 px-4"><img src="<?= htmlspecialchars($webUploadDir . $imagePath) ?>" alt="product" class="product-img-thumb"></td>
          <td class="py-2 px-4 font-semibold <?= $product['is_available'] == 0 ? 'line-through' : '' ?>"><?= htmlspecialchars($product['ProductName']) ?></td>
          <td class="py-2 px-4"><?= htmlspecialchars($product['ProductCategory']) ?></td>
          <td class="py-2 px-4 text-xs"><?= htmlspecialchars($product['Allergen'] ?? 'None') ?></td>
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
  <div id="pagination" class="pagination-bar" role="navigation" aria-label="Table pagination"></div>
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
          window.location.href = "../all/logout";
        }
      });
    });

    
function paginateTable(containerId, paginationId, rowsPerPage = 15) {
  const tbody = document.getElementById(containerId);
  const pagination = document.getElementById(paginationId);
  if (!tbody || !pagination) return;
  const rows = Array.from(tbody.children).filter(r => r.tagName === 'TR');
  const pageCount = Math.max(1, Math.ceil(rows.length / rowsPerPage));
  let currentPage = 1;
  const tableSection = pagination.closest('section');

  function showPage(page) {
    if (page < 1) page = 1;
    if (page > pageCount) page = pageCount;
    currentPage = page;
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    rows.forEach((row, i) => {
      row.style.display = (i >= start && i < end) ? '' : 'none';
    });
    renderPagination();
    if (tableSection) { tableSection.scrollTo({ top: 0, behavior: 'smooth' }); }
  }

  function createButton(label, onClick, options = {}) {
    const { disabled = false, current = false, ariaLabel } = options;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = label;
    btn.disabled = disabled;
    btn.className = 'px-3 py-1 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#4B2E0E] disabled:opacity-50';
    if (current) { btn.className += ' bg-[#4B2E0E] text-white cursor-default'; btn.setAttribute('aria-current','page'); }
    if (ariaLabel) btn.setAttribute('aria-label', ariaLabel);
    btn.addEventListener('click', (e)=>{ e.preventDefault(); if(!disabled && !current) onClick(); });
    return btn;
  }

  function renderPagination() {
    pagination.innerHTML = '';
    if (pageCount <= 1) return;
    pagination.appendChild(createButton('Prev', () => showPage(currentPage - 1), { disabled: currentPage === 1, ariaLabel: 'Previous page' }));
    const buttons = [];
    if (pageCount <= 7) {
      for (let i=1;i<=pageCount;i++) buttons.push(i);
    } else {
      const windowSize = 2;
      const pages = new Set([1, pageCount]);
      for (let i=currentPage-windowSize; i<=currentPage+windowSize; i++) {
        if (i>1 && i<pageCount) pages.add(i);
      }
      const sorted = Array.from(pages).sort((a,b)=>a-b);
      for (let i=0;i<sorted.length;i++) {
        buttons.push(sorted[i]);
        if (i < sorted.length-1 && sorted[i+1]-sorted[i] > 1) buttons.push('ellipsis');
      }
    }
    buttons.forEach(p => {
      if (p === 'ellipsis') {
        const span = document.createElement('span'); span.textContent='…'; span.className='px-2 text-gray-500 select-none'; pagination.appendChild(span);
      } else {
        pagination.appendChild(createButton(String(p), () => showPage(p), { current: p===currentPage, ariaLabel: 'Page '+p }));
      }
    });
    pagination.appendChild(createButton('Next', () => showPage(currentPage + 1), { disabled: currentPage === pageCount, ariaLabel: 'Next page' }));
  }

  showPage(1);
}
window.addEventListener('DOMContentLoaded', () => {
  paginateTable('product-body', 'pagination');
});

// Mobile navigation handlers
const mobileNavToggle = document.getElementById('mobile-nav-toggle');
const mobileNavPanel = document.getElementById('mobile-nav-panel');
const mobileNavClose = document.getElementById('mobile-nav-close');
const mobileNavBackdrop = document.getElementById('mobile-nav-backdrop');
const logoutBtnMobile = document.getElementById('logout-btn-mobile');
function closeMobileNav(){ mobileNavPanel?.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
if(mobileNavToggle){ mobileNavToggle.addEventListener('click', ()=>{ mobileNavPanel.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }); }
mobileNavClose?.addEventListener('click', closeMobileNav);
mobileNavBackdrop?.addEventListener('click', closeMobileNav);
if(logoutBtnMobile){ logoutBtnMobile.addEventListener('click', ()=>{ document.getElementById('logout-btn').click(); }); }
</script>
 
</body>
<!-- Removed collapsible sidebar script per user request -->
</html>
 
