<?PHP
@session_start();
include_once "connection.php";

if (get_magic_quotes_gpc()) {
    $email = stripslashes($_GET['email']);
	$pass = stripslashes($_GET['pass']);
}
else {
    $email = addslashes(trim($_GET['email']));
	$pass = addslashes(trim($_GET['pass']));
}
$result = $mysqli->query("SELECT * FROM `user` WHERE email = '". $email ."' AND password = '". $pass ."';");
$nums = $result->num_rows;
	if($nums === 1){
		while ($row = $result->fetch_object()){
			$data[] = $row;
		}
		
		$session_id = session_id();
		$user_id = $data[0]->user_id; 
		echo "<script>('user_id = ". $user_id ."')</script>"; 
		echo "<script>('json_encode = ". json_encode($data) ."')</script>"; 
		echo "<script>('session_id = ". $session_id ."')</script>"; 
		

		if($mysqli->query("INSERT INTO `session` (`no`, `user_id`, `session_id`) VALUES (NULL, '". $user_id ."', '". $session_id ."');")){
			echo "{\"status\":\"success\",\"sessionid\":\""$session_id"\"}";
		}else{
			echo "{\"status\":\"failed\"}";
		}
	}elseif($nums ===0){
	echo "{\"status\":\"failed\"}";
	}else{
		echo "{\"status\":\"failed\"}";
	}

?>