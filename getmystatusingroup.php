<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

function getmystatus($con,$userid,$groupid){
  $status = "";
  $sqlgetmystat = "SELECT * FROM `has_user` WHERE `user_id` = '$userid' AND `group_id` = '$groupid'";
  $querygetmystat = $con->query($sqlgetmystat);
  if($querygetmystat->num_rows>0){
    $row = $querygetmystat->fetch_assoc();
      if( $row['role_id'] == 1 ){
        $status = "admin";
      }elseif ($row['role_id'] == 2) {
        $status = "member";
      }elseif ($row['role_id'] == 3) {
        $status = "block";
      }elseif ($row['role_id'] == 4) {
        $status = "pending admin approve";
      }elseif ($row['role_id'] == 5) {
        $status = "pending user confirm";
      }else{
        $status = "error";
      }
  }else{
    $status = "error";
  }
  return $status;
}


$sessionid = $con->real_escape_string($_GET['sessionid']);
$groupid = $con->real_escape_string($_GET['groupid']);
$type = $con->real_escape_string($_GET['type']);

$userid = finduserid($sessionid,$con,$type);
if($userid != 0){
  $mystatus = getmystatus($con,$userid,$groupid);
  $response = array("status"=>"success","description"=>"get data success","mystatus"=>$mystatus);
}else{
  $response = array("status"=>"failed","description"=>"wrong session id","mystatus"=>"");
}

echo json_encode($response);
?>
