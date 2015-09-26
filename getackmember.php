<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

  $sessionid = $con->real_escape_string($_GET['sessionid']);
  $msgid = $con->real_escape_string($_GET['msgid']);
  $type = $con->real_escape_string($_GET['type']);

  $response = array("status"=>"failed","comment"=>"some problems");
  $userid = finduserid($sessionid,$con,$type);

  $readmember = array();
  $unreadmember = array();

  if($userid != 0){ //เชคว่า user มีอยู่จริง
    $userread = $con->query("SELECT * FROM `has_message` JOIN  `user` ON  `has_message`.`user_id` =  `user`.`user_id` WHERE `message_id` = '$msgid' AND `read_status` IN ('y','Y');"); //หา user ที่อ่านแล้ว
    if($userread->num_rows > 0){
      while($row = $userread->fetch_assoc()){
        $userdetail = array("id"=>$row["user_id"],
                            "message_id"=>$row["message_id"],
                            "group_id"=>$row["group_id"],
                            "read_status"=>$row["read_status"],
                            "email"=>$row["email"],
                            "firstname"=>$row["firstname"],
                            "lastname"=>$row["lastname"],
                            "nickname"=>$row["nickname"],
                            "phone"=>$row["phone"],
                            "picture"=>$row["picture"]
                          );
        array_push($readmember,$userdetail);
      }
    }

      $userunread = $con->query("SELECT * FROM `has_message`
                                  JOIN `user` ON `has_message`.`user_id` = `user`.`user_id`
                                  WHERE  `message_id` = '$msgid'
                                  AND `read_status` IS NULL;"); //หา user ที่ยังไม่ได้อ่าน

      if($userunread->num_rows > 0){
        while($row2 = $userunread->fetch_assoc()){
          $userdetail2 = array("id"=>$row2["user_id"],
                              "message_id"=>$row2["message_id"],
                              "group_id"=>$row2["group_id"],
                              "read_status"=>$row2["read_status"],
                              "email"=>$row2["email"],
                              "firstname"=>$row2["firstname"],
                              "lastname"=>$row2["lastname"],
                              "nickname"=>$row2["nickname"],
                              "phone"=>$row2["phone"],
                              "picture"=>$row2["picture"]
                            );
          array_push($unreadmember,$userdetail2);
        }
      }

    $response = array("status"=>"success","description"=>"get data success","readuser"=>$readmember,"unreadmember"=>$unreadmember);
  }else{
    $response = array("status"=>"failed","description"=>"missing permission token");
  }
echo json_encode($response);
?>
