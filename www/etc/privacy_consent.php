<?
include_once("./_common.php");

$board['bo_10']="12";
$board['bo_9']="2";
$board['bo_5'] = "ETC";
$board['bo_subject'] = "개인정보 수집 및 이용 안내";
$board['bo_4'] = '개인정보활용동의서';

include_once(G5_THEME_PATH.'/head.php');
?>
<div class="etc_clause clauseArea no"><div class="etc_clause_in">
	<p class="logo"><img src="/img/logo.svg" alt="<?php echo $config['cf_title']; ?>"></p>
	<div class="scroll"><? include_once(G5_THEME_PATH . '/privacy_consent.php'); ?></div>
</div></div>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>
