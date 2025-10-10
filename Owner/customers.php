<?php
session_start();
if (!isset($_SESSION['OwnerID'])) {
  header('Location: ../all/login');
  exit();
}

require_once('../classes/database.php');
$con = new database();

// Server-side search + pagination (safe defaults)
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$db = $con->opencon();

$where = '';
$params = [];
if ($q !== '') {
  $where = "WHERE (CustomerFN LIKE ? OR CustomerLN LIKE ? OR C_Username LIKE ? OR C_Email LIKE ? OR C_PhoneNumber LIKE ?)";
  $kw = "%" . $q . "%";
  $params = [$kw, $kw, $kw, $kw, $kw];
}

// Total count
$stmtCount = $db->prepare("SELECT COUNT(*) FROM customer " . $where);
$stmtCount->execute($params);
$totalRows = (int)$stmtCount->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $perPage; }

// Fetch page
$sql = "SELECT CustomerID, CustomerFN, CustomerLN, C_Username, C_Email, C_PhoneNumber FROM customer " . $where . " ORDER BY CustomerID DESC LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="../images/logo.png" type="image/png"/>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Customer Accounts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    .la-sidebar { width:70px; min-width:70px; flex:0 0 70px; }
    .la-sidebar img { width:48px; height:48px; }
    .pagination-bar { display:flex; gap:0.5rem; flex-wrap:wrap; justify-content:center; align-items:center; }
  </style>
</head>
<body class="bg-[rgba(255,255,255,0.7)] min-h-screen md:h-screen flex flex-col md:flex-row md:overflow-hidden">
  <!-- Mobile Top Bar -->
  <div class="md:hidden flex items-center justify-between px-4 py-2 bg-white shadow sticky top-0 z-30">
    <div class="flex items-center gap-2">
      <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full" />
      <span class="font-semibold text-[#4B2E0E] text-lg">Customers</span>
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
        <a href="../Owner/dashboard" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='dashboard.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="../Owner/mainpage" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='mainpage.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-home"></i> Home</a>
        <a href="../Owner/page" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='page.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-shopping-cart"></i> Cart</a>
        <a href="../all/tranlist" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='tranlist.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-list"></i> Orders</a>
        <a href="../Owner/product" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='product.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-box"></i> Products</a>
        <a href="../Owner/user" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='user.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-users"></i> Employees</a>
        <a href="../Owner/customers" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='customers.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-user"></i> Customers</a>
        <a href="../all/setting" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $current=='setting.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-cog"></i> Settings</a>
        <button id="logout-btn-mobile" class="flex items-center gap-2 px-3 py-2 rounded-md border border-gray-300 text-[#4B2E0E] text-left"><i class="fas fa-sign-out-alt"></i> Logout</button>
      </nav>
    </div>
  </div>

  <!-- Sidebar (Desktop) -->
  <aside class="hidden md:flex bg-white flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
    <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
    <?php $current = basename($_SERVER['PHP_SELF']); ?>
    <button title="Dashboard" onclick="window.location.href='../Owner/dashboard'">
      <i class="fas fa-chart-line text-xl <?= $current == 'dashboard.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Home" onclick="window.location.href='../Owner/mainpage'">
      <i class="fas fa-home text-xl <?= $current == 'mainpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Cart" onclick="window.location.href='../Owner/page'">
      <i class="fas fa-shopping-cart text-xl <?= $current == 'page.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Order List" onclick="window.location.href='../all/tranlist'">
      <i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Product List" onclick="window.location.href='../Owner/product'">
      <i class="fas fa-box text-xl <?= $current == 'product.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Employees" onclick="window.location.href='../Owner/user'">
      <i class="fas fa-users text-xl <?= $current == 'user.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Customers" onclick="window.location.href='../Owner/customers'">
      <i class="fas fa-user text-xl <?= $current == 'customers.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
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
        <h1 class="text-[#4B2E0E] font-semibold text-xl mb-1">Customer Accounts</h1>
        <p class="text-xs text-gray-400">View and search registered customers</p>
      </div>
      <form method="get" class="flex items-center gap-2" action="customers.php">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search name, username, email, phone" class="w-72 max-w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-[#c19a6b] focus:outline-none" />
        <button class="bg-[#4B2E0E] text-white rounded-full px-4 py-2 text-sm font-semibold hover:bg-[#6b3e14] transition"><i class="fa-solid fa-magnifying-glass mr-1"></i>Search</button>
      </form>
    </header>

    <section class="bg-white rounded-xl p-4 w-full shadow-lg flex-1 overflow-x-auto relative">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-[#4B2E0E] border-b">
            <th class="py-2 px-3 w-[8%]">ID</th>
            <th class="py-2 px-3 w-[20%]">Name</th>
            <th class="py-2 px-3 w-[18%]">Username</th>
            <th class="py-2 px-3 w-[24%]">Email</th>
            <th class="py-2 px-3 w-[18%]">Phone</th>
            <th class="py-2 px-3 w-[12%] text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($customers)): ?>
            <tr><td colspan="6" class="text-center py-6 text-gray-500">No customers found.</td></tr>
          <?php else: ?>
            <?php foreach ($customers as $c): ?>
              <?php
                $cid = (int)$c['CustomerID'];
                $fn = htmlspecialchars($c['CustomerFN']);
                $ln = htmlspecialchars($c['CustomerLN']);
                $un = htmlspecialchars($c['C_Username']);
                $em = htmlspecialchars($c['C_Email']);
                $ph = htmlspecialchars($c['C_PhoneNumber']);
              ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="py-2 px-3 align-top text-gray-700">#<?= $cid ?></td>
                <td class="py-2 px-3 align-top"><?= $fn ?> <?= $ln ?></td>
                <td class="py-2 px-3 align-top"><?= $un ?></td>
                <td class="py-2 px-3 align-top">
                  <span class="inline-flex items-center gap-2">
                    <span><?= $em ?></span>
                    <?php if ($em): ?>
                      <button class="text-xs text-blue-600 hover:underline" onclick="navigator.clipboard.writeText('<?= $em ?>'); Swal.fire('Copied','Email copied to clipboard','success');">Copy</button>
                    <?php endif; ?>
                  </span>
                </td>
                <td class="py-2 px-3 align-top"><?= $ph ?></td>
                <td class="py-2 px-3 align-top text-center">
                  <button class="inline-flex items-center gap-1 text-[#4B2E0E] hover:text-[#6b3e14] font-semibold"
                          onclick="viewCustomerDetails(this)"
                          data-id="<?= $cid ?>"
                          data-fn="<?= $fn ?>"
                          data-ln="<?= $ln ?>"
                          data-un="<?= $un ?>"
                          data-em="<?= $em ?>"
                          data-ph="<?= $ph ?>">
                    <i class="fa-regular fa-id-card"></i> View
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="pagination-bar mt-4">
        <?php
          $base = 'customers.php?';
          if ($q !== '') { $base .= 'q=' . urlencode($q) . '&'; }
        ?>
        <a href="<?= $base . 'page=1' ?>" class="px-3 py-1 rounded border <?= $page==1?'bg-gray-200 text-gray-500':'bg-white text-[#4B2E0E] hover:bg-gray-50' ?>">First</a>
        <a href="<?= $base . 'page=' . max(1,$page-1) ?>" class="px-3 py-1 rounded border <?= $page==1?'bg-gray-200 text-gray-500':'bg-white text-[#4B2E0E] hover:bg-gray-50' ?>">Prev</a>
        <span class="px-3 py-1 text-gray-600">Page <?= $page ?> of <?= $totalPages ?></span>
        <a href="<?= $base . 'page=' . min($totalPages,$page+1) ?>" class="px-3 py-1 rounded border <?= $page==$totalPages?'bg-gray-200 text-gray-500':'bg-white text-[#4B2E0E] hover:bg-gray-50' ?>">Next</a>
        <a href="<?= $base . 'page=' . $totalPages ?>" class="px-3 py-1 rounded border <?= $page==$totalPages?'bg-gray-200 text-gray-500':'bg-white text-[#4B2E0E] hover:bg-gray-50' ?>">Last</a>
      </div>
    </section>
  </main>

  <script>
    // Mobile nav logic
    const mobileNavToggle = document.getElementById('mobile-nav-toggle');
    const mobileNavPanel = document.getElementById('mobile-nav-panel');
    const mobileNavClose = document.getElementById('mobile-nav-close');
    const mobileNavBackdrop = document.getElementById('mobile-nav-backdrop');
    const logoutBtnMobile = document.getElementById('logout-btn-mobile');

    function closeMobile(){ mobileNavPanel.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
    mobileNavToggle?.addEventListener('click', ()=>{ mobileNavPanel.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); });
    mobileNavClose?.addEventListener('click', closeMobile);
    mobileNavBackdrop?.addEventListener('click', closeMobile);

    // Logout button wiring
    document.getElementById('logout-btn')?.addEventListener('click', function(e){
      e.preventDefault();
      Swal.fire({ title:'Are you sure you want to log out?', icon:'warning', showCancelButton:true, confirmButtonColor:'#4B2E0E', cancelButtonColor:'#d33', confirmButtonText:'Yes, log out' })
        .then(res=>{ if(res.isConfirmed){ window.location.href = '../all/logout'; } });
    });
    logoutBtnMobile?.addEventListener('click', ()=>{ document.getElementById('logout-btn')?.click(); });

    // View details
  function escHtml(str){ return str?.replace(/[&<>"]/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]); }) || ''; }
    window.viewCustomerDetails = (btn) => {
      const fn = btn.getAttribute('data-fn') || '';
      const ln = btn.getAttribute('data-ln') || '';
      const un = btn.getAttribute('data-un') || '';
      const em = btn.getAttribute('data-em') || '';
      const ph = btn.getAttribute('data-ph') || '';
      const html = `
        <div class="text-left space-y-2">
          <div><span class="font-semibold text-[#4B2E0E]">Name:</span> ${fn} ${ln}</div>
          <div><span class="font-semibold text-[#4B2E0E]">Username:</span> ${un}</div>
          <div><span class="font-semibold text-[#4B2E0E]">Email:</span> ${em || '-'}</div>
          <div><span class="font-semibold text-[#4B2E0E]">Phone:</span> ${ph || '-'}</div>
        </div>`;
      Swal.fire({ title: 'Customer Details', html, confirmButtonColor:'#4B2E0E' });
    }
  </script>
</body>
</html>
