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

function getgrouppict($con,$groupid){
  $sqlgetpict = "SELECT  `icon` FROM  `group` WHERE  `group_id` =  '$groupid'";
  $querygetpict = $con->query($sqlgetpict);
  if($querygetpict ->num_rows >0){
    $row = $querygetpict->fetch_assoc();
    if($row['icon'] != ""){
      return $row['icon'];
    }else {
      return "NULL";
    }
  }
  return "NULL";
}


$msg = array();
$response = array("status"=>"failed","description"=>"some problems","message"=>$msg);
$userid = finduserid($sessionid,$con,$type);
if($userid != 0){
    //query all message and message data
    $querymsg = $con->query("SELECT distinct `has_message`.`pathmsg`,
    `has_message`.`message_id`,
    `has_message`.`group_id`,
    `has_message`.`reach_status`,
    `has_message`.`read_status`,
    `has_message`.`user_id`,
    `message`.`create_date`,
    `message`.`create_time`,
    `message`.`from_user_id`,
    `message`.`identity`,
    `message`.`message_body`,
    `message`.`priority`,
    `message`.`to_groupid`,
    `message`.`to_groupname`
    FROM  `has_message`
    JOIN  `message` ON  `has_message`.`message_id` =  `message`.`message_id`
    WHERE  `has_message`.`user_id` = '$userid'
    ORDER BY  `message`.`message_id` DESC, CHAR_LENGTH(`has_message`.`pathmsg`)
	");
    $checkmsgid = 0;
    $grouppath = array();
    $id_db = "";
    $body_db = "";
    $priority_db = "";
    $fromid_db = "";
    $read_db = "";
    $reach_db = "";
    $c_date = "";
    $c_time = "";


    if($querymsg->num_rows > 0){ //check ว่ามี record ออกมารึป่าว
        while($msgdata = $querymsg->fetch_assoc()){
            $fromid = $msgdata["from_user_id"];
            $querysendername = $con->query("SELECT CONCAT(`firstname`,' ',`lastname`)
            AS name,`picture` FROM `user` WHERE `user_id` = '$fromid';");
            $sendernamedata = $querysendername->fetch_assoc();

            if($checkmsgid == 0){ //ถ้าเข้ามาครั้งแรกเก็บค่าไว้เช็คว่าเป็นตัวเดิมรึป่าว
                $id_db = $msgdata["message_id"];
                $body_db = $msgdata["message_body"];
                $priority_db = $msgdata["priority"];
                $fromid_db = $msgdata["from_user_id"];
                $fromname = $sendernamedata["name"];
                $pict = $sendernamedata["picture"];
                $read_db = $msgdata["read_status"];
                $reach_db = $msgdata["reach_status"];
                $c_date = $msgdata["create_date"];
                $c_time = $msgdata["create_time"];
                $checkmsgid = $id_db;
            }

            //check groupid ถ้ายังเหมือนเดิมอยู่ให้ add id กับ path เข้า $groupid
            if($msgdata["message_id"] == $checkmsgid){
                $groupiddetail = array(
                                        "id"=>$msgdata["group_id"],
                                        "path"=>findparentpath($msgdata["group_id"],$con),
                                        "pict"=>getgrouppict($con,$msgdata["group_id"])
                                      );
                array_push($grouppath,$groupiddetail);
            }else{
                //add element เข้า $msgdetail
                $msgdetail = array("id"=>$id_db,
                                   "grouppath"=>$grouppath,
                                   "body"=>$body_db,
                                   "priority"=>$priority_db,
                                   "fromid"=>$fromid_db,
                                   "pict"=>$pict,
                                   "formname"=>$fromname,
                                   "read"=>$read_db,
                                   "reach"=>$reach_db,
                                   "date"=>$c_date,
                                   "time"=>$c_time
                                  );
                array_push($msg,$msgdetail);
                $grouppath = array();
                //ย้าย id ใหม่ใส่ $checkmsgid
                $checkmsgid = $msgdata["message_id"];

                //เคลียค่า $groupid
                $groupid = array();

                //หา path ใส่ $groupid
                $path = findparentpath($msgdata["group_id"],$con);
                $pixx = getgrouppict($con,$msgdata["group_id"]);
                $groupiddetail = array(
                                        "id"=>$msgdata["group_id"],
                                        "path"=>$path,
                                        "pict"=>$pixx
                                      );
                array_push($grouppath,$groupiddetail);

                //get all rows from db to ตัวแปรใน php
                $id_db = $msgdata["message_id"];
                $body_db = $msgdata["message_body"];
                $priority_db = $msgdata["priority"];
                $fromid_db = $msgdata["from_user_id"];
                $fromname = $sendernamedata["name"];
                $pict = $sendernamedata["picture"];
                $read_db = $msgdata["read_status"];
                $reach_db = $msgdata["reach_status"];
                $c_date = $msgdata["create_date"];
                $c_time = $msgdata["create_time"];
            }
        }

        //หลุด loop แล้วยังต้องเอาค่า id สุดท้ายเก็บลง $msgdetail
        $msgdetail = array("id"=>$id_db,
                           "grouppath"=>$grouppath,
                           "body"=>$body_db,
                           "priority"=>$priority_db,
                           "fromid"=>$fromid_db,
                           "pict"=>$pict,
                           "formname"=>$fromname,
                           "read"=>$read_db,
                           "reach"=>$reach_db,
                           "date"=>$c_date,
                           "time"=>$c_time);
                array_push($msg,$msgdetail);

        $response = array("status"=>"success","description"=>"all data","message"=>$msg);
    }else{
        $response = array("status"=>"success","description"=>"no message","message"=>$msg);
    }
}else{
    //not found user_id
    $response = array("status"=>"failed","description"=>"invalid session id","message"=>$msg);
}

echo json_encode($response);
?>
