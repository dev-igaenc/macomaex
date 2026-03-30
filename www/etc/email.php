<?
include_once("./_common.php");

$board['bo_10']="12";
$board['bo_9']="2";
$board['bo_5'] = "ETC";
$board['bo_subject'] = "이메일무단수집거부";
$board['bo_4'] = '';

include_once(G5_THEME_PATH.'/head.php');
?>
<div class="etc_clause clauseArea no"><div class="etc_clause_in">
	<p class="logo"><img src="<?php echo G5_IMG_URL ?>/mem_logo.png" alt="<?php echo $config['cf_title']; ?>"></p>
	<div class="txt"> <span>이메일무단수집거부</span>안내</div>

	<div class="scroll"><? include_once(G5_THEME_PATH . '/email.php'); ?></div>
</div></div>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>