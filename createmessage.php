<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";
include "push.php";
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);
    $msgpayload = $con->real_escape_string($_GET['msgpayload']);
    $priority = $con->real_escape_string($_GET['priority']);

$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con);
if($userid != 0){
    $identity = generateRandomString();
    
    $queryCreate = $con->query("INSERT INTO  `workingalert`.`message` 
                                (`message_body` ,`priority` ,`from_user_id` ,`identity`)
                                VALUES ('$msgpayload',  '$priority',  '$userid', '$identity');");
    
    $querymsgid = $con->query("SELECT `message_id` FROM `workingalert`.`message` 
                                WHERE `from_user_id` = '$userid' 
                                AND `identity` = '$identity';");
    $msgiddata = $querymsgid->fetch_assoc();
    $msgid = $msgiddata["message_id"];
    
    $queryinsertmsg = $con->query("INSERT INTO  `workingalert`.`has_message` (
                                    `group_id` ,`message_id`)
                                    VALUES ('$groupid',  '$msgid');");
    
    
    
    $queryallDeviceId = $con->query("SELECT * FROM `user_deviceid`");
	if($queryallDeviceId->num_rows > 0){
	    // output data of each row
	    $arrayOfDeviceId = array();
 	   while($row = $queryallDeviceId->fetch_assoc()) {
 	       array_push($arrayOfDeviceId,$row['device_id']);
 	   }
 	   $title = 'title message';
 	   $msg = $msgpayload;
 	   $msgstatus = sendPush($arrayOfDeviceId,$title,$msg);
 	   $response = array("status"=>"success","description"=>"message create complete","push"=>$msgstatus);
	} else {
 	   $response = array("status"=>"success","description"=>"message create complete","push"=>"send fail");
	}
}else{
    $response = array("status"=>"failed","description"=>"You have no authorize to create group");
}

echo json_encode($response);
?>