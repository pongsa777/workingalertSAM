<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);
    $type = $con->real_escape_string($_GET['type']);


$user = array();
$response = array("status"=>"failed","description"=>"some problems","user"=>$user);
$userid = finduserid($sessionid,$con,$type);


if($userid != 0){
    //check user can view member
    $sql1 = "SELECT * FROM `has_user` WHERE `user_id` = '$userid' AND `group_id` = '$groupid'";
    $querypermission = $con->query($sql1);
    if($querypermission ->num_rows > 0){
        //have permission
        $sql = "SELECT DISTINCT * FROM `has_user` 
        JOIN `user` ON `user`.`user_id` = `has_user`.`user_id`
        WHERE `has_user`.`group_id` = '$groupid'";
        $queryuser = $con->query($sql);
        if($queryuser->num_rows > 0){
            while($row = $queryuser->fetch_assoc()){    
                array_push($user,array("id"=>$row['user_id'],
                           "role_id"=>$row['role_id'],
                           "email"=>$row['email'],
                           "firstname"=>$row['firstname'],
                           "lastname"=>$row['lastname'],
                           "nickname"=>$row['nickname'],
                           "phone"=>$row['phone'],
                           "picture"=>$row['picture']));
            }
            $response = array("status"=>"success","description"=>"groupmember is","user"=>$user);
        }else{
            $response = array("status"=>"failed","description"=>"don't have member in this group","user"=>$user);    
        }
    }else{
        //don't have permission
        $response = array("status"=>"failed","description"=>"you don't have permission to view this group","user"=>$user);
    }
}else{
    //userid = 0   
    $response = array("status"=>"failed","description"=>"wrong session","user"=>$user);
}
echo json_encode($response);
?>
