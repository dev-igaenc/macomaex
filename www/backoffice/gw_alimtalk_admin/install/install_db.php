<?php
@set_time_limit(0);
$gmnow = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
@header('Content-Type: text/html; charset=utf-8');
@header('X-Robots-Tag: noindex');

include_once ('./_common.php');

$title = "카카오 알림톡 설치 완료 3/3";
include_once ('./install.inc.php');
?>

<div class="ins_inner">
    <h2>카카오 알림톡 설치가 시작되었습니다.</h2>

    <ol>
<?php
// 테이블 생성 ------------------------------------
$file = implode('', file('./nc_kakao_alimtalk.sql'));
eval("\$file = \"$file\";");

$file = preg_replace('/^--.*$/m', '', $file);
$file = preg_replace('/`g5_([^`]+`)/', '`'.G5_TABLE_PREFIX.'$1', $file);
$f = explode(';', $file);
for ($i=0; $i<count($f); $i++) {
    if (trim($f[$i]) == '') continue;
    sql_query($f[$i], true);
}
// 테이블 생성 ------------------------------------
?>

        <li>전체 테이블 생성 완료</li>

<?php
//-------------------------------------------------------------------------------------------------
// alimtalk_config 테이블 설정
$sql = " insert into `".G5_TABLE_PREFIX."nc_alimtalk_config`
            set nc_plus_friend_id = '',
                nc_biz_msg_service_id = '',
                nc_sub_access_key_id = '',
				nc_sub_secret_key = '',
                nc_biz_msg_admin_hp = '',
                nc_sms = '',
                nc_sms_service_id = '',
                nc_sms_admin_hp = '' 
				";
sql_query($sql, true);
?>

        <li>DB설정 완료</li>

<?php
//-------------------------------------------------------------------------------------------------

// 카카오 알림톡 실행 파일 생성
$file = G5_EXTEND_PATH.'/nc_alimtalk.php';

// 카카오 알림톡 실행 파일이 이미 존재 한다면 삭제 후 다시 생성
if(is_file($file)) {
	@unlink($file);
}

$f = @fopen($file, 'a');

fwrite($f, "<?php\n");
fwrite($f, "if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가\n");
fwrite($f, "include_once(G5_ADMIN_PATH.'/gw_alimtalk_admin/gw_config.php');\n");

fclose($f);
@chmod($file, G5_FILE_PERMISSION);
?>

        <li>카카오 알림톡 실행 파일 생성 완료 (<?php echo $file ?>)</li>
    </ol>

    <p>축하합니다. 카카오 알림톡 설치가 완료되었습니다.</p>

</div>

<div class="ins_inner">

    <h2>환경설정 변경은 다음의 과정을 따르십시오.</h2>

    <ol>
        <li>관리자 로그인</li>
        <li>관리자 모드 접속</li>
        <li>알림톡설정 메뉴의 알림톡설정 페이지로 이동</li>
    </ol>

    <div class="inner_btn">
        <a href="<?php echo G5_ADMIN_URL ?>/gw_alimtalk_admin/nc_alimtalk_config.php">설치한 카카오 알림톡으로 이동</a>
    </div>

</div>

<?php
include_once ('./install.inc2.php');
