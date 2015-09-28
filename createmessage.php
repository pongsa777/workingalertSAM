<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";
include "push.php";
include "findchildgroup.php";
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
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
//end initial code

//start recieve parameters
$sessionid = $con->real_escape_string($_GET['sessionid']);
$groupid = $con->real_escape_string($_GET['groupid']);
$msgpayload = $con->real_escape_string($_GET['msgpayload']);
$priority = $con->real_escape_string($_GET['priority']);
$type = $con->real_escape_string($_GET['type']);
//end recieve parameters

//start initial variable
$response = array("status"=>"failed","description"=>"some problems");
$alluseridforpush = array();
$msgid = 0;
$arrayOfDeviceId = array();
//end initial variable

$userid = finduserid($sessionid,$con,$type);
if($userid != 0){ //เช็ค userid ว่ามีอยู่จริง

  //ใส่ข้อความลง db แล้วเอาตัวแปร message id มาเก็บไว้ชื่อ $msgid
  $identity = generateRandomString();
  $c_date = date("Y-m-d");
  $c_time = date("h:i:sa");
  $sql = "INSERT INTO  `workingalert`.`message`
          (`message_body` ,`priority` ,`from_user_id` ,`identity` ,`create_date` ,`create_time`)
          VALUES ('$msgpayload',  '$priority',  '$userid', '$identity', '$c_date', '$c_time');";
  if($con->query($sql)===TRUE){
          $querymsgid = $con->query("SELECT `message_id` FROM `workingalert`.`message`
                              WHERE `from_user_id` = '$userid'
                              AND `identity` = '$identity';");
          $msgiddata = $querymsgid->fetch_assoc();
          $msgid = $msgiddata["message_id"];

          $allgroupid = findallchild($groupid,$con); //ดึงเอา group ทั้งสายมาเก็บไว้ใน array

          foreach ($allgroupid as $eachgroupid) { //loop เอาค่าแต่ละตัวในอาเรย์ allgroupid
            //หา userid ที่อยู่ในกลุ่มโดยไม่เอา role เป็น 0
            $queryuserdata = $con->query("SELECT `user_id` FROM `has_user` WHERE `has_user`.`group_id` = '$eachgroupid' AND `has_user`.`role_id` != '0'");
            $alluserid = array();
            if($queryuserdata->num_rows > 0){
              while ($row = $queryuserdata->fetch_assoc()) {
                array_push($alluserid,$row["user_id"]); //เก็บ userid ทุกคนที่ได้จากกลุ่มนั้นใส่อาเรย์ alluserid
              }
              //insert ลงตาราง has_message ด้วย userid และ messageid  และ path
              $pathmsg = findparentpath($eachgroupid,$con); //
              foreach ($alluserid as $eachuserid) {
                $sqlinserthasmessage = "INSERT INTO  `workingalert`.`has_message`
                            (`message_id` ,`user_id` ,`group_id`,`pathmsg`)
                            VALUES ('$msgid',  '$eachuserid',  '$eachgroupid', '$pathmsg');";
                if($con->query($sqlinserthasmessage)===TRUE){
                  //insert ลงตารางสำเร็จก็เก็บ user id ไว้ push
                  array_push($alluseridforpush,$eachuserid);
                }
              }
            }
          }

          //เข้าสู่กระบวนการสร้าง push noti เข้าเครื่อง
          //echo 'alluserid : '.json_encode($alluseridforpush);
          $stralluseridforpush = implode(",",$alluseridforpush); //แปลง userid ให้เป็น string เพื่อไปคิวรี่
          $queryallDeviceId = $con->query("SELECT distinct `device_id` FROM `user_deviceid` where `user_id` in ($stralluseridforpush)");
          if($queryallDeviceId->num_rows > 0){ //เชคว่า userid มีออกมาในระบบรึป่าว
            while($row = $queryallDeviceId->fetch_assoc()) {
        	       array_push($arrayOfDeviceId,$row['device_id']);
        	  }

            //หาชื่อของผู้ส่งจาก userid ของคนที่ส่ง
            $querysendername = $con->query("SELECT * FROM `user` WHERE `user`.`user_id` = '$userid';");
             if($querysendername->num_rows > 0){
                 $row = $querysendername->fetch_assoc();
                 $sendername = $row['firstname'].' '.$row['lastname'].'('.$row['nickname'].')';
             }else{
                 $sendername = 'undefind';
             }

             //เตรียมข้อมูลไว้ส่ง push
             $title = $msgpayload;
         	   $msg = $sendername;
             //ส่ง push ด้วยข้อมูลตามที่กำหนดไว้ข้างต้น เก็บค่าการส่งไว้ใน $msgstatus
         	   $msgstatus = sendPush($arrayOfDeviceId,$title,$msg);
          }else{
            $msgstatus = "no device to send push";
          }

          //update read กับ reach ของ user
          $sqlupdateread = "UPDATE  `workingalert`.`has_message` SET  `read_status` =  'y',
          `reach_status` =  'y' WHERE  `has_message`.`user_id` = '$userid'
          AND `has_message`.`message_id` = '$msgid';";
          if($con->query($sqlupdateread) === TRUE){
            $response = array("status"=>"success","description"=>"message create complete","push"=>$msgstatus);
          }
  }else{ //ใส่ข้อความลง db ไม่สำเร็จ
    $response = array("status"=>"failed","description"=>"insert messege to db failed");
  }
}else{ //sessionid ผิดพลาดหา user ไม่เจอ
  $response = array("status"=>"failed","description"=>"not found user_id");
}
echo json_encode($response);
?>
