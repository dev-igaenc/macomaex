<?
include_once("./_common.php");

$board['bo_10']="7";
$board['bo_9']="1";
$board['bo_5'] = "스크립트";
$board['bo_subject'] = "따라다니는 배너";
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";

include_once(G5_THEME_PATH.'/head.php');
?>
<style>
	/* 따라다니는 베너 기본 */
		.followingBanner{width:100vw; margin-left:-50vw; position: relative; left:50%;}
		.followingBanner>*{position: absolute; left:0; top:0; z-index:7;}
		.followingBanner.bottom>*{position: fixed; top:auto; bottom:0;}

	/* 임시 */
		.moveBar{width:100%; padding:3.5rem 0; background-color:rgba(255,0,0,0.5); font-size:1.6rem; line-height:1.4; color: #fff; position: relative;}
		.moveBar2{width:100%; padding:4.5rem 0; background-color: rgba(0,0,0,0.5); font-size:1.6rem; line-height:1.4; color: #f8f8f8; position: relative;}
</style>

<div style="width:100%; height:20rem; background-color: rgba(0,0,0,0.3);"></div>
<div class="moveBar"><div class="inConts">
	기본 - $(움직일놈 class로 지정해야함).followingBanner(); // 따라다니는 베너(위치에 도달하면 움직임)
</div></div>
<div style="width:100%; height:60rem; background-color: #000;"></div>
<div style="width:100%; height:120rem; background-color: #000;"></div>
<div class="moveBar2"><div class="inConts">
	변형 - $(움직일놈 class로 지정해야함).followingBanner({direction:"bottom"}); // 따라다니는 베너 (하단에 따라다님)
</div></div>

<script>
	$(document).ready(function() {
		$(".moveBar").followingBanner(); // 따라다니는 베너(위치에 도달하면 움직임)
		$(".moveBar3").followingBanner(); // 따라다니는 베너(위치에 도달하면 움직임)
		$(".moveBar4").followingBanner(); // 따라다니는 베너(위치에 도달하면 움직임)
		$(".moveBar2").followingBanner({direction:"bottom"}); // 따라다니는 베너 (하단에 따라다님)
	});
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>