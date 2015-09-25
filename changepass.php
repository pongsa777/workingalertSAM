<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $password = $con->real_escape_string($_GET['password']);
    $newpassword = $con->real_escape_string($_GET['newpassword']);
    $newpassword2 = $con->real_escape_string($_GET['newpassword2']);
    $type = $con->real_escape_string($_GET['type']);



$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);

if($newpassword == $newpassword2 || $newpassword == ""){ //check new password match
  if($userid != 0){
    $queryUser = $con->query("SELECT * FROM `user` WHERE `user_id` = '$userid';");  //check password
    if($queryUser->num_rows > 0){
      $userdata = $queryUser->fetch_assoc();
      $userpass = $userdata["password"];
      if($userpass == $password){
        $sql = "UPDATE `workingalert`.`user` SET `password` = '$newpassword' WHERE `user`.`user_id` = '$userid';";
        if ($con->query($sql) === TRUE) {
            $response = array("status"=>"success","description"=>"update complete");
        }else{
            $response = array("status"=>"failed","description"=>"update not complete");
        }
      }else{
        $response = array("status"=>"failed","description"=>"old password is wrong");
      }
    }else{
      $response = array("status"=>"failed","description"=>"not found this user id in database");
    }
  }else{
    $response = array("status"=>"failed","description"=>"don't found your username or missing parameters");
  }
}else{
  $response = array("status"=>"failed","description"=>"newpassword not match");
}
echo json_encode($response);
?>
