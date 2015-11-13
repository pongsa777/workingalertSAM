<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);
    $type = $con->real_escape_string($_GET['type']);


$msg = array();
$group = array();

$response = array("status"=>"failed","description"=>"some problems","groupdetail"=>$group,"message"=>$msg);
$userid = finduserid($sessionid,$con,$type);

if($userid != 0){

  $sqlgroupdetail = "SELECT * FROM `group` WHERE `group_id` = '$groupid'";
  $querygroup = $con->query($sqlgroupdetail);
  $groupdetail = $querygroup->fetch_assoc();
  $toinsertgroup = array(
                          "id"=>$groupdetail['group_id'],
                          "name"=>$groupdetail["group_name"]
                        );
  array_push($group,$toinsertgroup);

  $response = array("status"=>"success","description"=>"your message","groupdetail"=>$group,"message"=>$msg);

  $sql = "SELECT * FROM `message` WHERE `from_user_id` = '$userid'
          AND `message_id` in (SELECT `message_id` FROM `has_message` WHERE `group_id` = '$groupid')";
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

    $response = array("status"=>"success","description"=>"your message","groupdetail"=>$group,"message"=>$msg);
  }

}else{
    //not found user_id
    $response = array("status"=>"failed","description"=>"invalid session id","groupdetail"=>$group,"message"=>$msg);
}

echo json_encode($response);

?>
