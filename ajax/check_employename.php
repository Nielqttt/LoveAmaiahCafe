<?php
require_once('../classes/database.php');
$con = new database();

if (isset($_POST['username'])) {
    $Euser = $_POST['username'];
    if ($con->isEmployeeUserExists($Euser)) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
