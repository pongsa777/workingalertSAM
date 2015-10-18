<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

$sessionid = $con->real_escape_string($_GET['sessionid']);
$type = $con->real_escape_string($_GET['type']);
$searchmsg = $con->real_escape_string($_GET['searchmsg']);

$result = array();
$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);
if($userid != 0){
  if($searchmsg != ""){
    $sqlsearch = "SELECT * FROM `user`
                  WHERE `firstname` like '$searchmsg%'
                  OR `lastname` like '$searchmsg%'
                  OR `nickname` like '$searchmsg%'
                  OR `phone` like '$searchmsg%'
                  OR `email` like '$searchmsg%'
                  LIMIT 0,50";
    $queryresult = $con->query($sqlsearch);
    if($queryresult->num_rows > 0){
      while($row = $queryresult->fetch_assoc()){
        $eachattr = array(
                          "id"=>$row["user_id"],
                          "facebookid"=>$row["facebook_id"],
                          "email"=>$row["email"],
                          "firstname"=>$row['firstname'],
                          "lastname"=>$row['lastname'],
                          "nickname"=>$row['nickname'],
                          "phone"=>$row['phone'],
                          "picture"=>$row['picture']
                          );
        array_push($result,$eachattr);
      }
      $response = array("status"=>"success","description"=>"list of member","result"=>$result);
    }else{
      $response = array("status"=>"success","description"=>"not found your member","result"=>$result);
    }
  }else{
    $response = array("status"=>"failed","description"=>"please input search message");
  }
}else{
  $response = array("status"=>"failed","description"=>"You don't hava permission to use this function");
}

echo json_encode($response);
?>
