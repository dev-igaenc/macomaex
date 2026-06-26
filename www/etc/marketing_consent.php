<?
include_once("./_common.php");

$board['bo_10']="12";
$board['bo_9']="5";
$board['bo_5'] = "ETC";
$board['bo_subject'] = "마케팅 수신 동의";
$board['bo_4'] = '마케팅 수신 동의서';

include_once(G5_THEME_PATH.'/head.php');
?>
<div class="etc_clause clauseArea no"><div class="etc_clause_in">
	<p class="logo"><img src="/img/logo.svg" alt="<?php echo $config['cf_title']; ?>"></p>
	<div class="scroll"><? include_once(G5_THEME_PATH . '/marketing_consent.php'); ?></div>
</div></div>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>
