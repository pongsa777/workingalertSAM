<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";
include "push.php";

//start recieve parameters
$sessionid = $con->real_escape_string($_GET['sessionid']);
$msgid = $con->real_escape_string($_GET['msgid']);
$type = $con->real_escape_string($_GET['type']);
//end recieve parameters

//start initial variable
$response = array("status"=>"failed","description"=>"some problems");
$arrayOfDeviceId = array();
//end initial variable

$userid = finduserid($sessionid,$con,$type);
if($userid != 0){ //found user id
  $sqlunread = "SELECT distinct `has_message`.`user_id` , `user_deviceid`.`device_id` FROM `has_message`
                JOIN `user_deviceid`
                ON `has_message`.`user_id`=`user_deviceid`.`user_id`
                WHERE `has_message`.`message_id` = '$msgid'
                AND `has_message`.`read_status` = 'N' ";         
  $userunread = $con->query($sqlunread);
  if($userunread->num_rows > 0){
    while($row = $userunread->fetch_assoc()){
      array_push($arrayOfDeviceId,$row['device_id']); //หา device id ใส่ array ไว้รอ push
    }

    //หาชื่อของผู้ส่งจาก userid ของคนที่ส่ง
    $querysendername = $con->query("SELECT * FROM `user` WHERE `user`.`user_id` = '$userid';");
     if($querysendername->num_rows > 0){
         $row = $querysendername->fetch_assoc();
         $sendername = $row['firstname'].' '.$row['lastname'].'('.$row['nickname'].')';
     }else{
         $sendername = 'undefind';
     }

     //หาข้อมูล message body มาเพื่อส่ง push
     $querymessage = $con->query("SELECT * FROM `message` WHERE `message`.`message_id` = '$msgid';");
      if($querymessage->num_rows > 0){
          $row2 = $querymessage->fetch_assoc();
          $msgpayload = $row2['message_body'];
      }else{
          $msgpayload = 'undefind';
      }

     //เตรียมข้อมูลไว้ส่ง push
     $title = $msgpayload;
     $msg = $sendername;
     //ส่ง push ด้วยข้อมูลตามที่กำหนดไว้ข้างต้น เก็บค่าการส่งไว้ใน $msgstatus
     $msgstatus = sendPush($arrayOfDeviceId,$title,$msg);

     $response = array("status"=>"success","description"=>"resend push complete","push"=>$msgstatus);
  }else{
    $response = array("status"=>"failed","description"=>"no unAck user");
  }



}else{ //not found user id
  $response = array("status"=>"failed","description"=>"missing permission token");
}

echo json_encode($response);
?>
