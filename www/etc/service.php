<?
include_once("./_common.php");
$pageNum="12";
$subNum="2";
$gr_subject = "ETC";
$bo_subject = "전자금융거래이용약관";
include_once(G5_THEME_PATH.'/head.php');
?>
<div class="etc_select">
	<select name="" id="" class="base" onchange="javascript:sel_it(this.value);">
		<option value="/etc/service.php">2022.01.01</option>
	</select>
</div>
<div class="etc_clause clauseArea no"><div class="etc_clause_in">
	<p class="logo"><img src="<?php echo G5_IMG_URL ?>/mem_logo.png" alt="<?php echo $config['cf_title']; ?>"></p>
	<div class="txt"> <span>전자금융거래이용약관</span>안내</div>

	<div class="scroll"><? include_once(G5_THEME_PATH . '/service.php'); ?></div>
</div></div>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>