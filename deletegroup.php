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

	//check ว่า groupid ของเรามี parent_id อะไร ถ้าไม่มีใส่เป็น 0
	$sqlcheckparentid = "SELECT `parent_id` FROM `group` WHERE `group_id` = '$groupid'";
	$queryparentid = $con->query($sqlcheckparentid);
	$row = $queryparentid->fetch_assoc();
	$parentid = $row['parent_id'];
	if($parentid != ""){
			//check ว่ามีกลุ่มไหนมี parent_id เป็น groupid ที่ส่งมาบ้าง
			$groupidtochange = array();
			$sqlgetgroup = "SELECT * FROM `group` WHERE `parent_id` = '$groupid'";
			$querycheck = $con->query($sqlgetgroup);
			if($querycheck -> num_rows > 0){
				while ($row2 = $querycheck->fetch_assoc()) {
					//เปลี่ยน parentid
					array_push($groupidtochange,$row2['group_id']);
				}
				//เปลี่ยน parentid ของกลุ่มนั้นให้เป็นตาม $parentid
				$str_groupidtochange = implode(",",$groupidtochange);
				$sqlchangeparent = "UPDATE `workingalert`.`group` SET `parent_id` = '$parentid' WHERE `group`.`group_id` in ($str_groupidtochange);";
				//echo $sqlchangeparent;
				if($con->query($sqlchangeparent)){
					//change success
					if($con->query("DELETE FROM `workingalert`.`group` WHERE `group`.`group_id` = '$groupid'")==true){
						$response = array("status"=>"success","description"=>"delete success");
					}else{
						$response = array("status"=>"failed","description"=>"delete row not complete");
					}
				}else{
					$response = array("status"=>"failed","description"=>"change child failed");
				}
			}else{
				//ถ้าไม่มีก็ลบกลุ่มได้เลย
				if($con->query("DELETE FROM `workingalert`.`group` WHERE `group`.`group_id` = '$groupid'")){
					$response = array("status"=>"success","description"=>"delete success");
				}else{
					$response = array("status"=>"failed","description"=>"delete row not complete");
				}
			}


	}else {
		$response = array("status"=>"failed","description"=>"not fount new parent id");
	}


}else{
	$response = array("status"=>"failed","description"=>"not have permission");
}
echo json_encode($response);
?>
