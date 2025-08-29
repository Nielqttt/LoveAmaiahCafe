<?php
require_once('../classes/database.php');
$con = new database();

if (isset($_POST['email'])){
    $emailN = $_POST['email'];
    if ($con->isEmployeEmailExists($emailN)){
        echo json_encode(['exists'=>true]);
    }else{
        echo json_encode(['exists'=>false]);
    }
}else{
    echo json_encode(['error'=>'Invalid request']);
}