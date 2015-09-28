<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

$sessionid = $con->real_escape_string($_GET['sessionid']);
$groupid = $con->real_escape_string($_GET['groupid']);
$groupname = $con->real_escape_string($_GET['groupname']);
$description = $con->real_escape_string($_GET['description']);
$password = $con->real_escape_string($_GET['password']);
$password2 = $con->real_escape_string($_GET['password2']);
$type = $con->real_escape_string($_GET['type']);

$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);
if($password == $password2){
  if($userid != 0){
      $queryGroup = $con->query("SELECT * FROM `workingalert`.`group`
                                  WHERE `group_id` = '$groupid';");
      if($queryGroup->num_rows > 0){
        $sqleditgroup = "UPDATE `workingalert`.`group`
        SET `group_name` = '$groupname', `description` = '$description', `password` = '$password'
        WHERE `group`.`group_id` = '$groupid'";

        if($con->query($sqleditgroup) === TRUE){
          $response = array("status"=>"success","description"=>"edit success!");
        }else {
          $response = array("status"=>"failed","description"=>"problems when update data");
        }
      }else{
        $response = array("status"=>"failed","description"=>"not have this group in database");
      }
  }else{
      $response = array("status"=>"failed","description"=>"You have no authorize to edit this group");
  }
}else{
  $response = array("status"=>"failed","description"=>"Password not match");
}
echo json_encode($response);
?>
