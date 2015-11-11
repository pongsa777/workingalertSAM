<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

function checkalreadyadd($con,$userid,$attrid){
  $sqlcheckalreadyadd = "SELECT * FROM `has_attribute` where `user_id` = '$userid' and `attr_id` = '$attrid'";
  $querycheckaladd = $con->query($sqlcheckalreadyadd);
  if($querycheckaladd->num_rows > 0){
    return 1;
  }else{
    return 0;
  }
}

$sessionid = $con->real_escape_string($_GET['sessionid']);
$type = $con->real_escape_string($_GET['type']);
$searchmsg = $con->real_escape_string($_GET['searchmsg']);

$result = array();
$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);
if($userid != 0){
  if($searchmsg != ""){
    $sqlsearch = "SELECT * FROM `attribute`
                  WHERE `attribute`.`attr_name` like '%$searchmsg%'
                  LIMIT 0 , 30";
    $queryresult = $con->query($sqlsearch);
    if($queryresult->num_rows > 0){
      while($row = $queryresult->fetch_assoc()){
        $eachattr = array(
                          "attr_id"=>$row["attr_id"],
                          "attr_name"=>$row["attr_name"],
                          "create_date"=>$row["create_date"],
                          "create_id"=>$row['create_id'],
                          "add_status"=> checkalreadyadd($con,$userid,$row["attr_id"])
                          );
        array_push($result,$eachattr);
      }
      $response = array("status"=>"success","description"=>"list of your group","result"=>$result);
    }else{
      $response = array("status"=>"success","description"=>"not found your attribute","result"=>$result);
    }
  }else{
    $response = array("status"=>"failed","description"=>"please input search message");
  }
}else{
  $response = array("status"=>"failed","description"=>"You don't hava permission to use this function");
}

echo json_encode($response);
?>
