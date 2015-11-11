<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

$sessionid = $con->real_escape_string($_GET['sessionid']);
$type = $con->real_escape_string($_GET['type']);

$attribute = array();
$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);
if($userid != 0){//ถ้าเจอ userid
  $sqlgetgroup = "SELECT * FROM `has_attribute` left join `attribute`
                  ON `has_attribute`.`attr_id` = `attribute`.`attr_id`
                  WHERE `has_attribute`.`user_id` = '$userid'";
  $queryattr = $con->query($sqlgetgroup);
  if($queryattr->num_rows > 0){
    while($row = $queryattr->fetch_assoc()){
      $eachattr = array(
                        "attr_id"=>$row["attr_id"],
                        "attr_name"=>$row["attr_name"],
                        "add_date"=>$row["add_date"]
                        );
      array_push($attribute,$eachattr);
    }
    $response = array("status"=>"success","description"=>"list of your attribute","attribute"=>$attribute);
  }else{
    $response = array("status"=>"success","description"=>"you didn't have any attribute","attribute"=>$attribute);
  }
}else{
  $response = array("status"=>"failed","description"=>"You don't hava permission to use this function");
}

echo json_encode($response);
?>
