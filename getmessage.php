<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);

$msg = array();
$groupname = "";
$response = array("status"=>"failed","description"=>"some problems","message"=>$msg,"groupname"=>$groupname);
$userid = finduserid($sessionid,$con);
if($userid != 0){
    
    //query all message and message data
    $querymsg = $con->query("SELECT distinct `message`.`message_id`,`has_message`.`user_id`,
    `group_id`,`read_status`,`reach_status`,`message`.`message_body`,`priority`,`message`.`from_user_id`
    FROM `has_message`
    JOIN `message` 
    ON `has_message`.`message_id` =  `message`.`message_id`
    WHERE `has_message`.`user_id` = '$userid'
    AND `has_message`.`group_id` = '$groupid';");
    if($querymsg->num_rows > 0){
        while($msgdata = $querymsg->fetch_assoc()){
            $msgdetail = array("id"=>$msgdata["message_id"],"body"=>$msgdata["message_body"],
                                "priority"=>$msgdata["priority"],"fromid"=>$msgdata["from_user_id"],
                              "read"=>$msgdata["read_status"],"read"=>$msgdata["reach_status"]);
            array_push($msg,$msgdetail);
        }
    }
    
    //query group name
    $querygroupname = $con->query("SELECT `group_name` FROM `group` WHERE `group_id` ='$groupid';");
    $groupnamedata = $querygroupname->fetch_assoc();
    $groupname = $groupnamedata["group_name"];
    
        
    $response = array("status"=>"success","description"=>"all message","message"=>$msg,"groupname"=>$groupname);
    
}else{
    //not found user_id
    $response = array("status"=>"failed","description"=>"invalid session id","message"=>$msg,"groupname"=>$groupname);
}

echo json_encode($response);

?>
