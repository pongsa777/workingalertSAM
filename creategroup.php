<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $parentgroupid = $con->real_escape_string($_GET['parentgroupid']);
    $groupname = $con->real_escape_string($_GET['groupname']);
    $startdate = $con->real_escape_string($_GET['startdate']);
    $enddate = $con->real_escape_string($_GET['enddate']);
    $description = $con->real_escape_string($_GET['description']);
    $password = $con->real_escape_string($_GET['password']);

if($parentid == "null"){
	$parentid = null;
}

$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con);
if($userid != 0){
    $queryCreate = $con->query("INSERT INTO `workingalert`.`group` (`group_name`,`description`,`start_date`,`end_date`,`create_user_id`,`parent_id`,`password`) 
    VALUES ('$groupname', '$description', '$startdate', '$enddate', '$userid', '$parentgroupid', '$password');");
    
    $queryGroupid = $con->query("SELECT `group_id` FROM `workingalert`.`group` 
                                WHERE `group_name` = '$groupname' 
                                AND `create_user_id` = '$userid';");
    $groupiddata = $queryGroupid->fetch_assoc();
    $groupid = $groupiddata["group_id"];
    
    $queryinsertmember = $con->query("INSERT INTO `workingalert`.`has_user` (`user_id`, `group_id`, `role_id`) 
    VALUES ('$userid', '$groupid', 1);");
    
    $response = array("status"=>"success","description"=>"create success!");
}else{
    $response = array("status"=>"failed","description"=>"You have no authorize to create group");
}
echo json_encode($response);
?>