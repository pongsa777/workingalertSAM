<?php
function findchildgroup($groupid,$con) {
    $allgroupid = array($groupid);
    $oldallgroupid = 0;
    while(count($allgroupid) > $oldallgroupid){
        $oldallgroupid = count($allgroupid);
        $strgroupid = implode(",",$allgroupid);
        $sql = "SELECT `group_id` FROM `group` WHERE `group`.`parent_id` in ($strgroupid);";
        //echo $sql;
        $querychild = $con->query($sql);
        if($querychild->num_rows > 0){
            while($row = $querychild->fetch_assoc()){
                array_push($allgroupid,$row['group_id']);
                $allgroupid = array_unique($allgroupid);
                
            }
        }
    }
    return $allgroupid;
}
?>