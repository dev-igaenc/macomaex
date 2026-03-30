<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

//연락처
	$wr_9 = $phone1."-".$phone2."-".$phone3;


if(is_array($_POST['wr_email'])){
	$_POST['wr_email']  = implode('@',$_POST['wr_email']);
}
if ($_POST['wr_email'] == "@") $_POST['wr_email'] = "";


// 주소
if( $mb_zip ){
	@sql_query(" ALTER TABLE `$write_table` ADD `mb_zip` varchar(20) NULL ");
	@sql_query(" ALTER TABLE `$write_table` ADD `mb_addr1` varchar(120) NULL ");
	@sql_query(" ALTER TABLE `$write_table` ADD `mb_addr2` varchar(120) NULL ");
}

$add_sql = "";
$add_sql .= ", mb_zip = '$mb_zip' ";
$add_sql .= ", mb_addr1 = '$mb_addr1' ";
$add_sql .= ", mb_addr2 = '$mb_addr2' ";

/*
if(is_array($_POST['wr_5'])){
	$wr_5=$_POST['wr_5'] = implode('-',$_POST['wr_5']);
}

if(is_array($_POST['wr_6'])){
	$wr_6=$_POST['wr_6'] = implode('-',$_POST['wr_6']);
}
*/
?>