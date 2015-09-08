<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);

$group = array();
$response = array("status"=>"failed","description"=>"some problems","group"=>$group);

$userid = finduserid($sessionid,$con);
if($userid != 0){
    $querygroup = $con->query("SELECT * FROM  `group` 
                                JOIN `has_user` on `has_user`.group_id=`group`.group_id 
                                WHERE (`group`.`parent_id` = 0 or `group`.`parent_id` is null)
                                AND `has_user`.user_id = '$userid';");
    if($querygroup->num_rows > 0){
        //found group
        while($groupdata = $querygroup->fetch_assoc()){
            $groupdetail = array("id"=>$groupdata["group_id"],"name"=>$groupdata["group_name"],
                                "description"=>$groupdata["description"],"date"=>$groupdata["end_date"],
                                "role"=>$groupdata["role_id"]);
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
