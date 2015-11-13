<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";


function getmystatus($con,$userid,$groupid){
  $status = "";
  $sqlgetmystat = "SELECT * FROM `has_user` WHERE `user_id` = '$userid' AND `group_id` = '$groupid'";
  $querygetmystat = $con->query($sqlgetmystat);
  if($querygetmystat->num_rows>0){
    $row = $querygetmystat->fetch_assoc();
      if( $row['role_id'] == 1 ){
        $status = "admin";
      }elseif ($row['role_id'] == 2) {
        $status = "member";
      }elseif ($row['role_id'] == 3) {
        $status = "block";
      }elseif ($row['role_id'] == 4) {
        $status = "pending admin approve";
      }elseif ($row['role_id'] == 5) {
        $status = "pending user confirm";
      }else{
        $status = "error";
      }
  }else{
    $status = "error";
  }
  return $status;
}

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);
    $type = $con->real_escape_string($_GET['type']);

$admin = array();
$member = array();
$block = array();
$pending = array();
$response = array("status"=>"success","description"=>"groupmember is","admin"=>$admin,"member"=>$member,"block"=>$block,"pending"=>$pending,"mystatus"=>"");
$userid = finduserid($sessionid,$con,$type);


if($userid != 0){
    //check user can view member ว่าuser อยู่ในกรุ๊ปนั่นจริงๆ
    $sql1 = "SELECT * FROM `has_user` WHERE `user_id` = '$userid' AND `group_id` = '$groupid'";
    $querypermission = $con->query($sql1);
    if($querypermission ->num_rows > 0){ //have permission
      $sqlselect = "SELECT *
                    FROM  `has_user`
                    JOIN  `user` ON  `has_user`.`user_id` =  `user`.`user_id`
                    WHERE  `has_user`.`group_id` = '$groupid'";
      $queryselect = $con->query($sqlselect);
      if($queryselect->num_rows > 0){
        $admin = array();
        $member = array();
        $block = array();
        $pending = array();
        $mystatus = getmystatus($con,$userid,$groupid);
        while($row = $queryselect->fetch_assoc()){
          if($row["role_id"] == '1' || $row["role_id"] == 1){
            array_push($admin,array(
                                    "id"=>$row["user_id"],
                                    "role_id"=>$row["user_id"],
                                    "facebook_id"=>$row["facebook_id"],
                                    "email"=>$row["email"],
                                    "firstname"=>$row["firstname"],
                                    "lastname"=>$row["lastname"],
                                    "nickname"=>$row["nickname"],
                                    "phone"=>$row["phone"],
                                    "picture"=>$row["picture"]
                                    ));
          }else if($row["role_id"] == '2' || $row["role_id"] == 2){
            array_push($member,array(
                                    "id"=>$row["user_id"],
                                    "role_id"=>$row["user_id"],
                                    "facebook_id"=>$row["facebook_id"],
                                    "email"=>$row["email"],
                                    "firstname"=>$row["firstname"],
                                    "lastname"=>$row["lastname"],
                                    "nickname"=>$row["nickname"],
                                    "phone"=>$row["phone"],
                                    "picture"=>$row["picture"]
                                    ));
          }else if ($row["role_id"] == '3' || $row["role_id"] == 3) {
            array_push($block,array(
                                    "id"=>$row["user_id"],
                                    "role_id"=>$row["user_id"],
                                    "facebook_id"=>$row["facebook_id"],
                                    "email"=>$row["email"],
                                    "firstname"=>$row["firstname"],
                                    "lastname"=>$row["lastname"],
                                    "nickname"=>$row["nickname"],
                                    "phone"=>$row["phone"],
                                    "picture"=>$row["picture"]
                                    ));
          }else if ($row["role_id"] == '4' || $row["role_id"] == 4) {
            array_push($pending,array(
                                    "id"=>$row["user_id"],
                                    "role_id"=>$row["user_id"],
                                    "facebook_id"=>$row["facebook_id"],
                                    "email"=>$row["email"],
                                    "firstname"=>$row["firstname"],
                                    "lastname"=>$row["lastname"],
                                    "nickname"=>$row["nickname"],
                                    "phone"=>$row["phone"],
                                    "picture"=>$row["picture"]
                                    ));
          }
        } //end while
        $response = array("status"=>"success","description"=>"groupmember is","admin"=>$admin,"member"=>$member,"block"=>$block,"pending"=>$pending,"mystatus"=>$mystatus);
      }else{
        $response = array("status"=>"success","description"=>"no member in this group","admin"=>$admin,"member"=>$member,"block"=>$block,"pending"=>$pending,"mystatus"=>$mystatus);
      }
    }else{
        //don't have permission
        $response = array("status"=>"failed","description"=>"you don't have permission to view this group","admin"=>$admin,"member"=>$member,"block"=>$block,"pending"=>$pending,"mystatus"=>$mystatus);
    }
}else{
    //userid = 0
    $response = array("status"=>"failed","description"=>"wrong session id","admin"=>$admin,"member"=>$member,"block"=>$block,"pending"=>$pending,"mystatus"=>$mystatus);
}
echo json_encode($response);
?>
