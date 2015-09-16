<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";
include "push.php";
include "findchildgroup.php";
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

    //recieve parameter
    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);
    $msgpayload = $con->real_escape_string($_GET['msgpayload']);
    $priority = $con->real_escape_string($_GET['priority']);


$response = array("status"=>"failed","description"=>"some problems");

//check user id status
$userid = finduserid($sessionid,$con);
if($userid != 0){
    
    //find all child of groupid
    $allgroupid = findchildgroup($groupid,$con);
    
    //insert ข้อความเข้าตาราง message และดึง id มาเก็บไว้
    $identity = generateRandomString();
    $c_date = date("Y-m-d");
    $c_time = date("h:i:sa");
    $sql = "INSERT INTO  `workingalert`.`message` 
            (`message_body` ,`priority` ,`from_user_id` ,`identity` ,`create_date` ,`create_time`)
            VALUES ('$msgpayload',  '$priority',  '$userid', '$identity', '$c_date', '$c_time');";
    if($con->query($sql)===TRUE){
            //query msgid from message table
            $querymsgid = $con->query("SELECT `message_id` FROM `workingalert`.`message` 
                                WHERE `from_user_id` = '$userid' 
                                AND `identity` = '$identity';");
            $msgiddata = $querymsgid->fetch_assoc();
            $msgid = $msgiddata["message_id"];
    }
    
    $alluserid = array();
    //loop แต่ละ child หา user id
    foreach($allgroupid as $eachgroupid){
        $querymember = $con->query("SELECT `user_id` FROM `has_user` WHERE `has_user`.`group_id` = '$eachgroupid';");
        if($querymember->num_rows > 0) {
            $arruserid = array();
            while($row = $querymember->fetch_assoc()) {
                array_push($arruserid,$row["user_id"]);
            }
            //loop insert ลงตาราง has_message
            foreach($arruserid as $eachuserid){
                $sqlinsert = "INSERT INTO  `workingalert`.`has_message` 
                            (`message_id` ,`user_id` ,`group_id`)
                            VALUES ('$msgid',  '$eachuserid',  '$eachgroupid');";
                if($con->query($sqlinsert) === TRUE){
                    //insert success
                    //store user_id for send push
                    array_push($alluserid,$eachuserid);
                }else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        }
    }
    
    //send push noti to all userid
    //query all device id
    $stralluserid = implode(",",$alluserid);
    $queryallDeviceId = $con->query("SELECT distinct `device_id` FROM `user_deviceid` where `user_id` in ($stralluserid)");
    if($queryallDeviceId->num_rows > 0){
	    // output data of each row
	    $arrayOfDeviceId = array();
 	   while($row = $queryallDeviceId->fetch_assoc()) {
 	       array_push($arrayOfDeviceId,$row['device_id']);
 	   }
        //find sender name
       $querysendername = $con->query("SELECT * FROM `user` WHERE `user`.`user_id` = '$userid';");
        if($querysendername->num_rows > 0){
            $row = $querysendername->fetch_assoc();
            $sendername = $row['nickname'];
        }else{
            $sendername = 'undefind';   
        }
        
        //prepare data before push
 	   $title = $msgpayload;
 	   $msg = $sendername;
 	   $msgstatus = sendPush($arrayOfDeviceId,$title,$msg);
 	   $response = array("status"=>"success","description"=>"message create complete","push"=>$msgstatus);
	} else {
 	   $response = array("status"=>"success","description"=>"message create complete","push"=>"send fail");
	}
    
}//close check user id 
echo json_encode($response);
?>