<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (preg_match("/_en|\/en\//i", $_SERVER['REQUEST_URI'])){
	//echo "영";
	include_once(G5_PATH.'/head_en.php');
}else if (preg_match('/_cn|\/cn\//i', $_SERVER['REQUEST_URI'])){
	//echo "중";
	include_once(G5_PATH.'/head_cn.php');
}else if (preg_match('/_jp|\/jp\//i', $_SERVER['REQUEST_URI'])){
	//echo "일";
	include_once(G5_PATH.'/head_jp.php');
}else{
	//echo "국";
	include_once(G5_PATH.'/head.php');
}