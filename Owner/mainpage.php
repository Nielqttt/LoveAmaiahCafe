<?php
session_start();
if (!isset($_SESSION['OwnerID'])) {
  header('Location: ../all/login.php');
  exit();
}
$ownerName = $_SESSION['OwnerFN'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Main Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-image: url('../images/LAbg.png');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
    }
    ::-webkit-scrollbar {
      width: 6px;
    }
    ::-webkit-scrollbar-thumb {
      background-color: #c4b09a;
      border-radius: 10px;
    }
  </style>
</head>
<body class="min-h-screen flex text-[#4B2E0E]">

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


  <!-- Main content -->
  <main class="flex-1 p-10 flex items-center justify-center text-center">
    <div class="bg-white bg-opacity-80 backdrop-blur-md rounded-2xl shadow-xl px-10 py-12 max-w-4xl w-100">

      <!-- greeting -->
      <h1 class="text-3xl font-extrabold mb-4">
        Welcome, <?php echo htmlspecialchars($ownerName); ?> 👋
      </h1>
      <p class="text-gray-700 mb-10">
        How would you like to place the order?
      </p>

      <form action="page.php" method="get" class="flex flex-col items-center gap-6">
        <label class="text-[#4B2E0E] font-semibold">
          Enter name:
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

      <div class="text-sm text-gray-400 mt-10"></div>
    </div>
  </main>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
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
          window.location.href = "../all/logout.php";
        }
      });
    });
  </script>
</body>
</html>