<?php

session_start();



if (!isset($_SESSION['OwnerID'])) {
    error_log("Unauthorized access attempt to update_product.php. Session: " . print_r($_SESSION, true));
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

require_once('../classes/database.php');
$con = new database();


error_log("Incoming RAW POST data to update_product.php: " . print_r($_POST, true));

header('Content-Type: application/json'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $priceID_raw = $_POST['priceID'] ?? '';
    $unitPrice_raw = $_POST['unitPrice'] ?? '';
    $effectiveFrom_raw = $_POST['effectiveFrom'] ?? '';
    $effectiveTo_raw = $_POST['effectiveTo'] ?? ''; 


    $priceID = filter_var($priceID_raw, FILTER_SANITIZE_NUMBER_INT);
    $unitPrice = filter_var($unitPrice_raw, FILTER_VALIDATE_FLOAT);

    
    error_log("Filtered values: priceID='{$priceID}', unitPrice='{$unitPrice}', effectiveFrom='{$effectiveFrom_raw}'");

   
    $validationMessages = [];
    if (empty($priceID) || $priceID === false || $priceID <= 0) { 
        $validationMessages[] = 'PriceID is invalid or missing.';
    }
    if ($unitPrice === false || $unitPrice < 0) { 
        $validationMessages[] = 'UnitPrice is invalid.';
    }
    if (empty($effectiveFrom_raw)) {
        $validationMessages[] = 'EffectiveFrom date is missing.';
    }

    if (!empty($validationMessages)) {
       
        error_log("Validation failed in update_product.php: " . implode(" ", $validationMessages));
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or missing data for update. Details: ' . implode(" ", $validationMessages)]);
        exit();
    }

   
    $result = $con->updateProductPrice($priceID, $unitPrice, $effectiveFrom_raw, $effectiveTo_raw);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully.']);
    } else {
        error_log("Product update failed for PriceID: {$priceID}. Database error or no rows affected.");
        echo json_encode(['success' => false, 'message' => 'Failed to update product. Database error or no changes made.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
