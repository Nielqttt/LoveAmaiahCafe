<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['OwnerID'])){
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Owner login required.']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'POST only']);
    exit();
}

$empID = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
if($empID <= 0){
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid employee id']);
    exit();
}

require_once('../classes/database.php');
$db = new database();
$res = $db->resetTodayAttendance($_SESSION['OwnerID'], $empID);
if($res['success']){
    echo json_encode(['success'=>true,'message'=>$res['message']]);
} else {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$res['message']]);
}
