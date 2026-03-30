<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
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

<!--[s] Search -->
<form name="fsearch" method="get">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
	<input type="hidden" name="sca" value="<?php echo $sca ?>">
	<input type="hidden" name="sop" value="and">

	<div class="board_search">
		<select name="sfl" id="sfl">
			<?php echo get_board_sfl_select_options($sfl); ?>
		</select>
		<input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input" size="25" maxlength="20" placeholder="<?=$lang_sch_long?>">
		<input type="submit" name="" value="<?=$lang_sch_btn?>" />
	</div>
</form>
<!--[e] Search -->

<form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
	<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
	<input type="hidden" name="stx" value="<?php echo $stx ?>">
	<input type="hidden" name="spt" value="<?php echo $spt ?>">
	<input type="hidden" name="sca" value="<?php echo $sca ?>">
	<input type="hidden" name="sst" value="<?php echo $sst ?>">
	<input type="hidden" name="sod" value="<?php echo $sod ?>">
	<input type="hidden" name="page" value="<?php echo $page ?>">
	<input type="hidden" name="sw" value="">

	<!-- 게시판 페이지 정보 및 버튼 시작 { -->
	<div class="board_btn">
		<div class="total">Total <strong><?php echo number_format($total_count) ?></strong>, &nbsp; Page <strong><?php echo $page ?></strong></div>
	</div>
	<!-- } 게시판 페이지 정보 및 버튼 끝 -->

	<!--[s] List -->
	<div class="photo_list popup <?php if (count($list) == 0) {?>noData<?php } ?>">
		<ul class="list">
			<?php for ($i=0; $i<count($list); $i++) {?>
			<li>
				<?php
				$thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);
				$thumb_src;

				if($thumb['src']) {
					$img_content = '<img src="'.$thumb['ori'].'" class="ofi thb" alt="'.$thumb['alt'].'" />';
					$thumb_src = $thumb['ori'];
				} else {
					$img_content = '<img src="/img/noimage.jpg" class="ofi thb bg" alt="'.$thumb['alt'].'" />';
					$thumb_src = "/img/noimage.jpg";
				}
				?>

				<a href="<?php echo $thumb_src ?>" data-fancybox="layPop" class="gall_cont">
					<div class="thumb portrait"><?php echo $img_content ?>
						<img src="/theme/prix/img/thumb/thumb_a4.png" alt="" /><!-- 고정 -->
						<p class="over"><span>view</span></p>
					</div>
					<div class="area"><div class="in_area">
						<?php if ($is_category && $list[$i]['ca_name']) { ?> <p class="cate">[<?php echo $list[$i]['ca_name'] ?>]</p> <?php } ?>

						<div class="title__area">
							<div class="tt title two"><?php echo $list[$i]['subject'] ?></div>
						</div>

						<!-- <div class="tt txt two"><?=str_replace('&nbsp;','',strip_tags($list[$i]['wr_content']));?></div> -->
						<div class="tt day"><?php echo $list[$i]['datetime'] ?></div>
					</div></div>
				</a>
			</li>
			<?php } ?>
			<?php if (count($list) == 0) { echo "<li class=\"empty_list\">".$lang_list_no."</li>"; } ?>
		</ul>
	</div>
	<!--[e] List -->

	<!-- 페이지 -->
	<?php echo $write_pages; ?>
	<!-- 페이지 -->

</form>
<!-- } 게시판 목록 끝 -->
