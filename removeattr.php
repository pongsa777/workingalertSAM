<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

$sessionid = $con->real_escape_string($_GET['sessionid']);
$type = $con->real_escape_string($_GET['type']);
$attrid = $con->real_escape_string($_GET['attrid']);

$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);
if($userid != 0){//ถ้าเจอ userid
  if($attrid != "" || $attrid != 0){ //check ว่ากรอก id มาจริงรึป่าว
    //check ว่า user คนนั้นมี attr นั้นอยู่รึยัง
    $sqlcheck = "SELECT * FROM `has_attribute` WHERE `has_attribute`.`user_id` = '$userid'
                  AND `has_attribute`.`attr_id` = '$attrid'";
    $queryCheck = $con->query($sqlcheck);
    if($queryCheck->num_rows > 0){ //มี attr นี้อยู่แล้ว ลบ
      $sqldelete = "DELETE FROM `workingalert`.`has_attribute`
                    WHERE `has_attribute`.`user_id` = '$userid'
                    AND `has_attribute`.`attr_id` = '$attrid'";
      if($con->query($sqldelete)){
        $response = array("status"=>"success","description"=>"remove attribute success");
      }else{
        $response = array("status"=>"success","description"=>"some problems when insert to db");
      }
    }else{ //ยังไม่มี attr นี้ ส่ง error ไป
      $response = array("status"=>"failed","description"=>"you didn't have this attribute");
    }
  }else{
    $response = array("status"=>"failed","description"=>"please insert attribute id");
  }
}else{
  $response = array("status"=>"failed","description"=>"You don't hava permission to use this function");
}
echo json_encode($response);
?>
