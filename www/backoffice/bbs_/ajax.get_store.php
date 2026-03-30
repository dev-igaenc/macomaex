<?php
include_once "./_common.php";
$wr_id=(int)$_POST['wr_id'];
$bo_table=$_POST['bo_table'];

$sql="select * from {$g5['write_prefix']}{$bo_table} where wr_id = {$_POST['wr_id']}";
$row=sql_fetch($sql);

//$sql="select * from {$g5['write_prefix']}store where wr_id={$wr_id}";
//$row=sql_fetch($sql);
$return=array();
if(!empty($row)){
	$return['wr_subject']=$row['wr_subject'];
	$return['wr_content']=$row['wr_content'];
	$return['area']=$row['wr_6']." {$row['wr_7']}";
	$return['addr']=$row['wr_4'];
	$return['tel']=$row['wr_5'];
	$return['open']=$row['wr_3'];
	$icon = explode(",", $row['wr_2']);
	$return['parking']=in_array("주차",$icon)?"주차가능":"주차불가";
	$files=get_file($bo_table,$row['wr_id']);
	for($i=0;$i<$files['count'];$i++){
		if($files[$i]['view']){
			$return['img'][]=$files[$i]['path']."/".$files[$i]['file'];
		}
	}
	$return['wr_8']=$row['wr_8'];
	$return['wr_9']=$row['wr_9'];
	$return['data']=1;
}else{
	$return['data']=0;
}
echo json_encode($return);
exit;