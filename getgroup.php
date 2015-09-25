<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
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

$group = array();
$response = array("status"=>"failed","description"=>"some problems","group"=>$group);

$userid = finduserid($sessionid,$con,$type);
if($userid != 0){
    $querygroup = $con->query("SELECT * FROM  `group`
                                JOIN `has_user` on `has_user`.group_id=`group`.group_id
                                WHERE `has_user`.user_id = '$userid';");
    if($querygroup->num_rows > 0){
        //found group
        while($groupdata = $querygroup->fetch_assoc()){
            $path = findparentpath($groupdata["group_id"],$con);
            $groupdetail = array("id"          => $groupdata["group_id"],
                                  "name"       => $groupdata["group_name"],
                                  "description"=> $groupdata["description"],
                                  "date"       => $groupdata["end_date"],
                                  "role"       => $groupdata["role_id"],
                                  "path"       => $path
                                );
            array_push($group,$groupdetail);
        }
        $response = array("status"=>"success","description"=>"","group"=>$group);
    }else{
        $response = array("status"=>"success","description"=>"not found group","group"=>$group);
    }
}else{
    //not found user_id
    $response = array("status"=>"failed","description"=>"not found user_id");
}
echo json_encode($response);

?>
