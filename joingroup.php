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
        $response = array("status"=>"failed","description"=>"you are already in this group");
    }else{
        $sqlcheckpass = "SELECT `password` FROM `group` WHERE `group_id` = '$groupid'"; //เชคว่ากลุ่มที่จะเข้ามีล๊อกพาสรึป่าว
        $checkpassdata = $con->query($sqlcheckpass);
        if($checkpassdata->num_rows > 0){
          while($row = $checkpassdata->fetch_assoc()){
            if($row["password"] != ""){ //ถ้ามีพาสเวิร์ดส่งไปว่าต้องไปหน้ากรอกพาส
              $response = array("status"=>"password","description"=>"go to password page");
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
    }
}else{
    $response = array("status"=>"failed","description"=>"you can not join this group");
}
echo json_encode($response);
?>
