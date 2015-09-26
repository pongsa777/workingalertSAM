<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);
    $type = $con->real_escape_string($_GET['type']);



$userid = finduserid($sessionid,$con,$type);
$response = array("status"=>"failed","description"=>"missing parameters");
if($userid != 0){
    $sqlcheck = "SELECT * FROM `has_user` WHERE `user_id` = '$userid' and `group_id` = '$groupid'";
    $checkdata = $con->query($sqlcheck);
    if($checkdata->num_rows > 0){
        $checkdatastatus = $checkdata->fetch_assoc();
        if($checkdatastatus['role_id'] == '0'){
          $response = array("status"=>"failed","description"=>"waiting admin for approve");
        }else if($checkdatastatus['role_id'] == '1'){
          $response = array("status"=>"failed","description"=>"you are admin in this group");
        }else {
          $response = array("status"=>"failed","description"=>"you are already in this group");
        }
    }else{
        $sqlcheckpass = "SELECT `password`,`approve` FROM `group` WHERE `group_id` = '$groupid'"; //เชคว่ากลุ่มที่จะเข้ามีล๊อกพาสรึป่าว
        $checkpassdata = $con->query($sqlcheckpass);
        if($checkpassdata->num_rows > 0){
          $row = $checkpassdata->fetch_assoc();
            if ($row["password"] != "") { //ถ้ามีพาสเวิร์ดส่งไปว่าต้องไปหน้ากรอกพาส
              $response = array("status"=>"password","description"=>"go to password page");
            }elseif ($row["approve"] != "" || $row["approve"] != NULL) { //เชคว่ากลุ่มมีการรอรับจาก admin รึป่าว
              $sql = "INSERT INTO `workingalert`.`has_user` (`user_id`, `group_id`, `role_id`) VALUES ('$userid', '$groupid', 0);";
              if($con->query($sql)=== true){
                $response = array("status"=>"success","description"=>"waiting admin for approve");
              }else {
                $response = array("status"=>"failed","description"=>"join something wrong");
              }
            }else{ //ถ้าไม่มีพาสก็จอยได้เลย
              $sql = "INSERT INTO `workingalert`.`has_user` (`user_id`, `group_id`, `role_id`) VALUES ('$userid', '$groupid', 2);";
              if($con->query($sql)=== true){
                  $response = array("status"=>"success","description"=>"join group successful");
              }else{
                  $response = array("status"=>"failed","description"=>"insert something worng");
              }
            }
        }
    }
}else{
    $response = array("status"=>"failed","description"=>"you can not join this group");
}
echo json_encode($response);
?>
