<?php
include "dbconnect.php";
include "finduserid.php";

$response = array("status"=>"failed","description"=>"Not found user id in table");

	$sessionid = $con->real_escape_string($_GET['sessionid']);
	$pushid = $con->real_escape_string($_GET['pushid']);
	$type = $con->real_escape_string($_GET['type']);


	$userid = finduserid($sessionid,$con,$type);

	$queryUser = $con->query("SELECT * FROM `workingalert`.`user_deviceid` WHERE `user_id` = '$userid';");
	if($queryUser->num_rows > 0){
    	//found id in table
    	$pueryUpdate = $con->query("UPDATE  `workingalert`.`user_deviceid` 
    								SET  `device_id` =  '$pushid' 
    								WHERE  `user_deviceid`.`user_id` ='$userid'");
		$response = array("status"=>"success","description"=>"Update device id success");
	}else{
		//don't found id in table
		$queryCreate = $con->query("INSERT INTO  `workingalert`.`user_deviceid` (`user_id` ,`device_id`)
								VALUES ('$userid',  '$pushid')");
		$response = array("status"=>"success","description"=>"insert device id success");
	}
echo json_encode($response);
?>