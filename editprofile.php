<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $_POST['sessionid'];
    $email = $con->real_escape_string($_POST['email']);
    $firstname = $con->real_escape_string($_POST['firstname']);
    $lastname = $con->real_escape_string($_POST['lastname']);
    $mobileno = $con->real_escape_string($_POST['mobileno']);
    $nickname = $con->real_escape_string($_POST['nickname']);
    $type = $con->real_escape_string($_POST['type']);


//echo $sessionid.' '.$email.' '.$firstname.' '.$lastname.' '.$mobileno.' '.$nickname.' '.$type;

$response = array("status"=>"failed","description"=>"some problems");

$userid = finduserid($sessionid,$con,$type);
if($userid != 0){ //find userid
  if(isset($_FILES['user_image'])){ //ใช้ฟังก์ชั่น upload รูป
    $target_dir = "../picture/profile/";
    $datepic = date('m_d_Y_hisa', time());
    $filetype = $_FILES["user_image"]["type"];
        $ext = "";
        switch($filetype){
            case "image/jpeg" : $ext = ".jpg"; break;
            case "image/jpg" : $ext = ".jpg"; break;
            case "image/png" : $ext = ".png"; break;
        }
        $target_file1 = $datepic . "_" . $userid . $ext;
        $target_file2 = $target_dir . $target_file1;
        if ($ext == "") {
            //echo "  what?  ";
        } else {
            if (move_uploaded_file($_FILES["user_image"]["tmp_name"], $target_file2)) {
                //save image success
                $filename = "http://workingalert.tk/picture/profile/" . $target_file1;
                $sql = "UPDATE `workingalert`.`user` SET `email` = '$email' , `firstname` = '$firstname' ,`lastname` = '$lastname',`nickname`='$nickname',`phone`='$mobileno',`picture`='$filename'  WHERE `user`.`user_id` = '$userid';";
                if ($con->query($sql) === TRUE) {
                  $response = array("status"=>"success","description"=>"update complete");
                }else{
                  $response = array("status"=>"failed","description"=>"update not complete");
                }
            }
        }
  }else{ // บันทึกธรรมดาไม่ต้องใส่รูป
    $sql = "UPDATE `workingalert`.`user` SET `email` = '$email' , `firstname` = '$firstname' ,`lastname` = '$lastname',`nickname`='$nickname',`phone`='$mobileno'  WHERE `user`.`user_id` = '$userid';";
    if ($con->query($sql) === TRUE) {
      $response = array("status"=>"success","description"=>"missing pict parameters");
    }else{
      $response = array("status"=>"failed","description"=>"update not complete");
    }
  }
}else{
    //don't found id
    $response = array("status"=>"failed","description"=>"don't found user");
}
echo json_encode($response);
?>
