<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $type = $con->real_escape_string($_GET['type']);

    function findparentpath($groupid,$con){
    	  $path = array();
        $parent = 0;
        do{
    		    $queryparent = $con->query("SELECT `parent_id`,`group_name` FROM `group` WHERE `group_id` ='$groupid';");
    		    $parentdata = $queryparent->fetch_assoc();
    		    $parent = $parentdata['parent_id'];
    	      $groupid = $parent;
    	      // $path = $parentdata['group_name'].' -> '.$path;
    	      array_push($path,$parentdata['group_name']);
    	  }while($parent != 0);
    	  return array_reverse($path);
    }

    function countunreadmsg($groupid,$userid,$con){
    	        $queryunreadmsg = $con->query("SELECT COUNT(`message_id`) AS cntmsg
                                          FROM  `has_message`
                                          WHERE  `user_id` = $userid
                                          AND  `group_id` = $groupid
                                          AND  `read_status` =  'N';");
    	        $unreaddata = $queryunreadmsg->fetch_assoc();
    	        $unread = $unreaddata['cntmsg'];
    	    return $unread;
    }

$group = array();
$toconfirmgroup = array();
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
            $unread = countunreadmsg($groupdata["group_id"],$userid,$con);
            $groupdetail = array("id"          => $groupdata["group_id"],
                                  "name"       => $groupdata["group_name"],
                                  "description"=> $groupdata["description"],
                                  "pict"       => $groupdata["icon"],
                                  "role"       => $groupdata["role_id"],
                                  "path"       => $path,
                                  "unreadmsg"  => $unread
                                );
            array_push($group,$groupdetail);
        }
        //$response = array("status"=>"success","description"=>"","group"=>$group);
    }//else{
        //$response = array("status"=>"success","description"=>"not found group","group"=>"notfound","toconfirmgroup"=>$toconfirmgroup);
    //}

    //echo 'eiei    '.$userid.'   eiei';
    $querytoconfirmgroup = $con->query("SELECT * FROM  `has_user`
                                        JOIN `group` ON `group`.`group_id`=`has_user`.`group_id`
                                        WHERE  `role_id` = '5' AND `has_user`.`user_id` = '$userid';");
    if($querytoconfirmgroup->num_rows > 0){
      while($row = $querytoconfirmgroup->fetch_assoc()){
        $unread2 = countunreadmsg($row["group_id"],$userid,$con);
        $path2 = findparentpath($row["group_id"],$con);
        $toconfirmgroupdetail = array("id"          => $row["group_id"],
                                      "name"        => $row["group_name"],
                                      "description" => $row["description"],
                                      "role"        => $row["role_id"],
                                      "path"        => $path2,
                                      "unreadmsg"   => $unread2
                                     );
        array_push($toconfirmgroup,$toconfirmgroupdetail);
      }
      $response = array("status"=>"success","description"=>"","group"=>$group,"toconfirmgroup"=>$toconfirmgroup);
    }else{
      $response = array("status"=>"success","description"=>"","group"=>$group,"toconfirmgroup"=>"empty");
    }
}else{
    //not found user_id
    $response = array("status"=>"failed","description"=>"not found user_id");
}
echo json_encode($response);
?>
