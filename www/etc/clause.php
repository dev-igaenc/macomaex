<?
include_once("./_common.php");

$board['bo_10']="12";
$board['bo_9']="1";
$board['bo_5'] = "ETC";
$board['bo_subject'] = "서비스 이용약관";
$board['bo_4'] = '';
$provision = sql_fetch("select * from g5_content where co_id='provision'");
include_once(G5_THEME_PATH.'/head.php');
?>
<!-- <div class="etc_select">
	<select name="" id="" class="base" onchange="javascript:sel_it(this.value);">
		<option value="/etc/clause.php">2022.04.01</option>
	</select>
</div> -->
<div class="etc_clause clauseArea no"><div class="etc_clause_in">
	<p class="logo"><img src="/img/logo.svg" alt="<?php echo $config['cf_title']; ?>"></p>
	<!-- <div class="txt"> <span>서비스이용약관</span>안내</div> -->

	<div class="scroll"><?php echo $provision['co_content']; ?></div>
</div></div>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>
