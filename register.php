<?PHP
header('Content-Type: application/json');
include "dbconnect.php";

    $email = $con->real_escape_string($_GET['email']);
  	$pass = $con->real_escape_string($_GET['pass']);
  	$repass = $con->real_escape_string($_GET['repass']);
  	$firstname = $con->real_escape_string($_GET['firstname']);
  	$lastname = $con->real_escape_string($_GET['lastname']);
  	$nickname = $con->real_escape_string($_GET['nickname']);
  	$phone = $con->real_escape_string($_GET['phone']);
    


$response = array("status"=>"failed","description"=>"");
if($pass == $repass){
    $queryUser = $con->query("SELECT * FROM `user` WHERE `email` = '$email';");
    if($queryUser->num_rows > 0){
       $response = array("status"=>"failed","description"=>"email has already registered");
    }else{
        $con->query("INSERT INTO `workingalert`.`user`
        (`email`, `password`, `firstname`, `lastname`, `nickname`, `phone`)
        VALUES ('$email','$pass','$firstname','$lastname','$nickname','$phone');");
        $response = array("status"=>"success","description"=>"account register successfully");
    }
}else{
    $response = array("status"=>"failed","description"=>"password mismatch");
}
echo json_encode($response);
?>
