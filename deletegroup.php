<?PHP
header('Content-Type: application/json');

include "dbconnect.php";
include "finduserid.php";

$sessionid = $con->real_escape_string($_GET['sessionid']);
$groupid = $con->real_escape_string($_GET['groupid']);
$type = $con->real_escape_string($_GET['type']);

$response = array("status"=>"failed","description"=>"some problems");
$userid = finduserid($sessionid,$con,$type);



if($userid != 0){
	if($con->query("DELETE FROM `workingalert`.`group` WHERE `group`.`group_id` = '$groupid'")==true){
		$response = array("status"=>"success","description"=>"delete success");
	}else{
		$response = array("status"=>"failed","description"=>"delete row not complete");
	}
}else{
	$response = array("status"=>"failed","description"=>"not have permission");
}
echo json_encode($response);
?>
