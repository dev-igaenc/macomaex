<?php
include_once('./_common.php');

if($_POST['bo_table']){
	$sql = (" update {$g5['board_table']} set bo_1='{$_POST['bo_1']}', bo_2='{$_POST['bo_2']}' where bo_table='{$_POST['bo_table']}' ");

	sql_query($sql);

	//echo $sql;
}

goto_url('./board.php?bo_table='.$bo_table.'&page='.$page);
?>