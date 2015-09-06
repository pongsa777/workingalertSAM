<?PHP
header('Content-Type: application/json');
include_once "connection.php";

$email = $mysqli->real_escape_string($_GET['email']);


$result = $mysqli->query("SELECT * FROM `user` WHERE email = '". $email ."'");
$nums = $result->num_rows;
	if($nums === 1){
		while ($row = $result->fetch_object()){
			$data[] = $row;
		}
		$session_id = session_id();
		$user_id = $data[0]->user_id;
		//$user_id = $data[0]['user_id']; // สำรอง
		$response = array("status"=>"success");
	}elseif($nums ===0){
		$response = array("status"=>"failed");
	}else{
		$response = array("status"=>"failed");
	}
echo json_encode($response);
?>