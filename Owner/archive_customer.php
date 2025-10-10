<?php
session_start();

if (!isset($_SESSION['OwnerID'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

require_once('../classes/database.php'); 
$con = new database();

header('Content-Type: application/json'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_id'])) {
    $customerID = filter_var($_POST['customer_id'], FILTER_SANITIZE_NUMBER_INT);

    if ($customerID) {
        if ($con->archiveCustomer($customerID)) {
            echo json_encode(['success' => true, 'message' => 'Customer archived successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to archive customer.']);
        }
    } else {
        http_response_code(400); 
        echo json_encode(['success' => false, 'message' => 'Invalid Customer ID provided.']);
    }
} else {
    http_response_code(400); 
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
