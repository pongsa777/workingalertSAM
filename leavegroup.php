<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

function isadmin($con,$groupid,$userid){
  $sqlcheckadmin = "SELECT * FROM `has_user` WHERE `user_id` = '$userid' AND `group_id` = '$groupid'";
  $querycheckadmin = $con->query($sqlcheckadmin);
  if($querycheckadmin->num_rows>0){
    while($row = $querycheckadmin->fetch_assoc()){
      if( $row["role_id"] == 1 ){
        return true;
      }else{
        return false;
      }
    }
  }else{
    return false;
  }
}

function islastadmin($con,$groupid){
  $sqllastadmin = "SELECT COUNT(`id`) FROM `has_user` WHERE `group_id` = '$groupid' AND `role_id` = 1";
  $querylastadmin = $con->query($sqllastadmin);
  if($querylastadmin->num_rows>0){
    while($row = $querylastadmin->fetch_assoc()){
      if($row[" COUNT(`id`) "] <= 1){
        return true;
      }else{
        return false;
      }
    }
  }else{
    echo "is last admin function error";
    return false;
  }
}

function leavethisgroup($con,$groupid,$userid){
  $sqlleavegroup = " DELETE FROM `workingalert`.`has_user` WHERE `has_user`.`user_id` = '$userid' AND `has_user`.`group_id` = '$groupid' ";
  if( $con->query($sqlleavegroup) ){
    return true;
  }else{
    return false;
  }
}

$sessionid = $con->real_escape_string($_GET['sessionid']);
$type = $con->real_escape_string($_GET['type']);
$groupid = $con->real_escape_string($_GET['groupid']);

$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);
if($userid != 0){

  //check ว่าเป็น user หรือ admin
  if( isadmin($con,$groupid,$userid) ){

    //ดูว่าเป็น admin คนสุดท้ายรึป่าว?
    if( islastadmin($con,$groupid) ){
      $response = array("status"=>"failed","description"=>"You are the last admin of this group");
    }else {
      if( leavethisgroup($con,$groupid,$userid) ){
        $response = array("status"=>"success","description"=>"leave group success");
      }else {
        $response = array("status"=>"failed","description"=>"leave group failed");
      }
    }
  }else{
    //member ออกกรุ๊ปได้เลย
    if( leavethisgroup($con,$groupid,$userid) ){
      $response = array("status"=>"success","description"=>"leave group success");
    }else {
      $response = array("status"=>"failed","description"=>"leave group failed");
    }
  }
}else{
  $response = array("status"=>"failed","description"=>"wrong session id");
}

echo json_encode($response);

?>
