<?php
session_start();

require_once('../classes/database.php');
$con = new database();

$ownerFirstName = 'Owner';

if (isset($_SESSION['OwnerID'])) {
    $ownerFirstName = $_SESSION['OwnerFN']; 
} else {
    header('Location: ../all/login.php');
    exit();
}


$totalSales = $con->getSystemTotalSales(30); 
$totalOrders = $con->getSystemTotalOrders(30); 
$totalSalesTransactions = $con->getSystemTotalTransactions();
$salesData = $con->getSystemSalesData(30); 
$topProducts = $con->getSystemTopProducts(30); 

$topSellerName = 'N/A';
if (!empty($topProducts['labels'][0])) {
    $topSellerName = $topProducts['labels'][0]; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoveAmiah - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: rgba(255, 255, 255, 0.7); }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- Sidebar -->
    <aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
    <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full mb-4" />
    <?php $current = basename($_SERVER['PHP_SELF']); ?>   
    <button title="Dashboard" onclick="window.location.href='../Owner/dashboard.php'">
        <i class="fas fa-chart-line text-xl <?= $current == 'dashboard.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Home" onclick="window.location.href='../Owner/mainpage.php'">
        <i class="fas fa-home text-xl <?= $current == 'mainpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Cart" onclick="window.location.href='../Owner/page.php'">
        <i class="fas fa-shopping-cart text-xl <?= $current == 'page.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Order List" onclick="window.location.href='../all/tranlist.php'">
        <i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Product List" onclick="window.location.href='../Owner/product.php'">
        <i class="fas fa-box text-xl <?= $current == 'product.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Employees" onclick="window.location.href='../Owner/user.php'">
        <i class="fas fa-users text-xl <?= $current == 'user.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Settings" onclick="window.location.href='../all/setting.php'">
        <i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button id="logout-btn" title="Logout">
        <i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i>
    </button>
</aside>

    <!-- Main Content -->
    <div class="flex-grow p-6 relative">
        <img src="https://storage.googleapis.com/a1aa/image/22cccae8-cc1a-4fb3-7955-287078a4f8d4.jpg" alt="Background" class="absolute inset-0 w-full h-full object-cover opacity-20 -z-10" />
        <header class="mb-6 flex justify-between items-center z-10">
            <div>
                <p class="text-xs text-gray-700 mb-0.5">Welcome, <?= htmlspecialchars($ownerFirstName); ?></p>
                <h1 class="text-[#4B2E0E] font-semibold text-2xl">System Dashboard</h1>
            </div>
            <div class="flex space-x-2">
                <button id="refreshBtn" class="bg-[#C4A07A] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#a17850] transition shadow-md"><i class="fas fa-sync-alt mr-2"></i> Refresh</button>
            </div>
        </header>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 z-10">
            <div class="bg-white rounded-lg shadow-md p-4">
                <h5 class="text-lg font-semibold text-gray-700">Total Sales</h5>
                <p class="text-3xl font-bold text-[#4B2E0E]">₱<?= number_format($totalSales, 2); ?></p>
                <small class="text-gray-500">Last 30 days</small>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4">
                <h5 class="text-lg font-semibold text-gray-700">Total Orders</h5>
                <p class="text-3xl font-bold text-[#4B2E0E]"><?= $totalOrders; ?></p>
                <small class="text-gray-500">Last 30 days</small>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4">
                <h5 class="text-lg font-semibold text-gray-700">Total Transactions</h5>
                <p class="text-3xl font-bold text-[#4B2E0E]"><?= htmlspecialchars($totalSalesTransactions); ?></p>
                <small class="text-gray-500">All Time</small>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4">
                <h5 class="text-lg font-semibold text-gray-700">Top Seller</h5>
                <p class="text-xl font-bold text-[#4B2E0E] overflow-hidden whitespace-nowrap overflow-ellipsis" title="<?= htmlspecialchars($topSellerName); ?>"><?= htmlspecialchars($topSellerName); ?></p>
                <small class="text-gray-500">Last 30 days</small>
            </div>
        </div>

        <!-- Sales Overview Chart -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 z-10 w-full">
    <h5 class="text-xl font-semibold text-gray-700 mb-4">Sales Overview (Last 30 Days)</h5>
    <div class="w-full h-[500px]">
        <canvas id="salesChart" class="w-full h-full"></canvas>
    </div>
</div>
    </div>
    <script>
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($salesData['labels']); ?>,
                datasets: [{
                    label: 'Sales',
                    data: <?php echo json_encode($salesData['data']); ?>,
                    borderColor: '#C4A07A', backgroundColor: 'rgba(196, 160, 122, 0.2)',
                    tension: 0.3, fill: true
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { tooltip: { callbacks: { label: (context) => '₱' + context.parsed.y.toLocaleString(undefined, { minimumFractionDigits: 2 }) } } },
                scales: { y: { beginAtZero: true, ticks: { callback: (value) => '₱' + value.toLocaleString() } } }
            }
        });
        document.getElementById('refreshBtn').addEventListener('click', () => location.reload());
        document.getElementById("logout-btn").addEventListener("click", () => {
            Swal.fire({
                title: 'Log out?', text: "Are you sure you want to log out?", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#4B2E0E', cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, log out'
            }).then((result) => { if (result.isConfirmed) { window.location.href = "../all/logout.php"; } });
        });
    </script>
</body>
</html>