<?php
session_start();
if (!isset($_SESSION['CustomerID'])) {
  header('Location: ../all/login.php');
  exit();
}
$customer = $_SESSION['CustomerFN'];
$currentPage = basename($_SERVER['PHP_SELF']);

// Load products & categories for signature showcase
require_once('../classes/database.php');
$con = new database();
$products = $con->getAllProductsWithPrice();
// Group products by category (case-insensitive keys preserved as original label)
$byCategory = [];
foreach ($products as $p) {
  $cat = trim($p['ProductCategory'] ?? 'Uncategorized');
  if (!isset($byCategory[$cat])) { $byCategory[$cat] = []; }
  $byCategory[$cat][] = $p;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LoveAmiah - Advertisement</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .main-content {
      flex-grow: 1;
      padding: 5vw;
      color: white;
      background: rgba(0, 0, 0, 0.3);
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .hero {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: flex-end;
      margin-bottom: 50px;
      gap: 60px;
      text-align: right;
    }
    .hero img {
      width: 100%;
      max-width: 800px;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    .hero-text {
      max-width: 700px;
      text-align: right;
    }
    .hero-text h1 {
      font-size: 4.5em;
      font-weight: bold;
      margin-bottom: 20px;
      line-height: 1.2;
    }
    .hero-text h1 span {
      color: #a17850;
    }
    .hero-text p {
      font-size: 1.7em;
      margin-bottom: 30px;
      line-height: 1.5;
    }
    .hero-text button {
      padding: 14px 32px;
      font-size: 1.2em;
      border: 2px solid white;
      border-radius: 6px;
      background: transparent;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .hero-text button:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }
    .coffee-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 2.5rem;
    }
    .card {
      background-color: #444;
      border-radius: 16px;
      overflow: hidden;
      color: white;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.4);
    }
    .card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }
    .card-body {
      padding: 16px;
    }
    .card h3 {
      font-size: 1.4em;
      margin-bottom: 10px;
      color: #a17850;
    }
    .card p {
      font-size: 1em;
      line-height: 1.5;
    }
  </style>
</head>
<body class="flex min-h-screen bg-cover bg-center bg-no-repeat" style="background-image: url('../images/LAbg.png');">

<!-- Sidebar (updated using Tailwind) -->
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
  <aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
  <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
  <button aria-label="Home" title="Home" type="button" onclick="window.location='../Customer/advertisement'">
    <i class="text-xl fas fa-home <?= $currentPage === 'advertisement.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
  </button>
  <button aria-label="Cart" title="Cart" type="button" onclick="window.location='../Customer/customerpage'">
    <i class="text-xl fas fa-shopping-cart <?= $currentPage === 'customerpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
  </button>
  <button aria-label="Order List" title="Order List" type="button" onclick="window.location='../Customer/transactionrecords'">
    <i class="text-xl fas fa-list <?= $currentPage === 'transactionrecords.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
  </button>
  <button aria-label="Settings" title="Settings" type="button" onclick="window.location='../all/setting'">
    <i class="text-xl fas fa-cog <?= $currentPage === 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
  </button>
  <button id="logout-btn" aria-label="Logout" name="logout" title="Logout" type="button">
    <i class="text-xl fas fa-sign-out-alt text-[#4B2E0E]"></i>
  </button>
</aside>

<!-- Main Content (unchanged) -->
<div class="main-content">
  <div class="hero">
    <img src="../images/mainpage_coffee.png" alt="Latte Art" />
    <div class="hero-text">
      <h1>Sip Happiness<br><span>One Cup at a Time</span></h1>
      <p>Begin your day with a cup of coffee—boost your energy, sharpen your focus, and set the tone for a productive, positive day ahead.</p>
      <button onclick="window.location.href='customerpage.php'">Order Coffee</button>
    </div>
  </div>
  <div class="coffee-cards">
    <?php
  // Dynamic SIGNATURE category cards (show all). Falls back to first available items if no signature category exists.
      // Assumption: product data & categories are accessible here via an include earlier on the page or session; if not, integrate fetch above.
      $signatureItems = [];
      if (!empty($byCategory)) {
        foreach ($byCategory as $catName => $items) {
          if (stripos($catName, 'SIGNATURE') !== false) { // match any category containing SIGNATURE
            $signatureItems = $items;
            break;
          }
        }
        // Fallback: if still empty, use first category's items
        if (empty($signatureItems)) {
          $first = reset($byCategory);
          if (is_array($first)) {
            $signatureItems = $first;
          }
        }
      }

  // Previously limited to 4 items; now display all signature products.

      if (!empty($signatureItems)) {
        foreach ($signatureItems as $prod) {
          $pName = htmlspecialchars($prod['ProductName'] ?? 'Coffee');
          $pDesc = htmlspecialchars($prod['Description'] ?? 'Delicious handcrafted beverage.');
          $img   = !empty($prod['ImagePath']) ? '../uploads/' . htmlspecialchars($prod['ImagePath']) : '../images/logo.png';
          echo '<div class="card">';
          echo '  <img src="' . $img . '" alt="' . $pName . '">';
          echo '  <div class="card-body">';
          echo '    <h3>' . $pName . '</h3>';
          echo '    <p>' . $pDesc . '</p>';
          echo '  </div>';
          echo '</div>';
        }
      } else {
        // Graceful fallback if no products found at all
        echo '<div class="card"><div class="card-body"><h3>No Products</h3><p>Signature offerings will appear here soon.</p></div></div>';
      }
    ?>
  </div>
</div>

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
        window.location.href = "../all/logoutcos.php";
      }
    });
  });
</script>
</body>
</html