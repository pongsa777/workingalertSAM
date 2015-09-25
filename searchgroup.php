<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

	$sessionid = $con->real_escape_string($_GET['sessionid']);
	$searchmsg = $con->real_escape_string($_GET['searchmsg']);
	$type = $con->real_escape_string($_GET['type']);

function findparentpath($groupid,$con){
	    $path = "";
	    $parent = 0;
	    do{
	        $queryparent = $con->query("SELECT `parent_id`,`group_name` FROM `group` WHERE `group_id` ='$groupid';");
	        $parentdata = $queryparent->fetch_assoc();
	        $parent = $parentdata['parent_id'];
	        $groupid = $parent;
	        $path = $parentdata['group_name'].' -> '.$path;
	    }while($parent != 0);
	    return substr($path,0,-3);
}


$userid = finduserid($sessionid,$con,$type);
if($userid == 0){
	$response = array("status"=>"failed","description"=>"you are not authorize to use this function","data"=>$data);
}else{
	$data = array();
    $que = "SELECT * FROM  `group` WHERE `group`.`group_name` like '%$searchmsg%'
			AND  `group`.`group_id` NOT IN (SELECT  `has_user`.`group_id`
											FROM  `has_user` WHERE  `has_user`.`user_id` = '$userid') LIMIT 0 , 30";
	$querygroup = $con->query($que);
	if($querygroup->num_rows > 0){
		while($groupdata = $querygroup->fetch_assoc()){
			$path = findparentpath($groupdata["group_id"],$con);
			array_push($data,array("groupid"		=>$groupdata['group_id'],
									"groupname"		=>$groupdata['group_name'],
									"lock"			=>$groupdata['password'],
									"description"	=>$groupdata['description'],
									"path"				=> $path
									));
		}
		$response = array("status"=>"success","description"=>"__","data"=>$data);
	}else{
		$response = array("status"=>"success","description"=>"__","data"=>$data);
	}
}

echo json_encode($response);
?>
