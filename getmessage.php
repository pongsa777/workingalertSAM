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

function checkapprove($con,$groupid){
  $permiss = -1;
  $sqlapprove = "SELECT * FROM `group` WHERE `group_id` = '$groupid'";
  $querycheckapprove = $con->query($sqlapprove);
  if( $querycheckapprove -> num_rows > 0 ){
    $dataa = $querycheckapprove->fetch_assoc();
    $permiss = $dataa['permission'];
    return $permiss;
  }
}

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);
    $type = $con->real_escape_string($_GET['type']);


$msg = array();
$group = array();
$cansend = "no";

$response = array("status"=>"failed","description"=>"some problems","groupdetail"=>$group,"message"=>$msg,"can_send"=>$cansend);
$userid = finduserid($sessionid,$con,$type);

if($userid != 0){
  //get this group detail [name,id,icon];
  $sqlgroupdetail = "SELECT * FROM `group` WHERE `group_id` = '$groupid'";
  $querygroup = $con->query($sqlgroupdetail);
  $groupdetail = $querygroup->fetch_assoc();
  $toinsertgroup = array(
                          "id"=>$groupdetail['group_id'],
                          "name"=>$groupdetail["group_name"]
                        );
  array_push($group,$toinsertgroup);

  $response = array("status"=>"success","description"=>"your message","groupdetail"=>$group,"message"=>$msg,"can_send"=>$cansend);

  $sql = "SELECT * FROM `message` WHERE `message_id` in (SELECT `message_id` FROM `has_message` WHERE `group_id` = '$groupid') ORDER BY  `message`.`message_id` DESC  ";
  $queryselect = $con->query($sql);
  if($queryselect->num_rows>0){

    while ($row = $queryselect->fetch_assoc()) {
      //find sender
      $fromid = $row["from_user_id"];
      $querysendername = $con->query("SELECT CONCAT(`firstname`,' ',`lastname`)
      AS name,`picture` FROM `user` WHERE `user_id` = '$fromid';");
      $sendernamedata = $querysendername->fetch_assoc();

      //find reach message
      $msgid = $row["message_id"];
      $sqlhas_message = "SELECT * FROM `has_message` WHERE `message_id` =  '$msgid'
                          AND `user_id` =  '$userid' AND `group_id` =  '$groupid'";
      $queryhas_message = $con->query($sqlhas_message);
      $has_message = $queryhas_message->fetch_assoc();



      $msgdetail = array(
                          "id"=>$row["message_id"],
                          "body"=>$row["message_body"],
                          "priority"=>$row["priority"],
                          "fromid"=>$fromid,
                          "fromname"=>$sendernamedata["name"],
                          "pict"=>$sendernamedata["picture"],
                          "read"=>$has_message["read_status"],
                          "reach"=>$has_message["reach_status"],
                          "date"=>$row["create_date"],
                          "time"=>$row["create_time"],
                          "fromgroupid"=>$groupdetail["group_id"],
                          "icon"=>$groupdetail["icon"]
                        );
      array_push($msg,$msgdetail);
    }


    $mystatus = getmystatus($con,$userid,$groupid);
    if($mystatus == "admin"){
      $cansend = "yes";
    }else{
      //check approve
      if( $mystatus == "member" && checkapprove($con,$groupid) == 1 ){
        $cansend = "yes";
      }
    }
    $response = array("status"=>"success","description"=>"your message","groupdetail"=>$group,"message"=>$msg,"can_send"=>$cansend);
  }

}else{
    //not found user_id
    $response = array("status"=>"failed","description"=>"invalid session id","groupdetail"=>$group,"message"=>$msg,"can_send"=>$cansend);
}

echo json_encode($response);

?>
