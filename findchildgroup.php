<?php
function findchildgroup($groupid,$con) {
        $str = implode(",",$groupid);
        $allgroupid = array();
        $sql = "SELECT `group_id` FROM `group` WHERE `group`.`parent_id` in ($str);";
        $querychild = $con->query($sql);
        if($querychild->num_rows > 0){
            while($row = $querychild->fetch_assoc()){
                array_push($allgroupid,$row['group_id']+0);
            }
        }
    return $allgroupid;
}
function findallchild($id,$con){
  $new = array($id+0);
  $old = array();
  while(count($new) > count($old)){
    $old = $new;

    $new = findchildgroup($old,$con);

    $new = array_unique(array_merge($old,$new));
  }
  return $new;
}

//echo json_encode(findallchild((1),$con));
?>
