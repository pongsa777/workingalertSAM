<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $type = $con->real_escape_string($_GET['type']);
    $groupidsave = $con->real_escape_string($_GET['groupid']);
    $icon = $con->real_escape_string($_GET['icon']);

    $groupname = $con->real_escape_string($_GET['groupname']);
    $description = $con->real_escape_string($_GET['description']);

    $password = $con->real_escape_string($_GET['password']);
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
    $sqleditgroup = "UPDATE `workingalert`.`group`
    SET `group_name` = '$groupname' , `description` = '$description' , `password` = '$password' ,
    `permission` = '$permission' , `approve` = '$approve' , `icon` = '$icon'
    WHERE `group`.`group_id` ='$groupidsave' ";

   //  echo $sqleditgroup;

    $queryedit = $con->query($sqleditgroup);
    if($queryedit){
        $response = array("status"=>"success","description"=>"edit success!");
    }else{
        $response = array("status"=>"failed","description"=>"update group data failed");
    }
  }else{
    $response = array("status"=>"failed","description"=>"Missing parameters");
  }
}else{
    $response = array("status"=>"failed","description"=>"You have no authorize to create group");
}
echo json_encode($response);
?>
