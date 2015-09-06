<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);

$group = array();
$response = array("status"=>"failed","description"=>"some problems","group"=>$group);
$queryUser = $con->query("SELECT * FROM `session` WHERE `session_id` = '$sessionid';");
if($queryUser->num_rows > 0){
    //found user_id
    $userdata = $queryUser->fetch_assoc();
    $userid = $userdata["user_id"];
    if($userid != ""){
        $querygroup = $con->query("SELECT * FROM `has_user` 
                            join `group` on `has_user`.group_id=`group`.group_id 
                            where `has_user`.user_id = '$userid'
                            AND `group`.`parent_id` = '$groupid';");
        if($querygroup->num_rows > 0){
            //found group
            while($groupdata = $querygroup->fetch_assoc()){
                $groupdetail = array("id"=>$groupdata["group_id"],"name"=>$groupdata["group_name"],
                                     "description"=>$groupdata["description"],"date"=>$groupdata["end_date"]);
                array_push($group,$groupdetail);
            }
            $response = array("status"=>"success","description"=>"","group"=>$group);
        }else{
            $response = array("status"=>"success","description"=>"not found group","group"=>$group);
        }
    }
    
}else{
    //not found user_id
    $response = array("status"=>"failed","description"=>"not found user_id");
}

echo json_encode($response);

?>