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
  $sqlgetgroupdetail = "SELECT * FROM `group` WHERE `group`.`group_id` = '$groupid'";
  $groupdata = $con->query($sqlgetgroupdetail);
  if($groupdata->num_rows > 0){
    $row = $groupdata->fetch_assoc();

    $groupid = $row['group_id'];
    $groupname = $row['group_name'];
    $description = $row['description'];
    $createid = $row['create_user_id'];
    $parentid = $row['parent_id'];
    $password = $row['password'];
    $approve = $row['approve'];

    $groupdata = array("groupid"         =>        $groupid,
                        "groupname"       =>        $groupname,
                        "description"     =>        $description,
                        "createid"        =>        $createid,
                        "parentid"        =>        $parentid,
                        "password"        =>        $password,
                        "approve"         =>        $approve);

    $response = array("status"=>"success","description"=>"group data is","groupdata"=>$groupdata);
  }else{
    $response = array("status"=>"failed","description"=>"don't found this group in database");
  }
}else {
  $response = array("status"=>"failed","description"=>"You have no authorize to edit this group");
}
echo json_encode($response);
?>
