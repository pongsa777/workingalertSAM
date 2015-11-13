<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

$sessionid = $con->real_escape_string($_GET['sessionid']);
$type = $con->real_escape_string($_GET['type']);
$groupid = $con->real_escape_string($_GET['groupid']);

$dataresult = array();
$response = array("status"=>"failed","description"=>"some problems","groupdata"=>$dataresult);
$userid = finduserid($sessionid,$con,$type);

if($userid != 0){
  if( $groupid == "" || $groupid == 0 ){
    $response = array("status"=>"failed","description"=>"missing groupid parameters");
  }else{
    $sqlgroupdetail = "SELECT * FROM  `group` WHERE  `group_id` = '$groupid' ";
    $querygroupdeatil = $con->query($sqlgroupdetail);
    if($querygroupdeatil->num_rows>0){
      $groupdata = $querygroupdeatil->fetch_assoc();

      $groupname = $groupdata['group_name'];
      $groupdesc = $groupdata['description'];

      //privacy to join
      $temppass = $groupdata['password'];
      $privacy = "none";
      if($temppass == "" || $temppass == null || $temppass == "null"){
      }else{
        if($privacy == "none"){
          $privacy = "password authen";
        }else{
          $privacy = $privacy.",password authen";
        }
      }

      if($groupdata['approve'] == "0" || $groupdata['approve'] == null || $groupdata['approve'] == "null" ){
      }else {
        if($privacy == "none"){
          $privacy = "require approver";
        }else{
          $privacy = $privacy.',require approver';
        }
      }

      //group permission
      if( $groupdata['permission'] == "" || $groupdata['permission'] == "1" || $groupdata['permission'] == null ){
        $permission = 'everyone can create message';
      }else {
        $permission = 'only admin can create message';
      }

      //insert data result
      $dataresult = array(
                            "groupname"            =>    $groupname,
                            "groupid"              =>    $groupdata['group_id'],
                            "description"          =>    $groupdesc,
                            "create_userid"        =>    $groupdata['create_user_id'],
                            "parent_id"            =>    $groupdata['parent_id'],
                            "privacy"              =>    $privacy,
                            "raw_approve"          =>    $groupdata['approve'],
                            "raw_password"         =>    $groupdata['password'],
                            "icon"                 =>    $groupdata['icon'],
                            "permission"           =>    $permission,
                            "raw_permission"       =>    $groupdata['permission']
                          );

      $response = array("status"=>"success","description"=>"get data success","groupdata"=>$dataresult);
    }else{
      $response = array("status"=>"failed","description"=>"no group data return from database","groupdata"=>$dataresult);
    }
  }
}else{
  $response = array("status"=>"failed","description"=>"wrong session id","groupdata"=>$dataresult);
}

echo json_encode($response);
?>
