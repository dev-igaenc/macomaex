<?
include_once("./_common.php");

$board['bo_10']="12";
$board['bo_9']="2";
$board['bo_5'] = "ETC";
$board['bo_subject'] = "개인정보처리방침";
$board['bo_4'] = '';
$privacy = sql_fetch("select * from g5_content where co_id='privacy'");

include_once(G5_THEME_PATH.'/head.php');
?>
<div class="etc_clause clauseArea no"><div class="etc_clause_in">
	<p class="logo"><img src="/img/logo.svg" alt="<?php echo $config['cf_title']; ?>"></p>
	<!-- <div class="txt"> <span>개인정보처리방침</span> 안내</div> -->

	<div class="scroll"><?php echo $privacy['co_content']; ?></div>
</div></div>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>
