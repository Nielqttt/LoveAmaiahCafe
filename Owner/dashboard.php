<?php
session_start();

require_once('../classes/database.php');
$con = new database();

$ownerFirstName = 'Owner';

if (isset($_SESSION['OwnerID'])) {
    $ownerFirstName = $_SESSION['OwnerFN']; 
} else {
    header('Location: ../all/login');
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

// Handle AJAX: Monthly Sales Report (filters: month YYYY-MM, optional category)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'monthly_report') {
        header('Content-Type: application/json');
        try {
                $month = isset($_POST['month']) ? trim($_POST['month']) : '';
                $category = isset($_POST['category']) ? trim($_POST['category']) : 'All';
                if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                        echo json_encode(['success'=>false,'message'=>'Invalid month']);
                        exit;
                }
                // Compute first/last day for the month in server timezone
                $start = new DateTime($month . '-01 00:00:00');
                $end = (clone $start); $end->modify('last day of this month')->setTime(23,59,59);
                $startStr = $start->format('Y-m-d H:i:s');
                $endStr = $end->format('Y-m-d H:i:s');

                $db = $con->opencon();
                // Ensure order status columns exist
                $con->ensureOrderStatus();

                $params = [':start'=>$startStr, ':end'=>$endStr];
                $catFilter = '';
                if ($category !== '' && strtolower($category) !== 'all') {
                        $catFilter = ' AND p.ProductCategory = :cat ';
                        $params[':cat'] = $category;
                }

                // Summary KPIs
                $sqlSummary = "
                        SELECT 
                            COALESCE(SUM(od.Subtotal),0) AS revenue,
                            COUNT(DISTINCT o.OrderID) AS orders,
                            COALESCE(SUM(od.Quantity),0) AS items,
                            COUNT(DISTINCT CASE WHEN os.CustomerID IS NOT NULL THEN os.CustomerID END) AS customers
                        FROM orders o
                        JOIN ordersection os ON o.OrderSID = os.OrderSID
                        JOIN orderdetails od ON od.OrderID = o.OrderID
                        JOIN product p ON p.ProductID = od.ProductID
                        WHERE o.Status = 'Complete' 
                            AND o.OrderDate BETWEEN :start AND :end
                            $catFilter
                ";
                $stmt = $db->prepare($sqlSummary);
                $stmt->execute($params);
                $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['revenue'=>0,'orders'=>0,'items'=>0,'customers'=>0];

                // Daily breakdown
                $sqlDaily = "
                        SELECT 
                            DATE(o.OrderDate) AS d,
                            COUNT(DISTINCT o.OrderID) AS orders,
                            COALESCE(SUM(od.Quantity),0) AS items,
                            COALESCE(SUM(od.Subtotal),0) AS revenue
                        FROM orders o
                        JOIN ordersection os ON o.OrderSID = os.OrderSID
                        JOIN orderdetails od ON od.OrderID = o.OrderID
                        JOIN product p ON p.ProductID = od.ProductID
                        WHERE o.Status = 'Complete'
                            AND o.OrderDate BETWEEN :start AND :end
                            $catFilter
                        GROUP BY DATE(o.OrderDate)
                        ORDER BY d ASC
                ";
                $stmt = $db->prepare($sqlDaily);
                $stmt->execute($params);
                $daily = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Products sold
                $sqlProducts = "
                        SELECT p.ProductName AS product, COALESCE(SUM(od.Quantity),0) AS qty
                        FROM orders o
                        JOIN orderdetails od ON od.OrderID = o.OrderID
                        JOIN product p ON p.ProductID = od.ProductID
                        WHERE o.Status = 'Complete'
                            AND o.OrderDate BETWEEN :start AND :end
                            $catFilter
                        GROUP BY p.ProductID, p.ProductName
                        ORDER BY qty DESC, p.ProductName ASC
                ";
                $stmt = $db->prepare($sqlProducts);
                $stmt->execute($params);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['success'=>true,'summary'=>$summary,'daily'=>$daily,'products'=>$products]);
                exit;
        } catch (Throwable $e) {
                echo json_encode(['success'=>false,'message'=>'Server error']);
                exit;
        }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="../images/logo.png" type="image/png"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoveAmiah - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: rgba(255, 255, 255, 0.7); }
        .la-sidebar { width:70px; min-width:70px; flex:0 0 70px; }
        .la-sidebar img { width:48px; height:48px; }
        /* Mobile adjustments */
        @media (max-width:767px){
          body.nav-open { overflow:hidden; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row">
    <!-- Mobile Top Bar -->
    <div class="md:hidden flex items-center justify-between px-4 py-2 bg-white/90 backdrop-blur-sm shadow sticky top-0 z-30">
        <div class="flex items-center gap-2">
            <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full" />
            <span class="font-semibold text-[#4B2E0E] text-lg">Dashboard</span>
        </div>
        <button id="mobile-nav-toggle" class="p-2 rounded-full border border-[#4B2E0E] text-[#4B2E0E]" aria-label="Open navigation">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <!-- Mobile Slide-over Nav -->
    <div id="mobile-nav-panel" class="md:hidden fixed inset-0 z-40 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-black/40" id="mobile-nav-backdrop"></div>
        <div class="absolute left-0 top-0 h-full w-60 bg-white shadow-lg p-4 flex flex-col gap-4 overflow-y-auto" role="dialog" aria-modal="true" aria-label="Navigation Menu">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-[#4B2E0E] font-semibold">Navigation</h2>
                <button id="mobile-nav-close" class="text-gray-500 text-xl" aria-label="Close navigation"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <?php $current = basename($_SERVER['PHP_SELF']); ?>
            <nav class="flex flex-col gap-2 text-sm" role="navigation">
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

    <!-- Sidebar (desktop only) -->
    <aside class="hidden md:flex bg-white bg-opacity-90 backdrop-blur-sm w-16 flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
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
    <div class="flex-grow p-6 relative">
        <img src="https://storage.googleapis.com/a1aa/image/22cccae8-cc1a-4fb3-7955-287078a4f8d4.jpg" alt="Background" class="absolute inset-0 w-full h-full object-cover opacity-20 -z-10" />
        <header class="mb-6 flex justify-between items-center z-10">
            <div>
                <p class="text-xs text-gray-700 mb-0.5">Welcome, <?= htmlspecialchars($ownerFirstName); ?></p>
                <h1 class="text-[#4B2E0E] font-semibold text-2xl">System Dashboard</h1>
            </div>
            <div class="flex space-x-2">
                <button id="refreshBtn" class="bg-[#C4A07A] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#a17850] transition shadow-md"><i class="fas fa-sync-alt mr-2"></i> Refresh</button>
                <button id="exportExcelBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition shadow-md"><i class="fas fa-file-excel mr-2"></i> Export Excel</button>
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

        <!-- Monthly Sales Report -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 z-10 w-full">
            <h5 class="text-xl font-semibold text-gray-700 mb-4">Monthly Sales Report</h5>
            <div class="flex flex-wrap gap-3 items-end mb-4">
                <div>
                    <label class="block text-xs text-gray-700 mb-1">Select Month</label>
                    <input id="rep-month" type="month" class="px-3 py-2 rounded-lg border border-gray-300" value="<?php echo date('Y-m'); ?>" />
                </div>
                <div>
                    <label class="block text-xs text-gray-700 mb-1">Category (optional)</label>
                    <select id="rep-category" class="px-3 py-2 rounded-lg border border-gray-300 min-w-[200px]">
                        <option value="All">All</option>
                        <?php
                            $cats = $con->getAllCategories();
                            foreach($cats as $cat){
                                $safe = htmlspecialchars($cat);
                                echo "<option value=\"{$safe}\">{$safe}</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="flex gap-2 ml-auto">
                    <button id="rep-load" class="bg-[#C4A07A] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#a17850] transition shadow-md"><i class="fas fa-sync-alt mr-2"></i> Load Report</button>
                    <button id="rep-csv" class="bg-white text-[#4B2E0E] px-4 py-2 rounded-lg font-semibold border border-[#4B2E0E]/30 hover:bg-[#f8f6f4] transition shadow-sm"><i class="fas fa-file-csv mr-2"></i> Download CSV</button>
                </div>
            </div>

            <div id="rep-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="text-xs text-gray-600 mb-1">Total Revenue</div>
                    <div class="text-2xl font-extrabold text-[#4B2E0E]"><span>₱</span><span id="rep-rev">0.00</span></div>
                    <small class="text-gray-500">Selected month</small>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="text-xs text-gray-600 mb-1">Total Orders</div>
                    <div class="text-2xl font-extrabold text-[#4B2E0E]" id="rep-orders">0</div>
                    <small class="text-gray-500">Selected month</small>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="text-xs text-gray-600 mb-1">Items Sold</div>
                    <div class="text-2xl font-extrabold text-[#4B2E0E]" id="rep-items">0</div>
                    <small class="text-gray-500">Selected month</small>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="text-xs text-gray-600 mb-1">Distinct Customers</div>
                    <div class="text-2xl font-extrabold text-[#4B2E0E]" id="rep-customers">0</div>
                    <small class="text-gray-500">Selected month</small>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-4">
                <div class="px-4 py-3 bg-gray-50 font-semibold text-gray-700">Daily Breakdown</div>
                <div class="px-4 pt-4 pb-2">
                    <div class="w-full h-64">
                        <canvas id="repChart"></canvas>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-white">
                            <tr class="text-left">
                                <th class="py-2 px-4">Date</th>
                                <th class="py-2 px-4">No. of Orders</th>
                                <th class="py-2 px-4">No. of Items Sold</th>
                                <th class="py-2 px-4">Revenue (₱)</th>
                            </tr>
                        </thead>
                        <tbody id="rep-daily" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 font-semibold text-gray-700">Products Sold (This Month)</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-white">
                            <tr class="text-left">
                                <th class="py-2 px-4">Product</th>
                                <th class="py-2 px-4">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="rep-products" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Mobile navigation logic
        const mobileNavToggle = document.getElementById('mobile-nav-toggle');
        const mobileNavPanel = document.getElementById('mobile-nav-panel');
        const mobileNavClose = document.getElementById('mobile-nav-close');
        const mobileNavBackdrop = document.getElementById('mobile-nav-backdrop');
        const logoutBtnMobile = document.getElementById('logout-btn-mobile');

        function closeMobileNav(){
            if(!mobileNavPanel) return;
            mobileNavPanel.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        mobileNavToggle?.addEventListener('click', ()=>{
            mobileNavPanel.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });
        mobileNavClose?.addEventListener('click', closeMobileNav);
        mobileNavBackdrop?.addEventListener('click', closeMobileNav);

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
        document.getElementById('exportExcelBtn').addEventListener('click', () => {
            // Trigger download of last 30 days sales data
            window.location.href = 'export_sales_excel.php?days=30';
        });
        document.getElementById("logout-btn").addEventListener("click", () => {
            Swal.fire({
                title: 'Log out?', text: "Are you sure you want to log out?", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#4B2E0E', cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, log out'
            }).then((result) => { if (result.isConfirmed) { window.location.href = "../all/logout"; } });
        });
        // Mobile logout proxies to desktop logic
        logoutBtnMobile?.addEventListener('click', ()=>{
            document.getElementById('logout-btn').click();
        });

                // --- Monthly Sales Report handlers ---
                const repMonth = document.getElementById('rep-month');
                const repCat = document.getElementById('rep-category');
                const repLoad = document.getElementById('rep-load');
                const repCSV = document.getElementById('rep-csv');
                const repRev = document.getElementById('rep-rev');
                const repOrders = document.getElementById('rep-orders');
                const repItems = document.getElementById('rep-items');
                const repCustomers = document.getElementById('rep-customers');
                const repDaily = document.getElementById('rep-daily');
                const repProducts = document.getElementById('rep-products');

            let repChartInst = null;
            async function loadMonthlyReport(){
                    if(!repMonth) return;
                    const month = repMonth.value;
                    const category = repCat ? repCat.value : 'All';
                    const fd = new FormData();
                    fd.append('action', 'monthly_report');
                    fd.append('month', month);
                    fd.append('category', category);
                    try{
                        const resp = await fetch('dashboard.php', { method:'POST', body: fd });
                        const data = await resp.json();
                        if(!data.success){
                            Swal.fire('Error', data.message || 'Failed to load report', 'error');
                            return;
                        }
                        // KPIs
                        const rev = Number(data.summary.revenue||0);
                        repRev.textContent = rev.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
                        repOrders.textContent = Number(data.summary.orders||0).toString();
                        repItems.textContent = Number(data.summary.items||0).toString();
                        repCustomers.textContent = Number(data.summary.customers||0).toString();
                                    // Daily table and mini chart
                        repDaily.innerHTML = (data.daily||[]).map(r=>`
                            <tr>
                                <td class="py-2 px-4">${r.d}</td>
                                <td class="py-2 px-4">${r.orders}</td>
                                <td class="py-2 px-4">${r.items}</td>
                                <td class="py-2 px-4">₱${Number(r.revenue||0).toLocaleString(undefined,{minimumFractionDigits:2})}</td>
                            </tr>
                        `).join('');
                                    // Mini chart (revenue by day in month)
                                    const labels = (data.daily||[]).map(r=>r.d);
                                    const series = (data.daily||[]).map(r=>Number(r.revenue||0));
                                    const ctx = document.getElementById('repChart')?.getContext('2d');
                                    if(ctx){
                                        if(repChartInst){ repChartInst.destroy(); }
                                        repChartInst = new Chart(ctx, {
                                            type: 'line',
                                            data: { labels, datasets: [{
                                                label: 'Sales',
                                                data: series,
                                                borderColor: '#C4A07A',
                                                backgroundColor: 'rgba(196, 160, 122, 0.15)',
                                                tension: 0.3, fill: true, pointRadius: 3
                                            }]},
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: { legend: { display: true }, tooltip: { callbacks: { label: (c)=> '₱' + (c.parsed.y||0).toLocaleString(undefined,{minimumFractionDigits:2}) } } },
                                                scales: { y: { beginAtZero: true, ticks: { callback: (v)=> '₱' + Number(v).toLocaleString() } } }
                                            }
                                        });
                                    }
                                    // Products
                        repProducts.innerHTML = (data.products||[]).map(p=>`
                            <tr>
                                <td class="py-2 px-4">${p.product}</td>
                                <td class="py-2 px-4">${p.qty}</td>
                            </tr>
                        `).join('');
                    }catch(e){
                        Swal.fire('Error', 'Network error while loading report', 'error');
                    }
                }

                function downloadCSV(){
                    const month = repMonth?.value || '';
                    const category = repCat?.value || 'All';
                    const rows = [];
                    rows.push(['Monthly Sales Report']);
                    rows.push(['Month', month]);
                    rows.push(['Category', category]);
                    rows.push([]);
                    rows.push(['Summary']);
                    rows.push(['Total Revenue','Total Orders','Items Sold','Distinct Customers']);
                    rows.push([
                        repRev?.textContent || '0.00',
                        repOrders?.textContent || '0',
                        repItems?.textContent || '0',
                        repCustomers?.textContent || '0'
                    ]);
                    rows.push([]);
                    rows.push(['Daily Breakdown']);
                    rows.push(['Date','No. of Orders','No. of Items Sold','Revenue (₱)']);
                    repDaily?.querySelectorAll('tr')?.forEach(tr => {
                        const cells = Array.from(tr.children).map(td=>td.textContent.trim());
                        rows.push(cells);
                    });
                    rows.push([]);
                    rows.push(['Products Sold (This Month)']);
                    rows.push(['Product','Qty']);
                    repProducts?.querySelectorAll('tr')?.forEach(tr => {
                        const cells = Array.from(tr.children).map(td=>td.textContent.trim());
                        rows.push(cells);
                    });
                    const csv = rows.map(r=>r.map(v=>`"${String(v).replace(/"/g,'""')}"`).join(',')).join('\n');
                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `monthly_report_${month || 'current'}.csv`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }

                repLoad?.addEventListener('click', loadMonthlyReport);
                repCSV?.addEventListener('click', downloadCSV);
                // Auto load initial month on page load
                if(repMonth){ loadMonthlyReport(); }
    </script>
 </body>
</html>