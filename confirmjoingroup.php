<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

$sessionid = $con->real_escape_string($_GET['sessionid']);
$groupid = $con->real_escape_string($_GET['groupid']);
$type = $con->real_escape_string($_GET['type']);

$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);

if($userid != 0){
  if($groupid != "" || $groupid != 0){
    //check user are now pending
    $sqlcheck = "SELECT * FROM `has_user` WHERE `role_id` = 5 AND `group_id` = '$groupid'";
    $querycheck = $con->query($sqlcheck);
    if($querycheck->num_rows > 0){
      //update user status
      $sqlchange = "UPDATE `workingalert`.`has_user` SET `role_id` = '2' WHERE `has_user`.`id` = '$userid';";
      $querychange = $con->query($sqlchange);
      if($querychange){
        $response = array("status"=>"success","description"=>"confirm join group success");
      }else{
        $response = array("status"=>"failed","description"=>"can't change not find user");
      }
    }else {
      $response = array("status"=>"failed","description"=>"this user not in this group");
    }
  }else {
    $response = array("status"=>"failed","description"=>"missing group id");
  }
}else{
  $response = array("status"=>"failed","description"=>"wrong session id");
}

echo json_encode($response);
?>
