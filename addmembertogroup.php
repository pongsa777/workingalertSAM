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
      //check ว่า member ไม่ได้อยู่ในกลุ่มนั้นจริงๆ

      $sqlcheckmember = "SELECT * FROM `has_user` WHERE `has_user`.`user_id` = '$memberid' AND `has_user`.`group_id` = '$groupid'";
      $querycheckmember = $con->query($sqlcheckmember);
      if($querycheckmember->num_rows > 0){ //มี user ในกลุ่มแล้ว
        //return กลับไปว่า user อยู่ในกลุ่มแล้วแต่เป็นสถานะอะไร
        $row2 = $querycheckmember->fetch_assoc();
        if($row2['role_id'] == 1){
          $eiei = 'admin';
        }elseif ($row2['role_id'] == 2) {
          $eiei = 'member';
        }elseif ($row2['role_id'] == 3) {
          $eiei = 'block';
        }elseif ($row2['role_id'] == 4) {
          $eiei = 'Pending admin approve';
        }elseif ($row2['role_id'] == 5) {
          $eiei = 'Pending user confirm';
        }
        $ret = "user is already in this group in ".$eiei;
        $response = array("status"=>"failed","description"=>$ret);
      }else {
        //เช็คสิทธิกลุ่มว่าต้องรอ admin approve หรือเปล่า
        $sqlcheckgroup = "SELECT * FROM `group` WHERE `group`.`group_id` = '$groupid'";
        $querycheckgroup = $con->query($sqlcheckgroup);
        if($querycheckgroup->num_rows > 0){
          //add user เข้ากลุ่ม
          $row = $querycheckgroup->fetch_assoc();
          if($row['approve'] == '1' || $row['approve'] == ''){ // ทุกคนโพสได้
            //insert to has_user in role 2
            $sqlinsertr2 = "INSERT INTO `workingalert`.`has_user` (`user_id`, `group_id`, `role_id`) VALUES ('$memberid', '$groupid', '2')";
            if($con->query($sqlinsertr2)){
              $response = array("status"=>"success","description"=>"add member success");
            }else {
              $response = array("status"=>"failed","description"=>"insert to db failed");
            }
          }
          if($row['approve'] == '2'){ 
            //insert to has_user in role 4
            $sqlinsertr4 = "INSERT INTO `workingalert`.`has_user` (`user_id`, `group_id`, `role_id`) VALUES ('$memberid', '$groupid', '4');";
            if($con->query($sqlinsertr4)){
              $response = array("status"=>"success","description"=>"waiting admin confirm");
            }else {
              $response = array("status"=>"failed","description"=>"insert to db failed");
            }
          }
        }else{
          $response = array("status"=>"failed","description"=>"group id error");
        }
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
