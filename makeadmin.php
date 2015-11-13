<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";
include "findchildgroup.php";

function makeadminchild($con,$groupid,$memberid){
  $status = true;
  //ทำให้ user เป็น admin ของกลุ่มลูกทั้งหมด
  $allgroupid = findallchild($groupid,$con);
  foreach ($allgroupid as $eachgroupid) {
    //check ว่าอยู่ในกลุ่มนั้นรึป่าว
    $sqlcheck_is_in = "SELECT * FROM `has_user` WHERE `has_user`.`user_id` = '$memberid' AND `has_user`.`group_id` = '$groupid'";
    $querycheck_is_in = $con->query($sqlcheck_is_in);
    if($querycheck_is_in->num_rows > 0){
      //ถ้าอยู่ update
      $sqlupdateadmin = "UPDATE  `workingalert`.`has_user` SET  `role_id` =  '1' WHERE  `has_user`.`user_id` ='$memberid' AND `has_user`.`group_id` = '$eachgroupid'";
      if( $con->query($sqlupdateadmin) ){
      }else{
        $status = false;
      }
    }else{
      //ถ้าไม่ insert
      $sqlinsertadmin = "INSERT INTO `workingalert`.`has_user` (`user_id`, `group_id`, `role_id`) VALUES ('$memberid', '$eachgroupid', '1');";
      if( $con->query($sqlinsertadmin) ){
      }else{
        $status = false;
      }
    }
  }
  return $status;
}

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
    if ($querycheckadmin->num_rows > 0){
      //เช็คว่า user เป็นสมาชิกในกลุ่มและไม่ได้เป็น admin
      $sqlcheckmember = "SELECT * FROM `has_user` WHERE `role_id` = '2' AND `user_id` = '$memberid' AND `has_user`.`group_id` = '$groupid'";
      $querycheckmember = $con->query($sqlcheckmember);
      if ($querycheckmember->num_rows > 0) {
        $sqlupdatestatus = "UPDATE  `workingalert`.`has_user` SET  `role_id` =  '1' WHERE  `has_user`.`user_id` ='$memberid' AND `has_user`.`group_id` = '$groupid'";
        if($con->query($sqlupdatestatus)){
          $childstatus = "but child group has some problems";
          if( makeadminchild($con,$groupid,$memberid) ){
            $childstatus = ",child group success too!";
          }
          $response = array("status"=>"success","description"=>"make admin success".$childstatus);
        }else {
          $response = array("status"=>"failed","description"=>"update database failed");
        }
      }else {
        $response = array("status"=>"failed","description"=>"user is not in this group or user already admin");
      }
    }else {
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
