<?php
header('Content-Type: application/json');
include "dbconnect.php";

    $email = $con->real_escape_string($_GET['email']);
    $fbid = $con->real_escape_string($_GET['fbid']);
    $firstname = $con->real_escape_string($_GET['firstname']);
    $lastname = $con->real_escape_string($_GET['lastname']);
    $nickname = $con->real_escape_string($_GET['nickname']);
    $phone = $con->real_escape_string($_GET['phone']);
    


if($email == "" | $fbid == "0" | $phone == "0"){
    $response = array("status"=>"failed","description"=>"missing parameters");
}else{
    $queryUser = $con->query("SELECT * FROM `user` WHERE `facebook_id` = '$fbid';");
    if($queryUser->num_rows > 0){
       $response = array("status"=>"failed","description"=>"account has already registered");
    }else{
        $sql = "INSERT INTO `workingalert`.`user` 
        (`facebook_id`, `email`, `firstname`, `lastname`, `nickname`, `phone`) 
        VALUES ('$fbid','$email','$firstname','$lastname','$nickname','$phone');";
        
        if($con->query($sql)===true){
            $response = array("status"=>"success","description"=>"account register successfully");
        }else{
             $response = array("status"=>"failed","description"=>"insert to db problems");
        }
    }
}
echo json_encode($response);
?>