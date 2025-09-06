<?php
// Unified sidebar based on setting.php style
if(!isset($current)) { $current = basename($_SERVER['PHP_SELF']); }
$role = '';
if(isset($_SESSION['OwnerID'])) $role='owner';
elseif(isset($_SESSION['EmployeeID'])) $role='employee';
elseif(isset($_SESSION['CustomerID'])) $role='customer';
?>
<aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
  <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full mb-4" />
  <?php if($role==='owner'): ?>
    <button title="Dashboard" onclick="window.location.href='../Owner/dashboard.php'"><i class="fas fa-chart-line text-xl <?= $current=='dashboard.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button title="Home" onclick="window.location.href='../Owner/mainpage.php'"><i class="fas fa-home text-xl <?= $current=='mainpage.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button title="Orders" onclick="window.location.href='../Owner/page.php'"><i class="fas fa-shopping-cart text-xl <?= $current=='page.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button title="Order List" onclick="window.location.href='../all/tranlist.php'"><i class="fas fa-list text-xl <?= $current=='tranlist.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button title="Inventory" onclick="window.location.href='../Owner/product.php'"><i class="fas fa-box text-xl <?= $current=='product.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button title="Users" onclick="window.location.href='../Owner/user.php'"><i class="fas fa-users text-xl <?= $current=='user.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button title="Settings" onclick="window.location.href='../all/setting.php'"><i class="fas fa-cog text-xl <?= $current=='setting.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
  <?php elseif($role==='employee'): ?>
    <button title="Home" onclick="window.location.href='../Employee/employesmain.php'"><i class="fas fa-home text-xl <?= $current=='employesmain.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button title="Cart" onclick="window.location.href='../Employee/employeepage.php'"><i class="fas fa-shopping-cart text-xl <?= $current=='employeepage.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button title="Transaction Records" onclick="window.location.href='../all/tranlist.php'"><i class="fas fa-list text-xl <?= $current=='tranlist.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button title="Box" onclick="window.location.href='../Employee/productemployee.php'"><i class="fas fa-box text-xl <?= $current=='productemployee.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button title="Settings" onclick="window.location.href='../all/setting.php'"><i class="fas fa-cog text-xl <?= $current=='setting.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
  <?php elseif($role==='customer'): ?>
    <button aria-label="Home" title="Home" type="button" onclick="window.location='../Customer/advertisement.php'"><i class="fas fa-home <?= $current=='advertisement.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button aria-label="Cart" title="Cart" type="button" onclick="window.location='../Customer/customerpage.php'"><i class="fas fa-shopping-cart <?= $current=='customerpage.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button aria-label="Order List" title="Order List" type="button" onclick="window.location='../Customer/transactionrecords.php'"><i class="fas fa-list <?= $current=='transactionrecords.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
    <button aria-label="Settings" title="Settings" type="button" onclick="window.location='../all/setting.php'"><i class="fas fa-cog <?= $current=='setting.php'?'text-[#C4A07A]':'text-[#4B2E0E]' ?>"></i></button>
  <?php endif; ?>
  <?php if($role): ?>
    <button id="logout-btn" title="Logout"><i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i></button>
  <?php endif; ?>
</aside>
