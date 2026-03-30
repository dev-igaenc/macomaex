<?php

	$enable_all_accept = $_REQUEST['enable_all_accept'];
	$enable_marketing	= $_REQUEST['enable_marketing'];
	$enable_functional	= $_REQUEST['enable_functional'];
	$enable_analytics	= $_REQUEST['enable_analytics'];


	if($enable_all_accept == "enable"){
	
		setcookie("cookie_setting_marketing" , "enable" , time() +3600 * 24 * 30 , "/" , "." . str_replace("www." , "" , $_SERVER[HTTP_HOST]));
		setcookie("cookie_setting_functional" , "enable" , time() +3600 * 24 * 30 , "/" , "." . str_replace("www." , "" , $_SERVER[HTTP_HOST]));
		setcookie("cookie_setting_analytics" , "enable"  , time() +3600 * 24 * 30 , "/" , "." . str_replace("www." , "" , $_SERVER[HTTP_HOST]));
	
	}else{
		
		setcookie("cookie_setting_marketing" , $enable_marketing , time() +3600 * 24 * 30 , "/" , "." . str_replace("www." , "" , $_SERVER[HTTP_HOST]));
		setcookie("cookie_setting_functional" , $enable_functional , time() +3600 * 24 * 30 , "/" , "." . str_replace("www." , "" , $_SERVER[HTTP_HOST]));
		setcookie("cookie_setting_analytics" , $enable_analytics  , time() +3600 * 24 * 30 , "/" , "." . str_replace("www." , "" , $_SERVER[HTTP_HOST]));
	
	}

	
	echo "<SCRIPT>parent.location.reload();</SCRIPT>";


?>