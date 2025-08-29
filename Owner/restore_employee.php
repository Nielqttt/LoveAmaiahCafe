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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'])) {    
    $employeeID = filter_var($_POST['employee_id'], FILTER_SANITIZE_NUMBER_INT);

    if ($employeeID) {
        if ($con->restoreEmployee($employeeID)) {
            echo json_encode(['success' => true, 'message' => 'Employee restored successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to restore employee.']);
        }
    } else {
        http_response_code(400); 
        echo json_encode(['success' => false, 'message' => 'Invalid Employee ID provided.']);
    }
} else {
    http_response_code(400); 
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>