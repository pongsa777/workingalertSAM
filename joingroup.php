<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);


$userid = finduserid($sessionid,$con);
$response = array("status"=>"failed","description"=>"missing parameters");
if($userid != 0){
    $sql = "INSERT INTO `workingalert`.`has_user` (`user_id`, `group_id`, `role_id`) VALUES ('$userid', '$groupid', 2);";
    if($con->query($sql)=== true){
        $response = array("status"=>"success","description"=>"join group successful");
    }else{
        $response = array("status"=>"failed","description"=>"insert something worng");
    }
}else{
    $response = array("status"=>"failed","description"=>"you can not join this group");
}
echo json_encode($response);
?>