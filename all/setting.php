<?php
session_start();

require_once('../classes/database.php');
$con = new database();

$loggedInUserType = ''; 
$userID = null;
$pageTitle = "Settings"; 

if (isset($_SESSION['OwnerID'])) {
    $loggedInUserType = 'owner';
    $userID = $_SESSION['OwnerID'];
    $pageTitle = "Owner Settings";
} elseif (isset($_SESSION['EmployeeID'])) {
    $loggedInUserType = 'employee';
    $userID = $_SESSION['EmployeeID'];
    $pageTitle = "Employee Settings";
} elseif (isset($_SESSION['CustomerID'])) {
    $loggedInUserType = 'customer';
    $userID = $_SESSION['CustomerID'];
    $pageTitle = "Customer Settings";
} else {
    header('Location: login.php'); 
    exit();
}


$updateResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $updateResult = $con->updateUserData($userID, $loggedInUserType, $_POST); 
}

$userData = $con->getUserData($userID, $loggedInUserType);

if (empty($userData)) {
    echo "Error: User could not be found. Please try logging in again.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="../images/logo.png" type="image/png"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url('../images/LAbg.png') no-repeat center center fixed;
            background-size: cover;
        }
        /* Fixed sidebar width (70px) consistent with other pages */
        .la-sidebar { width:70px; min-width:70px; flex:0 0 70px; }
        .la-sidebar img { width:48px; height:48px; }
        /* SweetAlert theme matching product/registration popups */
        .swal2-popup.ae-ap-popup { background: #F7F2EC; box-shadow: 0 12px 32px rgba(75,46,14,0.18), inset 0 1px 0 rgba(255,255,255,0.65); border-radius: 24px; padding: 24px 28px !important; }
        .swal2-popup.ae-narrow { width: min(520px, 92vw) !important; }
        .swal2-title { color: #21160E; font-weight: 800; }
        .swal2-confirm { background: linear-gradient(180deg, #A1764E 0%, #7C573A 100%) !important; color: #fff !important; border-radius: 9999px !important; border: 1px solid rgba(255,255,255,0.75) !important; box-shadow: inset 0 2px 0 rgba(255,255,255,0.6), inset 0 -2px 0 rgba(0,0,0,0.06), 0 4px 12px rgba(75,46,14,0.25) !important; }
        .swal2-deny { background: #CFCAC4 !important; color: #21160E !important; border-radius: 9999px !important; border: 3px solid rgba(255,255,255,0.85) !important; }
        .swal2-cancel { border-radius: 9999px !important; }
        .swal2-input { box-sizing: border-box !important; width: 100% !important; max-width: 100% !important; padding: 12px 16px !important; border-radius: 16px !important; border: 2px solid #ddd !important; outline: none !important; font-size: 14px !important; margin: 8px 6px !important; }
        .swal2-input:focus { border-color: #C4A07A !important; box-shadow: 0 0 0 3px rgba(196,160,122,0.2) !important; }
        .ae-ap-popup .swal2-html-container, .ae-ap-popup .swal2-actions { padding: 0 6px !important; }
    </style>
</head>
<body class="min-h-screen md:h-screen flex flex-col md:flex-row md:overflow-hidden">
    <!-- Mobile Top Bar -->
    <div class="md:hidden flex items-center justify-between px-4 py-2 bg-white shadow sticky top-0 z-40">
        <div class="flex items-center gap-2">
            <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full" />
            <span class="font-semibold text-[#4B2E0E] text-lg">Settings</span>
        </div>
        <button id="mobile-nav-toggle" class="p-2 rounded-full border border-[#4B2E0E] text-[#4B2E0E]"><i class="fa-solid fa-bars"></i></button>
    </div>

    <!-- Mobile Slide-over Nav -->
    <div id="mobile-nav-panel" class="md:hidden fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" id="mobile-nav-backdrop"></div>
        <div class="absolute left-0 top-0 h-full w-60 bg-white shadow-lg p-4 flex flex-col gap-4 overflow-y-auto">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-[#4B2E0E] font-semibold">Navigation</h2>
                <button id="mobile-nav-close" class="text-gray-500 text-xl"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <?php $currentMobile = basename($_SERVER['PHP_SELF']); ?>
            <nav class="flex flex-col gap-2 text-sm">
                <?php if ($loggedInUserType == 'owner'): ?>
                    <a href="../Owner/dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='dashboard.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-chart-line"></i> Dashboard</a>
                    <a href="../Owner/mainpage.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='mainpage.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-home"></i> Home</a>
                    <a href="../Owner/page.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='page.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-shopping-cart"></i> Cart</a>
                    <a href="../all/tranlist.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='tranlist.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-list"></i> Orders</a>
                    <a href="../Owner/product.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='product.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-box"></i> Products</a>
                    <a href="../Owner/user.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='user.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-users"></i> Employees</a>
                    <a href="../Owner/customers.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='customers.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-user"></i> Customers</a>
                    <a href="../all/setting.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='setting.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-cog"></i> Settings</a>
                <?php elseif ($loggedInUserType == 'employee'): ?>
                    <a href="../Employee/employesmain.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='employesmain.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-home"></i> Home</a>
                    <a href="../Employee/employeepage.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='employeepage.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-shopping-cart"></i> Cart</a>
                    <a href="../all/tranlist.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='tranlist.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-list"></i> Orders</a>
                    <a href="../Employee/productemployee.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='productemployee.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-box"></i> Products</a>
                    <a href="../all/setting.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='setting.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-cog"></i> Settings</a>
                <?php elseif ($loggedInUserType == 'customer'): ?>
                    <a href="../Customer/advertisement.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='advertisement.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-home"></i> Home</a>
                    <a href="../Customer/customerpage.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='customerpage.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-shopping-cart"></i> Cart</a>
                        <a href="../Customer/transactionrecords.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='transactionrecords.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-list"></i> Transactions</a>
                    <a href="../all/setting.php" class="flex items-center gap-2 px-3 py-2 rounded-md border <?= $currentMobile=='setting.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]'?>"><i class="fas fa-cog"></i> Settings</a>
                <?php endif; ?>
                <button id="logout-btn-mobile" class="flex items-center gap-2 px-3 py-2 rounded-md border border-gray-300 text-[#4B2E0E] text-left"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </nav>
        </div>
    </div>
  
<?php /* */ ?>
  <?php if ($loggedInUserType == 'owner'): ?>
    <!-- Owner Sidebar -->
    <aside class="hidden md:flex bg-white flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
        <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
        <?php $current = basename($_SERVER['PHP_SELF']); ?>   
        <button title="Dashboard" onclick="window.location.href='../Owner/dashboard.php'">
            <i class="fas fa-chart-line text-xl <?= $current == 'dashboard.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button title="Home" onclick="window.location.href='../Owner/mainpage.php'">
            <i class="fas fa-home text-xl <?= $current == 'mainpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button title="Orders" onclick="window.location.href='../Owner/page.php'">
            <i class="fas fa-shopping-cart text-xl <?= $current == 'page.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button title="Order List" onclick="window.location.href='../all/tranlist.php'">
            <i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button title="Inventory" onclick="window.location.href='../Owner/product.php'">
            <i class="fas fa-box text-xl <?= $current == 'product.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button title="Users" onclick="window.location.href='../Owner/user.php'">
            <i class="fas fa-users text-xl <?= $current == 'user.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button title="Customers" onclick="window.location.href='../Owner/customers.php'">
            <i class="fas fa-user text-xl <?= $current == 'customers.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button title="Settings" onclick="window.location.href='../all/setting.php'">
            <i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button id="logout-btn" title="Logout">
            <i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i>
        </button>
    </aside>
  <?php elseif ($loggedInUserType == 'employee'): ?>
    <!-- Employee Sidebar -->
    <aside class="hidden md:flex bg-white flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
        <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
        <?php $current = basename($_SERVER['PHP_SELF']); ?>   
        <button title="Home" onclick="window.location.href='../Employee/employesmain.php'">
            <i class="fas fa-home text-xl <?= $current == 'employesmain.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button title="Cart" onclick="window.location.href='../Employee/employeepage.php'">
            <i class="fas fa-shopping-cart text-xl <?= $current == 'employeepage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button title="Transaction Records" onclick="window.location.href='../all/tranlist.php'">
            <i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button title="Box" onclick="window.location.href='../Employee/productemployee.php'">
            <i class="fas fa-box text-xl <?= $current == 'productemployee.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button title="Settings" onclick="window.location.href='../all/setting.php'">
            <i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button id="logout-btn" title="Logout">
            <i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i>
        </button>
    </aside>
  <?php elseif ($loggedInUserType == 'customer'): ?>
    <!-- Customer Sidebar -->
     <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <aside class="hidden md:flex bg-white flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
          <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
        <button aria-label="Home" class="text-xl" title="Home" type="button" onclick="window.location='../Customer/advertisement.php'">
            <i class="fas fa-home <?= $currentPage === 'advertisement.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button aria-label="Cart" class="text-xl" title="Cart" type="button" onclick="window.location='../Customer/customerpage.php'">
            <i class="fas fa-shopping-cart <?= $currentPage === 'customerpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button aria-label="Order List" class="text-xl" title="Order List" type="button" onclick="window.location='../Customer/transactionrecords.php'">
            <i class="fas fa-list <?= $currentPage === 'transactionrecords.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button aria-label="Settings" class="text-xl" title="Settings" type="button" onclick="window.location='../all/setting.php'">
            <i class="fas fa-cog <?= $currentPage === 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button id="logout-btn" aria-label="Logout" name="logout" class="text-xl" title="Logout" type="button">
            <i class="fas fa-sign-out-alt text-[#4B2E0E]"></i>
        </button>
    </aside>
    <?php endif; ?>

        <div class="flex-grow flex items-stretch justify-stretch p-0 min-w-0">
            <div class="bg-white/90 w-full min-h-screen p-4 sm:p-8 overflow-y-auto">
                <div class="flex items-center justify-between flex-wrap gap-3 pb-4 mb-6 border-b border-gray-200">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#4B2E0E]">Account Settings</h2>
                <span class="text-xs sm:text-sm text-gray-500">Signed in as <strong><?= htmlspecialchars($userData['username'] ?? '') ?></strong> — <?= htmlspecialchars(ucfirst($loggedInUserType)) ?></span>
            </div>

                <form id="settings-form" method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Profile -->
                    <section class="col-span-1 bg-white/80 border border-gray-200 rounded-xl p-4 sm:p-6 h-full">
                    <h3 class="text-lg font-bold text-[#4B2E0E] mb-4">Profile</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[#4B2E0E] font-semibold mb-1">Username</label>
                            <input autocomplete="username" type="text" name="username" value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-[#c19a6b] focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-[#4B2E0E] font-semibold mb-1">Name</label>
                            <input autocomplete="name" type="text" name="name" value="<?php echo htmlspecialchars($userData['name'] ?? ''); ?>" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-[#c19a6b] focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-[#4B2E0E] font-semibold mb-1">Email</label>
                            <input autocomplete="email" type="email" name="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-[#c19a6b] focus:outline-none" required>
                            <p class="text-xs text-gray-500 mt-1">We’ll use this for receipts and account recovery.</p>
                        </div>
                        <div>
                            <label class="block text-[#4B2E0E] font-semibold mb-1">Phone Number</label>
                            <input inputmode="tel" pattern="[+0-9\-\s]{7,}" title="Digits, spaces, + or - only" type="text" name="phone" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-[#c19a6b] focus:outline-none" required>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="reset" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Reset</button>
                            <button type="submit" data-submit="profile" class="px-4 py-2 rounded-lg bg-[#c19a6b] hover:bg-[#a17850] text-white font-semibold">Save Changes</button>
                        </div>
                    </div>
                </section>

                <!-- Security -->
            <section class="col-span-1 bg-white/80 border border-gray-200 rounded-xl p-4 sm:p-6 h-full">
                    <h3 class="text-lg font-bold text-[#4B2E0E] mb-4">Security</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[#4B2E0E] font-semibold mb-1">New Password <span class="text-xs text-gray-400">(leave blank to keep current)</span></label>
                            <div class="relative">
                                <input id="new_password" autocomplete="new-password" type="password" name="new_password" class="w-full pr-12 px-4 py-3 rounded-lg border border-gray-300 focus:ring-[#c19a6b] focus:outline-none" placeholder="Enter new password">
                                <button type="button" data-toggle-password="#new_password" class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
                            </div>
                            <div id="pw-meter" class="mt-2 h-2 rounded bg-gray-200 overflow-hidden">
                                <div id="pw-meter-bar" class="h-2 w-0 bg-red-500 transition-all"></div>
                            </div>
                            <p id="pw-hint" class="text-xs text-gray-500 mt-1">Use 6+ characters with a mix of upper/lowercase, numbers, and symbols.</p>
                        </div>
                        <div>
                            <label class="block text-[#4B2E0E] font-semibold mb-1">Confirm New Password</label>
                            <div class="relative">
                                <input id="confirm_password" autocomplete="new-password" type="password" name="confirm_password" class="w-full pr-12 px-4 py-3 rounded-lg border border-gray-300 focus:ring-[#c19a6b] focus:outline-none" placeholder="Re-enter new password">
                                <button type="button" data-toggle-password="#confirm_password" class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-1">Current Password <span class="text-xs font-normal text-gray-500">(required when changing password)</span></label>
                            <div class="relative">
                                <input id="current_password" autocomplete="current-password" type="password" name="current_password" class="w-full pr-12 px-4 py-3 rounded-lg border border-gray-300 focus:ring-red-500 focus:outline-none" placeholder="Enter current password">
                                <button type="button" data-toggle-password="#current_password" class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="submit" data-submit="security" class="px-4 py-2 rounded-lg bg-[#c19a6b] hover:bg-[#a17850] text-white font-semibold">Update Security</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
  
            <?php if ($updateResult !== null): ?>
                Swal.fire({
                    title: '<?php echo $updateResult['success'] ? "Success!" : "Error!"; ?>',
                    text: '<?php echo addslashes($updateResult['message']); ?>',
                    icon: '<?php echo $updateResult['success'] ? "success" : "error"; ?>',
                    customClass: { popup: 'ae-ap-popup ae-narrow' }
                });
            <?php endif; ?>


            const logoutBtn = document.getElementById("logout-btn");
            if (logoutBtn) {
                logoutBtn.addEventListener("click", () => {
                    Swal.fire({
                        title: 'Are you sure you want to log out?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#4B2E0E',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, log out',
                        cancelButtonText: 'Cancel',
                        customClass: { popup: 'ae-ap-popup ae-narrow' }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            <?php if ($loggedInUserType == 'customer'): ?>
                                window.location.href = "../all/logoutcos.php";
                            <?php else: ?>
                                window.location.href = "../all/logout.php"; 
                            <?php endif; ?>
                        }
                    });
                });
            }

            // Toggle password visibility
            document.querySelectorAll('[data-toggle-password]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const sel = btn.getAttribute('data-toggle-password');
                    const input = document.querySelector(sel);
                    if (!input) return;
                    input.type = input.type === 'password' ? 'text' : 'password';
                    btn.querySelector('i')?.classList.toggle('fa-eye');
                    btn.querySelector('i')?.classList.toggle('fa-eye-slash');
                });
            });

            // Password strength meter
            const newPw = document.getElementById('new_password');
            const meter = document.getElementById('pw-meter-bar');
            const hint = document.getElementById('pw-hint');
            function scorePassword(pw){
                let score = 0; if (!pw) return 0; const tests = [/[a-z]/, /[A-Z]/, /\d/, /[^A-Za-z0-9]/];
                score += Math.min(10, pw.length) * 5; tests.forEach(rx => { if (rx.test(pw)) score += 20; });
                return Math.min(100, score);
            }
            function renderMeter(){
                const s = scorePassword(newPw.value);
                meter.style.width = s + '%';
                meter.className = 'h-2 transition-all ' + (s<40?'bg-red-500':s<70?'bg-yellow-500':'bg-green-500');
                hint.textContent = s<70? 'Use 8+ characters with upper/lowercase, numbers, and symbols.' : 'Strong password';
            }
            if (newPw){ newPw.addEventListener('input', renderMeter); renderMeter(); }

            // Client-side validation before submit
            const form = document.getElementById('settings-form');
            let submitType = 'profile';
            document.querySelectorAll('button[type="submit"][data-submit]').forEach(btn => {
                btn.addEventListener('click', () => { submitType = btn.getAttribute('data-submit') || 'profile'; });
            });
            let dirty = false; form?.addEventListener('input', ()=>{ dirty = true; });
            window.addEventListener('beforeunload', (e)=>{ if (dirty) { e.preventDefault(); e.returnValue=''; } });
            form?.addEventListener('submit', async (e) => {
                e.preventDefault();
                // Determine which section the user is saving
                const isSecuritySubmit = (submitType === 'security');

                const npw = newPw?.value.trim();
                const cpw = (document.getElementById('confirm_password')?.value || '').trim();
                const cur = (document.getElementById('current_password')?.value || '').trim();
                const phone = document.querySelector('input[name="phone"]').value.trim();
                const email = document.querySelector('input[name="email"]').value.trim();
                const username = document.querySelector('input[name="username"]').value.trim();
                const name = document.querySelector('input[name="name"]').value.trim();

                const errs = [];
                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errs.push('Please enter a valid email address.');
                if (phone && !/^[+0-9\-\s]{7,}$/.test(phone)) errs.push('Phone number can include digits, spaces, + or -.');
                // Only validate password fields when updating security explicitly
                if (isSecuritySubmit) {
                    if (npw) {
                        if (!cur) errs.push('Current password is required to set a new password.');
                        if (npw !== cpw) errs.push('New password and confirmation do not match.');
                        const s = scorePassword(npw); if (s < 40) errs.push('Please choose a stronger password.');
                    } else {
                        errs.push('Please enter a new password to update your security.');
                    }
                }
                if (errs.length){ Swal.fire({ icon:'warning', title:'Check your input', html: '<ul style="text-align:left">'+errs.map(x=>'<li>'+x+'</li>').join('')+'</ul>', customClass: { popup: 'ae-ap-popup ae-narrow' } }); return false; }

                // Decide if OTP is required (employee or customer only)
                const userType = '<?php echo $loggedInUserType; ?>';
                const payload = { username, name, email, phone, section: (isSecuritySubmit ? 'security' : 'profile') };
                // Only send password fields when updating security
                if (isSecuritySubmit) { payload.new_password = npw; payload.current_password = cur; payload.confirm_password = cpw; }

                async function doUpdate() {
                    try {
                        const resp = await fetch('../ajax/update_user_settings.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload)
                        });
                        const data = await resp.json();
                        if (data.success) {
                            dirty = false;
                            Swal.fire({
                                icon:'success',
                                title:'Done!',
                                text: data.message || 'All set.',
                                timer: 1400,
                                showConfirmButton: false,
                                customClass: { popup: 'ae-ap-popup ae-narrow' }
                            }).then(()=>{ window.location.reload(); });
                        } else {
                            Swal.fire({ icon:'error', title:'Not saved', text: data.message || 'Please try again.', customClass: { popup: 'ae-ap-popup ae-narrow' } });
                        }
                    } catch (err) {
                        Swal.fire({ icon:'error', title:'Network error', text:'Please try again.', customClass: { popup: 'ae-ap-popup ae-narrow' } });
                    }
                }

                const needsOtp = (userType === 'employee' || userType === 'customer') && isSecuritySubmit;
                if (needsOtp) {
                    // First, verify current password before sending OTP
                    try {
                        const curCheck = await fetch('../ajax/check_current_password.php', {
                            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
                            body: JSON.stringify({ current_password: cur })
                        });
                        const curData = await curCheck.json();
                        if (!curData.success) {
                            Swal.fire({ icon:'error', title:'Old password mismatch', text: curData.message || 'Old password is incorrect.', customClass:{ popup:'ae-ap-popup ae-narrow' } });
                            return false;
                        }
                    } catch (err) {
                        Swal.fire({ icon:'error', title:'Unable to verify', text:'Please try again.', customClass:{ popup:'ae-ap-popup ae-narrow' } });
                        return false;
                    }
                    // Show OTP prompt immediately, send OTP in background, enforce manual resend cooldown
                    let cooldown = 30;
                    let canResend = false;
                    let timerId = null;
                    let targetEmail = email;

                    const updateDenyButton = () => {
                        const btn = Swal.getDenyButton();
                        if (btn) { btn.disabled = !canResend; btn.textContent = canResend ? 'Resend code' : ('Resend in ' + cooldown + 's'); }
                    };

                    async function sendOtpInternal() {
                        try {
                            const resp = await fetch('../ajax/send_otp.php', {
                                method: 'POST', headers:{ 'Content-Type':'application/json' }, credentials:'same-origin',
                                body: JSON.stringify({ email: targetEmail })
                            });
                            const data = await resp.json();
                            if (data.success) {
                                targetEmail = data.email || targetEmail;
                                cooldown = data.cooldown || 30;
                                canResend = false;
                                const info = Swal.getHtmlContainer()?.querySelector('#otp-info');
                                if (info) info.textContent = 'We emailed a 6-digit code to ' + targetEmail + '.';
                                const err = Swal.getHtmlContainer()?.querySelector('#otp-error');
                                if (err) err.textContent = '';
                            } else {
                                const err = Swal.getHtmlContainer()?.querySelector('#otp-error');
                                if (err) err.textContent = (data.message || 'Failed to send code. You can try resending after the cooldown.');
                                if (typeof data.cooldown === 'number') { cooldown = data.cooldown; }
                            }
                            updateDenyButton();
                        } catch (e) {
                            const err = Swal.getHtmlContainer()?.querySelector('#otp-error');
                            if (err) err.textContent = 'Network error while sending the code.';
                            updateDenyButton();
                        }
                    }

                    const result = await Swal.fire({
                        title: 'Verify your email',
                        html: '<p id="otp-info">Sending a 6-digit code to <b>' + targetEmail + '</b>...</p>' +
                              '<p id="otp-error" style="color:#b91c1c; margin-top:6px;"></p>',
                        input: 'text', inputPlaceholder: 'e.g. 123456', inputAttributes: { maxlength: 6, inputmode: 'numeric' },
                        showCancelButton: true, confirmButtonText: 'Verify', cancelButtonText: 'Cancel',
                        showDenyButton: true, denyButtonText: 'Resend in ' + cooldown + 's', allowOutsideClick: false,
                        customClass: { popup: 'ae-ap-popup ae-narrow' },
                        didOpen: () => {
                            updateDenyButton();
                            // Start countdown timer
                            timerId = setInterval(() => { if (!canResend) { cooldown -= 1; if (cooldown <= 0) { canResend = true; } updateDenyButton(); } }, 1000);
                            // Kick off initial send
                            sendOtpInternal();
                        },
                        willClose: () => { clearInterval(timerId); },
                        preDeny: async () => {
                            // Handle resend without closing the modal
                            if (!canResend) { return false; }
                            await sendOtpInternal();
                            return false; // keep modal open
                        },
                        preConfirm: async (code) => {
                            const c = String(code || '').trim();
                            if (!/^\d{6}$/.test(c)) { Swal.showValidationMessage('Enter the 6-digit code'); return false; }
                            try {
                                const verifyResp = await fetch('../ajax/verify_otp.php', {
                                    method:'POST', headers:{ 'Content-Type':'application/json' }, credentials:'same-origin', body: JSON.stringify({ otp: c })
                                });
                                const verifyData = await verifyResp.json();
                                if (!verifyData.success) { Swal.showValidationMessage(verifyData.message || 'Invalid or expired code'); return false; }
                                return true;
                            } catch (e) {
                                Swal.showValidationMessage('Network error. Please try again.');
                                return false;
                            }
                        }
                    });

                    if (result.isConfirmed) { await doUpdate(); }
                } else {
                    // Owner: no OTP requirement (can be enabled if needed)
                    await doUpdate();
                }
            });
            // Mobile nav toggle logic
            const mobileNavToggle = document.getElementById('mobile-nav-toggle');
            const mobileNavPanel = document.getElementById('mobile-nav-panel');
            const mobileNavClose = document.getElementById('mobile-nav-close');
            const mobileNavBackdrop = document.getElementById('mobile-nav-backdrop');
            const logoutBtnMobile = document.getElementById('logout-btn-mobile');
            function closeMobileNav(){ mobileNavPanel?.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
            if(mobileNavToggle){ mobileNavToggle.addEventListener('click', ()=>{ mobileNavPanel.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }); }
            mobileNavClose?.addEventListener('click', closeMobileNav);
            mobileNavBackdrop?.addEventListener('click', closeMobileNav);
            if(logoutBtnMobile){ logoutBtnMobile.addEventListener('click', ()=>{ logoutBtn?.click(); }); }

        });
    </script>
</body>
</html>