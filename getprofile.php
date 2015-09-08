<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);

$user = array();
$response = array("status"=>"failed","description"=>"some problems","user"=>$user);
$userid = finduserid($sessionid,$con);

if($userid != 0){
    $queryprofile = $con->query("SELECT * FROM  `user` WHERE `user`.`user_id` = '$userid'");
    $profiledata = $queryprofile->fetch_assoc();
    
    $fbid = $profiledata['facebook_id'];
    $email = $profiledata['email'];
    $password = $profiledata['password'];
    $firstname = $profiledata['firstname'];
    $lastname = $profiledata['lastname'];
    $nickname = $profiledata['nickname'];
    $phone = $profiledata['phone'];
    $picture = $profiledata['picture'];
    $user = array("fbid"=>$fbid,"email"=>$email,"password"=>$password,"firstname"=>$firstname,"lastname"=>$lastname,"nickname"=>$nickname,"phone"=>$phone,"picture"=>$picture);
    
    $response = array("status"=>"success","description"=>"your user id is..","user"=>$user);
}else{
    $response = array("status"=>"failed","description"=>"don't find this user","user"=>$user);
}

echo json_encode($response);
?>