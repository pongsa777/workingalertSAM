<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

$sessionid = $con->real_escape_string($_GET['sessionid']);
$type = $con->real_escape_string($_GET['type']);

$response = array("status"=>"failed","description"=>"some problems");
$blockedgroup = array();
$userattribute = array();
$waitadmin = array();
if($type != ""){
  $userid = finduserid($sessionid,$con,$type);
  if ($userid != 0) {
    $sqlblock = "SELECT * FROM `has_user` JOIN `group` on `has_user`.`group_id` = `group`.`group_id` WHERE `role_id` = 3 AND `user_id` = '$userid'";
    $queryblock = $con->query($sqlblock);
    if($queryblock->num_rows > 0){
      while($row = $queryblock->fetch_assoc()){
        $eachgroup = array(
                            'groupid' => $row['group_id'],
                            'groupname' => $row['group_name'],
                            'groupdesc' => $row['description'],
                            'createid' => $row['create_user_id'],
                            'pareneid' => $row['parent_id'],
                            'password' => $row['password'],
                            'approve' => $row['approve'],
                            'icon' => $row['icon'],
                            'permision' => $row['permission']
                          );
        array_push($blockedgroup,$eachgroup);
      }
    }

    $sqlattr = "SELECT * FROM `has_attribute` JOIN `attribute` ON `attribute`.`attr_id` = `has_attribute`.`attr_id` WHERE `user_id` ='$userid'";
    $queryattr = $con->query($sqlattr);
    if($queryattr->num_rows > 0){
      while($row = $queryattr->fetch_assoc()){
        $eachattr = array(
                            'attr_id' => $row['attr_id'],
                            'attr_name' => $row['attr_name'],
                            'create_id' => $row['create_id'],
                            'add_date' => $row['add_date']
                         );
        array_push($userattribute,$eachattr);
      }
    }

    $sqlwaitadmin = "SELECT * FROM `has_user` JOIN `group` on `has_user`.`group_id` = `group`.`group_id` WHERE `role_id` = 4 AND `user_id` = '$userid'";
    $querywaitadmin = $con->query($sqlwaitadmin);
    if ($querywaitadmin->num_rows > 0) {
      while($row = $querywaitadmin->fetch_assoc()){
        $eachwaitadmin = array(
                                'groupid' => $row['group_id'],
                                'groupname' => $row['group_name'],
                                'groupdesc' => $row['description'],
                                'createid' => $row['create_user_id'],
                                'pareneid' => $row['parent_id'],
                                'password' => $row['password'],
                                'approve' => $row['approve'],
                                'icon' => $row['icon'],
                                'permision' => $row['permission']
                              );
        array_push($waitadmin,$eachwaitadmin);
      }
    }
    $response = array("status"=>"success","description"=>"get data success","blockgroup"=>$blockedgroup,"userattribute"=>$userattribute,"waitadmin"=>$waitadmin);
  }else {
    $response = array("status"=>"failed","description"=>"wrong session id");
  }
}else{
  $response = array("status"=>"failed","description"=>"missing type parameter");
}
echo json_encode($response);
?>
