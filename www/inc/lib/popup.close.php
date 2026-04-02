<?

	$uid = $_REQUEST['uid'];

	if( $uid ) {
		$app_div_name = "event_popup_div_" . $uid;
		// 쿠키 적용
		SetCookie("AuthPopupClose_" . $uid , "Y" , time() +3600 * 24 , "/" , "." . str_replace("www." , "" , $_SERVER['HTTP_HOST']));
		echo "<SCRIPT>parent.document.getElementById('". $app_div_name ."').style.display = 'none';</SCRIPT>";
	}


?>