<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $parentgroupid = $con->real_escape_string($_GET['parentgroupid']);
    $groupname = $con->real_escape_string($_GET['groupname']);
    $description = $con->real_escape_string($_GET['description']);
    $password = $con->real_escape_string($_GET['password']);
    $type = $con->real_escape_string($_GET['type']);
    $icon = $con->real_escape_string($_GET['icon']);
    $permission = $con->real_escape_string($_GET['permission']);
    $approve = $con->real_escape_string($_GET['approve']);

//change when create with no parentid
if($parentgroupid == ""){
	$parentgroupid = null;
}

//initial response
$response = array("status"=>"failed","description"=>"some problems");

//convert session to userid
$userid = finduserid($sessionid,$con,$type);
if($userid != 0){
  //check missing parameters
  if($groupname != "" && $description != "" && $icon != "" && $permission != "" && $approve != ""){
    //insert new group to db
    $sqlcreategroup = "INSERT INTO `workingalert`.`group` (`group_name`, `description`, `create_user_id`, `parent_id`, `password`, `approve`, `icon`, `permission`)
                                                    VALUES ('$groupname', '$description', '$userid', '$parentgroupid', '$password', '$approve', '$icon', '$permission');";
    $queryCreate = $con->query($sqlcreategroup);
    if($queryCreate){
      //ดึง groupid เมื่อกี้ออกมา
      $sqlgroupid = "SELECT `group_id` FROM `workingalert`.`group`
                      WHERE `group_name` = '$groupname'
                      AND `create_user_id` = '$userid'
                      AND `description` = '$description'
                      AND `approve` = '$approve'
                      AND `icon` = '$icon'
                      AND `permission` = '$permission';";
      $queryGroupid = $con->query($sqlgroupid);
      if($queryGroupid->num_rows > 0){
        $groupiddata = $queryGroupid->fetch_assoc();
        $groupid = $groupiddata["group_id"];
        //insert user คนนั้นเป็น admin ของกลุ่ม
        $queryinsertmember = $con->query("INSERT INTO `workingalert`.`has_user` (`user_id`, `group_id`, `role_id`)
        VALUES ('$userid', '$groupid', 1);");
        if($queryinsertmember){
          $response = array("status"=>"success","description"=>"create success!");
        }else{
          $response = array("status"=>"failed","description"=>"insert user to own group failed");
        }
      }else {
        $response = array("status"=>"failed","description"=>"problems when get group id to create group");
      }
    }else {
      $response = array("status"=>"failed","description"=>"create new group in database failed");
    }
  }else{
    $response = array("status"=>"failed","description"=>"Missing parameters");
  }
}else{
    $response = array("status"=>"failed","description"=>"You have no authorize to create group");
}
echo json_encode($response);
?>
