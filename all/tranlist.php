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
  // Archive completed or rejected orders by not including them in active lists
  $st = $transaction['Status'] ?? '';
  if ($st === 'Complete' || $st === 'Rejected') { continue; }
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
  <link rel="icon" href="../images/logo.png" type="image/png"/>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Transaction Records</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../assets/js/order-badge.js"></script>
  <style>
    body { font-family: 'Inter', sans-serif; background: url('../images/LAbg.png') no-repeat center center fixed; background-size: cover; }
    /* Match page.php fixed sidebar sizing */
    .la-sidebar { width:70px; min-width:70px; flex:0 0 70px; }
    .la-sidebar img { width:48px; height:48px; }
    @media (max-width:767px){ body.nav-open { overflow:hidden; } }
    /* White veil overlay similar to settings page for better contrast */
    .main-content { flex-grow: 1; padding: 1rem; position: relative; display: flex; flex-direction: column; background: rgba(255,255,255,0.92); backdrop-filter: blur(4px); }
    .main-content .bg-image { display:none; }
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
  .status-complete { background:#e5e7eb; color:#374151; }
  .status-rejected { background:#fee2e2; color:#991b1b; }
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
    /* Numeric badge for new orders */
    .notif-badge { position:absolute; top:-6px; right:-6px; min-width:18px; height:18px; padding:0 4px; display:none; align-items:center; justify-content:center; background:#ef4444; color:#fff; border-radius:9999px; font-size:11px; font-weight:700; line-height:1; box-shadow:0 0 0 2px #fff; }
  </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row">
<!-- Mobile Top Bar -->
<div class="md:hidden flex items-center justify-between px-4 py-2 bg-white/90 backdrop-blur-sm shadow sticky top-0 z-40">
  <div class="flex items-center gap-2">
    <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full" />
    <span class="font-semibold text-[#4B2E0E] text-lg">Transactions</span>
  </div>
  <button id="mobile-nav-toggle" class="p-2 rounded-full border border-[#4B2E0E] text-[#4B2E0E]" aria-label="Open navigation">
    <i class="fa-solid fa-bars"></i>
  </button>
</div>

<!-- Mobile Slide-over Nav -->
<div id="mobile-nav-panel" class="md:hidden fixed inset-0 z-50 hidden" aria-hidden="true">
  <div class="absolute inset-0 bg-black/40" id="mobile-nav-backdrop"></div>
  <div class="absolute left-0 top-0 h-full w-72 bg-white shadow-lg p-4 flex flex-col gap-4 overflow-y-auto" role="dialog" aria-modal="true" aria-label="Navigation Menu">
    <div class="flex justify-between items-center mb-2">
      <h2 class="text-[#4B2E0E] font-semibold">Navigation</h2>
      <button id="mobile-nav-close" class="text-gray-500 text-xl" aria-label="Close navigation"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <?php $current = basename($_SERVER['PHP_SELF']); $isOwner = $loggedInUserType==='owner'; ?>
    <nav class="flex flex-col gap-2 text-base" role="navigation">
      <?php if($isOwner): ?>
        <a href="../Owner/dashboard.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='dashboard.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="../Owner/mainpage.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='mainpage.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-home"></i> Home</a>
        <a href="../Owner/page.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='page.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-shopping-cart"></i> Cart</a>
        <a href="../all/tranlist.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='tranlist.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-list"></i> Orders</a>
  <a href="../Owner/product.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='product.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-box"></i> Products</a>
  <a href="../Owner/user.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='user.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-users"></i> Employees</a>
  <a href="../Owner/customers.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='customers.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-user"></i> Customers</a>
        <a href="../all/setting.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='setting.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-cog"></i> Settings</a>
      <?php else: ?>
        <a href="../Employee/employesmain.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='employesmain.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-home"></i> Home</a>
        <a href="../Employee/employeepage.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='employeepage.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-shopping-cart"></i> Cart</a>
        <a href="../all/tranlist.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='tranlist.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-list"></i> Orders</a>
        <a href="../Employee/productemployee.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='productemployee.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-box"></i> Products</a>
        <a href="../all/setting.php" class="flex items-center gap-2 px-4 py-3 rounded-md border <?php echo $current=='setting.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-cog"></i> Settings</a>
      <?php endif; ?>
      <button id="logout-btn-mobile" class="flex items-center gap-2 px-3 py-2 rounded-md border border-gray-300 text-[#4B2E0E] text-left"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </nav>
  </div>
</div>
<?php if ($loggedInUserType == 'owner'): ?>
  <aside class="hidden md:flex bg-white flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
      <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
      <?php $current = basename($_SERVER['PHP_SELF']); ?>   
      <button title="Dashboard" onclick="window.location.href='../Owner/dashboard.php'"><i class="fas fa-chart-line text-xl <?= $current == 'dashboard.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Home" onclick="window.location.href='../Owner/mainpage.php'"><i class="fas fa-home text-xl <?= $current == 'mainpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
  <button id="orders-icon-owner" class="relative" title="Orders" onclick="window.location.href='../Owner/page.php'"><i class="fas fa-shopping-cart text-xl <?= $current == 'page.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button id="orderlist-icon-owner" class="relative" title="Order List" onclick="window.location.href='../all/tranlist.php'"><span class="notif-dot"></span><span class="notif-badge" id="orders-badge-owner" aria-hidden="true"></span><i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button title="Inventory" onclick="window.location.href='../Owner/product.php'"><i class="fas fa-box text-xl <?= $current == 'product.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
  <button title="Users" onclick="window.location.href='../Owner/user.php'"><i class="fas fa-users text-xl <?= $current == 'user.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
  <button title="Customers" onclick="window.location.href='../Owner/customers.php'"><i class="fas fa-user text-xl <?= $current == 'customers.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button title="Settings" onclick="window.location.href='../all/setting.php'"><i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button id="logout-btn" title="Logout"><i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i></button>
    </aside>
<?php elseif ($loggedInUserType == 'employee'): ?>
  <aside class="hidden md:flex bg-white flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
      <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
      <?php $current = basename($_SERVER['PHP_SELF']); ?>   
    <button title="Home" onclick="window.location.href='../Employee/employesmain.php'"><i class="fas fa-home text-xl <?= $current == 'employesmain.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
  <button id="orders-icon-emp" class="relative" title="Cart" onclick="window.location.href='../Employee/employeepage.php'"><i class="fas fa-shopping-cart text-xl <?= $current == 'employeepage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button id="orderlist-icon-emp" class="relative" title="Transaction Records" onclick="window.location.href='../all/tranlist.php'"><span class="notif-dot"></span><span class="notif-badge" id="orders-badge-emp" aria-hidden="true"></span><i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button title="Box" onclick="window.location.href='../Employee/productemployee.php'"><i class="fas fa-box text-xl <?= $current == 'productemployee.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button title="Settings" onclick="window.location.href='../all/setting.php'"><i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
      <button id="logout-btn" title="Logout"><i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i></button>
    </aside>
<?php endif; ?>

<div class="main-content">
  <img src="../images/Labg.png" alt="Background image" class="bg-image" />
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
        <!-- Notification controls -->
        <div class="flex items-center gap-2 sm:ml-2">
          <button id="enable-desktop-notifs" class="px-2 py-1 rounded-md border border-[#4B2E0E]/40 text-[#4B2E0E] text-xs hidden"><i class="fa-regular fa-bell mr-1"></i>Enable desktop notif</button>
          <label class="flex items-center gap-1 text-xs text-[#4B2E0E] bg-[#4B2E0E]/10 border border-[#4B2E0E]/20 rounded-md px-2 py-1">
            <input id="sound-toggle" type="checkbox" class="accent-[#4B2E0E]" />
            <span class="whitespace-nowrap"><i class="fa-solid fa-volume-high mr-1"></i>Sound</span>
          </label>
        </div>
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
                <div class="flex flex-col gap-2 items-end sm:flex-row sm:flex-wrap sm:justify-end">
                  <?php if (!empty($transaction['ReceiptPath'])): ?>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg text-xs shadow transition view-receipt-btn" data-img="../<?= htmlspecialchars($transaction['ReceiptPath']) ?>" title="View Payment Proof"><i class="fas fa-image mr-1"></i>Receipt</button>
                  <?php endif; ?>
                  <button class="bg-[#4B2E0E] hover:bg-[#3a240c] text-white px-3 py-1 rounded-lg text-xs shadow transition" data-id="<?= $transaction['OrderID'] ?>" data-status="Preparing Order"><i class="fas fa-utensils mr-1"></i>Prepare</button>
                  <button class="bg-green-700 hover:bg-green-800 text-white px-3 py-1 rounded-lg text-xs shadow transition" data-id="<?= $transaction['OrderID'] ?>" data-status="Order Ready"><i class="fas fa-check-circle mr-1"></i>Ready</button>
                  <button class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded-lg text-xs shadow transition <?php if ((($transaction['Status'] ?? 'Pending')) !== 'Ready') echo 'hidden'; ?>" data-id="<?= $transaction['OrderID'] ?>" data-status="Complete"><i class="fas fa-box-archive mr-1"></i>Complete</button>
                  <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-xs shadow transition" data-id="<?= $transaction['OrderID'] ?>" data-status="Rejected" title="Reject payment / cancel order"><i class="fas fa-ban mr-1"></i>Reject</button>
                </div>
              </div>
            </div>
            <div class="text-right text-[11px] text-gray-500 mt-1">Ref: <?= htmlspecialchars($transaction['ReferenceNo'] ?? 'N/A') ?></div>
            <?php if (!empty($transaction['PickupAt'])): ?>
              <div class="mt-1 flex justify-end">
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                  <i class="fa-regular fa-clock"></i>
                  Pickup: <?= htmlspecialchars(date('M d, Y H:i', strtotime($transaction['PickupAt']))) ?>
                </span>
              </div>
            <?php endif; ?>
            <?php
                $statusRaw = $transaction['Status'] ?? 'Pending';
                $statusLabel = 'Pending';
                $statusClass = 'status-line status-pending';
        if ($statusRaw === 'Preparing') { $statusLabel = 'Preparing Order'; $statusClass = 'status-line status-preparing'; }
        elseif ($statusRaw === 'Ready') { $statusLabel = 'Order Ready'; $statusClass = 'status-line status-ready'; }
  elseif ($statusRaw === 'Complete') { $statusLabel = 'Complete'; $statusClass = 'status-line status-complete'; }
  elseif ($statusRaw === 'Rejected') { $statusLabel = 'Rejected'; $statusClass = 'status-line status-rejected'; }
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
                <div class="flex flex-col gap-2 items-end sm:flex-row sm:flex-wrap sm:justify-end">
                  <button class="bg-[#4B2E0E] hover:bg-[#3a240c] text-white px-3 py-1 rounded-lg text-xs shadow transition" data-id="<?= $transaction['OrderID']; ?>" data-status="Preparing Order"><i class="fas fa-utensils mr-1"></i>Prepare</button>
                  <button class="bg-green-700 hover:bg-green-800 text-white px-3 py-1 rounded-lg text-xs shadow transition" data-id="<?= $transaction['OrderID']; ?>" data-status="Order Ready"><i class="fas fa-check-circle mr-1"></i>Ready</button>
                  <button class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded-lg text-xs shadow transition <?php if ((($transaction['Status'] ?? 'Pending')) !== 'Ready') echo 'hidden'; ?>" data-id="<?= $transaction['OrderID']; ?>" data-status="Complete"><i class="fas fa-box-archive mr-1"></i>Complete</button>
                </div>
              </div>
            </div>
            <div class="text-right text-[11px] text-gray-500 mt-1">Ref: <?= htmlspecialchars($transaction['ReferenceNo'] ?? 'N/A') ?></div>
            <?php if (!empty($transaction['PickupAt'])): ?>
              <div class="mt-1 flex justify-end">
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                  <i class="fa-regular fa-clock"></i>
                  Pickup: <?= htmlspecialchars(date('M d, Y H:i', strtotime($transaction['PickupAt']))) ?>
                </span>
              </div>
            <?php endif; ?>
            <?php
                $statusRaw = $transaction['Status'] ?? 'Pending';
                $statusLabel = 'Pending';
                $statusClass = 'status-line status-pending';
        if ($statusRaw === 'Preparing') { $statusLabel = 'Preparing Order'; $statusClass = 'status-line status-preparing'; }
        elseif ($statusRaw === 'Ready') { $statusLabel = 'Order Ready'; $statusClass = 'status-line status-ready'; }
  elseif ($statusRaw === 'Complete') { $statusLabel = 'Complete'; $statusClass = 'status-line status-complete'; }
  elseif ($statusRaw === 'Rejected') { $statusLabel = 'Rejected'; $statusClass = 'status-line status-rejected'; }
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
// Mobile navigation logic
const mobileNavToggle = document.getElementById('mobile-nav-toggle');
const mobileNavPanel  = document.getElementById('mobile-nav-panel');
const mobileNavClose  = document.getElementById('mobile-nav-close');
const mobileNavBackdrop = document.getElementById('mobile-nav-backdrop');
const logoutBtnMobile = document.getElementById('logout-btn-mobile');
function closeMobileNav(){ mobileNavPanel?.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
mobileNavToggle?.addEventListener('click', ()=>{ mobileNavPanel.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); });
mobileNavClose?.addEventListener('click', closeMobileNav);
mobileNavBackdrop?.addEventListener('click', closeMobileNav);
logoutBtnMobile?.addEventListener('click', ()=>{ document.getElementById('logout-btn')?.click(); });

// Update count badges
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

// Keep pagination state per list so search or live updates can re-render without losing page unless filtered
const paginationState = {};
function paginate(containerId, paginationId, itemsPerPage = 10, opts={}) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const pagination = document.getElementById(paginationId);
    if (!pagination) return;
    const items = Array.from(container.children).filter(el => el.classList.contains('order-card') && el.getAttribute('data-match') !== 'false');
    const totalPages = Math.ceil(items.length / itemsPerPage) || 1;
  // If caller requests reset, start from 1; else use stored page clamped to bounds
  if (opts.reset || paginationState[containerId] === undefined) paginationState[containerId] = 1;
  if (paginationState[containerId] > totalPages) paginationState[containerId] = totalPages;
  let currentPage = paginationState[containerId];

    function showPage(page) {
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
    currentPage = page;
    paginationState[containerId] = currentPage;
        items.forEach((item, i) => {
            const show = (i >= (page - 1) * itemsPerPage && i < page * itemsPerPage);
            item.style.display = show ? '' : 'none';
        });
        renderPagination();
        updateCounts();
    // Smooth scroll container to top when changing page for better UX
    if (!opts.noScroll && container.scrollTo) {
      container.scrollTo({ top: 0, behavior: 'smooth' });
    }
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
    // Reset to first page after search changes
    paginate(sec.listId, sec.pagId, 5, { reset: true });
  });
  updateCounts();
};

document.addEventListener('DOMContentLoaded', () => {
    paginate('customer-orders', 'customer-pagination', 5);
    paginate('walkin-orders', 'walkin-pagination', 5);
    updateCounts();
    // Clear the persistent badge count when Orders page is opened
    try {
      localStorage.removeItem('la_orders_badge_count');
    } catch(_){}
    const b1 = document.getElementById('orders-badge-owner');
    const b2 = document.getElementById('orders-badge-emp');
    [b1,b2].forEach(b=>{ if(b){ b.textContent=''; b.style.display='none'; b.setAttribute('aria-hidden','true'); } });
    document.getElementById('orderlist-icon-owner')?.classList.remove('has-new');
    document.getElementById('orderlist-icon-emp')?.classList.remove('has-new');

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
    // Clear reupload badge when viewed
    const card = btn.closest('.order-card');
    if (card) {
      card.removeAttribute('data-reupload');
      card.querySelector('[data-reupload-badge]')?.remove();
    }
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
        const completeBtn = card.querySelector('button[data-status="Complete"]');
        if (txt.includes('Order Ready')) {
          [prepBtn, readyBtn].forEach(b=>{ if(b){ b.disabled=true; b.classList.add('opacity-50','cursor-not-allowed'); }});
          if (completeBtn) completeBtn.classList.remove('hidden');
        } else if (txt.includes('Preparing Order')) {
          if (prepBtn) { prepBtn.disabled = true; prepBtn.classList.add('opacity-50','cursor-not-allowed'); }
          if (completeBtn) completeBtn.classList.add('hidden');
        } else {
          if (completeBtn) completeBtn.classList.add('hidden');
        }
    });
    // Notification controls: desktop + sound
    const notifBtn = document.getElementById('enable-desktop-notifs');
    const soundToggle = document.getElementById('sound-toggle');
    try {
      if (window.Notification && Notification.permission !== 'granted') {
        notifBtn?.classList.remove('hidden');
      }
    } catch(e) { /* ignore */ }
    notifBtn?.addEventListener('click', async () => {
      try {
        if (!window.Notification) return;
        const perm = await Notification.requestPermission();
        if (perm === 'granted') {
          notifBtn.classList.add('hidden');
          new Notification('Notifications enabled', { body: 'You will get alerts for new and ready orders.', icon: '../images/logo.png' });
        }
      } catch(e) { /* ignore */ }
    });
    const pref = localStorage.getItem('la_sound_enabled');
    if (pref !== null) { soundToggle.checked = pref === '1'; }
    soundToggle?.addEventListener('change', () => {
      localStorage.setItem('la_sound_enabled', soundToggle.checked ? '1' : '0');
    });
});

function applyStatusVisual(el, statusCode, animate=true){
    if(!el) return;
  el.classList.remove('status-pending','status-preparing','status-ready','status-complete','status-rejected','fade-in');
    let label='Pending', cls='status-pending';
    if(statusCode==='Preparing'){ label='Preparing Order'; cls='status-preparing'; }
    else if(statusCode==='Ready'){ label='Order Ready'; cls='status-ready'; }
  else if(statusCode==='Complete'){ label='Complete'; cls='status-complete'; }
  else if(statusCode==='Rejected'){ label='Rejected'; cls='status-rejected'; }
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
                // Confirm before rejecting and choose rejection type
                let rejectionReason = null;
                if (displayStatus === 'Rejected' && typeof Swal !== 'undefined') {
                  const resp = await Swal.fire({
                    title: 'Reject this order?',
                    html: `<div class="text-left space-y-3">
                             <p class="text-sm text-gray-700">Choose how to reject this order. You can either fully reject, or mark as Incomplete Payment to let the customer re-upload their receipt.</p>
                             <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                               <button id="btn-reject-hard" type="button" class="px-3 py-2 rounded-md bg-red-600 text-white text-sm"><i class="fa-solid fa-ban mr-1"></i>Reject (final)</button>
                               <button id="btn-reject-incomplete" type="button" class="px-3 py-2 rounded-md bg-amber-500 text-white text-sm"><i class="fa-solid fa-file-circle-exclamation mr-1"></i>Incomplete payment</button>
                             </div>
                             <div class="pt-2">
                               <label class="block text-xs text-gray-600 mb-1">Reason (visible to customer)</label>
                               <textarea id="rej-reason" class="w-full p-2 border rounded" rows="4" placeholder="e.g., Reference number doesn’t match records / unclear screenshot / cropped image"></textarea>
                             </div>
                           </div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    showConfirmButton: false,
                    cancelButtonText: 'Close',
                    didOpen: () => {
                      const hard = document.getElementById('btn-reject-hard');
                      const inc = document.getElementById('btn-reject-incomplete');
                      function submit(type){
                        const val = (document.getElementById('rej-reason')?.value || '').trim();
                        if (!val) { Swal.showValidationMessage('Please enter a reason'); return; }
                        const reasonText = type==='incomplete' ? (`[INCOMPLETE PAYMENT] ${val}`) : val;
                        Swal.close({ isConfirmed: true, value: reasonText, type });
                      }
                      hard?.addEventListener('click', ()=> submit('hard'));
                      inc?.addEventListener('click', ()=> submit('incomplete'));
                    }
                  });
                  if (!resp || !resp.isConfirmed) { return; }
                  rejectionReason = resp.value || '';
                }
                btn.disabled = true; btn.classList.add('opacity-50','cursor-not-allowed');
                // Optimistic archive for Complete: remove card immediately and restore on failure
                let optimistic = null;
                if (displayStatus === 'Complete') {
                  const card = btn.closest('.order-card');
                  if (card) {
                    const parentList = card.parentElement;
                    const containerId = parentList?.id;
                    const pagId = containerId === 'customer-orders' ? 'customer-pagination' : 'walkin-pagination';
                    const sibling = card.nextElementSibling;
                    optimistic = { card, parentList, containerId, pagId, sibling };
                    try { parentList?.removeChild(card); } catch(_){}
                    if (containerId && pagId) { paginate(containerId, pagId, 5, { noScroll: true }); updateCounts(); }
                    if (typeof Swal !== 'undefined') {
                      Swal.fire({ toast:true, position:'top', timer:900, showConfirmButton:false, icon:'info', title:'Archiving order…' });
                    }
                  }
                }
                try {
                    const formData = new URLSearchParams();
                    formData.append('order_id', orderId);
                    formData.append('status', displayStatus);
                    if (rejectionReason !== null) formData.append('reason', rejectionReason);
                    const res = await fetch('../ajax/update_order_status.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: formData.toString() });
                    const json = await res.json().catch(()=>({success:false,message:'Invalid JSON'}));
                    if (!res.ok || !json.success) throw new Error(json.message || 'Update failed');
                    const statusEl = document.getElementById(`status-${orderId}`);
                    if (statusEl) {
                        const code = (displayStatus === 'Preparing Order') ? 'Preparing'
                          : (displayStatus === 'Order Ready' ? 'Ready'
                          : (displayStatus === 'Complete' ? 'Complete' : (displayStatus === 'Rejected' ? 'Rejected' : 'Pending')));
                        applyStatusVisual(statusEl, code);
                        sessionStorage.setItem(`orderStatus-${orderId}`, code);
                    }
          const card = btn.closest('.order-card');
          if (card) {
            const prepBtn = card.querySelector('button[data-status="Preparing Order"]');
            const readyBtn = card.querySelector('button[data-status="Order Ready"]');
            if (displayStatus === 'Preparing Order') {
              if (prepBtn) { prepBtn.disabled = true; prepBtn.classList.add('opacity-50','cursor-not-allowed'); }
              const completeBtn = card.querySelector('button[data-status="Complete"]');
              if (completeBtn) completeBtn.classList.add('hidden');
            } else if (displayStatus === 'Order Ready') {
              [prepBtn, readyBtn].forEach(b=>{ if(b){ b.disabled=true; b.classList.add('opacity-50','cursor-not-allowed'); }});
              const completeBtn = card.querySelector('button[data-status="Complete"]');
              if (completeBtn) completeBtn.classList.remove('hidden');
            } else if (displayStatus === 'Complete' || displayStatus === 'Rejected') {
              // For Complete: may already be optimistically removed; for Rejected: remove now
              const parentList = card.parentElement;
              const containerId = parentList?.id;
              const pagId = containerId === 'customer-orders' ? 'customer-pagination' : 'walkin-pagination';
              if (displayStatus === 'Rejected') {
                try { card.remove(); } catch(_){}
                if (containerId && pagId) { paginate(containerId, pagId, 5, { noScroll: true }); updateCounts(); }
              }
              // Toast
              if (typeof Swal !== 'undefined') {
                const isRejected = displayStatus === 'Rejected';
                const title = isRejected ? 'Order rejected and removed' : 'Order completed and archived';
                Swal.fire({ toast:true, position:'top', timer:1500, showConfirmButton:false, icon: isRejected ? 'warning' : 'success', title });
              }
            }
          }
        } catch (err) {
          btn.disabled = false; btn.classList.remove('opacity-50','cursor-not-allowed');
          // Rollback optimistic removal if we did it
          if (optimistic && optimistic.parentList && optimistic.card) {
            try { optimistic.parentList.insertBefore(optimistic.card, optimistic.sibling || null); } catch(_){}
            if (optimistic.containerId && optimistic.pagId) { paginate(optimistic.containerId, optimistic.pagId, 5, { noScroll: true }); updateCounts(); }
          }
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon:'error', title:'Update Failed', text: err.message || 'Could not update status', confirmButtonColor:'#4B2E0E' });
          }
        }
      });
    });
}

// Ensure existing buttons are bound now
bindStatusButtons(document);

// Add Enter key to trigger search explicitly
document.getElementById('orderSearch')?.addEventListener('keyup', (e)=>{ if(e.key==='Enter'){ window.applySearch(); }});

// ================= Real-time updates (polling) =================
(function(){
  let latestId = 0; // highest OrderID we've seen
  const pollIntervalMs = 4000; // 4s
  const customerListId = 'customer-orders';
  const walkinListId = 'walkin-orders';
  const customerPagId = 'customer-pagination';
  const walkinPagId = 'walkin-pagination';
  const ordersIconOwner = document.getElementById('orderlist-icon-owner');
  const ordersIconEmp = document.getElementById('orderlist-icon-emp');
  const ordersBadgeOwner = document.getElementById('orders-badge-owner');
  const ordersBadgeEmp = document.getElementById('orders-badge-emp');
  const soundToggle = document.getElementById('sound-toggle');
  const BADGE_KEY = 'la_orders_badge_count';

  function canNotify(){
    try { return !!window.Notification && Notification.permission === 'granted'; } catch(e){ return false; }
  }
  function notifyDesktop(title, body){
    try { if (canNotify()) { new Notification(title, { body, icon: '../images/logo.png' }); } } catch(e){}
  }
  // Play notification sound: prefer custom MP3; fallback to synthesized beep if blocked
  const NOTIF_SOUND_URL = '../NotifSound/sound.mp3';
  let notifAudioEl = null;
  function playBeep(){
    try {
      if (!soundToggle?.checked) return;
      // Try to play custom mp3 first
      if (!notifAudioEl) {
        notifAudioEl = new Audio(NOTIF_SOUND_URL);
        notifAudioEl.preload = 'auto';
        notifAudioEl.volume = 1.0; // adjust if needed
      }
      // Attempt mp3 playback
      const p = notifAudioEl.cloneNode(true).play();
      if (p && typeof p.catch === 'function') {
        p.catch(()=>{
          // Fallback: short oscillator beep
          try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const o = ctx.createOscillator(); const g = ctx.createGain();
            o.type = 'sine'; o.frequency.value = 880; // A5
            o.connect(g); g.connect(ctx.destination);
            g.gain.setValueAtTime(0.0001, ctx.currentTime);
            g.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + 0.02);
            o.start();
            setTimeout(()=>{ g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.15); o.stop(ctx.currentTime + 0.18); }, 160);
          } catch(_) { /* ignore */ }
        });
      }
    } catch(e) { /* ignore */ }
  }

  function money(amount){
    try { return '₱' + (Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })); }
    catch(e){ return '₱' + amount; }
  }

  function statusToLabelAndClass(statusRaw){
    let s = (statusRaw || 'Pending');
    let label = 'Pending'; let cls = 'status-line status-pending';
    if (s === 'Preparing') { label = 'Preparing Order'; cls = 'status-line status-preparing'; }
    else if (s === 'Ready') { label = 'Order Ready'; cls = 'status-line status-ready'; }
    else if (s === 'Complete') { label = 'Complete'; cls = 'status-line status-complete'; }
    else if (s === 'Rejected') { label = 'Rejected'; cls = 'status-line status-rejected'; }
    return { label, cls };
  }

  function renderPickupBadge(pickupAt){
    if (!pickupAt) return '';
    const dt = new Date(pickupAt.replace(' ', 'T'));
    const formatted = dt.toLocaleString(undefined, { month:'short', day:'2-digit', hour:'2-digit', minute:'2-digit' });
    return `<div class="mt-1 flex justify-end">
              <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                <i class="fa-regular fa-clock"></i>
                Pickup: ${formatted}
              </span>
            </div>`;
  }

  function makeOrderCard(t){
    const isCustomer = (t.category === 'customer');
    const statusInfo = statusToLabelAndClass(t.Status);
    const receiptBtn = (t.ReceiptPath && t.ReceiptPath !== 'WALKIN')
      ? `<button class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg text-xs shadow transition view-receipt-btn" data-img="../${t.ReceiptPath}" title="View Payment Proof"><i class="fas fa-image mr-1"></i>Receipt</button>`
      : '';
    const completeHidden = (t.Status === 'Ready') ? '' : 'hidden';
    const prepDisabled = (t.Status === 'Preparing' || t.Status === 'Ready') ? 'opacity-50 cursor-not-allowed' : '';
    const prepDisabledAttr = (t.Status === 'Preparing' || t.Status === 'Ready') ? 'disabled' : '';
    const readyDisabled = (t.Status === 'Ready') ? 'opacity-50 cursor-not-allowed' : '';
    const readyDisabledAttr = (t.Status === 'Ready') ? 'disabled' : '';
    const customerLine = isCustomer
      ? `Customer: ${escapeHtml(t.CustomerUsername || '')}<br>`
      : '';
    const walkinTag = isCustomer ? '' : ' <span class="ml-2 inline-block bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded">Walk-in</span>';

    // Safely format order items with <br> separators
    const safeItems = (t.OrderItems || '').split('; ').map(escapeHtml).join('<br>');
    const reupBadge = (isCustomer && t.reuploaded) ? `<span class="ml-2 inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200" data-reupload-badge="1"><i class=\"fa-solid fa-rotate\"></i> New receipt uploaded</span>` : '';
    return `
      <div class="order-card border border-gray-200 rounded-lg p-4 bg-gray-50 shadow-sm mb-4 new-flash" data-oid="${t.OrderID}">
        <div class="flex justify-between items-start gap-4">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-[#4B2E0E] mb-1">Order #${t.OrderID} ${reupBadge}</p>
            <p class="text-xs text-gray-600 mb-2">${customerLine}Date: ${escapeHtml(formatPhpDate(t.OrderDate))}${walkinTag}</p>
            <ul class="text-sm text-gray-700 list-disc list-inside mb-2"><li>${safeItems}</li></ul>
          </div>
          <div class="flex flex-col items-end gap-2">
            <span class="font-bold text-lg text-[#4B2E0E] whitespace-nowrap">${money(t.TotalAmount)}</span>
            <div class="flex flex-col gap-2 items-end sm:flex-row sm:flex-wrap sm:justify-end">
              ${receiptBtn}
              <button ${prepDisabledAttr} class="bg-[#4B2E0E] hover:bg-[#3a240c] text-white px-3 py-1 rounded-lg text-xs shadow transition ${prepDisabled}" data-id="${t.OrderID}" data-status="Preparing Order"><i class="fas fa-utensils mr-1"></i>Prepare</button>
              <button ${readyDisabledAttr} class="bg-green-700 hover:bg-green-800 text-white px-3 py-1 rounded-lg text-xs shadow transition ${readyDisabled}" data-id="${t.OrderID}" data-status="Order Ready"><i class="fas fa-check-circle mr-1"></i>Ready</button>
              <button class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded-lg text-xs shadow transition ${completeHidden}" data-id="${t.OrderID}" data-status="Complete"><i class="fas fa-box-archive mr-1"></i>Complete</button>
              ${isCustomer ? '<button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-xs shadow transition" data-id="'+t.OrderID+'" data-status="Rejected" title="Reject payment / cancel order"><i class="fas fa-ban mr-1"></i>Reject</button>' : ''}
            </div>
          </div>
        </div>
        <div class="text-right text-[11px] text-gray-500 mt-1">Ref: ${escapeHtml(t.ReferenceNo || 'N/A')}</div>
        ${renderPickupBadge(t.PickupAt)}
        <div class="mt-2"><span id="status-${t.OrderID}" class="${statusInfo.cls}">${statusInfo.label}</span></div>
      </div>
    `;
  }

  function escapeHtml(s){
    return String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatPhpDate(phpDateStr){
    const d = new Date(phpDateStr.replace(' ', 'T'));
    if (isNaN(d)) return phpDateStr;
    const mo = d.toLocaleString(undefined, { month:'short' });
    const day = String(d.getDate()).padStart(2,'0');
    const hour = String(d.getHours()).padStart(2,'0');
    const min = String(d.getMinutes()).padStart(2,'0');
    return `${mo} ${day}, ${d.getFullYear()} ${hour}:${min}`;
  }

  function insertOrUpdateOrders(listId, pagId, items){
    const list = document.getElementById(listId);
    if (!list || !items || !items.length) return { added: 0, removed: 0, updated: 0 };
    let added = 0, removed = 0, updated = 0;
    for (const t of items){
      const oid = String(t.OrderID);
      let card = list.querySelector(`.order-card[data-oid="${oid}"]`);
      const isArchived = (t.Status === 'Complete' || t.Status === 'Rejected');
      if (isArchived && card){
        card.remove(); removed++;
        continue;
      }
      if (!card && !isArchived){
        // new card at top
        const html = makeOrderCard(t);
        const tmp = document.createElement('div'); tmp.innerHTML = html.trim();
        const el = tmp.firstElementChild; if (el) {
          // Attach dataset flag for reupload notification to avoid duplicate alerts
          if (t.reuploaded) { el.dataset.reupload = '1'; }
          list.prepend(el); added++;
          notifyDesktop('New order', `Order #${t.OrderID} received`); playBeep();
          if (t.reuploaded && !el.dataset.reuploadNotified) {
            notifyDesktop('New receipt uploaded', `Order #${t.OrderID} has a new payment receipt`);
            playBeep();
            el.dataset.reuploadNotified = '1';
          }
        }
      } else if (card) {
        // update existing status if changed
        const statusEl = card.querySelector(`#status-${oid}`);
        if (statusEl) {
          const info = statusToLabelAndClass(t.Status);
          applyStatusVisual(statusEl, t.Status);
          // Reupload badge update
          if (t.reuploaded) {
            card.dataset.reupload = '1';
            let titleEl = card.querySelector('p.text-sm.font-semibold');
            if (titleEl && !titleEl.querySelector('[data-reupload-badge]')) {
              const span = document.createElement('span');
              span.setAttribute('data-reupload-badge','1');
              span.className = 'ml-2 inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200';
              span.innerHTML = '<i class="fa-solid fa-rotate"></i> New receipt uploaded';
              titleEl.appendChild(span);
              if (!card.dataset.reuploadNotified) {
                notifyDesktop('New receipt uploaded', `Order #${t.OrderID} has a new payment receipt`);
                playBeep();
                card.dataset.reuploadNotified = '1';
              }
            }
          } else {
            // If not reuploaded anymore, remove badge
            card.removeAttribute('data-reupload');
            card.querySelector('[data-reupload-badge]')?.remove();
          }
          // Toggle Complete button visibility
          const completeBtn = card.querySelector('button[data-status="Complete"]');
          const prepBtn = card.querySelector('button[data-status="Preparing Order"]');
          const readyBtn = card.querySelector('button[data-status="Order Ready"]');
          if (t.Status === 'Ready') {
            completeBtn?.classList.remove('hidden');
            prepBtn?.classList.add('opacity-50','cursor-not-allowed'); prepBtn && (prepBtn.disabled = true);
            readyBtn?.classList.add('opacity-50','cursor-not-allowed'); readyBtn && (readyBtn.disabled = true);
            if (!statusEl.dataset._wasReady) {
              notifyDesktop('Order Ready', `Order #${t.OrderID} is ready for pickup`);
              playBeep();
            }
            statusEl.dataset._wasReady = '1';
          } else if (t.Status === 'Preparing') {
            completeBtn?.classList.add('hidden');
            prepBtn?.classList.add('opacity-50','cursor-not-allowed'); prepBtn && (prepBtn.disabled = true);
            statusEl.dataset._wasReady = '';
          }
          else { statusEl.dataset._wasReady = ''; }
          updated++;
        }
      }
    }
    // Rebind any new buttons and re-apply search filter/pagination
    bindStatusButtons(list);
    if (window.currentSearchQuery) { window.applySearch(); }
    paginate(listId, pagId, 5, { noScroll: true });
    updateCounts();
    return { added, removed, updated };
  }

  async function poll(){
    try {
      const url = new URL('../ajax/get_transactions.php', window.location.href);
      if (latestId > 0) url.searchParams.set('since_id', String(latestId));
      url.searchParams.set('limit', '50');
      const res = await fetch(url.toString(), { cache: 'no-store', headers: { 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('Network');
      const json = await res.json();
      if (!json || !json.success) throw new Error(json?.message || 'Bad response');
      latestId = Math.max(latestId, json.latest_id || 0);
      const data = json.data || [];
      if (!data.length) return;
      // Split into customer and walkin and drop archived
      const customer = data.filter(d => d.category === 'customer' && d.Status !== 'Complete' && d.Status !== 'Rejected');
      const walkin = data.filter(d => d.category !== 'customer' && d.Status !== 'Complete' && d.Status !== 'Rejected');
      const custRes = insertOrUpdateOrders(customerListId, customerPagId, customer);
      const walkRes = insertOrUpdateOrders(walkinListId, walkinPagId, walkin);
      // Toggle notification dot and numeric badge if any new items were added
      const newlyAdded = (custRes.added + walkRes.added);
      if (newlyAdded > 0) {
        ordersIconOwner?.classList.add('has-new');
        ordersIconEmp?.classList.add('has-new');
        // Increment persistent numeric badges
        const stored = parseInt(localStorage.getItem(BADGE_KEY) || '0', 10) || 0;
        const next = stored + newlyAdded;
        localStorage.setItem(BADGE_KEY, String(next));
        [ordersBadgeOwner, ordersBadgeEmp].forEach(b => {
          if (!b) return;
          b.textContent = String(next);
          b.style.display = next > 0 ? 'inline-flex' : 'none';
          b.setAttribute('aria-hidden', next > 0 ? 'false' : 'true');
        });
      }
    } catch (e) {
      // Ignore transient errors
    }
  }

  // Seed latestId with highest existing
  function seedLatestId(){
    const all = Array.from(document.querySelectorAll('.order-card')).map(el => parseInt(el.getAttribute('data-oid') || '0', 10)).filter(n=>!isNaN(n));
    latestId = Math.max(0, ...all);
  }
  // Initialize badges from persisted count (kept until Orders page is opened)
  (function initBadges(){
    try {
      const stored = parseInt(localStorage.getItem(BADGE_KEY) || '0', 10) || 0;
      if (stored > 0) {
        ordersIconOwner?.classList.add('has-new');
        ordersIconEmp?.classList.add('has-new');
        [ordersBadgeOwner, ordersBadgeEmp].forEach(b => { if(b){ b.textContent = String(stored); b.style.display = 'inline-flex'; b.setAttribute('aria-hidden','false'); }});
      } else {
        [ordersBadgeOwner, ordersBadgeEmp].forEach(b => { if(b){ b.textContent = ''; b.style.display = 'none'; b.setAttribute('aria-hidden','true'); }});
      }
    } catch(_){}
  })();

  seedLatestId();
  setInterval(poll, pollIntervalMs);

  // Every N polls, also request active snapshot to reconcile status changes for existing orders
  let sweepCounter = 0;
  setInterval(async () => {
    try {
      sweepCounter = (sweepCounter + 1) % 3; // about every 12s when pollIntervalMs=4s
      if (sweepCounter !== 0) return;
      const url = new URL('../ajax/get_transactions.php', window.location.href);
      url.searchParams.set('active', '1');
      const res = await fetch(url.toString(), { cache: 'no-store', headers: { 'Accept': 'application/json' } });
      if (!res.ok) return;
      const json = await res.json();
      if (!json || !json.success) return;
      const data = json.data || [];
      const customer = data.filter(d => d.category === 'customer');
      const walkin = data.filter(d => d.category !== 'customer');
      insertOrUpdateOrders(customerListId, customerPagId, customer);
      insertOrUpdateOrders(walkinListId, walkinPagId, walkin);
    } catch(e) { /* ignore */ }
  }, pollIntervalMs);
})();

</script>
</body>
</html>