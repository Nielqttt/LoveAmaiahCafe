<?php
session_start();
if (!isset($_SESSION['OwnerID'])) {
  header('Location: ../all/login');
  exit();
}
$ownerName = $_SESSION['OwnerFN'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="../images/logo.png" type="image/png"/>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Main Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body { font-family:'Inter',sans-serif; background-image:url('../images/LAbg.png'); background-size:cover; background-position:center; background-attachment:fixed; }
    ::-webkit-scrollbar { width:6px; }
    ::-webkit-scrollbar-thumb { background-color:#c4b09a; border-radius:10px; }
    .la-sidebar { width:70px; min-width:70px; flex:0 0 70px; }
    .la-sidebar img { width:48px; height:48px; }
    /* Shared UI polish to align with employee page */
    .glass { background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(0,0,0,0.06); }
    .input-primary { padding:.6rem .75rem; border-radius:.5rem; border:1px solid #d1d5db; width:100%; }
    .big-clock { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.2rem; padding:.5rem; }
    .big-clock-time { font-weight:800; font-size: clamp(1.6rem, 4.2vw, 2.4rem); letter-spacing: .4px; color:#1f2937; }
    .big-clock-date { color:#6b7280; font-weight:600; font-size:.95rem; }
    .kbd { display:inline-block; padding:.15rem .4rem; border:1px solid #d1d5db; border-bottom-width:2px; border-radius:.375rem; background:#fff; font-size:.8rem; }
  </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row md:overflow-hidden text-[#4B2E0E]">
  <!-- Mobile Top Bar -->
  <div class="md:hidden flex items-center justify-between px-4 py-2 bg-white/90 backdrop-blur-sm shadow sticky top-0 z-30">
    <div class="flex items-center gap-2">
      <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full" />
      <span class="font-semibold text-[#4B2E0E] text-lg">Main</span>
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
  <aside class="hidden md:flex bg-white bg-opacity-90 backdrop-blur-sm flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
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


  <!-- Main content -->
  <main class="flex-1 p-6 md:p-10 flex items-center justify-center min-w-0">
    <div class="glass rounded-2xl shadow-xl px-6 md:px-10 py-8 md:py-12 max-w-4xl w-full">

      <!-- greeting -->
      <h1 class="text-3xl font-extrabold mb-1 text-center">Welcome Home, <?php echo htmlspecialchars($ownerName); ?> 👋</h1>
      <p class="text-gray-700 mb-4 text-center">How would you like to place the order?</p>

      <!-- compact live clock -->
      <div class="big-clock mb-6">
        <div id="live-clock" class="big-clock-time">--:--</div>
        <div id="live-date" class="big-clock-date">—</div>
      </div>

      <form action="page.php" method="get" class="flex flex-col items-center gap-6">
        <label class="text-[#4B2E0E] font-semibold w-full max-w-md text-left">
          Customer name
          <input type="text" name="customer_name" required class="mt-2 input-primary" placeholder="Enter customer name" />
        </label>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full max-w-xl mt-2">
          <button type="submit" name="order_type" value="Dine-In" class="group p-6 rounded-2xl shadow bg-white/90 hover:bg-white transition text-[#4B2E0E] border border-gray-200">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shadow-inner"><i class="fas fa-utensils fa-lg"></i></div>
              <div>
                <div class="font-extrabold text-lg">Dine-In</div>
                <div class="text-sm text-gray-600">Serve at table</div>
              </div>
            </div>
          </button>

          <button type="submit" name="order_type" value="Take-Out" class="group p-6 rounded-2xl shadow bg-white/90 hover:bg-white transition text-[#4B2E0E] border border-gray-200">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shadow-inner"><i class="fas fa-shopping-bag fa-lg"></i></div>
              <div>
                <div class="font-extrabold text-lg">Take-Out</div>
                <div class="text-sm text-gray-600">Pack for pickup</div>
              </div>
            </div>
          </button>
        </div>

        <div class="text-gray-500 text-sm mt-2 text-center">Shortcuts: <span class="kbd">D</span> Dine-In · <span class="kbd">T</span> Take-Out</div>
      </form>

      <div class="text-sm text-gray-400 mt-8"></div>
    </div>
  </main>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Live clock (Asia/Manila)
    const PH_TZ = 'Asia/Manila';
    const liveClockEl = document.getElementById('live-clock');
    const liveDateEl = document.getElementById('live-date');
    function tickClock(){
      try {
        const now = new Date();
        const time = new Intl.DateTimeFormat('en-PH', { timeZone: PH_TZ, hour:'2-digit', minute:'2-digit', second:'2-digit' }).format(now);
        const date = new Intl.DateTimeFormat('en-PH', { timeZone: PH_TZ, weekday:'short', year:'numeric', month:'short', day:'2-digit' }).format(now);
        if(liveClockEl) liveClockEl.textContent = time;
        if(liveDateEl) liveDateEl.textContent = date;
      } catch(e){}
    }
    tickClock();
    setInterval(tickClock, 1000);

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
    // Mobile nav logic
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

    // Keyboard shortcuts: D (dine-in), T (take-out)
    window.addEventListener('keydown', (e)=>{
      const tag = (e.target && e.target.tagName) || '';
      if(tag === 'INPUT' || tag === 'TEXTAREA' || e.ctrlKey || e.metaKey || e.altKey) return;
      const dineBtn = document.querySelector('button[name="order_type"][value="Dine-In"]');
      const takeBtn = document.querySelector('button[name="order_type"][value="Take-Out"]');
      const k = e.key.toLowerCase();
      if(k === 'd' && dineBtn){ e.preventDefault(); dineBtn.click(); }
      else if(k === 't' && takeBtn){ e.preventDefault(); takeBtn.click(); }
    });
  </script>
</body>
</html>