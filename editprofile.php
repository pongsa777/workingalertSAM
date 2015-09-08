<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $email = $con->real_escape_string($_GET['email']);
    $password = $con->real_escape_string($_GET['password']);
    $repassword = $con->real_escape_string($_GET['repassword']);
    $firstname = $con->real_escape_string($_GET['firstname']);
    $lastname = $con->real_escape_string($_GET['lastname']);
    $mobileno = $con->real_escape_string($_GET['mobileno']);
    $nickname = $con->real_escape_string($_GET['nickname']);
    $picurl = $con->real_escape_string($_GET['picurl']);


function validatealldata($password,$repassword){
    $result = false;
        if($password === $repassword){
            $result = true;    
        }else{
            $result = false;   
        }
    return $result;
}

$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con);
if($userid != 0){
    //found id
    if(validatealldata($password,$repassword)){
        $sql = "UPDATE `workingalert`.`user` SET `email` = '$email', `password` = '$password', `nickname` = 'print' WHERE `user`.`user_id` = 20;";
        if ($con->query($sql) === TRUE) {
            $response = array("status"=>"success","description"=>"update complete");
        }else{
            $response = array("status"=>"failed","description"=>"update not complete");
        }
    }
}else{
    //don't found id
    $response = array("status"=>"failed","description"=>"don't found your username or missing parameters");
}

echo json_encode($response);
?>
