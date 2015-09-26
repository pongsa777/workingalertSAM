<?PHP
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

$groupid = $con->real_escape_string($_GET['groupid']);
$pass = $con->real_escape_string($_GET['pass']);
$sessionid = $con->real_escape_string($_GET['sessionid']);
$type = $con->real_escape_string($_GET['type']);

$userid = finduserid($sessionid,$con,$type);
$response = array("status"=>"failed","description"=>"missing parameters");
if($userid != 0){ //หา user id
  $sqlcheckpass = "SELECT `password`,`approve` FROM `group` WHERE `group_id` = '$groupid'";
  $checkpassdata = $con->query($sqlcheckpass);
  if($checkpassdata->num_rows > 0){
    $row = $checkpassdata->fetch_assoc();
    if ($row["password"] == $pass){ // ใส่พาสถูกต้อง
      if ($row["approve"] != "" || $row["approve"] != NULL) { //  เช็คเป็นกลุ่มที่ต้องรอ admin กดรับ
        $sql = "INSERT INTO `workingalert`.`has_user` (`user_id`, `group_id`, `role_id`) VALUES ('$userid', '$groupid', 0);";
        if($con->query($sql)=== true){
          $response = array("status"=>"success","description"=>"waiting admin for approve");
        }else {
          $response = array("status"=>"failed","description"=>"join something wrong");
        }
      }else{ //ใส่พาสถูกเข้าได้เลย
        $sql = "INSERT INTO `workingalert`.`has_user` (`user_id`, `group_id`, `role_id`) VALUES ('$userid', '$groupid', 2);";
        if($con->query($sql)=== true){
            $response = array("status"=>"success","description"=>"join group successful");
        }else{
            $response = array("status"=>"failed","description"=>"insert something worng");
        }
      }
    }else{ //ใส่พาสผิด
      $response = array("status"=>"failed","description"=>"Your password is wrong");
    }
  }
}else{ // ไม่เจอ user ในระบบ
  $response = array("status"=>"failed","description"=>"you can not join this group");
}
echo json_encode($response);
?>
