<?php
session_start();
if (!isset($_SESSION['OwnerID'])) { header('Location: ../all/login'); exit(); }
require_once('../classes/database.php');
$db = new database();
$logs = $db->getTodayLogsWithGeo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Attendance Locations - Today</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-sA+4J08z8JbMra0z6Zep2zj0mQ6ZkHf7gE1tBf0z0XQ=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-o9N1j7kGStG3sG/QAd9kziEtGlUZt6Zjv2hP6rpVY3s=" crossorigin=""></script>
<style>
body { background: #f7f5f2; }
#map { height: 70vh; }
.badge { @apply text-xs px-2 py-0.5 rounded-full font-semibold; }
</style>
</head>
<body class="min-h-screen flex">
  <aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
    <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
    <?php $current = basename($_SERVER['PHP_SELF']); ?>
    <button title="Dashboard" onclick="window.location.href='dashboard'">
      <i class="fas fa-home text-xl <?= $current=='dashboard.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Users" onclick="window.location.href='user'">
      <i class="fas fa-users text-xl <?= $current=='user.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Products" onclick="window.location.href='product'">
      <i class="fas fa-box text-xl <?= $current=='product.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Attendance Geo" onclick="window.location.href='attendance_geo'">
      <i class="fas fa-map-location-dot text-xl <?= $current=='attendance_geo.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Settings" onclick="window.location.href='../all/setting'">
      <i class="fas fa-cog text-xl <?= $current=='setting.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Logout" onclick="window.location.href='../all/logout'">
      <i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i>
    </button>
  </aside>
  <main class="flex-1 p-6 space-y-6">
    <header>
      <h1 class="text-2xl font-bold text-[#4B2E0E]">Today's Attendance Locations</h1>
      <p class="text-sm text-gray-600">Mapped clock-in & clock-out positions (if location permission granted by employees).</p>
    </header>
    <div id="map" class="rounded-lg shadow bg-white"></div>
    <section class="bg-white rounded-lg shadow p-4 overflow-x-auto">
      <h2 class="font-semibold mb-3 text-[#4B2E0E]">Detail Table</h2>
      <table class="w-full text-sm">
        <thead class="text-left bg-[#4B2E0E] text-white">
          <tr>
            <th class="p-2">Employee</th>
            <th class="p-2">Clock In</th>
            <th class="p-2">Clock In Location</th>
            <th class="p-2">Clock Out</th>
            <th class="p-2">Clock Out Location</th>
            <th class="p-2">Break Start</th>
            <th class="p-2">Break End</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($logs as $r): ?>
            <tr class="border-b last:border-none">
              <td class="p-2 font-medium"><?= htmlspecialchars(($r['EmployeeFN']??'')." ".($r['EmployeeLN']??'')) ?></td>
              <td class="p-2"><?= $r['clock_in']?htmlspecialchars($r['clock_in']):'-' ?></td>
              <td class="p-2 text-xs"><?= ($r['clock_in_lat'] && $r['clock_in_lng'])? (round($r['clock_in_lat'],5).', '.round($r['clock_in_lng'],5)):'-' ?></td>
              <td class="p-2"><?= $r['clock_out']?htmlspecialchars($r['clock_out']):'-' ?></td>
              <td class="p-2 text-xs"><?= ($r['clock_out_lat'] && $r['clock_out_lng'])? (round($r['clock_out_lat'],5).', '.round($r['clock_out_lng'],5)):'-' ?></td>
              <td class="p-2"><?= $r['break_start']?htmlspecialchars($r['break_start']):'-' ?></td>
              <td class="p-2"><?= $r['break_end']?htmlspecialchars($r['break_end']):'-' ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if(empty($logs)): ?>
            <tr><td colspan="7" class="p-3 text-center text-gray-500">No logs yet today.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>
<script>
const map = L.map('map').setView([12.8797, 121.7740], 5); // Philippines center approx
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
const logs = <?php echo json_encode($logs); ?>;
let bounds = [];
logs.forEach(l => {
  if(l.clock_in_lat && l.clock_in_lng){
    const m = L.marker([l.clock_in_lat, l.clock_in_lng]).addTo(map);
    m.bindPopup(`<strong>${(l.EmployeeFN||'')+' '+(l.EmployeeLN||'')}</strong><br/>Clock In<br/>${l.clock_in || ''}`);
    bounds.push([l.clock_in_lat, l.clock_in_lng]);
  }
  if(l.clock_out_lat && l.clock_out_lng){
    const m2 = L.marker([l.clock_out_lat, l.clock_out_lng], {icon: L.icon({ iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png', iconSize:[25,41], iconAnchor:[12,41], popupAnchor:[1,-34], shadowUrl:'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png' })}).addTo(map);
    m2.bindPopup(`<strong>${(l.EmployeeFN||'')+' '+(l.EmployeeLN||'')}</strong><br/>Clock Out<br/>${l.clock_out || ''}`);
    bounds.push([l.clock_out_lat, l.clock_out_lng]);
  }
});
if(bounds.length){ map.fitBounds(bounds, { padding: [40,40] }); }
</script>
</body>
</html>
