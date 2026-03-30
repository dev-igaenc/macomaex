<?

	if( !$_SESSION["no_usr_id"]){
		alert("로그인이 필요합니다.", $NO_IS_SUBDIR."/pages/userprocess/signIn.php");
		exit;
	}

?>