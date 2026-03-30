<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 8;

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
	<!--form name="fsearch" method="get">
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
	</form-->
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

		<!--[s] List -->
		<div class="public_list2_pc">
			<table>
				<colgroup>
					<col width="7.5%">
					<col width="15%">
					<col width="*">
					<col width="10%">
					<col width="15%">
					<col width="15%">
					<col width="10%">
					<col width="10%">
				</colgroup>
				<thead>
				<tr>
					<th scope="col">구분</th>
					<th scope="col">이미지</th>
					<th scope="col">품명</th>
					<th scope="col">규격</th>
					<th scope="col">모델명</th>
					<th scope="col">제조사</th>
					<th scope="col">단가(원)</th>
					<th scope="col">식별번호</th>
				</tr>
				</thead>
				<tbody>
				<?php
				for ($i=0; $i<count($list); $i++) {
					$thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);
						if($thumb['src']) {
							$img_content = '<img src="'.$thumb['ori'].'" class="ofi alt="'.$thumb['alt'].'" />';
						} else {
							$img_content = '<img src="/img/noimage.jpg" class="ofi bg" alt="'.$thumb['alt'].'" />';
						}
				?>
				<tr class="">
					<td class="txt_1">
						<?=$list[$i]['num'];?>
					</td>
					<td class="txt_2">
						<?=$img_content?>
					</td>
					<td class="txt_3">
						<?=$list[$i]['wr_2']?>
					</td>
					<td class="txt_4">
						<?=$list[$i]['wr_3']?>
					</td>
					<td class="txt_5">
						<?=$list[$i]['wr_4']?>
					</td>
					<td class="txt_6">
						<?=$list[$i]['wr_5']?>
					</td>
					<td class="txt_7">
						<?=$list[$i]['wr_6']?>
					</td>
					<td class="txt_7">
						<?=$list[$i]['wr_subject']?>
					</td>
				</tr>
				<?php } ?>
				<?php if (count($list) == 0) { echo '<tr class="noPost"><td colspan="'.$colspan.'">'.$lang_list_no.'</td></tr>'; } ?>
				</tbody>
			</table>
		</div>
		<!--[e] List -->

		<!--[s] List -->
		<div class="public_list2_mo">
			<table>
                <colgroup>
                    <col width="40%">                    
                    <col width="*">                    
                </colgroup>
				<tbody>
				<?php
				for ($i=0; $i<count($list); $i++) {
					$thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);
						if($thumb['src']) {
							$img_content = '<img src="'.$thumb['ori'].'" class="ofi alt="'.$thumb['alt'].'" />';
						} else {
							$img_content = '<img src="/img/noimage.jpg" class="ofi bg" alt="'.$thumb['alt'].'" />';
						}
				?>
				<tr class="line1">
					<td class="txt_11">
						<?=$img_content?>
					</td>
					<td class="txt_12">
						<p class="txt_121"><span>품명 : </span><?=$list[$i]['wr_2']?></p>
						<p class="txt_122"><span>모델명 : </span><?=$list[$i]['wr_4']?></p>
						<p class="txt_123"><span>식별번호: </span><?=$list[$i]['wr_subject']?></p>
					</td>
				</tr>
				<tr class="line2">
					<td  colspan="2">
						<p class="txt_221"><span>규격 : </span><?=$list[$i]['wr_3']?></p>
						<p class="txt_222"><span>제조사 : </span><?=$list[$i]['wr_5']?></p>
						<p class="txt_223"><span>단가(원) : </span><?=$list[$i]['wr_6']?></p>
					</td>
				</tr>
				<?php } ?>
				<?php if (count($list) == 0) {?>
					<tr>
						<td colspan="2"> 자료가 없습니다.</td>
					</tr>
				<? } ?>
				</tbody>
			</table>
		</div>
		<!--[e] List -->

		<!-- 페이지 -->
		<?php echo $write_pages; ?>
		<!-- 페이지 -->
	</form>
	<!-- } 게시판 목록 끝 -->