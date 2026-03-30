<?

	$db_host = macomaex_env("LEGACY_DB_HOST", macomaex_env("G5_DB_HOST", "localhost"));
	$db_name = macomaex_env("LEGACY_DB_NAME", macomaex_env("G5_DB_NAME", ""));
	$db_uid = macomaex_env("LEGACY_DB_USER", macomaex_env("G5_DB_USER", ""));
	$db_passwd = macomaex_env("LEGACY_DB_PASSWORD", macomaex_env("G5_DB_PASSWORD", ""));

	 $connect = mysqli_connect($db_host, $db_uid, $db_passwd, $db_name) or die("MySQL Server 연결에 실패했습니다");

	 mysqli_select_db($connect,$db_name);

	mysqli_query($connect, "set names utf8");
?>
