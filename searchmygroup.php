<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

	$sessionid = $con->real_escape_string($_GET['sessionid']);
	$searchmsg = $con->real_escape_string($_GET['searchmsg']);
	$type = $con->real_escape_string($_GET['type']);

$userid = finduserid($sessionid,$con,$type);
if($userid == 0){
	$response = array("status"=>"failed","description"=>"you are not authorize to use this function","data"=>$data);
}else{
	$data = array();
    $que = "SELECT * FROM  `group` WHERE `group`.`group_name` like '%$searchmsg%'
			AND  `group`.`group_id` IN (SELECT  `has_user`.`group_id`
											FROM  `has_user` WHERE  `has_user`.`user_id` = '$userid') LIMIT 0 , 30";
	$querygroup = $con->query($que);
	if($querygroup->num_rows > 0){
		while($groupdata = $querygroup->fetch_assoc()){
			array_push($data,array(
															"groupid"			=> $groupdata['group_id'],
															"groupname"		=> $groupdata['group_name'],
															"lock"				=> $groupdata['password'],
															"description"	=> $groupdata['description'],
															"icon"				=> $groupdata['icon'],
															"approve"			=> $groupdata['approve'],
															"permission"	=> $groupdata['permission'],
														));
		}
		$response = array("status"=>"success","description"=>"__","data"=>$data);
	}else{
		$response = array("status"=>"success","description"=>"__","data"=>$data);
	}
}

echo json_encode($response);
?>
