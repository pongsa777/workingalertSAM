<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);
    $type = $con->real_escape_string($_GET['type']);


$group = array();
$grouptojoin = array();
$response = array("status"=>"failed","description"=>"some problems","group"=>$group,"parentname"=>"");
$userid = finduserid($sessionid,$con,$type);
if($userid != 0){
    //found user_id
    $queryparentname = $con->query("SELECT `group_name` FROM `group` WHERE `group`.`group_id` = '$groupid'");
    $parentnamedata = $queryparentname->fetch_assoc();
    $parentname = $parentnamedata['group_name'];

        $querygroup = $con->query("SELECT * FROM `has_user` join `group` on `has_user`.group_id=`group`.group_id where `has_user`.user_id = '$userid' and `group`.`parent_id` = '$groupid'");
            //found group
            while($groupdata = $querygroup->fetch_assoc()){
                $groupdetail = array("id"=>$groupdata["group_id"],"name"=>$groupdata["group_name"],
                                     "description"=>$groupdata["description"],"role"=>$groupdata["role_id"]);
                array_push($group,$groupdetail);
            }

            $querygroup2 = $con->query("SELECT DISTINCT `group`.`group_id`, `group`.`group_name`, `group`.`description`, `group`.`create_user_id`, `group`.`parent_id`, `group`.`password` FROM `has_user` join `group` on `has_user`.group_id=`group`.group_id where `has_user`.user_id != '$userid' and `group`.`parent_id` = '$groupid' and `group`.`group_id` not IN (SELECT `group`.`group_id` FROM `has_user` join `group` on `has_user`.group_id=`group`.group_id where `has_user`.user_id = '$userid' and `group`.`parent_id` = '$groupid')");
            if($querygroup2->num_rows > 0){
                while($groupdata2 = $querygroup2->fetch_assoc()){
                  $groupdetail2 = array('id'          => $groupdata2['group_id'],
                                        'name'        => $groupdata2['group_name'],
                                        'description' => $groupdata2['description'] );
                  array_push($grouptojoin,$groupdetail2);
                }
            }

            $response = array("status"=>"success","description"=>"found","group"=>$group,"grouptojoin"=>$grouptojoin,"parentname"=>$parentname);
        }else{
    //not found user_id
    $response = array("status"=>"failed","description"=>"not found user_id");
}

echo json_encode($response);

?>
