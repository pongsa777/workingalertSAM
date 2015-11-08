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


function groupisreal($con,$groupid){
  $sqlccheckgroup = "SELECT * FROM `group` WHERE `group_id` = '$groupid';";
  echo $sqlccheckgroup;
  $querycheckgroup = $con->query($sqlccheckgroup);
  if($querycheckgroup ->num_rows > 0 ){
    while ($row = $querycheckgroup->fetch_assoc()) {
      if($row['permission'] == "" || $row['permission'] == null){
        return 1;
      }else{
        return $row['permission'];
      }
    }
  }else {
    return -1;
  }
}

function findgroupname($con,$groupid){
  $sqlfindgroupname = "SELECT * FROM `group` WHERE `group_id` = '$groupid';";
  $queryfindgroupname = $con->query($sqlfindgroupname);
  if($queryfindgroupname ->num_rows > 0 ){
    $row = $queryfindgroupname->fetch_assoc();
    $groupname = $row['group_name'];
    return $groupname;
  }else{
    return "";
  }
}

function createmessage($msgpayload,$priority,$userid,$con,$groupid){
  $identity = generateRandomString();
  $c_date = date("Y-m-d");
  $c_time = date("h:i:sa");
  $groupname = findgroupname($con,$groupid);
  $sql = "INSERT INTO  `workingalert`.`message`
          (`message_body` ,`priority` ,`from_user_id` ,`identity` ,`create_date` ,`create_time`, `to_groupid`, `to_groupname`)
          VALUES ('$msgpayload',  '$priority',  '$userid', '$identity', '$c_date', '$c_time', '$groupid', '$groupname');";
  if($con->query($sql)===TRUE){
          $querymsgid = $con->query("SELECT `message_id` FROM `workingalert`.`message`
                              WHERE `from_user_id` = '$userid'
                              AND `identity` = '$identity';");
          $msgiddata = $querymsgid->fetch_assoc();
          $msgid = $msgiddata["message_id"];
          return $msgid;
  }else{
    return 0;
  }
}


function isAdmin($groupid,$con,$userid){
  $sqlcheckisadmin = "SELECT * FROM `has_user` WHERE `has_user`.`group_id` = '$groupid' AND `has_user`.`role_id` = '1' AND `has_user`.`user_id` = '$userid'";
  $querycheckisadmin = $con->query($sqlcheckisadmin);
  if($querycheckisadmin->num_rows>0){
    return true;
  }else{
    return false;
  }
}


function prepareAndPush($con,$alluseridforpush,$userid,$msgpayload){
  //print_r($alluseridforpush);
  $arrayOfDeviceId = array();
  $stralluseridforpush = implode(",",$alluseridforpush); //แปลง userid ให้เป็น string เพื่อไปคิวรี่
  //echo $stralluseridforpush;
  $queryallDeviceId = $con->query("SELECT distinct `device_id` FROM `user_deviceid` where `user_id` in ($stralluseridforpush)");
  if($queryallDeviceId->num_rows > 0){ //เชคว่า userid มีออกมาในระบบรึป่าว
    while($row = $queryallDeviceId->fetch_assoc()) {
        //echo $row['device_id'];
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
  return $msgstatus;
}


function findAllDestinationUserId($con,$groupid,$msgid){
  $result = array();
  $allgroupid = findallchild($groupid,$con);

  $tempuserid = array();
  foreach ($allgroupid as $eachgroupid) { //loop เอาค่าแต่ละตัวในอาเรย์ allgroupid
    $sqlgetalluserid = "SELECT `has_user`.`user_id` FROM `has_user`
                        WHERE `has_user`.`group_id` = '$eachgroupid'
                        AND `has_user`.`role_id` != '0'";
    $queryallId = $con->query($sqlgetalluserid);
    if($queryallId->num_rows > 0){
      //ใส่ userid ทั้งหมดลงใน $tempuserid
      while ($roo = $queryallId->fetch_assoc()) {
        array_push($tempuserid,$roo['user_id']);
      }

      $pathmsg = findparentpath($eachgroupid,$con);
      foreach ($tempuserid as $eachuserid) {
        $sqlinserthasmessage = "INSERT INTO  `workingalert`.`has_message`
                    (`message_id` ,`user_id` ,`group_id`,`pathmsg`)
                    VALUES ('$msgid',  '$eachuserid',  '$eachgroupid', '$pathmsg');";
        if($con->query($sqlinserthasmessage)===TRUE){
          //insert ลงตารางสำเร็จก็เก็บ user id ไว้ push
          array_push($result,$eachuserid);
        }
      }
    }
  }
  return $result;
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
  if($groupid != "" && $msgpayload != "" && $priority != ""){ //validate parameters
    //check ว่ากลุ่มมีอยู่จริง
    if(groupisreal($con,$groupid) == -1){
      $response = array("status"=>"failed","description"=>"Don't have this group in database");
    }else if(groupisreal($con,$groupid) == 1){ //everyone can send message
      $msgid = createmessage($msgpayload,$priority,$userid,$con,$groupid);
      if($msgid == 0){
        $response = array("status"=>"failed","description"=>"insert messege to db failed");
      }else{
        //หาคนที่จะถูกส่งไปถึงทั้งหมด
        $alluserid = findAllDestinationUserId($con,$groupid,$msgid);
        //ไปส่ง push
        $pushresult = prepareAndPush($con,$alluserid,$userid,$msgpayload);
        //update reach+read ของคนส่ง
        $sqlupdateread = "UPDATE  `workingalert`.`has_message` SET  `read_status` =  'y',
        `reach_status` =  'y' WHERE  `has_message`.`user_id` = '$userid'
        AND `has_message`.`message_id` = '$msgid';";
        if($con->query($sqlupdateread) === TRUE){
          $response = array("status"=>"success","description"=>"message create complete","push"=>$pushresult);
        }
      }
    }else if(groupisreal($con,$groupid) == 2){ //only admin can send message
      if(isAdmin($groupid,$con,$userid)){
        $msgid = createmessage($msgpayload,$priority,$userid,$con,$groupid);
        if($msgid == 0){
          $response = array("status"=>"failed","description"=>"insert messege to db failed");
        }else{
          //หาคนที่จะถูกส่งไปถึงทั้งหมด
          $alluserid = findAllDestinationUserId($con,$groupid,$msgid);
          //ไปส่ง push
          $pushresult = prepareAndPush($con,$alluserid,$userid,$msgpayload);
          //update reach+read ของคนส่ง
          $sqlupdateread = "UPDATE  `workingalert`.`has_message` SET  `read_status` =  'y',
          `reach_status` =  'y' WHERE  `has_message`.`user_id` = '$userid'
          AND `has_message`.`message_id` = '$msgid';";
          if($con->query($sqlupdateread) === TRUE){
            $response = array("status"=>"success","description"=>"message create complete","push"=>$pushresult);
          }
        }
      }else{
        $response = array("status"=>"failed","description"=>"invalid group permission you are not admin of this group");
      }
    }else{
      $response = array("status"=>"failed","description"=>"permission failed");
    }
  }
}else{ //sessionid ผิดพลาดหา user ไม่เจอ
  $response = array("status"=>"failed","description"=>"not found user_id");
}
echo json_encode($response);
?>
