<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";
    
    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $messageid = $con->real_escape_string($_GET['messageid']);

$userid = finduserid($sessionid,$con);
$response = array("status"=>"failed","description"=>"missing parameters");
if($userid != 0){
    $sqlcheck = "SELECT * FROM `has_read` WHERE `has_read`.`user_id` = '$userid' AND `has_read`.`message_id` = '$messageid';";
    $checkdata = $con->query($sqlcheck);
    if($checkdata->num_rows > 0){
        $response = array("status"=>"failed","description"=>"already read");
    }else{
        $sqlread = "INSERT INTO  `workingalert`.`has_read` (`user_id` ,`message_id`)VALUES ('$userid',  '$messageid');";
        if($con->query($sqlread)===TRUE){
            $response = array("status"=>"success","description"=>"read finish");
        }else{
            $response = array("status"=>"failed","description"=>"insert to db problems");
        }
    }
}else{
    $response = array("status"=>"failed","description"=>"session error");
}

echo json_encode($response);
?>