<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['EmployeeID'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

require_once('../classes/database.php');
$db = new database();

// Ensure table exists before any action
$db->ensureTimeLogsTable();

$employeeID = intval($_SESSION['EmployeeID']);
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $status = $db->getTodayAttendance($employeeID);
        echo json_encode(['success' => true, 'data' => $status]);
        exit();
    }

    if ($method === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
    switch ($action) {
            case 'clock_in':
                $res = $db->clockIn($employeeID);
                break;
            case 'start_break':
                $res = $db->startBreak($employeeID);
                break;
            case 'end_break':
                $res = $db->endBreak($employeeID);
                break;
            case 'clock_out':
                $res = $db->clockOut($employeeID);
                break;
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action.']);
                exit();
        }
        if ($res['success']) {
            $status = $db->getTodayAttendance($employeeID);
            echo json_encode(['success' => true, 'message' => $res['message'], 'data' => $status]);
        } else {
            echo json_encode(['success' => false, 'message' => $res['message']]);
        }
        exit();
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
