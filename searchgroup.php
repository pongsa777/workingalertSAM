<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

	$sessionid = $con->real_escape_string($_GET['sessionid']);
	$searchmsg = $con->real_escape_string($_GET['searchmsg']);
	
$userid = finduserid($sessionid,$con);
if($userid == 0){
	$response = array("status"=>"failed","description"=>"you are not authorize to use this function","data"=>$data);	
}else{
	$data = array();
    $que = "SELECT * FROM `group` WHERE `group`.`group_name` like '%$searchmsg%' AND `group`.`parent_id` = 0 LIMIT 0 , 30;";
	$querygroup = $con->query($que);
	if($querygroup->num_rows > 0){
		while($groupdata = $querygroup->fetch_assoc()){
			array_push($data,array("groupid"		=>$groupdata['group_id'],
									"groupname"		=>$groupdata['group_name'],
									"lock"			=>$groupdata['password'],
									"description"	=>$groupdata['description']
									));
		}
		$response = array("status"=>"success","description"=>"__","data"=>$data);
	}else{
		$response = array("status"=>"success","description"=>"__","data"=>$data);
	}
}

echo json_encode($response);
?>