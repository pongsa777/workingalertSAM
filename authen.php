<?PHP
//header('Content-Type: application/json');
include "dbconnect.php";

$email = $con->real_escape_string($_GET['email']);
$pass = $con->real_escape_string($_GET['pass']);
$type = $con->real_escape_string($_GET['type']);

function generateRandomString($length = 48) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

//echo $email.' '.$pass.' '.$type;
$response = array("status"=>"failed","sessionid"=>"");
$queryUser = $con->query("SELECT * FROM `user` WHERE email = '$email';");
if($queryUser->num_rows > 0){
    //found email in user table
    $userdata = $queryUser->fetch_assoc();
    if($pass == $userdata["password"]){
        $tokenid = generateRandomString();
        $response = array("status"=>"success","sessionid"=>$tokenid);
        $userid = $userdata["user_id"];
        $querytoken = $con->query("SELECT * FROM `session` WHERE `user_id` = '$userid';");
        if($queryUser->num_rows > 0){
        	//found old session
        	$con->query("DELETE FROM `workingalert`.`session` WHERE `user_id` = '$userid'");
        	$con->query("INSERT INTO `workingalert`.`session` (`user_id`, `session_id`) VALUES ('$userid', '$tokenid');");
        }else{
        	//first time login
        	$con->query("INSERT INTO `workingalert`.`session` (`user_id`, `session_id`) VALUES ('$userid', '$tokenid');");
        }
    }else{
        $response = array("status"=>"success","sessionid"=>"");
    }
}
echo json_encode($response);
        
        

?>