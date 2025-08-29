<?php

require_once('../classes/database.php');
$con = new database();
 
if (isset($_POST['email'])){
    $email = $_POST['email'];
   
    //check username exist
if($con->isEmailExists($email)){
    echo json_encode(['exists' => true]);
 
}else{
    echo json_encode(['exists' => false]);
}
 
}else{
    echo json_encode(['error' => 'invalid request']);
}