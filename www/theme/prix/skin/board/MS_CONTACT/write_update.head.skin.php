<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

//연락처
if(is_array($_POST['w_10'])){
	$_POST['wr_10'] = implode('-',$_POST['w_10']);
}

if(is_array($_POST['wr_email'])){
	$_POST['wr_email']  = implode('@',$_POST['wr_email']);
}
if ($_POST['wr_email'] == "@") $_POST['wr_email'] = "";

?>