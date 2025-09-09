<?php

session_start();
if (!isset($_SESSION['OwnerID'])) {
  header('Location: ../all/login');
  exit();
}

require_once('../classes/database.php'); 
$con = new database();
$sweetAlertConfig = "";

// [IMAGE UPLOAD] config
// uploads directory relative to this file
$uploadDir = __DIR__ . '/../uploads/';
$webUploadDir = '../uploads/'; // used for src attribute in <img> 
$placeholderImage = 'placeholder.png'; ///uploads/placeholder.png in uploads folder

// ensure uploads directory exists
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}


if (isset($_POST['add_product'])) {
  $ownerID = $_SESSION['OwnerID'];
  $productName = $_POST['productName'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $effectiveFrom = $_POST['effectiveFrom'];
  $effectiveTo = !empty($_POST['effectiveTo']) ? $_POST['effectiveTo'] : null;
  $description = $_POST['description'] ?? null;
  $allergens = $_POST['allergens'] ?? null;

  // [IMAGE UPLOAD] validate file
  $imageFileName = null;
  if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $fileError = $_FILES['product_image']['error'];
    if ($fileError === UPLOAD_ERR_OK) {
      $tmp = $_FILES['product_image']['tmp_name'];
      $size = $_FILES['product_image']['size'];
      $mime = mime_content_type($tmp);
      $allowed = ['image/jpeg', 'image/png', 'image/gif'];
      if (!in_array($mime, $allowed)) {
        $sweetAlertConfig = "<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire('Error','Invalid image type. Use JPG, PNG, or GIF.','error'));</script>";
      } elseif ($size > 5 * 1024 * 1024) {
        $sweetAlertConfig = "<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire('Error','Image must be 5MB or less.','error'));</script>";
      } else {
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $imageFileName = pathinfo($_FILES['product_image']['name'], PATHINFO_FILENAME) . '_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
        $dest = $uploadDir . $imageFileName;
        if (!move_uploaded_file($tmp, $dest)) {
          $imageFileName = null;
          $sweetAlertConfig = "<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire('Error','Failed to save image.','error'));</script>";
        }
      }
    } else {
      $sweetAlertConfig = "<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire('Error','File upload error.','error'));</script>";
    }
  } else {
    // If no file provided during add, treat as error (you asked Add requires image)
    $sweetAlertConfig = "<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire('Error','Please choose an image when adding a product.','error'));</script>";
  }

  // If no failure message yet, proceed to add product
  if ($sweetAlertConfig === "") {
    $productID = $con->addProduct($productName, $category, $price, date('Y-m-d'), $effectiveFrom, $effectiveTo, $ownerID, $description, $allergens);

    if ($productID) {
      // update product row with ImagePath if an image was uploaded
      if ($imageFileName) {
        try {
          $db = $con->opencon();
          $stmt = $db->prepare("UPDATE product SET ImagePath = ? WHERE ProductID = ?");
          $stmt->execute([$imageFileName, $productID]);
        } catch (PDOException $e) {
          // log but continue
          error_log("Set ImagePath error: " . $e->getMessage());
        }
      }
      $sweetAlertConfig = "
      <script>
      document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Product added.',
          confirmButtonText: 'OK'
        }).then(() => {
          window.location.href = 'product.php';
        });
      });
      </script>";
    } else {
      $sweetAlertConfig = "
      <script>
      document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to add product.',
          confirmButtonText: 'OK'
        });
      });
      </script>";
      // if product adding failed, optionally remove uploaded image to avoid orphaned file
      if (!empty($imageFileName) && file_exists($uploadDir . $imageFileName)) {
        @unlink($uploadDir . $imageFileName);
      }
    }
  }
}

/*
  HANDLE: Update product price + optional image replacement
  This branch handles FormData POSTs that include priceID, productID (we'll accept price updates + image in same request)
  For existing no-image edit, the JS still uses update_product.php so this branch handles only cases where client uploaded an image while editing.
*/
if (isset($_POST['update_price_and_image'])) {
  // expected fields: priceID, unitPrice, effectiveFrom, effectiveTo, productID, oldImage (optional)
  $priceID = $_POST['priceID'] ?? null;
  $unitPrice = $_POST['unitPrice'] ?? null;
  $effectiveFrom = $_POST['effectiveFrom'] ?? null;
  $effectiveTo = !empty($_POST['effectiveTo']) ? $_POST['effectiveTo'] : null;
  $productID = $_POST['productID'] ?? null;
  $oldImage = $_POST['oldImage'] ?? null;
  $newDescription = $_POST['description'] ?? null;

  // basic validation
  if (!$priceID || !$unitPrice || !$effectiveFrom || !$productID) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
  }

  // update price via your database method
  $priceUpdated = $con->updateProductPrice($priceID, $unitPrice, $effectiveFrom, $effectiveTo);

  // handle uploaded image if present
  $newImageFileName = $oldImage;
  if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $fileError = $_FILES['product_image']['error'];
    if ($fileError === UPLOAD_ERR_OK) {
      $tmp = $_FILES['product_image']['tmp_name'];
      $size = $_FILES['product_image']['size'];
      $mime = mime_content_type($tmp);
      $allowed = ['image/jpeg', 'image/png', 'image/gif'];
      if (!in_array($mime, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type.']);
        exit;
      } elseif ($size > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image too large (max 5MB).']);
        exit;
      } else {
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $newImageFileName = pathinfo($_FILES['product_image']['name'], PATHINFO_FILENAME) . '_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
        $dest = $uploadDir . $newImageFileName;
        if (!move_uploaded_file($tmp, $dest)) {
          echo json_encode(['success' => false, 'message' => 'Failed to save image.']);
          exit;
        } else {
          // delete old image if not placeholder and exists
          if (!empty($oldImage) && $oldImage !== $placeholderImage && file_exists($uploadDir . $oldImage)) {
            @unlink($uploadDir . $oldImage);
          }
        }
      }
    } else {
      echo json_encode(['success' => false, 'message' => 'File upload error.']);
      exit;
    }
  }

  // save new ImagePath to product table (if changed)
  try {
    $db = $con->opencon();
    $stmt = $db->prepare("UPDATE product SET ImagePath = ? WHERE ProductID = ?");
    $stmt->execute([$newImageFileName, $productID]);
    // also update Description if provided
    if ($newDescription !== null) {
      $stmt2 = $db->prepare("UPDATE product SET Description = ? WHERE ProductID = ?");
      $stmt2->execute([$newDescription, $productID]);
    }
  } catch (PDOException $e) {
    error_log("Update ImagePath error: " . $e->getMessage());
  }

  // respond with success/failure for the price update as well
  if ($priceUpdated) {
    echo json_encode(['success' => true, 'message' => 'Updated']);
  } else {
    echo json_encode(['success' => false, 'message' => 'Failed to update price.']);
  }
  exit;
}

/*
  HANDLE: Archive / Restore actions (these still use separate endpoints in your app - unchanged)
  (No changes made here)
*/

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>Product List</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
  <style>
    body { font-family: 'Inter', sans-serif; }
    .swal-input-label { font-size: 0.875rem; color: #4B2E0E; text-align: left; width: 100%; margin-top: 10px; margin-bottom: 5px; }
    .pagination-bar {
      position: absolute;
      bottom: 1rem;
      left: 0;
      right: 0;
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 0.5rem;
    }
    .product-img-thumb { width: 64px; height: 64px; object-fit: cover; border-radius: 6px; }
    .swal-image-preview { max-width: 160px; max-height: 160px; object-fit: cover; display:block; margin:6px auto; border-radius:6px; }

  /* SweetAlert Add Product modal styles (reuse employee modal theme) */
  .ae-grid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 20px; align-items: start; }
  .ae-input { width: 100%; padding: 12px 16px; border-radius: 16px; border: 2px solid #ddd; outline: none; font-size: 14px; }
  .ae-input:focus { border-color: #C4A07A; box-shadow: 0 0 0 3px rgba(196,160,122,0.2); }
  .ae-select { width: 100%; padding: 10px 14px; border-radius: 12px; border: 2px solid #ddd; outline: none; }
  .ae-select:focus { border-color: #C4A07A; box-shadow: 0 0 0 3px rgba(196,160,122,0.2); }
  .swal2-popup.ae-wide { width: 980px !important; }
  .swal2-title { color: #21160E; font-weight: 800; }
  .ap-section-title { color: #21160E; font-weight: 800; }
  .ap-label { color: #4B2E0E; font-weight: 600; font-size: 0.9rem; }
  .ap-soft-field { background: #EEE6DC; border-radius: 16px; padding: 14px; border: 1px solid rgba(196,160,122,0.28); }
  .ap-image-wrap { background: #E8E0D7; border-radius: 18px; padding: 12px; border: 1px solid rgba(196,160,122,0.28); }
  .ap-image { border-radius: 14px; width: 100%; height: 300px; object-fit: cover; }
  .swal2-popup.ae-ap-popup { background: #F7F2EC; box-shadow: 0 12px 32px rgba(75,46,14,0.18), inset 0 1px 0 rgba(255,255,255,0.65); border-radius: 24px; }
  .btn-soft-hollow { background: #CFCAC4; color: #21160E; border-radius: 9999px; padding: 0.5rem 1rem; border: 3px solid rgba(255,255,255,0.85); font-weight: 700; }
  .btn-soft-gray { background: linear-gradient(180deg, #A1764E 0%, #7C573A 100%); color: #FFFFFF; border-radius: 9999px; padding: 0.5rem 1.25rem; font-weight: 700; border: 1px solid rgba(255,255,255,0.75); box-shadow: inset 0 2px 0 rgba(255,255,255,0.6), inset 0 -2px 0 rgba(0,0,0,0.06), 0 4px 12px rgba(75,46,14,0.25); }
  .btn-soft-gray:hover { filter: brightness(1.02); }
  .btn-soft-gray:active { transform: translateY(1px); }
  .swal2-styled.btn-soft-gray { background: linear-gradient(180deg, #A1764E 0%, #7C573A 100%) !important; color: #FFFFFF !important; border-radius: 9999px !important; border: 1px solid rgba(255,255,255,0.75) !important; box-shadow: inset 0 2px 0 rgba(255,255,255,0.6), inset 0 -2px 0 rgba(0,0,0,0.06), 0 4px 12px rgba(75,46,14,0.25) !important; }
  .swal2-styled.btn-soft-hollow { background: #CFCAC4 !important; color: #21160E !important; border-radius: 9999px !important; border: 3px solid rgba(255,255,255,0.85) !important; box-shadow: none !important; }
  .swal2-styled:focus { box-shadow: none !important; }

    /* Soft gray pill button (top-right Add Product) */
    .btn-soft-gray {
      background: linear-gradient(180deg, #D9D9D9 0%, #CFCFCF 100%);
      color: #FFFFFF;
      border-radius: 9999px;
      padding: 0.5rem 1.25rem; /* px-5 py-2 */
      font-weight: 600;
      box-shadow: inset 0 2px 0 rgba(255,255,255,0.7), inset 0 -2px 0 rgba(0,0,0,0.07), 0 2px 8px rgba(0,0,0,0.12);
      border: 1px solid rgba(255,255,255,0.9);
      transition: transform .08s ease, filter .2s ease;
    }
    .btn-soft-gray:hover { filter: brightness(0.98); }
    .btn-soft-gray:active { transform: translateY(1px); }

    /* Add Product Modal styling to match the screenshot */
    .ap-modal-overlay { background: rgba(0,0,0,0.3); backdrop-filter: blur(2px); }
    .ap-card { background: #F5F3F1; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.6); }
    .ap-inner { background: rgba(255,255,255,0.6); border-radius: 18px; }
    .ap-soft-field { background: #EAE7E3; border-radius: 16px; padding: 14px; }
    .ap-image-wrap { background: #E1DED9; border-radius: 18px; padding: 12px; }
    .ap-image { border-radius: 14px; width: 100%; height: 180px; object-fit: cover; }
  /* Make Add Product modal preview image a bit larger without affecting Edit modal */
  #ap_modal .ap-image { height: 300px; }
  /* Make Edit Product modal preview image a bit larger */
  #ep_modal .ap-image { height: 300px; }
    .btn-soft-hollow { background: #CFCAC4; color: #fff; border-radius: 9999px; padding: 0.5rem 1rem; border: 3px solid rgba(255,255,255,0.8); box-shadow: inset 0 -1px 0 rgba(0,0,0,0.06); font-weight: 600; }
    .btn-soft-hollow:hover { filter: brightness(0.98); }
    .ap-section-title { color: #21160E; font-weight: 800; }
    .ap-label { color: #4B2E0E; font-weight: 600; font-size: 0.9rem; }
    .ap-radio:checked { accent-color: #4B2E0E; }
    .ap-checkbox:checked { accent-color: #4B2E0E; }
    .ap-currency { position: relative; }
    .ap-currency span { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #4B2E0E; font-weight: 600; }
    .ap-currency input { padding-left: 40px; }
  /* Themed Add/Edit modals to match site coffee palette */
  #ap_modal .ap-card, #ep_modal .ap-card { background: #F7F2EC; box-shadow: 0 12px 32px rgba(75,46,14,0.18), inset 0 1px 0 rgba(255,255,255,0.65); }
  /* Removed top accent bar */
  #ap_modal .ap-card::before, #ep_modal .ap-card::before { content: none; display: none; }
  #ap_modal .ap-inner, #ep_modal .ap-inner { background: rgba(255,255,255,0.75); border-radius: 18px; border: 1px solid rgba(196,160,122,0.28); }
  #ap_modal .ap-soft-field, #ep_modal .ap-soft-field { background: #EEE6DC; border-radius: 16px; padding: 14px; border: 1px solid rgba(196,160,122,0.28); }
  #ap_modal .ap-image-wrap, #ep_modal .ap-image-wrap { background: #E8E0D7; border-radius: 18px; padding: 12px; border: 1px solid rgba(196,160,122,0.28); }
  #ap_modal .btn-soft-hollow, #ep_modal .btn-soft-hollow { background: #C4A07A; color: #21160E; border: 3px solid rgba(255,255,255,0.85); font-weight: 700; }
  #ap_modal .btn-soft-gray, #ep_modal .btn-soft-gray { background: linear-gradient(180deg, #A1764E 0%, #7C573A 100%); color: #FFFFFF; border: 1px solid rgba(255,255,255,0.75); }
  #ap_modal .btn-soft-gray:hover, #ep_modal .btn-soft-gray:hover { filter: brightness(1.02); }
  #ap_modal .btn-soft-gray:active, #ep_modal .btn-soft-gray:active { transform: translateY(1px); }
  /* Ensure larger preview for both modals */
  #ep_modal .ap-image { height: 300px; }

  /* Header Add Product button now matches the Add Employee button styles (Tailwind classes on the element) */
  </style>
</head>
<body class="bg-[rgba(255,255,255,0.7)] min-h-screen flex">

<aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg la-sidebar">
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
  <button title="Settings" onclick="window.location.href='../all/setting'">
        <i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button id="logout-btn" title="Logout">
        <i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i>
    </button>
</aside>

<main class="flex-1 p-6 relative flex flex-col">
  <header class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-[#4B2E0E] font-semibold text-xl mb-1">Product List</h1>
      <p class="text-xs text-gray-400">Manage your products here</p>
    </div>
  <a href="#" id="add-product-btn" class="bg-[#4B2E0E] text-white rounded-full px-5 py-2 text-sm font-semibold shadow-md hover:bg-[#6b3e14] transition flex items-center">
    <i class="fas fa-plus mr-2"></i>Add Product
  </a>
  </header>

  <section class="bg-white rounded-xl p-4 w-full shadow-lg flex-1 overflow-x-auto relative">
    <table id="product-table" class="w-full text-sm">
      <thead>
        <tr class="text-left text-[#4B2E0E] border-b">
          <th class="py-2 px-4 w-[5%]">#</th>
          <th class="py-2 px-4 w-[15%]">Image</th>
          <th class="py-2 px-4 w-[18%]">Product Name</th>
          <th class="py-2 px-4 w-[13%]">Category</th>
          <th class="py-2 px-4 w-[15%]">Allergens</th>
          <th class="py-2 px-4 w-[10%]">Status</th>
          <th class="py-2 px-4 w-[10%]">Unit Price</th>
          <th class="py-2 px-4 w-[10%]">Effective From</th>
          <th class="py-2 px-4 w-[10%]">Effective To</th>
          <th class="py-2 px-4 w-[9%] text-center">Actions</th>
        </tr>
      </thead>
      <tbody id="product-body">
          <?php
      $products = $con->getJoinedProductData();
      usort($products, function($a, $b) {
          return $a['ProductID'] <=> $b['ProductID']; 
      });
      foreach ($products as $product) {
          // [IMAGE UPLOAD] fetch ImagePath for this product (database.php's getJoinedProductData doesn't include ImagePath)
          $imagePath = $placeholderImage;
      try {
        $db = $con->opencon();
        $stmt = $db->prepare("SELECT ImagePath, Description FROM product WHERE ProductID = ?");
        $stmt->execute([$product['ProductID']]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r && !empty($r['ImagePath'])) $imagePath = $r['ImagePath'];
        $description = $r['Description'] ?? '';
      } catch (Exception $e) {
              // fallback to placeholder
              $imagePath = $placeholderImage;
        $description = '';
          }
              ?>
        <tr class="border-b hover:bg-gray-50 <?= $product['is_available'] == 0 ? 'bg-red-50 text-gray-500' : '' ?>">
          <td class="py-2 px-4"><?= htmlspecialchars($product['ProductID']) ?></td>
          <td class="py-2 px-4">
            <img src="<?= htmlspecialchars($webUploadDir . $imagePath) ?>" alt="product" class="product-img-thumb">
          </td>
          <td class="py-2 px-4 font-semibold <?= $product['is_available'] == 0 ? 'line-through' : '' ?>"><?= htmlspecialchars($product['ProductName']) ?></td>
          <td class="py-2 px-4"><?= htmlspecialchars($product['ProductCategory']) ?></td>
          <td class="py-2 px-4 text-xs"><?= htmlspecialchars($product['Allergen'] ?? 'None') ?></td>
          <td class="py-2 px-4">
            <?php if ($product['is_available'] == 1): ?>
              <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-600 bg-green-200">Available</span>
            <?php else: ?>
              <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-red-600 bg-red-200">Archived</span>
            <?php endif; ?>
          </td>
          <td class="py-2 px-4">₱<?= htmlspecialchars(number_format($product['UnitPrice'], 2)) ?></td>
          <td class="py-2 px-4"><?= htmlspecialchars($product['Effective_From']) ?></td>
          <td class="py-2 px-4"><?= htmlspecialchars((string)($product['Effective_To'] ?? 'N/A')) ?></td>
          <td class="py-2 px-4 text-center">
            <?php if ($product['is_available'] == 1): ?>
              <!-- [IMAGE UPLOAD] add data-product-id & data-image so edit modal can preview/change image -->
        <button class="text-blue-600 hover:underline text-lg mr-2 edit-product-btn"
                      title="Edit Price"
                      data-product-id="<?= htmlspecialchars($product['ProductID']) ?>"
                      data-product-name="<?= htmlspecialchars($product['ProductName']) ?>"
                      data-price-id="<?= htmlspecialchars($product['PriceID']) ?>"
                      data-unit-price="<?= htmlspecialchars($product['UnitPrice']) ?>"
                      data-effective-from="<?= htmlspecialchars($product['Effective_From']) ?>"
                      data-effective-to="<?= htmlspecialchars((string)($product['Effective_To'] ?? '')) ?>"
          data-image="<?= htmlspecialchars($imagePath) ?>"
          data-description="<?= htmlspecialchars($description ?? '') ?>"
                      ><i class="fas fa-edit"></i></button>
              <button class="text-red-600 hover:underline text-lg archive-product-btn" title="Archive" data-product-id="<?= htmlspecialchars($product['ProductID']) ?>" data-product-name="<?= htmlspecialchars($product['ProductName']) ?>"><i class="fas fa-archive"></i></button>
            <?php else: ?>
              <button class="text-green-600 hover:underline text-lg restore-product-btn" title="Restore" data-product-id="<?= htmlspecialchars($product['ProductID']) ?>" data-product-name="<?= htmlspecialchars($product['ProductName']) ?>"><i class="fas fa-undo-alt"></i></button>
            <?php endif; ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div id="pagination" class="pagination-bar"></div>
  </section>

  <!-- Add Product Modal (custom UI) -->
  <div id="ap_modal" class="ap-modal-overlay fixed inset-0 hidden items-center justify-center z-50">
    <div class="ap-card w-[1000px] max-w-[95vw] p-6">
      <div class="flex items-start justify-between mb-4">
        <h2 class="text-2xl ap-section-title">Add Product</h2>
        <button id="ap_close" class="text-[#4B2E0E] hover:opacity-70"><i class="fa-solid fa-xmark text-xl"></i></button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Left column -->
        <div class="space-y-4">
          <div>
            <input id="ap_name" type="text" placeholder="Product Name" class="w-full border border-[#4B2E0E] rounded-xl px-4 py-3 focus:outline-none" />
          </div>

          <div>
            <p class="ap-section-title mb-2">Description</p>
            <div class="ap-inner p-3">
              <textarea id="ap_desc" rows="4" placeholder="Describe the product..." class="w-full bg-transparent outline-none"></textarea>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="ap-section-title mb-2">Pricing</p>
              <div class="ap-soft-field ap-currency">
                <p class="ap-label mb-2">Unit Price:</p>
                <span>₱</span>
                <input id="ap_price" type="number" step="0.01" class="w-full rounded-md px-3 py-2 outline-none" placeholder="0.00" />
              </div>
            </div>
            <div>
              <p class="ap-section-title mb-2">Allergens</p>
              <div class="ap-inner p-3">
                <p class="ap-label mb-2">Select Type:</p>
                <div class="grid grid-cols-2 gap-y-2 text-sm">
                  <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Milk</label>
                  <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Soy</label>
                  <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Tree Nuts</label>
                  <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Wheat</label>
                  <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Eggs</label>
                  <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Gelatin</label>
                  <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Fish</label>
                  <label class="flex items-center gap-2"><input id="ap_allergen_none" type="checkbox" class="ap-checkbox"> <span class="font-semibold">NONE</span></label>
                </div>
              </div>
            </div>
          </div>

          <!-- Moved Effective Dates to the left column -->
          <div class="ap-inner p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <p class="ap-label mb-1">Effective from:</p>
                <input id="ap_eff_from" type="date" class="w-full rounded-md px-3 py-2 outline-none bg-white" />
              </div>
              <div>
                <p class="ap-label mb-1">Effective to:</p>
                <input id="ap_eff_to" type="date" class="w-full rounded-md px-3 py-2 outline-none bg-white" />
              </div>
            </div>
          </div>
        </div>

        <!-- Right column -->
        <div class="space-y-4">
          <div class="ap-inner p-4">
            <div class="ap-image-wrap">
              <img id="ap_image_preview" src="<?= htmlspecialchars($webUploadDir . $placeholderImage) ?>" alt="preview" class="ap-image" />
            </div>
            <div class="mt-4 flex justify-center">
              <button id="ap_image_btn" class="btn-soft-hollow">Add Image</button>
              <input id="ap_image_file" type="file" accept="image/png, image/jpeg, image/gif" class="hidden" />
            </div>
          </div>

          <div class="ap-inner p-4">
            <p class="ap-section-title mb-3">Category</p>
            <div id="ap_categories" class="space-y-2"></div>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button id="ap_cancel" class="px-4 py-2 rounded-full border border-[#4B2E0E] text-[#4B2E0E] font-semibold">Cancel</button>
        <button id="ap_submit" class="btn-soft-gray">Add Product</button>
      </div>
    </div>
  </div>

  <!-- Edit Product Modal (custom UI similar to Add Product) -->
  <div id="ep_modal" class="ap-modal-overlay fixed inset-0 hidden items-center justify-center z-50">
    <div class="ap-card w-[900px] max-w-[95vw] p-6">
      <div class="flex items-start justify-between mb-4">
        <h2 id="ep_title" class="text-2xl ap-section-title">Edit Product</h2>
        <button id="ep_close" class="text-[#4B2E0E] hover:opacity-70"><i class="fa-solid fa-xmark text-xl"></i></button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Left: Price, dates, and description -->
        <div class="space-y-4">
          <div class="ap-soft-field">
            <p class="ap-label mb-2">Unit Price</p>
            <input id="ep_unitPrice" type="number" step="0.01" class="w-full rounded-md px-3 py-2 outline-none" placeholder="0.00" />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="ap-soft-field">
              <p class="ap-label mb-2">Effective From</p>
              <input id="ep_effectiveFrom" type="date" class="w-full rounded-md px-3 py-2 outline-none bg-white" />
            </div>
            <div class="ap-soft-field">
              <p class="ap-label mb-2">Effective To (Optional)</p>
              <input id="ep_effectiveTo" type="date" class="w-full rounded-md px-3 py-2 outline-none bg-white" />
            </div>
          </div>

          <div>
            <p class="ap-section-title mb-2">Description</p>
            <div class="ap-inner p-3">
              <textarea id="ep_desc" rows="4" placeholder="Describe the product..." class="w-full bg-transparent outline-none"></textarea>
            </div>
          </div>
        </div>

        <!-- Right: Image -->
        <div class="space-y-4">
          <div class="ap-inner p-4">
            <div class="ap-image-wrap">
              <img id="ep_image_preview" src="<?= htmlspecialchars($webUploadDir . $placeholderImage) ?>" alt="preview" class="ap-image" />
            </div>
            <div class="mt-4 flex justify-center">
              <button id="ep_image_btn" class="btn-soft-hollow">Change Image</button>
              <input id="ep_image_file" type="file" accept="image/png, image/jpeg, image/gif" class="hidden" />
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button id="ep_cancel" class="px-4 py-2 rounded-full border border-[#4B2E0E] text-[#4B2E0E] font-semibold">Cancel</button>
        <button id="ep_submit" class="btn-soft-gray">Save Changes</button>
      </div>

      <!-- hidden holders for ids -->
      <input type="hidden" id="ep_productID" />
      <input type="hidden" id="ep_priceID" />
      <input type="hidden" id="ep_oldImage" />
  <input type="hidden" id="ep_hidden_desc" />
    </div>
  </div>
  <!-- Hidden form for adding a product (multipart, to support file) -->
  <form id="add-product-form" method="POST" enctype="multipart/form-data" style="display:none;">
    <input type="hidden" name="productName" id="form-productName">
    <input type="hidden" name="category" id="form-category">
    <input type="hidden" name="price" id="form-price">
    <input type="hidden" name="effectiveFrom" id="form-effectiveFrom">
    <input type="hidden" name="effectiveTo" id="form-effectiveTo">
    <input type="file" name="product_image" id="form-product-image" accept="image/png, image/jpeg, image/gif" />
    <input type="hidden" name="add_product" value="1">
  </form>

  <?= $sweetAlertConfig ?>
</main>


<script>
// Add Product — SweetAlert modal (matches Add Employee popup)
(function() {
  const categories = <?php echo json_encode($con->getAllCategories()); ?>;
  const openBtn = document.getElementById('add-product-btn');
  function openAddProduct() {
    Swal.fire({
      title: 'Add Product',
      customClass: { popup: 'ae-wide ae-ap-popup', confirmButton: 'btn-soft-gray rounded-full', cancelButton: 'btn-soft-hollow rounded-full' },
      html:
        `<div class="ae-grid">
           <div>
             <div class="ap-section-title">Product</div>
             <div class="ap-soft-field">
               <label class="ap-label" for="swal-prod-name">Product Name</label>
               <input id="swal-prod-name" class="ae-input" placeholder="e.g., Caramel Latte" />
               <div style="height:12px"></div>
               <label class="ap-label" for="swal-prod-desc">Description</label>
               <textarea id="swal-prod-desc" class="ae-input" rows="3" placeholder="Describe the product..."></textarea>
             </div>
             <div style="height:14px"></div>
             <div class="ap-section-title">Pricing & Dates</div>
             <div class="ap-soft-field">
               <label class="ap-label" for="swal-prod-price">Unit Price</label>
               <input id="swal-prod-price" class="ae-input" type="number" min="0" step="0.01" placeholder="0.00" />
               <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px">
                 <div>
                   <label class="ap-label" for="swal-prod-efffrom">Effective From</label>
                   <input id="swal-prod-efffrom" class="ae-input" type="date" />
                 </div>
                 <div>
                   <label class="ap-label" for="swal-prod-effto">Effective To (Optional)</label>
                   <input id="swal-prod-effto" class="ae-input" type="date" />
                 </div>
               </div>
             </div>
             <div style="height:14px"></div>
             <div class="ap-section-title">Allergens</div>
             <div class="ap-soft-field" id="swal-prod-allergens">
               <div class="grid grid-cols-2 gap-y-2 text-sm">
                 <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Milk</label>
                 <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Soy</label>
                 <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Tree Nuts</label>
                 <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Wheat</label>
                 <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Eggs</label>
                 <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Gelatin</label>
                 <label class="flex items-center gap-2"><input type="checkbox" class="ap-checkbox"> Fish</label>
                 <label class="flex items-center gap-2"><input id="swal-allergen-none" type="checkbox" class="ap-checkbox"> <span class="font-semibold">NONE</span></label>
               </div>
             </div>
           </div>
           <div>
             <div class="ap-section-title">Image</div>
             <div class="ap-soft-field">
               <div class="ap-image-wrap">
                 <img id="swal-prod-preview" class="ap-image" alt="preview" />
               </div>
               <div style="margin-top:10px;text-align:center">
                 <button id="swal-prod-image-btn" class="btn-soft-hollow">Choose Image</button>
                 <input id="swal-prod-image" type="file" accept="image/png, image/jpeg, image/gif" style="display:none" />
               </div>
             </div>
             <div style="height:14px"></div>
             <div class="ap-section-title">Category</div>
             <div id="swal-prod-cats" class="ap-soft-field" style="text-align:left"></div>
           </div>
         </div>`,
      showCancelButton: true,
      confirmButtonText: 'Add',
      cancelButtonText: 'Cancel',
      focusConfirm: false,
      heightAuto: false,
      scrollbarPadding: false,
      preConfirm: () => {
        const name = document.getElementById('swal-prod-name').value.trim();
        const price = document.getElementById('swal-prod-price').value;
        const effFrom = document.getElementById('swal-prod-efffrom').value;
        const effTo = document.getElementById('swal-prod-effto').value;
        const cat = document.querySelector('input[name="swal-prod-category"]:checked');
        const newCatInput = document.getElementById('swal-new-category');
        let finalCategory = cat ? cat.value : '';
        if (finalCategory === '__new__') {
          finalCategory = (newCatInput?.value || '').trim();
          if (!finalCategory) {
            Swal.showValidationMessage('Please enter a new category name.');
            return false;
          }
          if (finalCategory.length > 40) {
            Swal.showValidationMessage('Category name is too long (max 40 characters).');
            return false;
          }
        }
        const file = document.getElementById('swal-prod-image').files[0];
        if (!name || !price || !effFrom || !cat) {
          Swal.showValidationMessage('Please complete Name, Price, Category, and Effective From.');
          return false;
        }
        if (!file) { Swal.showValidationMessage('Please choose a product image.'); return false; }
        if (!['image/jpeg','image/png','image/gif'].includes(file.type)) { Swal.showValidationMessage('Invalid image type (JPG/PNG/GIF).'); return false; }
        if (file.size > 5*1024*1024) { Swal.showValidationMessage('Image must be 5MB or less.'); return false; }
        return { name, price, effFrom, effTo, category: finalCategory };
      },
      didOpen: () => {
        // categories
        const wrap = document.getElementById('swal-prod-cats');
        if (wrap) {
          wrap.style.textAlign = 'left';
          const listHtml = categories.map((c, i) => `
          <label class="flex items-center gap-2" style="display:block;margin:.35rem 0;">
            <input type="radio" name="swal-prod-category" value="${c}" class="ap-radio" ${i===0?'checked':''}>
            <span>${c}</span>
          </label>`).join('');
          const newHtml = `
          <div style="margin-top:.5rem;border-top:1px dashed #d9d9d9;padding-top:.5rem;">
            <label class="flex items-center gap-2" style="display:block;margin:.35rem 0;">
              <input type="radio" name="swal-prod-category" value="__new__" class="ap-radio">
              <span><strong>New Category</strong></span>
            </label>
            <div id="swal-new-cat-wrap" style="display:none;margin-top:.35rem;">
              <input id="swal-new-category" class="ae-input" placeholder="Type new category name" />
            </div>
          </div>`;
          wrap.innerHTML = listHtml + newHtml;
          // toggle input visibility
          const radios = wrap.querySelectorAll('input[name="swal-prod-category"]');
          const newWrap = document.getElementById('swal-new-cat-wrap');
          const onChange = () => {
            const selected = document.querySelector('input[name="swal-prod-category"]:checked');
            if (selected && selected.value === '__new__') { newWrap.style.display = 'block'; }
            else { newWrap.style.display = 'none'; }
          };
          radios.forEach(r => r.addEventListener('change', onChange));
          onChange();
        }

        // image picker
        const btn = document.getElementById('swal-prod-image-btn');
        const file = document.getElementById('swal-prod-image');
        const prev = document.getElementById('swal-prod-preview');
        if (btn) btn.addEventListener('click', (e)=>{ e.preventDefault(); file.click(); });
        if (file) file.addEventListener('change', () => {
          const f = file.files && file.files[0];
          if (!f) return;
          if (!['image/jpeg','image/png','image/gif'].includes(f.type)) { Swal.fire('Invalid image','Use JPG, PNG, or GIF.','error'); file.value=''; return; }
          if (f.size > 5*1024*1024) { Swal.fire('Too large','Image must be 5MB or less.','error'); file.value=''; return; }
          const r = new FileReader(); r.onload = e => prev.src = e.target.result; r.readAsDataURL(f);
        });

        // allergens NONE toggle
        const none = document.getElementById('swal-allergen-none');
        if (none) none.addEventListener('change', () => {
          document.querySelectorAll('#swal-prod-allergens .ap-checkbox').forEach(b=>{ if (b !== none) b.checked = false; });
        });
        document.querySelectorAll('#swal-prod-allergens .ap-checkbox').forEach(cb => {
          if (cb.id === 'swal-allergen-none') return;
          cb.addEventListener('change', ()=>{ const none = document.getElementById('swal-allergen-none'); if (cb.checked && none) none.checked = false; });
        });
      }
    }).then(async (result) => {
      if (!result.isConfirmed) return;
      const name = document.getElementById('swal-prod-name').value.trim();
      const desc = document.getElementById('swal-prod-desc').value.trim();
      const price = document.getElementById('swal-prod-price').value;
      const effFrom = document.getElementById('swal-prod-efffrom').value;
      const effTo = document.getElementById('swal-prod-effto').value;
      const cat = document.querySelector('input[name="swal-prod-category"]:checked');
      const newCatInput = document.getElementById('swal-new-category');
      let finalCategory = cat ? cat.value : '';
      if (finalCategory === '__new__') {
        finalCategory = (newCatInput?.value || '').trim();
      }
      const file = document.getElementById('swal-prod-image').files[0];
      // Allergens CSV
      const labels = [];
      document.querySelectorAll('#swal-prod-allergens .ap-checkbox').forEach(cb=>{
        if (cb.id === 'swal-allergen-none') return; if (cb.checked) labels.push(cb.parentElement?.textContent?.trim());
      });
      const allergens = labels.length ? labels.join(', ') : 'None';
      const fd = new FormData();
      fd.append('add_product', '1');
      fd.append('productName', name);
  fd.append('category', finalCategory);
      fd.append('price', price);
      fd.append('effectiveFrom', effFrom);
      fd.append('effectiveTo', effTo);
      fd.append('description', desc);
      fd.append('allergens', allergens);
      fd.append('product_image', file);
      try {
        await fetch('product.php', { method: 'POST', body: fd });
        window.location.reload();
      } catch (e) {
        Swal.fire('Error','Upload failed. Try again.','error');
      }
    });
  }
  openBtn && openBtn.addEventListener('click', (e)=>{ e.preventDefault(); openAddProduct(); });
})();


function paginateTable(containerId, paginationId, rowsPerPage = 15) {
  const tbody = document.getElementById(containerId);
  const pagination = document.getElementById(paginationId);
  if (!tbody || !pagination) return;
  const rows = Array.from(tbody.children);
  const pageCount = Math.ceil(rows.length / rowsPerPage);
  let currentPage = 1;

  function showPage(page) {
    rows.forEach((row, i) => {
      row.style.display = (i >= (page - 1) * rowsPerPage && i < page * rowsPerPage) ? '' : 'none';
    });
    renderPagination();
  }

  function renderPagination() {
    pagination.innerHTML = '';
    const createButton = (text, onClick, isDisabled = false) => {
        const btn = document.createElement('button');
        btn.textContent = text;
        btn.disabled = isDisabled;
        btn.onclick = onClick;
        btn.className = "px-3 py-1 border rounded disabled:opacity-50";
        return btn;
    };
    
    pagination.appendChild(createButton('Prev', () => { if (currentPage > 1) { currentPage--; showPage(currentPage); } }, currentPage === 1));
    for (let i = 1; i <= pageCount; i++) {
        const btn = createButton(i, () => { currentPage = i; showPage(currentPage); });
        if (i === currentPage) btn.className += ' bg-[#4B2E0E] text-white';
        pagination.appendChild(btn);
    }
    pagination.appendChild(createButton('Next', () => { if (currentPage < pageCount) { currentPage++; showPage(currentPage); } }, currentPage === pageCount));
  }
  if (pageCount > 1) { showPage(currentPage); }
}

function initializeActionButtons() {
    document.querySelectorAll('.archive-product-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); 
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to archive "${productName}".`, icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, archive it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    fetch('archive_product.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Archived!', `${productName} has been archived.`, 'success').then(() => window.location.reload());
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to archive.', 'error');
                        }
                    });
                }
            });
        });
    });

    document.querySelectorAll('.restore-product-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); 
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to restore "${productName}".`, icon: 'info', showCancelButton: true,
                confirmButtonColor: '#28a745', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    fetch('restore_product.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Restored!', `${productName} has been restored.`, 'success').then(() => window.location.reload());
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to restore.', 'error');
                        }
                    });
                }
            });
        });
    });

  // EDIT PRODUCT — custom modal (mirrors Add Product modal)
  const epModal = document.getElementById('ep_modal');
  const epOpen = (cfg) => {
    document.getElementById('ep_title').textContent = `Edit ${cfg.productName}`;
    document.getElementById('ep_productID').value = cfg.productId;
    document.getElementById('ep_priceID').value = cfg.priceId;
    document.getElementById('ep_unitPrice').value = cfg.unitPrice;
    document.getElementById('ep_effectiveFrom').value = cfg.effectiveFrom;
    document.getElementById('ep_effectiveTo').value = cfg.effectiveTo || '';
    document.getElementById('ep_oldImage').value = cfg.image;
    document.getElementById('ep_image_preview').src = `<?= htmlspecialchars($webUploadDir) ?>${cfg.image}`;
  const desc = cfg.description || '';
  document.getElementById('ep_desc').value = desc;
  document.getElementById('ep_hidden_desc').value = desc;
    epModal.classList.remove('hidden');
    epModal.classList.add('flex');
  };
  const epClose = () => { epModal.classList.add('hidden'); epModal.classList.remove('flex'); };
  const epCloseBtn = document.getElementById('ep_close');
  const epCancelBtn = document.getElementById('ep_cancel');
  epCloseBtn.addEventListener('click', epClose);
  epCancelBtn.addEventListener('click', (e)=>{ e.preventDefault(); epClose(); });
  epModal.addEventListener('click', (e)=>{ if (e.target === epModal) epClose(); });

  // open from row buttons
  document.querySelectorAll('.edit-product-btn').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      epOpen({
        productId: this.dataset.productId,
        productName: this.dataset.productName,
        priceId: this.dataset.priceId,
        unitPrice: this.dataset.unitPrice,
        effectiveFrom: this.dataset.effectiveFrom,
        effectiveTo: this.dataset.effectiveTo,
  image: this.dataset.image || '<?= $placeholderImage ?>',
  description: this.dataset.description || ''
      });
    });
  });

  // image change
  const epImageBtn = document.getElementById('ep_image_btn');
  const epImageFile = document.getElementById('ep_image_file');
  const epImagePrev = document.getElementById('ep_image_preview');
  epImageBtn.addEventListener('click', (e)=>{ e.preventDefault(); epImageFile.click(); });
  epImageFile.addEventListener('change', ()=>{
    const f = epImageFile.files && epImageFile.files[0];
    if (!f) return;
    if (!['image/jpeg','image/png','image/gif'].includes(f.type)) { Swal.fire('Invalid image','Use JPG, PNG, or GIF.','error'); epImageFile.value=''; return; }
    if (f.size > 5*1024*1024) { Swal.fire('Too large','Image must be 5MB or less.','error'); epImageFile.value=''; return; }
    const r = new FileReader(); r.onload = e => epImagePrev.src = e.target.result; r.readAsDataURL(f);
  });

  // save
  const epSubmit = document.getElementById('ep_submit');
  epSubmit.addEventListener('click', async (e)=>{
    e.preventDefault();
    const priceID = document.getElementById('ep_priceID').value;
    const unitPrice = document.getElementById('ep_unitPrice').value;
    const effectiveFrom = document.getElementById('ep_effectiveFrom').value;
    const effectiveTo = document.getElementById('ep_effectiveTo').value;
    const productID = document.getElementById('ep_productID').value;
    const oldImage = document.getElementById('ep_oldImage').value;
  const description = document.getElementById('ep_desc').value.trim();
    const file = epImageFile.files && epImageFile.files[0];

    if (!unitPrice || !effectiveFrom) {
      Swal.fire('Missing info','Price and Effective From are required.','warning');
      return;
    }

    // Use combined endpoint in this page for both flows (with or without image)
    const fd2 = new FormData();
    fd2.append('update_price_and_image', '1');
    fd2.append('priceID', priceID);
    fd2.append('unitPrice', unitPrice);
    fd2.append('effectiveFrom', effectiveFrom);
    fd2.append('effectiveTo', effectiveTo);
    fd2.append('productID', productID);
    fd2.append('oldImage', oldImage);
    fd2.append('description', description);
    if (file) { fd2.append('product_image', file); }
    try {
      const r = await fetch('product.php', { method: 'POST', body: fd2 });
      const j = await r.json();
      if (j.success) { Swal.fire('Updated','Product updated.','success').then(()=>{ epClose(); window.location.reload(); }); }
      else { Swal.fire('Error', j.message || 'Failed to update.','error'); }
    } catch(err) {
      Swal.fire('Error','Upload/update failed.','error');
    }
  });
}

window.addEventListener('DOMContentLoaded', () => {
  paginateTable('product-body', 'pagination');
  initializeActionButtons();
});

document.getElementById('logout-btn').addEventListener('click', () => {
    Swal.fire({
        title: 'Are you sure you want to log out?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#4B2E0E', cancelButtonColor: '#d33', confirmButtonText: 'Yes, log out'
    }).then((result) => {
  if (result.isConfirmed) { window.location.href = "../all/logout"; }
    });
});
</script>

</body>
</html>
