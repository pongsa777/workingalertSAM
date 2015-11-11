<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

$sessionid = $con->real_escape_string($_GET['sessionid']);
$type = $con->real_escape_string($_GET['type']);

$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);

if($userid != 0){
  $sqlremove = "DELETE FROM `workingalert`.`user_deviceid` WHERE `user_deviceid`.`user_id` = '$userid'";
  $queryremove = $con->query($sqlremove);
  if($queryremove){
    $response = array("status"=>"success","description"=>"logout success");
  }
}

echo json_encode($response);

?>
