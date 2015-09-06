<?php
header('Content-Type: application/json');
include "dbconnect.php";
include "finduserid.php";

    $sessionid = $con->real_escape_string($_GET['sessionid']);
    $groupid = $con->real_escape_string($_GET['groupid']);

$msg = array();
$groupname = "";
$response = array("status"=>"failed","description"=>"some problems","message"=>$msg,"groupname"=>$groupname);
$userid = finduserid($sessionid,$con);
if($userid != 0){
    $querymsgid = $con->query("SELECT `message_id` FROM `has_message` 
                                WHERE `group_id`='$groupid' ORDER BY `message_id` DESC;");
    if($querymsgid->num_rows > 0){
        //found message id
        $querygroupname = $con->query("SELECT `group_name` FROM `group` WHERE `group_id` ='$groupid';");
        $groupnamedata = $querygroupname->fetch_assoc();
        $groupname = $groupnamedata["group_name"];
        $allmsgid = array();
        while($msgiddata = $querymsgid->fetch_assoc()){
            array_push($allmsgid,$msgiddata["message_id"]);
        }
        
        $allmsgidlength = count($allmsgid);
        for($i = 0; $i < $allmsgidlength; $i++){
            $querymsgdata = $con->query("SELECT * FROM `message`
                                        WHERE `message`.`message_id` = '$allmsgid[$i]';");
            if($querymsgdata->num_rows > 0){
                while($msgdata = $querymsgdata->fetch_assoc()){
                    $msgdetail = array("id"=>$msgdata["message_id"],"body"=>$msgdata["message_body"],
                                      "priority"=>$msgdata["priority"],"fromid"=>$msgdata["from_user_id"]);
                    array_push($msg,$msgdetail);
                }
            }
        }
        $response = array("status"=>"success","description"=>"all message","message"=>$msg,"groupname"=>$groupname);
    }else{
        //not found message id
        $response = array("status"=>"failed","description"=>"not message","group"=>$msg);
    }
    
    
}else{
    //not found user_id
    $response = array("status"=>"failed","description"=>"not found user_id","groupname"=>$groupname);
}

echo json_encode($response);

?>
