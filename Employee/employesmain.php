

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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Employee Main Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      body {
        font-family: 'Inter', sans-serif;
        background-image: url('../images/LAbg.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        min-height: 100vh;
      }
      ::-webkit-scrollbar { width: 6px; }
      ::-webkit-scrollbar-thumb { background-color: #c4b09a; border-radius: 10px; }
    </style>
  </head>
  <body class="min-h-screen flex text-[#4B2E0E] bg-[rgba(255,255,255,0.7)]">
    <!-- Sidebar -->
  <aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
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
    <main class="flex-1 p-10 flex items-center justify-center text-center">
      <div class="bg-white bg-opacity-80 backdrop-blur-md rounded-2xl shadow-xl px-10 py-12 max-w-4xl w-100">
        <!-- attendance toolbar -->
        <div class="mb-8">
          <h2 class="text-2xl font-bold mb-3">Attendance</h2>
          <div id="att-status" class="text-sm text-gray-700 mb-4">Loading status...</div>
          <div class="flex items-center justify-center gap-3 flex-wrap">
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
        <!-- greeting -->
        <h1 class="text-3xl font-extrabold mb-4">
          Welcome 👋
        </h1>
        <p class="text-gray-700 mb-10">

        </p>
        <form action="../Employee/employeepage" method="get" class="flex flex-col items-center gap-6">
          <label class="text-[#4B2E0E] font-semibold">
            Enter your name:
            <input type="text" name="customer_name" required class="mt-2 p-2 rounded border border-gray-300" />
          </label>
          <div class="flex gap-10 mt-6">
            <button type="submit" name="order_type" value="Dine-In" class="bg-white p-6 rounded-xl shadow text-[#4B2E0E] hover:bg-[#f5f5f5]">
              <i class="fas fa-utensils fa-2x"></i>
              <div class="mt-2 font-semibold">Dine-In</div>
            </button>
            <button type="submit" name="order_type" value="Take-Out" class="bg-white p-6 rounded-xl shadow text-[#4B2E0E] hover:bg-[#f5f5f5]">
              <i class="fas fa-shopping-bag fa-2x"></i>
              <div class="mt-2 font-semibold">Take-Out</div>
            </button>
          </div>
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
        const cout = state && (state.clock_out_time || state.clock_out);
      }

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

      $clockIn.addEventListener('click', () => postAction('clock_in'));
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
