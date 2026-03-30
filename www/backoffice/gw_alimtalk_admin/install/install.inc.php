<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!$title) $title = "카카오 알림톡 설치";
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?php echo $title; ?></title>
<link rel="stylesheet" href="install.css">
</head>
<body>

<div id="ins_bar">
    <span id="bar_img">카카오 알림톡</span>
    <span id="bar_txt">GNUWIZ</span>
</div>

<?php
// 카카오 알림톡 설정 테이블이 존재한다면 설치할 수 없다.
// 카카오 알림톡 설정 테이블이 있는가?
if ($alimtalk_table) {
?>
<h1>카카오 알림톡 프로그램이 이미 설치되어 있습니다.</h1>

<div class="ins_inner">
    <p>프로그램이 이미 설치되어 있습니다.<br />새로 설치하시려면 다음 데이터베이스 테이블을 삭제 하신 후 새로고침 하십시오.</p>
    <ul>
        <li><strong><?php echo $g5['nc_alimtalk_config'] ?></strong></li>
        <li><strong><?php echo $g5['nc_alimtalk_history'] ?></strong></li>
    </ul>
</div>
<?php
    exit;
}
