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
    </style>
</head>
<body class="min-h-screen flex">
  
  <?php if ($loggedInUserType == 'owner'): ?>
    <!-- Owner Sidebar -->
    <aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
        <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full mb-4" />
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
        <button title="Settings" onclick="window.location.href='../all/setting.php'">
            <i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
        </button>
        <button id="logout-btn" title="Logout">
            <i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i>
        </button>
    </aside>
  <?php elseif ($loggedInUserType == 'employee'): ?>
    <!-- Employee Sidebar -->
    <aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
        <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full mb-4" />
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
     <aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
        <img src="../images/logo.png" alt="Logo" style="width: 56px; height: 56px; border-radius: 9999px; margin-bottom: 25px;" />
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

        <div class="flex-grow flex items-stretch justify-stretch p-0">
            <div class="bg-white/90 w-full min-h-screen p-4 sm:p-8">
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
                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#c19a6b] hover:bg-[#a17850] text-white font-semibold">Save Changes</button>
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
                            <p id="pw-hint" class="text-xs text-gray-500 mt-1">Use 8+ characters with a mix of upper/lowercase, numbers, and symbols.</p>
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
                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#c19a6b] hover:bg-[#a17850] text-white font-semibold">Update Security</button>
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
                    icon: '<?php echo $updateResult['success'] ? "success" : "error"; ?>'
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
                        cancelButtonText: 'Cancel'
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
            let dirty = false; form?.addEventListener('input', ()=>{ dirty = true; });
            window.addEventListener('beforeunload', (e)=>{ if (dirty) { e.preventDefault(); e.returnValue=''; } });
            form?.addEventListener('submit', (e) => {
                const npw = newPw?.value.trim();
                const cpw = (document.getElementById('confirm_password')?.value || '').trim();
                const cur = (document.getElementById('current_password')?.value || '').trim();
                const phone = document.querySelector('input[name="phone"]').value.trim();
                const email = document.querySelector('input[name="email"]').value.trim();
                const errs = [];
                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errs.push('Please enter a valid email address.');
                if (phone && !/^[+0-9\-\s]{7,}$/.test(phone)) errs.push('Phone number can include digits, spaces, + or -.');
                if (npw) {
                    if (!cur) errs.push('Current password is required to set a new password.');
                    if (npw !== cpw) errs.push('New password and confirmation do not match.');
                    const s = scorePassword(npw); if (s < 60) errs.push('Please choose a stronger password.');
                }
                if (errs.length){ e.preventDefault(); Swal.fire({ icon:'warning', title:'Check your input', html: '<ul style="text-align:left">'+errs.map(x=>'<li>'+x+'</li>').join('')+'</ul>' }); return false; }
                dirty = false; // allow navigation on successful submit
            });
        });
    </script>
</body>
</html>