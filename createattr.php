<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

//start recieve parameter
$sessionid = $con->real_escape_string($_GET['sessionid']);
$type = $con->real_escape_string($_GET['type']);
$attrname = $con->real_escape_string($_GET['attrname']);

$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);
if($userid != 0){//ถ้าเจอ userid
  if($attrname != ""){//check ว่าได้ตั้งชื่อมารึป่าว
    //หาว่ามีชื่อซ้ำอยู่รึป่าว
    $sqlfinddup = "SELECT * FROM `attribute` WHERE `attribute`.`attr_name` = '$attrname'";
    $queryFind = $con->query($sqlfinddup);
    if($queryFind->num_rows > 0){  //ถ้ามีแล้วให้แจ้ง error ไป
      $response = array("status"=>"failed","description"=>"this attribute already exist");
    }else{  //ถ้าไม่มีให้ครีเอท
      $sqlcreate = "INSERT INTO  `workingalert`.`attribute` (  `attr_name` ,
                                                              `create_date` ,
                                                              `create_id`)
                                                    VALUES (   '$attrname',
                                                              CURRENT_TIMESTAMP ,
                                                              '$userid' );";
      if($con->query($sqlcreate)){
        $response = array("status"=>"success","description"=>"create attribute success");
      }
    }
  }else{
    $response = array("status"=>"failed","description"=>"please insert name of attribute");
  }
}else {
  $response = array("status"=>"failed","description"=>"You don't hava permission to use this function");
}

echo json_encode($response);
?>
