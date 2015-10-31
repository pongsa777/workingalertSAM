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
$icon = $con->real_escape_string($_GET['icon']);
$permission = $con->real_escape_string($_GET['permission']);
$approve = $con->real_escape_string($_GET['approve']);


$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);
//check password match
if($password == $password2){
  //check missing parameters
  if($sessionid != "" && $groupid != "" && $groupname != "" && $description != "" && $password != "" && $type != "" && $icon != "" && $permission != "" && $approve != ""){
    //check userid
    if($userid != 0){
      //check admin
      $sqlcheckadmin = "SELECT * FROM `has_user` WHERE `user_id` = '$userid' AND `group_id` = '$groupid' AND `role_id` = 1";
      $querycheckadmin = $con->query($sqlcheckadmin);
      if($querycheckadmin->num_rows > 0){
        //check group ว่ามีอยู่จริง
        $queryGroup = $con->query("SELECT * FROM `workingalert`.`group`
                                    WHERE `group_id` = '$groupid';");
        if($queryGroup->num_rows > 0){
          $sqleditgroup = "UPDATE `workingalert`.`group` SET `group_name` = '$groupname',
                          `password` = '$password',
                          `approve` = '$approve',
                          `icon` = '$icon',
                          `permission` = '$permission',
                          `description` = '$description'
                          WHERE `group`.`group_id` = 1;";

          if($con->query($sqleditgroup) === TRUE){
            $response = array("status"=>"success","description"=>"edit success!");
          }else {
            $response = array("status"=>"failed","description"=>"problems when update data");
          }
        }else{
          $response = array("status"=>"failed","description"=>"not have this group in database");
        }
      }else {
        $response = array("status"=>"failed","description"=>"you are not admin");
      }
    }else{
        $response = array("status"=>"failed","description"=>"You have no authorize to edit this group");
    }
  }else {
    $response = array("status"=>"failed","description"=>"missing parameters");
  }
}else{
  $response = array("status"=>"failed","description"=>"Password not match");
}
echo json_encode($response);
?>
