<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<!--[s] Search -->
<form name="fsearch" method="get">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
	<input type="hidden" name="sca" value="<?php echo $sca ?>">
	<input type="hidden" name="sop" value="and">

	<!-- <div class="board_search">
		<select name="sfl" id="sfl">
			<?php echo get_board_sfl_select_options($sfl); ?>
		</select>
		<input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input" size="25" maxlength="20" placeholder="<?=$lang_sch_long?>">
		<input type="submit" name="" value="<?=$lang_sch_btn?>" />
	</div> -->
</form>
<!--[e] Search -->

<!-- 게시판 카테고리 시작 { -->
<?php if ($is_category) { ?>
<nav id="bo_cate" class="category_top">
	<h2><!-- <?php echo $board['bo_subject'] ?> 카테고리 --></h2>
	<ul id="bo_cate_ul">
		<?php echo $category_option ?>
	</ul>
</nav>
<?php } ?>
<!-- } 게시판 카테고리 끝 -->

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
	<!--[s] List -->
	<div class="photo_list product_list dynamic <?php if (count($list) == 0) {?>noData<?php } ?>">
		<ul class="list">
			<?php for ($i=0; $i<count($list); $i++) {?>
			<li class="item <?php if ($is_category && $list[$i]['ca_name']) { ?><?php echo str_replace(" ","",$list[$i]['ca_name']) ?><?php } ?>" data-category="<?php if ($is_category && $list[$i]['ca_name']) { ?><?php echo $list[$i]['ca_name'] ?><?php } ?>">
				<a href="<?php echo $list[$i]['href'] ?>" class="gall_cont">
					<div class="thumb">
						<?php
						$thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);

						if($thumb['src']) {
							$img_content = '<img src="'.$thumb['ori'].'" class=" alt="'.$thumb['alt'].'" />';
						} else {
							//$img_content = '<span class="no_image" style="line-height:'.$board['bo_gallery_height'].'px;">no image</span>';
							$img_content = '<img src="/img/noimage.jpg" class=" bg" alt="'.$thumb['alt'].'" />';
						}

						echo $img_content;
						?>
					</div>
					<div class="area"><div class="in_area">
                        <div class="crf_icon">
                                <?
                                $c_icon=explode("||",$list[$i]['wr_2']);
                                foreach($c_icon AS $value){
									if($value>-1) {
                                ?>
                                <img src="/img/crt_<?=$value?>.jpg" class="icons">
                                <?}?>
								<?
								}
								?>
						</div>
						<?php if ($is_category && $list[$i]['ca_name']) { ?> <p class="cate">[<?php echo $list[$i]['ca_name'] ?>]</p> <?php } ?>
						<div class="txt_area">
							<div class="tt title"><span class="new_icon">N</span> <?php echo $list[$i]['subject'] ?></div>
                            <div class="tt txt"><?=strip_tags($list[$i]['wr_3']);?></div>
						</div>
					</div></div>
                    <p class="over"><span>+</span></p>
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

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

<?php if ($is_checkbox) { ?>
<script>
	function all_checked(sw) {
		var f = document.fboardlist;

		for (var i=0; i<f.length; i++) {
			if (f.elements[i].name == "chk_wr_id[]")
				f.elements[i].checked = sw;
		}
	}

	function fboardlist_submit(f) {
		var chk_count = 0;

		for (var i=0; i<f.length; i++) {
			if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
				chk_count++;
		}

		if (!chk_count) {
			alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
			return false;
		}

		if(document.pressed == "선택복사") {
			select_copy("copy");
			return;
		}

		if(document.pressed == "선택이동") {
			select_copy("move");
			return;
		}

		if(document.pressed == "선택삭제") {
			if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
				return false;

			f.removeAttribute("target");
			f.action = "./board_list_update.php";
		}

		return true;
	}

	// 선택한 게시물 복사 및 이동
	function select_copy(sw) {
		var f = document.fboardlist;

		if (sw == 'copy')
			str = "복사";
		else
			str = "이동";

		var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

		f.sw.value = sw;
		f.target = "move";
		f.action = "./move.php";
		f.submit();
	}
</script>
<?php } ?>

<!-- } 게시판 목록 끝 -->
