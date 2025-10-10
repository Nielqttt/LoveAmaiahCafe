    <?php
session_start();
if (!isset($_SESSION['OwnerID'])) {
  header('Location: ../all/login');
  exit();
}

require_once('../classes/database.php');
$con = new database();
// Ensure columns exist (status + verified)
$con->ensureCustomerActive();
$con->ensureCustomerEmailVerified();

// One-time gentle backfill: if column exists and no customers are marked verified yet,
// mark all as verified (historically OTP-gated registration implies verified email).
try {
  $dbProbe = $con->opencon();
  $probe = $dbProbe->query("SELECT COUNT(*) AS total, SUM(CASE WHEN email_verified=1 THEN 1 ELSE 0 END) AS verified FROM customer")->fetch(PDO::FETCH_ASSOC);
  if ($probe && (int)$probe['total'] > 0 && (int)$probe['verified'] === 0) {
    $dbProbe->exec("UPDATE customer SET email_verified = 1");
  }
} catch (Throwable $e) { /* ignore if column missing or perms restricted */ }

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
$sql = "SELECT CustomerID, CustomerFN, CustomerLN, C_Username, C_Email, C_PhoneNumber,
         IFNULL(is_active,1) AS is_active, IFNULL(email_verified,0) AS email_verified
  FROM customer " . $where . " ORDER BY CustomerID DESC LIMIT $perPage OFFSET $offset";
$customers = [];
try {
  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  // Fallback if host does not permit ALTER or column still missing
  $sql2 = "SELECT CustomerID, CustomerFN, CustomerLN, C_Username, C_Email, C_PhoneNumber FROM customer " . $where . " ORDER BY CustomerID DESC LIMIT $perPage OFFSET $offset";
  $stmt2 = $db->prepare($sql2);
  $stmt2->execute($params);
  $customers = $stmt2->fetchAll(PDO::FETCH_ASSOC);
  // Business rule: if account exists (registration flow gated by OTP), treat as Verified when column is absent
  foreach ($customers as &$c) { $c['is_active'] = 1; $c['email_verified'] = 1; }
  unset($c);
}
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
  /* Pagination container (static and consistent with Product page) */
  .pagination-bar { position: static; display:flex; gap:0.5rem; flex-wrap:wrap; justify-content:center; align-items:center; margin-top:1rem; padding-top:.5rem; border-top:1px solid #e5e7eb; }
    /* Thin scrollbar (match tranlist) */
    .thin-scroll::-webkit-scrollbar { width: 6px; }
    .thin-scroll::-webkit-scrollbar-track { background: transparent; }
    .thin-scroll::-webkit-scrollbar-thumb { background: #c19a6b55; border-radius: 9999px; }
    .thin-scroll::-webkit-scrollbar-thumb:hover { background: #c19a6b; }
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
    <header class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h1 class="text-[#4B2E0E] font-semibold text-xl mb-1">Customer Accounts</h1>
        <p class="text-xs text-gray-400">View and search registered customers</p>
      </div>
      <div class="flex flex-col sm:flex-row gap-3 sm:items-center w-full md:w-auto">
        <form method="get" action="customers.php" class="relative w-full sm:w-72">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#4B2E0E]/60"><i class="fas fa-search"></i></span>
          <input id="customerSearch" name="q" type="text" autocomplete="off" value="<?= htmlspecialchars($q) ?>" class="w-full pl-9 pr-9 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#c19a6b]/60 bg-white placeholder:text-gray-400 text-sm" placeholder="Search (name, username, email, phone)" />
          <button type="button" id="clearCustomerSearch" class="<?= $q === '' ? 'hidden' : '' ?> absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#4B2E0E] transition" title="Clear search"><i class="fas fa-times-circle"></i></button>
        </form>
        <div class="text-[11px] sm:text-xs text-gray-500">Press Enter to search</div>
      </div>
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
            <th class="py-2 px-3 w-[12%]">Verified Status</th>
            <th class="py-2 px-3 w-[10%] text-center">History</th>
            <th class="py-2 px-3 w-[12%] text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($customers)): ?>
            <tr><td colspan="8" class="text-center py-6 text-gray-500">No customers found.</td></tr>
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
              <tr class="border-b hover:bg-gray-50 <?= (isset($c['is_active']) && (int)$c['is_active'] == 0) ? 'bg-red-50 text-gray-500' : '' ?>">
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
                <td class="py-2 px-3 align-top">
                  <?php if (!isset($c['email_verified']) || (int)$c['email_verified'] == 0): ?>
                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-gray-700 bg-gray-200">Not Verified</span>
                  <?php else: ?>
                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-700 bg-green-200">Verified</span>
                  <?php endif; ?>
                </td>
                <td class="py-2 px-3 align-top text-center">
                  <button type="button" class="text-blue-600 hover:underline text-sm view-history-btn" data-id="<?= $cid ?>" data-name="<?= htmlspecialchars($fn . ' ' . $ln, ENT_QUOTES) ?>" title="View Order History">View</button>
                </td>
                <td class="py-2 px-3 align-top text-center">
                  <?php if (!isset($c['is_active']) || (int)$c['is_active'] == 1): ?>
                    <button class="text-red-600 hover:underline text-lg archive-customer-btn ml-3" title="Archive"
                            data-id="<?= $cid ?>" data-name="<?= $fn . ' ' . $ln ?>">
                      <i class="fas fa-archive"></i>
                    </button>
                  <?php else: ?>
                    <button class="text-green-600 hover:underline text-lg restore-customer-btn ml-3" title="Restore"
                            data-id="<?= $cid ?>" data-name="<?= $fn . ' ' . $ln ?>">
                      <i class="fas fa-undo"></i>
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Pagination (Product-style with numbered pages and ellipses) -->
      <div class="pagination-bar mt-4" role="navigation" aria-label="Table pagination">
        <?php
          $base = 'customers.php?';
          if ($q !== '') { $base .= 'q=' . urlencode($q) . '&'; }
          $makeBtn = function($label,$targetPage,$disabled=false,$current=false,$aria=null) use ($base) {
            $classes = 'px-3 py-1 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#4B2E0E] disabled:opacity-50 ';
            if ($disabled) $classes .= ' bg-gray-200 text-gray-500';
            else if ($current) $classes .= ' bg-[#4B2E0E] text-white cursor-default';
            else $classes .= ' bg-white text-[#4B2E0E] hover:bg-gray-50';
            $href = htmlspecialchars($base . 'page=' . max(1,$targetPage));
            $ariaAttr = $aria ? ' aria-label="'.htmlspecialchars($aria).'"' : '';
            $ariaCurrent = $current ? ' aria-current="page"' : '';
            $disabledAttr = $disabled ? ' aria-disabled="true"' : '';
            return '<a href="'.$href.'" class="'.$classes.'"'.$ariaAttr.$ariaCurrent.$disabledAttr.'>'.$label.'</a>';
          };

          // Prev
          echo $makeBtn('Prev', $page-1, $page<=1, false, 'Previous page');

          // Page numbers (windowed with ellipses like product.php)
          if ($totalPages <= 7) {
            for ($i=1; $i<=$totalPages; $i++) {
              echo $makeBtn((string)$i, $i, false, $i==$page, 'Page '.$i);
            }
          } else {
            $windowSize = 2;
            $pages = [1, $totalPages];
            for ($i = $page - $windowSize; $i <= $page + $windowSize; $i++) {
              if ($i > 1 && $i < $totalPages) $pages[] = $i;
            }
            $pages = array_values(array_unique($pages));
            sort($pages);
            for ($i = 0; $i < count($pages); $i++) {
              $p = $pages[$i];
              echo $makeBtn((string)$p, $p, false, $p==$page, 'Page '.$p);
              if ($i < count($pages)-1 && $pages[$i+1] - $p > 1) {
                echo '<span class="px-2 text-gray-500 select-none">…</span>';
              }
            }
          }

          // Next
          echo $makeBtn('Next', $page+1, $page>=$totalPages, false, 'Next page');
        ?>
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

    // View details removed by request

    // Archive Customer
    document.querySelectorAll('.archive-customer-btn').forEach(button => {
      button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name') || 'this customer';
        Swal.fire({
          title: 'Archive customer?',
          text: `You are about to archive "${name}". They will not be able to log in.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#4B2E0E',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, archive',
        }).then((result) => {
          if(result.isConfirmed){
            const formData = new FormData();
            formData.append('customer_id', id);
            fetch('archive_customer.php', { method: 'POST', body: formData })
              .then(r => r.json())
              .then(data => {
                if (data.success) {
                  Swal.fire('Archived!', `${name} has been archived.`, 'success').then(()=> window.location.reload());
                } else {
                  Swal.fire('Error', data.message || 'Failed to archive customer.', 'error');
                }
              })
              .catch(() => Swal.fire('Error','Request failed.','error'));
          }
        });
      });
    });

    // Restore Customer
    document.querySelectorAll('.restore-customer-btn').forEach(button => {
      button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name') || 'this customer';
        Swal.fire({
          title: 'Restore customer?',
          text: `You are about to restore "${name}". They will be able to log in again.`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#4B2E0E',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, restore',
        }).then((result) => {
          if(result.isConfirmed){
            const formData = new FormData();
            formData.append('customer_id', id);
            fetch('restore_customer.php', { method: 'POST', body: formData })
              .then(r => r.json())
              .then(data => {
                if (data.success) {
                  Swal.fire('Restored!', `${name} has been restored.`, 'success').then(()=> window.location.reload());
                } else {
                  Swal.fire('Error', data.message || 'Failed to restore customer.', 'error');
                }
              })
              .catch(() => Swal.fire('Error','Request failed.','error'));
          }
        });
      });
    });

    // Slide-over: Order History (initialized globally)
    (function(){
      // Create panel once
      const panel = document.createElement('div');
      panel.id = 'history-panel';
      panel.className = 'fixed inset-0 z-50 hidden';
      panel.innerHTML = `
        <div id="history-backdrop" class="absolute inset-0 bg-black/40" aria-hidden="true"></div>
        <aside class="absolute right-0 top-0 h-full w-full sm:w-[520px] bg-white shadow-xl p-4 sm:p-6 flex flex-col" role="dialog" aria-modal="true" aria-labelledby="history-title">
          <div class="flex items-start justify-between mb-3">
            <div>
              <h2 id="history-title" class="text-lg font-bold text-[#4B2E0E]">Order History</h2>
              <p id="history-subtitle" class="text-xs text-gray-500"></p>
            </div>
            <button id="history-close" class="text-gray-500 text-xl" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
          </div>
          <div id="history-content" class="flex-1 overflow-y-auto thin-scroll">
            <div class="text-sm text-gray-500">Select a customer to load history…</div>
          </div>
        </aside>`;
      document.body.appendChild(panel);

      const backdrop = panel.querySelector('#history-backdrop');
      const closeBtn  = panel.querySelector('#history-close');
      const content   = panel.querySelector('#history-content');
      const subtitle  = panel.querySelector('#history-subtitle');

      function openPanel(name){
        panel.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        subtitle.textContent = name || '';
      }
      function closePanel(){
        panel.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      }
      backdrop.addEventListener('click', closePanel);
      closeBtn.addEventListener('click', closePanel);
      document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closePanel(); });

      function renderOrders(orders){
        if (!orders || !orders.length) {
          content.innerHTML = '<div class="text-sm text-gray-500">No orders found for this customer.</div>';
          return;
        }
        const rows = orders.map(o => {
          const date = o.OrderDate ? new Date(o.OrderDate.replace(' ', 'T')) : null;
          const dateStr = date ? date.toLocaleString() : '';
          const items = (o.OrderItems || '').split('; ').map(s=>`<li>${s.replace(/</g,'&lt;')}</li>`).join('');
          const status = (o.Status||'Pending');
          const statusClass = status==='Complete' ? 'bg-gray-200 text-gray-700' : (status==='Ready' ? 'bg-green-200 text-green-700' : (status==='Preparing' ? 'bg-amber-200 text-amber-700' : 'bg-blue-200 text-blue-700'));
          return `
            <div class="border border-gray-200 rounded-lg p-3 mb-3">
              <div class="flex items-center justify-between gap-2">
                <div class="text-sm font-semibold text-[#4B2E0E]">Order #${o.OrderID}</div>
                <span class="text-[11px] px-2 py-0.5 rounded-full ${statusClass}">${status}</span>
              </div>
              <div class="text-xs text-gray-600">${dateStr}</div>
              <ul class="text-sm text-gray-700 list-disc list-inside mt-2">${items}</ul>
              <div class="text-xs text-gray-600 mt-2">Total: ₱${(Number(o.TotalAmount)||0).toFixed(2)} • ${o.PaymentMethod||'—'} ${o.ReferenceNo?('• Ref: '+o.ReferenceNo):''}</div>
            </div>`;
        }).join('');
        content.innerHTML = rows;
      }

      // Event delegation for reliability
      document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.view-history-btn');
        if (!btn) return;
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name') || '';
        openPanel(name);
        content.innerHTML = '<div class="text-sm text-gray-500">Loading…</div>';
        try {
          const resp = await fetch('get_customer_orders.php?customer_id=' + encodeURIComponent(id));
          const data = await resp.json();
          if (data && data.success) renderOrders(data.orders||[]); else content.innerHTML = '<div class="text-sm text-red-600">Failed to load orders.</div>';
        } catch(e){ content.innerHTML = '<div class="text-sm text-red-600">Network error loading orders.</div>'; }
      });
    })();
  </script>
  <script>
    // Clear search (mobile-friendly) — mirrors tranlist behavior
    document.getElementById('clearCustomerSearch')?.addEventListener('click', function(e){
      e.preventDefault();
      window.location.href = 'customers.php';
    });
  </script>
</body>
</html>
