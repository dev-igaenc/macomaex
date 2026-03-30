<?php
if (!function_exists('macomaex_load_env')) {
	function macomaex_load_env($env_path)
	{
		static $loaded_paths = array();

		if (isset($loaded_paths[$env_path])) {
			return;
		}

		$loaded_paths[$env_path] = true;

		if (!is_file($env_path) || !is_readable($env_path)) {
			return;
		}

		$scanner = defined('INI_SCANNER_RAW') ? INI_SCANNER_RAW : INI_SCANNER_NORMAL;
		$values = @parse_ini_file($env_path, false, $scanner);

		if (!is_array($values)) {
			return;
		}

		foreach ($values as $name => $value) {
			if (getenv($name) !== false) {
				continue;
			}

			$_ENV[$name] = $value;
			$_SERVER[$name] = $value;
			@putenv($name . '=' . $value);
		}
	}

	function macomaex_env($name, $default = '')
	{
		$value = getenv($name);
		if ($value !== false) {
			return $value;
		}

		if (isset($_ENV[$name])) {
			return $_ENV[$name];
		}

		if (isset($_SERVER[$name])) {
			return $_SERVER[$name];
		}

		return $default;
	}
}

macomaex_load_env(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/.env');


session_start();

if(!function_exists('samesiteCookie')) {
	function samesiteCookie($name = '', $value =''  , $expires = '' , $path = '', $domain = '', $secure = false, $httponly = false)
	{   
		if($name != ''){
			// 기본 쿠키 방식
			setcookie($name, $value , $expires , $path, $domain, $secure , $httponly);

			// 추가 쿠키 방식 PHP VER >= 7.2
			/*
				$options = array(
					'expires' => $expires,
					'path' => $path,
					'domain' => $domain,
					'secure' => $secure,     // or false
					'httponly' => $httponly,    // or false
					'samesite' => 'None' // None || Lax  || Strict                  
				);
				setcookie($name, $value, $options);
			*/
		}
		
		$res = @session_start();
		// IE 브라우저 또는 엣지브라우저 일때는 secure; SameSite=None 을 설정하지 않습니다.
		if( preg_match('/Edge/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']) || preg_match('~Trident/7.0(; Touch)?; rv:11.0~',$_SERVER['HTTP_USER_AGENT']) ){
			return $res;
		}
		
		$httpsChk = false;
		if(isset($_SERVER['HTTPS'])) {
			if(strtolower($_SERVER['HTTPS']) == 'on') $httpsChk = true;
			else if($_SERVER['HTTPS'] == '1') $httpsChk = true;
		}

		if($httpsChk !== true){ return $res; }
		$headers = headers_list();
		krsort($headers);

		foreach ($headers as $header) {
			//if (!preg_match('~^Set-Cookie: PHPSESSID=~', $header)) continue;
			if (preg_match('~^Set-Cookie: PHPSESSID=~', $header)) {  $_SESSION['SESS_SAME_SITE_COOKIE'] = true;  }
			if (preg_match('/(SameSite=None)/', $header)) continue;
			if (!preg_match('~^Set-Cookie:~', $header)) continue;
			if (preg_match('/=deleted;/i', $header)) continue;
			$header = preg_replace('~; secure(; HttpOnly)?$~', '', $header) . '; secure; SameSite=None';
			header($header, false);
			//break;
		}
		
		// 세션쿠키에 same site 적용이 안됬을 경우 다시한번 생성해 준다.
		if( $_SESSION['SESS_SAME_SITE_COOKIE'] !== true){
			$res = session_regenerate_id();
			samesiteCookie();
		}

		return $res;
	}
}
samesiteCookie(); // PHPSESSID 을 위한 처리


/*
function session_start_samesite($options = array()){
  $res = @session_start($options);
  $headers = headers_list();
  foreach ($headers as $header) {
    if (!preg_match('~^Set-Cookie: PHPSESSID=~', $header)) continue;
    $header = preg_replace('~; secure(; HttpOnly)?$~', '', $header) . '; secure; SameSite=None';
    header($header, false);
  }
  return $res;
}

session_start_samesite();
*/

session_cache_limiter("private"); 
ini_set("session.cookie_lifetime", 86400); 
ini_set("session.cache_expire", 86400); 
ini_set("session.gc_maxlifetime", 86400);

header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

$NO_SITE_UNIQUE_KEY = "MCMX01";
$NO_CURRENT_URL = $_SERVER['HTTP_HOST'];
$NO_MAKE_KEY = hash("sha256", $NO_CURRENT_URL.$NO_SITE_UNIQUE_KEY);

if(!isset($_SERVER["HTTPS"])) {
//header('Location: https://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);
}

/* uploads 폴더 하위 포함 쓰기 권한 설정 해야 함 */

//error_reporting( E_ALL & ~( E_NOTICE | E_USER_NOTICE | E_WARNING | E_COMPILE_WARNING | E_CORE_WARNING | E_USER_WARNING | E_DEPRECATED | E_USER_DEPRECATED ) );

// 현재 파일명 확인
$EX_FILENAME = explode("/" , $_SERVER['SCRIPT_FILENAME']);
$CURR_FILENAME = $EX_FILENAME[(sizeof($EX_FILENAME)-1)]; // 현재파일명
$END_FILENAME = end(explode('/', dirname($_SERVER['SCRIPT_NAME']))); // 현재 최하위 DIR명

$PHP_SELF = $_SERVER['PHP_SELF'];

# 서버에 지정된 업로드 최대용량 확인
$MAX_SERVER_UPLOAD_SIZE = (ini_get('upload_max_filesize')?ini_get('upload_max_filesize'):'서버설정용량');


/* 기본 설정 값 */
$BOARD_DEFAULT_LIST_SIZE = 20;
$BOARD_DEFULAT_BLOCK_SIZE = 10; //페이지 카운트

/* 경로 설정 */

/* 서브 경로 없는 경우 아래 내용 비움 */
$NO_IS_SUBDIR = "";

$UPLOAD_DIR_BASE						= $_SERVER['DOCUMENT_ROOT'] . $NO_IS_SUBDIR.  "/uploads";
$UPLOAD_WDIR_BASE						= $NO_IS_SUBDIR. "/uploads";

$UPLOAD_DIR_BANNER					= $UPLOAD_DIR_BASE."/banner";
$UPLOAD_WDIR_BANNER					= $UPLOAD_WDIR_BASE."/banner";

$UPLOAD_DIR_POPUP						= $UPLOAD_DIR_BASE."/popup";
$UPLOAD_WDIR_POPUP					= $UPLOAD_WDIR_BASE."/popup";

$UPLOAD_DIR_BOARD						= $UPLOAD_DIR_BASE."/board";
$UPLOAD_WDIR_BOARD					= $UPLOAD_WDIR_BASE."/board";

$UPLOAD_SITEINFO_DIR_LOGO		= $UPLOAD_DIR_BASE."/logo";
$UPLOAD_SITEINFO_WDIR_LOGO		= $UPLOAD_WDIR_BASE."/logo";

$UPLOAD_META_DIR						= $UPLOAD_DIR_BASE."/meta";
$UPLOAD_META_WDIR						= $UPLOAD_WDIR_BASE."/meta";	

$UPLOAD_MATCHING_DIR						= $UPLOAD_DIR_BASE."/matching";
$UPLOAD_MATCHING_WDIR						= $UPLOAD_WDIR_BASE."/matching";	



/* 관리자 메뉴 권한 */
$NO_ADMIN_GNB_BOARD_OPEN = true; /*게시판*/
$NO_ADMIN_GNB_DESIGN_OPEN = true; /*디자인*/
$NO_ADMIN_GNB_REQUEST_OPEN = true; /*상담문의*/
$NO_ADMIN_GNB_SMS_OPEN = false; /*문자관리*/
$NO_ADMIN_GNB_SETTING_OPEN = true; /*설정*/
$NO_ADMIN_GNB_LOG_OPEN = true; /*접속통계*/
$NO_ADMIN_GNB_MEMBER_OPEN = false;
$NO_ADMIN_LNB_BOARD_MENU_OPEN = false; /*게시판 관리 */
$NO_ADMIN_LNB_BOARD_MENU_ROLE_OPEN = false; /*게시판 권한 관리 */


$devIArrIP = array("220.72.73.182", "125.128.228.224","1.228.9.177");

if(in_array($_SERVER[REMOTE_ADDR], $devIArrIP)){
	$NO_ADMIN_LNB_BOARD_MENU_OPEN = true;
	$NO_ADMIN_LNB_BOARD_MENU_ROLE_OPEN = true;
	$NO_ADMIN_GNB_MEMBER_OPEN = true;
}


/*사용되는 테이블
nb_admin
nb_siteinfo

nb_board
nb_board_manage
nb_board_lev_manage

nb_banner
nb_popup
nb_request

nb_counter
nb_counter_config
nb_counter_data
nb_counter_route


nb_member
nb_member_level
*/

$NO_SMS_USERID = macomaex_env('NO_SMS_USERID', '');
$NO_SMS_APIKEY = macomaex_env('NO_SMS_APIKEY', '');
$NO_KAKAO_SENDERKEY = macomaex_env('NO_KAKAO_SENDERKEY', '');
$NO_SMS_CALLBACK = macomaex_env('NO_SMS_CALLBACK', '');

/* 사용자 세션 변수 */
$NO_USR_NO			= $_SESSION['no_usr_no'];
$NO_USR_ID				= $_SESSION['no_usr_id'];
$NO_USR_NAME		= $_SESSION['no_usr_name'];
$NO_USR_LEV			= $_SESSION['no_usr_lev'];

/* 비로그인 상태면 비회원 코드값 0으로 부여 */
if(!$NO_USR_LEV)
	$NO_USR_LEV = 0;

if(!$NO_USR_NO)
	$NO_USR_NO = 0;

/* 관리자 세션 변수 */
$NO_ADM_NO			= $_SESSION['no_adm_login_no'];
$NO_ADM_ID			= $_SESSION['no_adm_login_uid'];
$NO_ADM_NAME		= $_SESSION['no_adm_login_uname'];


$_path_str = dirname(__FILE__);

include_once $_path_str.'/PasswordHashClass.php';
include_once $_path_str.'/db.php';
include_once $_path_str.'/func.php';
include_once $_path_str.'/board.class.php';
include_once $_path_str.'/matching.class.php';
include_once $_path_str.'/payment.class.php';
include_once $_path_str.'/injectionguard.php';
include_once $_path_str.'/cache.inc.php';
include_once $_path_str.'/site.info.php';
include_once $_path_str.'/var.php';
include_once $_path_str.'/license.php';

if( strpos(dirname($_SERVER['SCRIPT_NAME']), "/admin/") !== false){
}else{
	include_once $_path_str.'/counter.main.php';
}

if(!in_array($NO_MAKE_KEY, $NO_LICENSE_UQ_KEY)){
	//echo "허용되지 않는 라이센스입니다.";exit;
}


?>
