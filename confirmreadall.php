<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $messageid = $con->real_escape_string($_GET['messageid']);
    $type = $con->real_escape_string($_GET['type']);


$userid = finduserid($sessionid,$con,$type);

$response = array("status"=>"failed","description"=>"missing parameters");
if($userid != 0){ //หาsession

    $sqlcheck = "SELECT * FROM  `has_message`
    WHERE  `user_id` = '$userid'
    AND  `message_id` = '$messageid'";
    $checkdata = $con->query($sqlcheck);
    if($checkdata->num_rows > 0){
        //มี ข้อความนี้ที่ยังไม่ได้อ่านจริง
        //update
        $sql = "UPDATE  `workingalert`.`has_message` SET  `read_status` =  'y',
        `reach_status` =  'y' WHERE  `has_message`.`user_id` = '$userid' 
        AND `has_message`.`message_id` = '$messageid';";
        if($con->query($sql)===TRUE){
            $response = array("status"=>"success","description"=>"update read success");
        }else{
            $response = array("status"=>"failed","description"=>"update db error");
        }
    }else{
        //ไม่มีข้อความตามเงื่อนไขนี้อยู่ในระบบ
        $response = array("status"=>"failed","description"=>"not found message in system");
    }
}else{
    //หา session ไม่เจอ
    $response = array("status"=>"failed","description"=>"session error");
}

echo json_encode($response);
?>
