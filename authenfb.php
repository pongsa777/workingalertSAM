<?PHP
header('Content-Type: application/json');
include "dbconnect.php";

$fbid = $con->real_escape_string($_GET['fbid']);

function generateRandomString($length = 48) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if($fbid == ""){
    $response = array("status"=>"failed","sessionid"=>"");
}else{
    $response = array("status"=>"failed","sessionid"=>"");
    $queryUser = $con->query("SELECT * FROM `user` WHERE facebook_id = '$fbid';");
    if($queryUser->num_rows > 0){
        //found email in user table
        $userdata = $queryUser->fetch_assoc();
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
    }
}
echo json_encode($response);
        
        

?>