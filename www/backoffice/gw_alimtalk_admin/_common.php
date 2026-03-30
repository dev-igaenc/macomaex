<?php
define('G5_IS_ADMIN', true);
include_once ('../../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once('./gw_config.php');   // 설정 파일

//==============================================================================
// 공통
//------------------------------------------------------------------------------
if (!$alimtalk_table) {
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>오류! 카카오 알림톡 설치하기</title>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL ?>/gw_alimtalk_admin/install/install.css">
</head>
<body>

<div id="ins_bar">
    <span id="bar_img">카카오 알림톡</span>
    <span id="bar_txt">GNUWIZ</span>
</div>
<h1>카카오 알림톡을 먼저 설치해주십시오.</h1>
<div class="ins_inner">
    <p>데이터베이스에서 다음 카카오 알림톡 설정 테이블을 찾을 수 없습니다.</p>
    <ul>
        <li><strong><?php echo $g5['nc_alimtalk_config'] ?></strong></li>
        <li><strong><?php echo $g5['nc_alimtalk_history'] ?></strong></li>
        <li><strong><?php echo $g5['nc_friendtalk_history'] ?></strong></li>
        <li><strong><?php echo $g5['nc_friendtalk_image'] ?></strong></li>
    </ul>
    <p>카카오 알림톡을 설치 후 다시 실행하시기 바랍니다.</p>
    <div class="inner_btn">
        <a href="<?php echo G5_ADMIN_URL; ?>/gw_alimtalk_admin/install/">카카오 알림톡 설치하기</a>
    </div>
</div>
<div id="ins_ft">
    <strong>GNUWIZ</strong>
    <a href="http://gnuwiz.com" target="_blank"><p>http://gnuwiz.com</p></a>
</div>

</body>
</html>

<?php
    exit;
}
