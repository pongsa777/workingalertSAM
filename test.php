<?php
include "dbconnect.php";

function findparentpath($groupid,$con){
    $path = "";
    $parent = 0;
    do{
        $queryparent = $con->query("SELECT `parent_id`,`group_name` FROM `group` WHERE `group_id` ='$groupid';");
        $parentdata = $queryparent->fetch_assoc();
        $parent = $parentdata['parent_id'];
        $groupid = $parent;
        $path = $parentdata['group_name'].' -> '.$path;
    }while($parent != 0);
    return substr($path,0,-3);
}

echo findparentpath(5,$con);
?>