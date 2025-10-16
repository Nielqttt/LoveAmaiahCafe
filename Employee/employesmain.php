

  <?php
  session_start();
  if (!isset($_SESSION['EmployeeID'])) {
      header('Location: ../all/login');
      exit();
  }

  $employeeDisplay = isset($_SESSION['E_Username']) ? $_SESSION['E_Username'] : (isset($_SESSION['EmployeeFN']) ? $_SESSION['EmployeeFN'] : 'Employee');
  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <link rel="icon" href="../images/logo.png" type="image/png"/>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Employee Main Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      body { font-family: 'Inter', sans-serif; background-image:url('../images/LAbg.png'); background-size:cover; background-position:center; background-attachment:fixed; min-height:100vh; }
      ::-webkit-scrollbar { width:6px; }
      ::-webkit-scrollbar-thumb { background-color:#c4b09a; border-radius:10px; }
      .la-sidebar { width:70px; min-width:70px; flex:0 0 70px; }
      .la-sidebar img { width:48px; height:48px; }
      /* UI polish */
      .la-card { background: rgba(255,255,255,0.92); border-radius: 1rem; padding: 1rem; box-shadow: 0 8px 24px rgba(11,7,5,0.12); }
      .att-status { font-weight:700; color:#2b2b2b; }
      .att-meta { color:#475569; font-size:.95rem; }
      .att-controls button { min-width:130px; }
      .input-primary { padding:.6rem .75rem; border-radius:.5rem; border:1px solid #d1d5db; width:280px; }
  .glass { background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(0,0,0,0.06); }
  .big-clock { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.3rem; padding:1rem; }
  .big-clock-time { font-weight:800; font-size: clamp(1.8rem, 4.6vw, 3rem); letter-spacing: .5px; color:#1f2937; }
  .big-clock-date { color:#6b7280; font-weight:600; }
  .kbd { display:inline-block; padding:.15rem .4rem; border:1px solid #d1d5db; border-bottom-width:2px; border-radius:.375rem; background:#fff; font-size:.8rem; }
      @media (max-width:640px){ .input-primary { width:100%; } .att-controls button { min-width:110px; } }
    </style>
  </head>
  <body class="min-h-screen flex flex-col md:flex-row md:overflow-hidden text-[#4B2E0E] bg-[rgba(255,255,255,0.7)]">
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
  <aside class="hidden md:flex bg-white bg-opacity-90 backdrop-blur-sm flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
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
    <main class="flex-1 p-6 md:p-10 flex items-center justify-center">
      <div class="glass rounded-2xl shadow-xl px-6 md:px-10 py-8 md:py-12 max-w-4xl w-full">
        <!-- attendance toolbar (card) -->
        <div class="mb-8 la-card">
          <div class="grid md:grid-cols-2 gap-6 items-center">
            <div>
              <h2 class="text-2xl font-bold mb-2">Attendance</h2>
              <div id="att-status" class="att-status mb-1">Loading status...</div>
              <div class="att-meta" id="last-times">Last in: <span id="last-clock-in">—</span> · Last out: <span id="last-clock-out">—</span></div>
            </div>
            <div class="flex flex-col items-center justify-center gap-3">
              <div class="big-clock">
                <div class="big-clock-time" id="live-clock">--:--</div>
                <div class="big-clock-date" id="live-date">—</div>
              </div>
              <div class="att-controls flex flex-wrap justify-center gap-2">
              <button id="btnClockIn" class="px-4 py-2 rounded-full bg-green-600 text-white font-semibold shadow hover:bg-green-700 disabled:opacity-50" disabled>
                <i class="fa-solid fa-right-to-bracket mr-2"></i>Clock In
              </button>
              <button id="btnStartBreak" class="px-4 py-2 rounded-full bg-amber-500 text-white font-semibold shadow hover:bg-amber-600 hidden">
                <i class="fa-solid fa-mug-hot mr-2"></i>Start Break
              </button>
              <button id="btnEndBreak" class="px-4 py-2 rounded-full bg-amber-700 text-white font-semibold shadow hover:bg-amber-800 hidden">
                <i class="fa-solid fa-play mr-2"></i>End Break
              </button>
              <button id="btnClockOut" class="px-4 py-2 rounded-full bg-red-600 text-white font-semibold shadow hover:bg-red-700" disabled>
                <i class="fa-solid fa-door-open mr-2"></i>Clock Out
              </button>
              </div>
            </div>
          </div>
        </div>
        <!-- greeting -->
  <h1 class="text-3xl font-extrabold mb-1 text-center">Welcome, <?php echo htmlspecialchars($employeeDisplay, ENT_QUOTES, 'UTF-8'); ?> 👋</h1>
  <p class="text-gray-700 mb-6 text-center">Good to see you. Manage attendance and start orders from here.</p>

        <!-- Order type -->
        <form action="../Employee/employeepage" method="get" class="flex flex-col items-center gap-6">
          <label class="text-[#4B2E0E] font-semibold w-full max-w-md text-left">
            Customer name
            <input type="text" name="customer_name" required class="mt-2 input-primary w-full" placeholder="Enter customer name" />
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

          <div class="text-gray-500 text-sm mt-2">Shortcuts: <span class="kbd">I</span> Clock In · <span class="kbd">O</span> Clock Out · <span class="kbd">B</span> Break</div>
        </form>
      </div>
    </main>

    <script>
      document.getElementById('logout-btn').addEventListener('click', function() {
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
            window.location.href = '../all/logout';
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

      // Attendance wiring
      const $status = document.getElementById('att-status');
      const $clockIn = document.getElementById('btnClockIn');
      const $startBreak = document.getElementById('btnStartBreak');
      const $endBreak = document.getElementById('btnEndBreak');
      const $clockOut = document.getElementById('btnClockOut');

      // Format timestamp in Philippine Standard Time with date and time
      const PH_TZ = 'Asia/Manila';
      function fmtDateTime(ts) {
        if(!ts) return '';
        // Replace space with T for Safari compatibility, treat as local then convert using Intl with timeZone
        const d = new Date(ts.replace(' ', 'T'));
        if(isNaN(d)) return ts;
        const opts = { timeZone: PH_TZ, year:'numeric', month:'short', day:'2-digit', hour:'2-digit', minute:'2-digit' };
        return new Intl.DateTimeFormat('en-PH', opts).format(d);
      }

      function setButtons(state) {
        const cin = state.clock_in_time || state.clock_in;
        const cout = state.clock_out_time || state.clock_out;
        const bs = state.break_start_time || state.break_start;
        const be = state.break_end_time || state.break_end;
        const clockedIn = !!cin;
        const clockedOut = !!cout;
        const onBreak = state.on_break === 1 || state.on_break === true || (!!bs && (!be || new Date(bs) > new Date(be)));

        $clockIn.disabled = clockedIn;
        $clockIn.classList.toggle('hidden', clockedIn);

        // Always visible clock out; enable only when clocked in and not out yet
        $clockOut.disabled = !clockedIn || clockedOut;

        // Break toggle
        $startBreak.classList.toggle('hidden', !clockedIn || onBreak || clockedOut);
        $endBreak.classList.toggle('hidden', !clockedIn || !onBreak || clockedOut);
      }

      function describe(state) {
        if (!state) return 'Status: Not clocked in';
        const cin = state.clock_in_time || state.clock_in;
        const cout = state.clock_out_time || state.clock_out;
        const bs = state.break_start_time || state.break_start;
        const be = state.break_end_time || state.break_end;
        if (!cin) return 'Status: Not clocked in';
  if (cout) return `Status: Clocked out at ${fmtDateTime(cout)}`;
        const onBreak = state.on_break === 1 || state.on_break === true || (!!bs && (!be || new Date(bs) > new Date(be)));
  if (onBreak) return `Status: On break since ${fmtDateTime(bs)}`;
  return `Status: Clocked in at ${fmtDateTime(cin)}`;
      }

      function appendLastClockOut(state){
        const cin = state && (state.clock_in_time || state.clock_in);
        const cout = state && (state.clock_out_time || state.clock_out);
        const elIn = document.getElementById('last-clock-in');
        const elOut = document.getElementById('last-clock-out');
        if(elIn) elIn.textContent = cin ? fmtDateTime(cin) : '—';
        if(elOut) elOut.textContent = cout ? fmtDateTime(cout) : '—';
      }

      // Live clock (Asia/Manila)
      const liveClockEl = document.getElementById('live-clock');
      const liveDateEl = document.getElementById('live-date');
      function tickClock(){
        try {
          const now = new Date();
          const time = new Intl.DateTimeFormat('en-PH', { timeZone: PH_TZ, hour:'2-digit', minute:'2-digit', second:'2-digit' }).format(now);
          const date = new Intl.DateTimeFormat('en-PH', { timeZone: PH_TZ, weekday:'short', year:'numeric', month:'short', day:'2-digit' }).format(now);
          if(liveClockEl) liveClockEl.textContent = time;
          if(liveDateEl) liveDateEl.textContent = date;
        } catch (e) {}
      }
      tickClock();
      setInterval(tickClock, 1000);

    async function fetchStatus() {
        try {
          const r = await fetch('../ajax/attendance.php', { headers: { 'Accept': 'application/json' } });
          const j = await r.json();
          const data = j.data || {};
          $status.textContent = describe(data);
          setButtons(data);
      appendLastClockOut(data);
        } catch (e) {
          $status.textContent = 'Status: unavailable';
        }
      }

      async function postAction(action) {
        try {
          const fd = new FormData();
          fd.append('action', action);
          // Attach geolocation if available
          if(window.__geo){
            fd.append('lat', window.__geo.lat);
            fd.append('lng', window.__geo.lng);
            fd.append('acc', window.__geo.acc);
          }
          const r = await fetch('../ajax/attendance.php', { method: 'POST', body: fd });
          const j = await r.json();
          if (j.success) {
            $status.textContent = describe(j.data || {});
            setButtons(j.data || {});
            appendLastClockOut(j.data || {});
            Swal.fire({ icon: 'success', title: j.message || 'Done', timer: 1200, showConfirmButton: false });
          } else {
            Swal.fire({ icon: 'error', title: j.message || 'Action failed' });
          }
        } catch (e) {
          Swal.fire({ icon: 'error', title: 'Network error' });
        }
      }

      // Fresh geo capture for clock in to ensure accurate entry location
      function captureAndClockIn(){
        if(navigator.geolocation){
          navigator.geolocation.getCurrentPosition(pos=>{
            window.__geo = { lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy };
            postAction('clock_in');
          }, err=>{
            // On error still attempt clock in without geo
            postAction('clock_in');
          }, { enableHighAccuracy:true, timeout:8000, maximumAge:0 });
        } else {
          postAction('clock_in');
        }
      }
      $clockIn.addEventListener('click', captureAndClockIn);
      $startBreak.addEventListener('click', () => postAction('start_break'));
      $endBreak.addEventListener('click', () => postAction('end_break'));
      // Fresh geo capture for clock out to ensure accurate exit location
      function captureAndClockOut(){
        if(navigator.geolocation){
          navigator.geolocation.getCurrentPosition(pos=>{
            window.__geo = { lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy };
            postAction('clock_out');
          }, err=>{
            // On error still attempt clock out without new geo
            postAction('clock_out');
          }, { enableHighAccuracy:true, timeout:8000, maximumAge:0 });
        } else {
          postAction('clock_out');
        }
      }
      $clockOut.addEventListener('click', captureAndClockOut);

      // Keyboard shortcuts: I (clock in), O (clock out), B (toggle break)
      window.addEventListener('keydown', (e)=>{
        const tag = (e.target && e.target.tagName) || '';
        if(tag === 'INPUT' || tag === 'TEXTAREA' || e.ctrlKey || e.metaKey || e.altKey) return;
        const k = e.key.toLowerCase();
        if(k === 'i' && !$clockIn.disabled){ e.preventDefault(); captureAndClockIn(); }
        else if(k === 'o' && !$clockOut.disabled){ e.preventDefault(); captureAndClockOut(); }
        else if(k === 'b'){
          if(!$startBreak.classList.contains('hidden')){ e.preventDefault(); $startBreak.click(); }
          else if(!$endBreak.classList.contains('hidden')){ e.preventDefault(); $endBreak.click(); }
        }
      });

      // Acquire geolocation early (user may need to allow). Non-blocking.
      if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(pos=>{
          window.__geo = { lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy };
        }, ()=>{}, { enableHighAccuracy:true, timeout:8000, maximumAge:60000 });
      }

      // initial
      fetchStatus();
    </script>
  </body>
  </html>
