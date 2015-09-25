<?PHP
header('Content-Type: application/json');

include "dbconnect.php";
include "finduserid.php";

$sessionid = $con->real_escape_string($_GET['sessionid']);
$group_id = $con->real_escape_string($_GET['group_id']);
$type = $con->real_escape_string($_GET['type']);

$response = array("status"=>"failed","comment"=>"some problems");
$userid = finduserid($sessionid,$con,$type);



if($userid != 0){
	$con->query("DELETE FROM `group` WHERE `group_id` = '$group_id';");


		$response = array("status"=>"success","description"=>"delete success");


	}else{
		$response = array("status"=>"failed","description"=>"not have permission");
	}
echo json_encode($response);
?>
