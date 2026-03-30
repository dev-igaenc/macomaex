<?php
include_once ('./_common.php');
$gmnow = gmdate('D, d M Y H:i:s').' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
@header('Content-Type: text/html; charset=utf-8');
@header('X-Robots-Tag: noindex');

$title = "카카오 알림톡 초기환경설정 2/3";
include_once ('./install.inc.php');

if (!isset($_POST['agree']) || $_POST['agree'] != '동의함') {
    echo "<div class=\"ins_inner\"><p>라이센스(License) 내용에 동의하셔야 설치를 계속하실 수 있습니다.</p>".PHP_EOL;
    echo "<div class=\"inner_btn\"><a href=\"./\">뒤로가기</a></div></div>".PHP_EOL;
    exit;
}
?>

<script>
// 자바스크립트에서 사용하는 전역변수 선언
var g5_url       = "<?php echo G5_URL ?>";
var g5_bbs_url   = "<?php echo G5_BBS_URL ?>";
var g5_is_member = "<?php echo isset($is_member)?$is_member:''; ?>";
var g5_is_admin  = "<?php echo isset($is_admin)?$is_admin:''; ?>";
var g5_is_mobile = "<?php echo G5_IS_MOBILE ?>";
var g5_bo_table  = "<?php echo isset($bo_table)?$bo_table:''; ?>";
var g5_sca       = "<?php echo isset($sca)?$sca:''; ?>";
var g5_editor    = "<?php echo ($config['cf_editor'] && $board['bo_use_dhtml_editor'])?$config['cf_editor']:''; ?>";
var g5_cookie_domain = "<?php echo G5_COOKIE_DOMAIN ?>";
</script>
<script src="<?php echo G5_JS_URL ?>/jquery-1.8.3.min.js"></script>

<form id="frm_install" method="post" action="./install_db.php" autocomplete="off" onsubmit="return frm_install_submit(this)">

<div class="ins_inner">
    <p>
        <strong class="st_strong">주의! 이미 카카오 알림톡이 존재한다면 DB 자료가 초기화되므로 주의하십시오.</strong><br>
        주의사항을 이해했으며, 카카오 알림톡 설치를 계속 진행하시려면 다음을 누르십시오.
    </p>

    <div id="ins_agree">
        <label for="agree">동의합니다.</label>
        <input type="checkbox" name="agree" value="동의함" id="agree">
    </div>

    <div class="inner_btn">
        <input type="submit" value="다음">
    </div>
</div>

<script>
function frm_install_submit(f)
{
	if (!f.agree.checked) {
        alert("주의사항에 동의하셔야 설치가 가능합니다.");
        return false;
    }

    return true;
}
</script>

<?php
include_once ('./install.inc2.php');
