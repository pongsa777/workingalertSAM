<?php
function finduserid($token,$con,$type){
    $userid = 0;
    $queryUser = $con->query("SELECT * FROM `session` WHERE `session_id` = '$token' AND `type` = '$type';");
    if($queryUser->num_rows > 0){
        //found user_id
        $userdata = $queryUser->fetch_assoc();
        $userid = $userdata["user_id"];
        return $userid;
    }
    return $userid;
}
?>
