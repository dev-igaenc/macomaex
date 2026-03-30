<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');
include_once $board_skin_path . "/board_head.skin.php";
//add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/css/style.css">', 20);
//add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/css/lightbox.css">', 21);
?>
<?php
function ConvertLineFeed($cVal, $tag){
	$tmpContent = "";

	If ($cVal!=""){
		//$cVal = nl2br($cVal);

		$arrVal = explode("\r\n", $cVal);
		for($i=0;$i< count($arrVal);$i++){
			If($arrVal[$i]!=""){
				$tmpContent = $tmpContent."<".$tag.">".$arrVal[$i]."</".$tag.">";
			}
		}
		return $tmpContent;
	}
}
?>
<!-- 게시판 카테고리 시작 { -->
<?php if ($is_category) { ?>
<nav id="bo_cate" class="category_top">
	<h2><?php echo $board['bo_subject'] ?> 카테고리</h2>
	<ul id="bo_cate_ul">
		<?php echo $category_option ?>
	</ul>
</nav>
<?php } ?>
<!-- } 게시판 카테고리 끝 -->
<style>
.history-area li:nth-child(odd) p span {color:var(--point); font-weight:700; display:inline-block; width:10px; margin-left:33px;}
.history-area li:nth-child(even) p {position:relative; padding-right:4rem; padding-left:0px;}
.history-area li:nth-child(even) p span {position:absolute; right:0px; display:inline-block; color:var(--point); font-weight:700; }
@media only screen and (max-width : 840px){
.history-area li:nth-child(odd) p span {margin-left:0px;}
.history-area li:nth-child(even) p {position:relative; padding-left:4rem;}
.history-area li:nth-child(even) p span {position:relative;  width:10px;}
}
</style>
<form name="fboardlist" id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
	<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
	<input type="hidden" name="stx" value="<?php echo $stx ?>">
	<input type="hidden" name="spt" value="<?php echo $spt ?>">
	<input type="hidden" name="page" value="<?php echo $page ?>">
	<input type="hidden" name="sw" value="">

	<ul class="history-area move flex <?php if ($is_admin) echo 'admin'?>">
		<div id="his_progress"></div>
		<?php for ($i=0; $i<count($list); $i++) {?>
		<li>
			<?php
			$thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);

			if($thumb['src']) {
				$img_content = '<img src="'.$thumb['ori'].'" class="ofi thb" alt="'.$thumb['alt'].'" />';
			}else{
				$img_content = "";
			}
			?>

			<a class="history-in">

				<strong data-aos="fade-up"><?php echo $list[$i]['subject'] ?></strong>

				<div class="txt_wrap" data-aos="fade-up" data-aos-delay="200">
					<?//echo ConvertLineFeed($list[$i]['wr_content'], "p");?>
					<?
					$key=explode("||",$list[$i]['wr_content']);
					foreach($key AS $value){
						$exp=explode("##",$value);
					?>
					<p><span><?=$exp[0]?></span><?=$exp[1]?></p>
					<?}?>
				</div>
			</a>
			<div class="thumb" data-aos="fade-up" data-aos-delay="300"><?php echo $img_content; ?></div>
		</li>
		<?php } ?>
		<?php if (count($list) == 0) { echo "<li class=\"empty_list\">".$lang_list_no."</li>"; } ?>
	</ul>
</div>

<!-- } 게시판 목록 끝 -->
