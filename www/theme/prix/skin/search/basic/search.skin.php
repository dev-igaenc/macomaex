<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH .'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$search_skin_url.'/style.css">', 0);
?>

<style>
	.board_btn{padding-top:0; display:block; text-align: center;}
	.board_btn>.total{padding:0 0 3rem; font-weight: 500; font-size:24px; text-align:center;}
	.board_btn>.total>strong{font-family: '0425', '0425_i', sans-serif !important; font-weight: bold; font-size:30px;}
	.board_btn+h5.bul{margin-top:0;}
</style>

<div class="board_btn">
	<div class="total"><strong>"<?php echo $stx ?>"</strong>, 검색결과 <strong><?php echo number_format($total_count) ?></strong>건</div>
</div>

<!-- 전체검색 시작 { -->
<form name="fsearch" onsubmit="return fsearch_submit(this);" method="get">
<input type="hidden" name="srows" value="<?php echo $srows ?>">
<fieldset id="sch_res_detail">
	<legend>상세검색</legend>

	<div class="board_search">
		<!--
		<?php echo $group_select ?>
		<script>document.getElementById("gr_id").value = "<?php echo $gr_id ?>";</script>
		-->
		<select name="sfl" id="sfl">
			<option value="wr_subject||wr_content"<?php echo get_selected($sfl, "wr_subject||wr_content") ?>>제목+내용</option>
			<option value="wr_subject"<?php echo get_selected($sfl, "wr_subject") ?>>제목</option>
			<option value="wr_content"<?php echo get_selected($sfl, "wr_content") ?>>내용</option>
			<!-- <option value="mb_id"<?php echo get_selected($sfl, "mb_id") ?>>회원아이디</option>
			<option value="wr_name"<?php echo get_selected($sfl, "wr_name") ?>>이름</option> -->
		</select>
		<input type="text" name="stx" value="<?php echo $text_stx ?>" required id="stx" class="sch_input" size="25" maxlength="20" placeholder="">
		<input type="submit" name="" value="검색" />
	</div>
	<script>
		function fsearch_submit(f){
			if (f.stx.value.length < 2) {
				alert("검색어는 두글자 이상 입력하십시오.");
				f.stx.select();
				f.stx.focus();
				return false;
			}

			// 검색에 많은 부하가 걸리는 경우 이 주석을 제거하세요.
			var cnt = 0;
			for (var i=0; i<f.stx.value.length; i++) {
				if (f.stx.value.charAt(i) == ' ')
					cnt++;
			}

			if (cnt > 1) {
				alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다.");
				f.stx.select();
				f.stx.focus();
				return false;
			}

			f.action = "";
			return true;
		}
	</script>
	<!--
	<div class="switch_field">
		<input type="radio" value="and" <?php echo ($sop == "and") ? "checked" : ""; ?> id="sop_and" name="sop">
		<label for="sop_and">AND</label>
		<input type="radio" value="or" <?php echo ($sop == "or") ? "checked" : ""; ?> id="sop_or" name="sop" >
		<label for="sop_or">OR</label>
	</div>
	-->
</fieldset>
</form>
<!-- 검색결과 상세
<?php
	if ($stx) {
		if ($board_count) {
	?>
	<section id="sch_res_ov">
		<h2><strong><?php echo $stx ?></strong> 전체검색 결과</h2>
		<ul>
			<li>게시판 <?php echo $board_count ?>개</li>
			<li>게시물 <?php echo number_format($total_count) ?>개</li>
			<li><?php echo number_format($page) ?>/<?php echo number_format($total_page) ?> 페이지 열람 중</li>
		</ul>
	</section>
	<?php
		}
	}
?>
-->

<?php
	if ($stx) {
		if ($board_count) {
	?>
	<div class="search-result">
		<h2>전체게시판</h2>
		<ul id="sch_res_board">
			<li><a href="?<?php echo $search_query ?>&amp;gr_id=<?php echo $gr_id ?>" <?php echo $sch_all ?>>전체게시판</a></li>
			<?php echo $str_board_list; ?>
		</ul>
	</div>
	<?php
		} else {
	?>
	<script>alert("검색된 결과가 없습니다.")</script>
<?php } }  ?>

<?php
	$k=0;
	for ($idx=$table_index, $k=0; $idx<count($search_table) && $k<$rows; $idx++) {
	?>
	<h5 class="bul" data-aos="fade-up"><?php echo $bo_subject[$idx] ?> 검색 결과 <a href="<?php echo get_pretty_url($search_table[$idx], '', $search_query); ?>" class="more">+ 더보기</a></h5>

	<div class="board_area">
		<ul class="list" data-aos="fade-up" data-aos-delay="100">
			<?php
				for ($i=0; $i<count($list[$idx]) && $k<$rows; $i++, $k++) {
					if ($list[$idx][$i]['wr_is_comment']){
						$comment_def = '<span class="cmt_def"><i class="fa fa-commenting-o" aria-hidden="true"></i><span class="sound_only">댓글</span></span> ';
						$comment_href = '#c_'.$list[$idx][$i]['wr_id'];
					}else{
						$comment_def = '';
						$comment_href = '';
					}

					// 이미지 출력 부분
					$thumb_info=get_list_thumbnail($search_table[$idx], $list[$idx][$i]['wr_id'],100,100);
					$search_thums=$thumb_info['src'];

					$file_img['file']=get_file($search_table[$idx], $list[$idx][$i]['wr_id']);
					$search_file=$file_img['file']['0']['source'];
					//echo $search_file;
					if(!$search_thums){
						//echo "이미지 없음";
						$iimg="";
					}else{
						$iimg=($search_file) ? '<img src="'. $search_thums .'" class="thumb">' : '<img src="/img/noimage.jpg" class="thumb">';
					}

					/*
					var pattern = /\.(gif|jpg|png|jpeg)$/i; // jpeg 는 제외
					if(!pattern.test($search_file)){
						if(wrestFld != null){
							$iimg=($search_file) ? '<img src="'. $search_thums .'" class="thumb">' : '<img src="/img/noimage.jpg" class="thumb">';
						}
					}
					*/

					// 이미지 출력 부분
				?>
				<li>
					<a href="<?php echo $list[$idx][$i]['href'] ?><?php echo $comment_href ?>">
						<?php echo $iimg?>
						<dl class="title">
							<dt><?php echo $comment_def ?><?php echo $list[$idx][$i]['subject'] ?></dt>
							<dd><div><?=str_replace('&nbsp;','',strip_tags($list[$idx][$i]['wr_content']));?></div></dd>
						</dl>
					</a>
					<div class="etc">
						<!-- <p class="category"><span><?php echo $list[$i]['wr_3'] ?></span></p> -->
						<!-- <p class="day"><?php echo $list[$idx][$i]['name'] ?></p> -->
						<p class="day"><?php echo date("Y.m.d", strtotime($list[$idx][$i]['wr_datetime'])) ?></p>
					</div>
				</li>
			<?php }  ?>
		</ul>
	</div>
<?php } //end for?>

<?php echo $write_pages ?>
<!-- } 전체검색 끝 -->