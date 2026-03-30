<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

@sql_query(" ALTER TABLE `$write_table` MODIFY wr_9 text ");
@sql_query(" ALTER TABLE `$write_table` MODIFY wr_10 text ");

if(is_array($_POST['specNm'])){
	$wr_9 = $_POST['specNm'] = implode('^|^',$_POST['specNm']);
}

if(is_array($_POST['specCon'])){
	$wr_10 = $_POST['specCon'] = implode('^|^',$_POST['specCon']);
}

//echo "wr_9 : ".$wr_9."<br>";
//echo "wr_10 : ".$wr_10."<br>";
?>