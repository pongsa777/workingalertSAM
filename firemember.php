<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

$sessionid = $con->real_escape_string($_GET['sessionid']);
$memberid = $con->real_escape_string($_GET['memberid']);
$groupid = $con->real_escape_string($_GET['groupid']);
$type = $con->real_escape_string($_GET['type']);

$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);

if($userid != 0){
  if($groupid != "" || $groupid != 0 || $memberid != "" || $memberid != 0){
    //เช็คว่า userid เป็น admin ในกลุ่มนั้น
    $sqlcheckadmin = "SELECT * FROM `has_user` WHERE `has_user`.`user_id` = '$userid' AND `has_user`.`role_id` = 1 AND `has_user`.`group_id` = '$groupid'";
    $querycheckadmin = $con->query($sqlcheckadmin);
    if ($querycheckadmin->num_rows > 0) {
      //เช็คว่า memberid เป็นสถานะ member จริง
      $sqlcheckmember = "SELECT * FROM `has_user` WHERE (`role_id` = '2' OR `role_id` = '4' OR `role_id` = '3') AND `user_id` = '$memberid' AND `has_user`.`group_id` = '$groupid'";
      $querycheckmember = $con->query($sqlcheckmember);
      if ($querycheckmember->num_rows > 0) {
        $sqlupdatestatus = "DELETE FROM `workingalert`.`has_user` WHERE `has_user`.`user_id` = '$memberid' ";
        if($con->query($sqlupdatestatus)){
          $response = array("status"=>"success","description"=>"fire user success");
        }else {
          $response = array("status"=>"failed","description"=>"update database failed");
        }
      }else {
        $response = array("status"=>"failed","description"=>"user is not in this group");
      }
    }else{
      $response = array("status"=>"failed","description"=>"You are not admin");
    }
  }else{
    $response = array("status"=>"failed","description"=>"missing member id or group id");
  }
}else{
  $response = array("status"=>"failed","description"=>"wrong session id");
}

echo json_encode($response);
?>
