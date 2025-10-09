<?php

session_start();

if (!isset($_SESSION['CustomerID'])) {

  if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
  header('Location: ../all/coffee');
      exit();
  }
}

require_once('../classes/database.php');
$con = new database();

// AJAX endpoint for order submission with optional GCash receipt upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajaxOrderSubmit'])) {
  header('Content-Type: application/json');
  if (!isset($_SESSION['CustomerID'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired, please login again.']);
    exit();
  }
  $customerID = (int)$_SESSION['CustomerID'];
  $paymentMethod = $_POST['paymentMethod'] ?? 'gcash';
  $orderDataRaw = $_POST['orderData'] ?? '[]';
  $orderData = json_decode($orderDataRaw, true);
  if (!is_array($orderData) || empty($orderData)) {
    echo json_encode(['success' => false, 'message' => 'Order data missing.']);
    exit();
  }
  $receiptPath = null;
  if ($paymentMethod === 'gcash' && isset($_FILES['gcashReceipt']) && $_FILES['gcashReceipt']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/receipts/';
    if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }
    $ext = strtolower(pathinfo($_FILES['gcashReceipt']['name'], PATHINFO_EXTENSION));
    $validExt = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext, $validExt)) {
      echo json_encode(['success' => false, 'message' => 'Invalid file type.']);
      exit();
    }
    if ($_FILES['gcashReceipt']['size'] > 5 * 1024 * 1024) {
      echo json_encode(['success' => false, 'message' => 'File too large (max 5MB).']);
      exit();
    }
    $safeName = 'gcash_' . date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
    $target = $uploadDir . $safeName;
    if (move_uploaded_file($_FILES['gcashReceipt']['tmp_name'], $target)) {
      $receiptPath = 'uploads/receipts/' . $safeName; // Storable relative path
    }
  }
  $result = $con->processOrder($orderData, $paymentMethod, $customerID, 'customer', $receiptPath);
  echo json_encode($result);
  exit();
}

$customer = isset($_SESSION['CustomerFN']) ? $_SESSION['CustomerFN'] : 'Guest';
$products = $con->getAllProductsWithPrice();
$categories = $con->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="../images/logo.png" type="image/png"/>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>Customer Order Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    #menu-scroll::-webkit-scrollbar { width: 6px; }
    #menu-scroll::-webkit-scrollbar-thumb { background-color: #c4b09a; border-radius: 10px; }
    /* Thinner horizontal scrollbar for category nav */
    #category-nav { scrollbar-width: thin; scrollbar-color: #c4b09a transparent; }
    #category-nav::-webkit-scrollbar { height:4px; }
    #category-nav::-webkit-scrollbar-track { background: transparent; }
    #category-nav::-webkit-scrollbar-thumb { background-color:#c4b09a; border-radius:10px; }
    #category-nav:hover::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); }
    /* Lock sidebar width */
    .la-sidebar { width:70px; min-width:70px; flex:0 0 70px; }
    .la-sidebar img { width:48px; height:48px; }
    /* Extra small screens: force single column */
    @media (max-width:380px){
      #menu-items { display:grid; grid-template-columns:1fr !important; }
    }
    /* Mobile order summary drawer */
    @media (max-width:767px){
      #order-summary { position:fixed; bottom:0; left:0; right:0; max-height:70vh; overflow-y:auto; transform:translateY(100%); transition:transform .3s ease; border-top-left-radius:1rem; border-top-right-radius:1rem; box-shadow:0 -6px 16px -4px rgba(0,0,0,0.25); }
      #order-summary.open { transform:translateY(0); }
      body.drawer-open { overflow:hidden; }
    }
  </style>
</head>
<body class="bg-[rgba(255,255,255,0.7)] min-h-screen md:h-screen flex flex-col md:flex-row md:overflow-hidden">
  <!-- Mobile Top Bar -->
  <div class="md:hidden flex items-center justify-between px-4 py-2 bg-white/90 backdrop-blur-sm shadow sticky top-0 z-30">
    <div class="flex items-center gap-2">
      <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full" />
      <span class="font-semibold text-[#4B2E0E] text-lg">Menu</span>
    </div>
    <div class="flex items-center gap-2">
      <button id="mobile-order-toggle" class="relative px-3 py-1 rounded-full bg-[#4B2E0E] text-white text-sm font-semibold">Order (<span id="order-count">0</span>)</button>
      <button id="mobile-nav-toggle" class="p-2 rounded-full border border-[#4B2E0E] text-[#4B2E0E]"><i class="fa-solid fa-bars"></i></button>
    </div>
  </div>
  <!-- Mobile Slide-over Nav -->
  <div id="mobile-nav-panel" class="md:hidden fixed inset-0 z-40 hidden">
    <div class="absolute inset-0 bg-black/40" id="mobile-nav-backdrop"></div>
    <div class="absolute left-0 top-0 h-full w-60 bg-white shadow-lg p-4 flex flex-col gap-4 overflow-y-auto">
      <div class="flex justify-between items-center mb-2">
        <h2 class="text-[#4B2E0E] font-semibold">Navigation</h2>
        <button id="mobile-nav-close" class="text-gray-500 text-xl"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
      <nav class="flex flex-col gap-2 text-sm">
        <a href="../Customer/advertisement" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='advertisement.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-home"></i> Home</a>
        <a href="../Customer/customerpage" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='customerpage.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-shopping-cart"></i> Cart</a>
        <a href="../Customer/transactionrecords" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='transactionrecords.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-list"></i> Transactions</a>
        <a href="../all/setting" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='setting.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>"><i class="fas fa-cog"></i> Settings</a>
        <button id="logout-btn-mobile" class="flex items-center gap-2 px-3 py-2 rounded-md border border-gray-300 text-[#4B2E0E] text-left"><i class="fas fa-sign-out-alt"></i> Logout</button>
      </nav>
    </div>
  </div>
  <!-- Sidebar (Desktop) -->
  <aside class="hidden md:flex bg-white bg-opacity-90 backdrop-blur-sm flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
    <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
    <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <button title="Home" onclick="window.location.href='../Customer/advertisement'"><i class="fas fa-home text-xl <?= $currentPage == 'advertisement.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Cart" onclick="window.location.href='../Customer/customerpage'"><i class="fas fa-shopping-cart text-xl <?= $currentPage == 'customerpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Orders" onclick="window.location.href='../Customer/transactionrecords'"><i class="fas fa-list text-xl <?= $currentPage == 'transactionrecords.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button title="Settings" onclick="window.location.href='../all/setting'"><i class="fas fa-cog text-xl <?= $currentPage == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
    <button id="logout-btn" title="Logout"><i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i></button>
  </aside>
  <!-- Main content -->
  <main class="flex-1 p-6 relative flex flex-col min-h-0 min-w-0 md:overflow-hidden">
    <img alt="Background image of coffee beans" aria-hidden="true" class="absolute inset-0 w-full h-full object-cover opacity-20 -z-10" height="800" src="https://storage.googleapis.com/a1aa/image/22cccae8-cc1a-4fb3-7955-287078a4f8d4.jpg" width="1200"/>
    <header class="mb-4">
      <p class="text-xs text-gray-400 mb-0.5">Welcome Home, <?php echo htmlspecialchars($customer); ?></p>
      <h1 class="text-[#4B2E0E] font-semibold text-xl mb-3"><?php echo htmlspecialchars($customer); ?>'s Order</h1>
    </header>
    <!-- Toolbar: Search + Sort -->
    <div class="flex flex-wrap items-center gap-3 mb-3">
      <div class="relative">
        <input id="menu-search" type="text" placeholder="Search menu" class="w-72 max-w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-[#c19a6b] focus:outline-none" />
        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-magnifying-glass"></i></span>
      </div>
      <select id="menu-sort" class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-[#c19a6b] focus:outline-none">
        <option value="popular">Sort: Recommended</option>
        <option value="price-asc">Price: Low to High</option>
        <option value="price-desc">Price: High to Low</option>
        <option value="name-asc">Name: A → Z</option>
        <option value="name-desc">Name: Z → A</option>
      </select>
      <div class="text-sm text-gray-500">Tip: Click an image to see details.</div>
    </div>
 
    <!-- Category buttons -->
   <nav aria-label="Coffee categories" id="category-nav"
  class="flex gap-3 mb-3 overflow-x-auto whitespace-nowrap scrollbar-thin scrollbar-thumb-[#c4b09a] scrollbar-track-transparent px-1">
</nav>
   <!-- Coffee Menu Grid -->
  <section aria-label="Coffee menu" class="bg-white bg-opacity-90 backdrop-blur-sm rounded-xl p-4 overflow-y-auto shadow-lg flex-1 min-h-0" id="menu-scroll">
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-4" id="menu-items"></div>
</section>
  </main>
  <!-- Order summary -->
  <aside aria-label="Order summary" id="order-summary" class="w-full md:w-80 bg-white bg-opacity-90 backdrop-blur-sm rounded-t-xl md:rounded-xl shadow-lg flex flex-col justify-between p-4 overflow-hidden mt-4 md:mt-0 md:ml-0">
   <div>
    <h2 class="font-semibold text-[#4B2E0E] mb-2"><?php echo htmlspecialchars($customer); ?>'s Order:</h2>
    <div class="text-xs text-gray-700" id="order-list"></div>
   </div>
   <div class="mt-6 text-center">
    <p class="font-semibold mb-1">Total:</p>
    <p class="text-4xl font-extrabold text-[#4B2E0E] flex justify-center items-center gap-1" id="order-total"><span>₱</span> 0.00</p>
   </div>
   <div class="mt-6 flex gap-4">
    <button class="flex-1 bg-green-500 text-white rounded-lg py-2 font-semibold hover:bg-green-600 transition" type="submit" id="confirm-btn" disabled>Confirm</button>
    <button class="flex-1 bg-red-500 text-white rounded-lg py-2 font-semibold hover:bg-red-600 transition" type="button" id="cancel-btn" disabled>Cancel</button>
   </div>
  </aside>
 
  <script>
   const menuData = <?php
echo json_encode(array_map(function($p) {
    return [
        'id' => 'product-' . $p['ProductID'],
        'name' => $p['ProductName'],
        'price' => floatval($p['UnitPrice']),
        'img' => !empty($p['ImagePath']) ? '../uploads/' . $p['ImagePath'] : 'https://placehold.co/80x80/png?text=' . urlencode($p['ProductName']),
        'alt' => $p['ProductName'],
        'category' => strtolower($p['ProductCategory']),
  'price_id' => $p['PriceID'],
  'description' => $p['Description'] ?? '',
  'allergen' => $p['Allergen'] ?? 'None'
    ];
}, $products));
?>;
 
   const categories = <?php echo json_encode($categories); ?>;
   const categoriesList = ['All', ...categories];
   const categoryNav = document.getElementById('category-nav');
   function renderCategories() {
     categoryNav.innerHTML = categoriesList.map((cat, idx) => `
       <button aria-pressed="${idx === 0 ? 'true' : 'false'}"
         class="flex items-center gap-2
           ${idx === 0 ? 'bg-[#4B2E0E] text-white shadow-md' : 'bg-white border border-gray-300 text-gray-700'}
           rounded-full py-2 px-5 text-sm font-semibold category-btn
           ${cat.trim().toLowerCase() === 'signatures' || cat.trim().toLowerCase() === 'signature' ? 'ring-2 ring-[#c19a6b] bg-yellow-100 text-[#4B2E0E] border-yellow-400' : ''}"
         data-category="${cat.toLowerCase()}" type="button">
         <i class="fas fa-coffee"></i> ${cat}
       </button>
     `).join('');
   }
   renderCategories();
 
   const menuContainer = document.getElementById("menu-items");
   const orderList = document.getElementById("order-list");
  const orderTotalEl = document.getElementById("order-total");
  const confirmBtn = document.getElementById("confirm-btn");
  const cancelBtn = document.getElementById("cancel-btn");
  const orderCountEl = document.getElementById('order-count');
  const mobileOrderToggle = document.getElementById('mobile-order-toggle');
  const orderSummary = document.getElementById('order-summary');
  const mobileNavToggle = document.getElementById('mobile-nav-toggle');
  const mobileNavPanel = document.getElementById('mobile-nav-panel');
  const mobileNavClose = document.getElementById('mobile-nav-close');
  const mobileNavBackdrop = document.getElementById('mobile-nav-backdrop');
  const logoutBtnMobile = document.getElementById('logout-btn-mobile');
 
  let order = {};
  let currentCategory = categoriesList.length > 0 ? categoriesList[0].toLowerCase() : "";
  let currentSearch = '';
  let currentSort = 'popular';
 
   function renderMenu() {
     menuContainer.innerHTML = "";
     let filteredItems = menuData.filter(item => {
       const catOK = (currentCategory === 'all') || (item.category === currentCategory);
       const q = currentSearch.trim().toLowerCase();
       const searchOK = !q || (item.name.toLowerCase().includes(q) || (item.description||'').toLowerCase().includes(q));
       return catOK && searchOK;
     });
     // sorting
     filteredItems.sort((a,b)=>{
       switch(currentSort){
         case 'price-asc': return (a.price||0) - (b.price||0);
         case 'price-desc': return (b.price||0) - (a.price||0);
         case 'name-asc': return a.name.localeCompare(b.name);
         case 'name-desc': return b.name.localeCompare(a.name);
         default: return 0;
       }
     });

     if (filteredItems.length === 0) {
       const empty = document.createElement('div');
       empty.className = 'col-span-full text-center text-gray-500 py-10';
       empty.innerHTML = '<i class="fa-regular fa-face-frown mr-2"></i>No items match your filters.';
       menuContainer.appendChild(empty);
       return;
     }

     filteredItems.forEach(item => {
       const isInOrder = order[item.id] !== undefined;
       const quantity = isInOrder ? order[item.id].quantity : 0;
 
       const article = document.createElement("article");
       article.setAttribute("aria-label", `${item.name} coffee item`);
       article.className = "bg-white rounded-lg shadow-md p-3 flex flex-col items-center";
 
       const img = document.createElement("img");
       img.src = item.img;
       img.alt = item.alt;
       img.loading = 'lazy';
       img.className = "mb-2 w-20 h-20 object-cover rounded";
 
       const h3 = document.createElement("h3");
       h3.className = "font-semibold text-sm text-[#4B2E0E] mb-1 text-center";
       h3.textContent = item.name;
 
       const pPrice = document.createElement("p");
       pPrice.className = "font-semibold text-xs text-[#4B2E0E] mb-2";
       pPrice.textContent = `₱ ${item.price.toFixed(2)}`;
 
       article.appendChild(img);
       article.appendChild(h3);
       article.appendChild(pPrice);
 
  if (isInOrder) {
         const controls = document.createElement("div");
         controls.className = "flex items-center gap-2";
 
         const btnMinus = document.createElement("button");
         btnMinus.type = "button";
         btnMinus.className = "bg-gray-300 rounded-full w-7 h-7 text-gray-600";
         btnMinus.textContent = "-";
         btnMinus.setAttribute("aria-label", `Decrease quantity of ${item.name}`);
         btnMinus.disabled = false;
         btnMinus.addEventListener("click", () => {
           if (quantity <= 1) {
             delete order[item.id];
             renderMenu();
             renderOrder();
           } else {
             updateQuantity(item.id, quantity - 1);
           }
         });
 
         const spanQty = document.createElement("span");
         spanQty.className = "text-sm font-semibold text-[#4B2E0E]";
         spanQty.textContent = quantity;
 
         const btnPlus = document.createElement("button");
         btnPlus.type = "button";
         btnPlus.className = "bg-[#C4A07A] rounded-full w-7 h-7 text-white font-bold";
         btnPlus.textContent = "+";
         btnPlus.setAttribute("aria-label", `Increase quantity of ${item.name}`);
         btnPlus.addEventListener("click", () => {
           updateQuantity(item.id, quantity + 1);
         });
 
         controls.appendChild(btnMinus);
         controls.appendChild(spanQty);
         controls.appendChild(btnPlus);
 
         article.appendChild(controls);
  } else {
         const addBtn = document.createElement("button");
         addBtn.type = "button";
         addBtn.className = "bg-[#C4A07A] rounded-full w-full py-1 text-xs font-semibold text-white";
         addBtn.textContent = "Add Item";
         addBtn.addEventListener("click", () => {
           addToOrder(item.id);
         });
         article.appendChild(addBtn);
       }
 
       menuContainer.appendChild(article);
     });
   }
 
   function addToOrder(id) {
     if (!order[id]) {
       const item = menuData.find(i => i.id === id);
       order[id] = {...item, quantity: 1};
       renderMenu();
       renderOrder();
     }
   }
 
   function updateQuantity(id, newQty) {
     if (newQty < 1) {
       delete order[id];
     } else {
       order[id].quantity = newQty;
     }
     renderMenu();
     renderOrder();
   }
 
   function renderOrder() {
     orderList.innerHTML = '';
     const entries = Object.values(order);
     if (entries.length === 0) {
       orderTotalEl.textContent = "₱ 0.00";
       confirmBtn.disabled = true;
       cancelBtn.disabled = true;
       try { localStorage.removeItem('customer_cart'); } catch(e){}
       if(orderCountEl){ orderCountEl.textContent = '0'; }
       return;
     }
     let total = 0;
    entries.forEach(item => {
      total += item.price * item.quantity;
      const div = document.createElement("div");
      div.className = "flex items-center gap-2 mb-1";
      const spanName = document.createElement("span");
      spanName.className = "font-semibold flex-1 truncate";
      spanName.textContent = item.name;
      const spanPriceQty = document.createElement("span");
      spanPriceQty.className = "text-right whitespace-nowrap";
      spanPriceQty.innerHTML = `<span class=\"font-semibold\">₱ ${item.price.toFixed(2)}</span><span class=\"ml-1\">x${item.quantity}</span>`;
      const rm = document.createElement('button');
      rm.type = 'button';
      rm.className = 'text-red-600 hover:text-red-700 text-xs';
      rm.title = 'Remove item';
      rm.innerHTML = '<i class="fa-solid fa-xmark"></i>';
      rm.addEventListener('click', ()=>{ delete order[item.id]; renderMenu(); renderOrder(); });
      div.appendChild(spanName);
      div.appendChild(spanPriceQty);
      div.appendChild(rm);
      orderList.appendChild(div);
    });
     orderTotalEl.innerHTML = `<span>₱</span> ${total.toFixed(2)}`;
     confirmBtn.disabled = false;
     cancelBtn.disabled = false;
     // persist
     try { localStorage.setItem('customer_cart', JSON.stringify(order)); } catch(e){}
     if(orderCountEl){
       const count = entries.reduce((s,i)=> s + i.quantity,0);
       orderCountEl.textContent = count;
     }
   }
 
  cancelBtn.addEventListener("click", () => {
     order = {};
     renderMenu();
     renderOrder();
   });
 
   function attachCategoryEvents() {
     document.querySelectorAll(".category-btn").forEach(btn => {
       btn.addEventListener("click", () => {
         const selectedCategory = btn.getAttribute("data-category");
         if (selectedCategory === currentCategory) return;
         currentCategory = selectedCategory;
         document.querySelectorAll(".category-btn").forEach(b => {
           if (b === btn) {
             b.setAttribute("aria-pressed", "true");
             b.classList.add("bg-[#4B2E0E]", "text-white", "shadow-md");
             b.classList.remove("bg-white", "border", "border-gray-300", "text-gray-700");
           } else {
             b.setAttribute("aria-pressed", "false");
             b.classList.remove("bg-[#4B2E0E]", "text-white", "shadow-md");
             b.classList.add("bg-white", "border", "border-gray-300", "text-gray-700");
           }
         });
         renderMenu();
       });
     });
   }

   // Search and sort handlers
   const searchInput = document.getElementById('menu-search');
   const sortSelect = document.getElementById('menu-sort');
   let searchTimer;
   searchInput.addEventListener('input', () => {
     clearTimeout(searchTimer);
     searchTimer = setTimeout(()=>{ currentSearch = searchInput.value; renderMenu(); }, 150);
   });
   sortSelect.addEventListener('change', () => { currentSort = sortSelect.value; renderMenu(); });

   // Load persisted cart if any
   try {
     const saved = localStorage.getItem('customer_cart');
     if (saved) {
       const parsed = JSON.parse(saved);
       if (parsed && typeof parsed === 'object') {
         // only keep items still in menu
         order = Object.fromEntries(Object.entries(parsed).filter(([id, val]) => menuData.some(i=>i.id===id)));
       }
     }
   } catch(e) {}
 
   document.getElementById("logout-btn").addEventListener("click", () => {
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
         window.location.href = "../all/logoutcos";
       }
     });
   });

    if(logoutBtnMobile){
      logoutBtnMobile.addEventListener('click', ()=>{
        document.getElementById("logout-btn").click();
      });
    }

   // Mobile order summary toggle
   if(mobileOrderToggle && orderSummary){
     const ensureDrawerState = () => {
       if(window.innerWidth >= 768){
         orderSummary.classList.add('md:static');
         orderSummary.classList.add('open');
         document.body.classList.remove('drawer-open');
       } else {
         orderSummary.classList.remove('open');
       }
     };
     mobileOrderToggle.addEventListener('click', ()=>{
       const isOpen = orderSummary.classList.toggle('open');
       if(isOpen){ document.body.classList.add('drawer-open'); } else { document.body.classList.remove('drawer-open'); }
     });
     ensureDrawerState();
     window.addEventListener('resize', ensureDrawerState);
   }

   // Mobile navigation panel toggling
   function closeMobileNav(){
     mobileNavPanel?.classList.add('hidden');
     document.body.classList.remove('overflow-hidden');
   }
   if(mobileNavToggle){
     mobileNavToggle.addEventListener('click', ()=>{
       mobileNavPanel.classList.remove('hidden');
       document.body.classList.add('overflow-hidden');
     });
   }
   mobileNavClose?.addEventListener('click', closeMobileNav);
   mobileNavBackdrop?.addEventListener('click', closeMobileNav);
 
   confirmBtn.addEventListener("click", () => {
     const orderArray = Object.values(order).map(item => ({
       id: item.id,
       price: item.price,
       quantity: item.quantity,
       price_id: item.price_id
     }));
     if(orderArray.length===0) return;
     const totalAmount = orderArray.reduce((s,i)=>s + (i.price * i.quantity),0).toFixed(2);
     Swal.fire({
       title: 'GCash Payment',
       html: `
         <div style="text-align:center">
           <p class="text-sm mb-2">Scan and pay exactly <strong>₱ ${totalAmount}</strong>.</p>
           <img src="../images/gcash_qr2.jpg" alt="GCash QR" style="max-width:600px;width:100%;border:6px solid #fff;box-shadow:0 2px 10px rgba(0,0,0,0.15);border-radius:14px;margin:0 auto 10px;" />
           <label class="block text-left text-sm font-semibold mb-1" for="gcash-receipt">Upload Receipt Screenshot</label>
           <input type="file" id="gcash-receipt" accept="image/*" class="w-full text-sm border rounded px-2 py-1" />
           <p class="text-xs text-gray-500 mt-1">Max 5MB. JPG / PNG / GIF / WEBP only.</p>
         </div>
       `,
       confirmButtonText: 'Submit Payment',
       showCancelButton: true,
       focusConfirm: false,
       preConfirm: () => {
         const fi = document.getElementById('gcash-receipt');
         if(!fi.files || !fi.files[0]) { Swal.showValidationMessage('Upload your receipt.'); return false; }
         const file = fi.files[0];
         const validTypes = ['image/jpeg','image/png','image/gif','image/webp'];
         if(!validTypes.includes(file.type)) { Swal.showValidationMessage('Unsupported file type.'); return false; }
         if(file.size > 5*1024*1024) { Swal.showValidationMessage('File too large (max 5MB).'); return false; }
         return file;
       }
     }).then(res => {
       if(!res.isConfirmed) return;
       const fd = new FormData();
       fd.append('ajaxOrderSubmit','1');
       fd.append('paymentMethod','gcash');
       fd.append('orderData', JSON.stringify(orderArray));
       fd.append('gcashReceipt', res.value);
       Swal.fire({title:'Submitting...',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
       fetch('customerpage.php', {method:'POST', body: fd}).then(r=>r.json()).then(data=>{
         if(data.success){
           Swal.fire({icon:'success',title:'Success',text:'Payment submitted successfully.'}).then(()=>{
             // clear cart
             order = {}; renderMenu(); renderOrder();
             window.location.href = '../Customer/transactionrecords';
           });
         } else {
           Swal.fire({icon:'error',title:'Failed',text:data.message || 'Order failed.'});
         }
       }).catch(()=>{
         Swal.fire({icon:'error',title:'Network Error',text:'Please try again.'});
       });
     });
   });
 
   renderMenu();
   renderOrder();
   attachCategoryEvents();

   // Click image to show product info popup (non-intrusive, no design changes)
   menuContainer.addEventListener('click', (e) => {
     const imgEl = e.target.closest('img');
     if (!imgEl) return;
     const article = imgEl.closest('article');
     if (!article) return;
  const name = (article.querySelector('h3')?.textContent || '').trim();
  const price = (article.querySelector('p')?.textContent || '').trim();
     // Items shown are filtered by currentCategory, so we can use it directly
     const catPretty = currentCategory ? currentCategory.charAt(0).toUpperCase() + currentCategory.slice(1) : '';
     const imgSrc = imgEl.getAttribute('src');
  const item = menuData.find(i => i.name === name && i.category === currentCategory);
  const desc = item?.description || '';
  const allergen = item?.allergen || 'None';

     Swal.fire({
       title: 'Product Information',
        html: `
          <div style="text-align:center;margin-bottom:12px">
            <img src="${imgSrc}" alt="${name}" style="display:block;margin:0 auto;width:100%;max-width:100%;height:auto;max-height:50vh;object-fit:contain;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.12);" />
          </div>
         <div style="text-align:left;line-height:1.6">
           <div><strong>Product Name:</strong> ${name}</div>
           <div><strong>Category:</strong> ${catPretty}</div>
           <div><strong>Price:</strong> ${price}</div>
           ${desc ? `<div><strong>Description:</strong> ${desc}</div>` : ''}
           <div><strong>Allergens:</strong> ${allergen}</div>
         </div>
       `,
      confirmButtonColor: '#4B2E0E',
      backdrop: false,
      heightAuto: false,
      scrollbarPadding: false,
      didOpen: () => {
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
        document.body.style.paddingRight = '';
      },
      willClose: () => {
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
        document.body.style.paddingRight = '';
      }
     });
   });
  </script>
 </body>
</html>