<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>
<?php if ($is_checkbox) { ?>
<script>
	$( function() {
		$( "#photo-move" ).sortable({
			handle: ".portlet-header",
			start: function(e, ui) {
				$(this).attr('data-previndex', ui.item.index()); // 기존 순서값을 data-previndex에 저장
			},
			update : function(e,ui){
				var f = document.fboardlist;
				f.target = "hidden";
				f.action = "<?php echo $board_skin_url; ?>/changeOrder.php";
				f.submit();
			}
		});
		$( "#photo-move").disableSelection();
	});
</script>
<style>

	.ui-state-default {}
	.ui-sortable-placeholder{visibility: visible !important; display:block; position: relative;}
	.ui-sortable-placeholder:before{content: ""; background-color:#f1f1f1; border:1px dashed #000; display:block; position: absolute; left:4rem; right:0; top:4rem; bottom:0;}
</style>
<?php } ?>

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

	<!-- 게시판 페이지 정보 및 버튼 시작 { -->
	<div class="board_btn">
		<div class="total">Total <strong><?php echo number_format($total_count) ?></strong>, &nbsp; Page <strong><?php echo $page ?></strong></div>

		<ul class="board_user">
			<?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="click admin"><i class="fa fa-cog fa-spin fa-fw"></i> 관리자</a></li><?php } ?>
			<?php if ($is_admin == 'super' || $is_auth) {  ?>
				<li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="click"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
				<!--
				<li><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value" class="click"><i class="fa fa-files-o" aria-hidden="true"></i> 선택복사</button></li>
				<li><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value" class="click"><i class="fa fa-arrows" aria-hidden="true"></i> 선택이동</button></li>
				-->
			<?php } ?>
			<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="click write">글쓰기 <i class="fa fa-pencil" aria-hidden="true"></i></a></li><?php } ?>
		</ul>
	</div>
	<!-- } 게시판 페이지 정보 및 버튼 끝 -->

	<!--[s] List -->
	<div class="photo_list <?php if (count($list) == 0) {?>noData<?php } ?>">
		<ul id="photo-move" class="list">
			<?php for ($i=0; $i<count($list); $i++) {
				if($i == 0) {
					echo "<input type='hidden' name='biggest' value='".$list[$i]['wr_10']."'>";
				}
			?>

			<li class="ui-state-default" data-wr="<?=$list[$i]['wr_10']?>">
				<?php if ($is_checkbox) { ?>

				<div class="gall_box">
					<p class="checkIn noTxt">
						<input type="hidden" name="order_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>">
						<input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>">
						<label for="chk_wr_id_<?php echo $i ?>"><?php echo $list[$i]['subject'] ?></label>
					</p>
					<div class="portlet-header">이동</div>
				</div>
				<?php } ?>
				<a href="<?php echo $list[$i]['href'] ?>" class="gall_cont">
					<div class="thumb"><img src="/theme/prix/img/thumb/thumb_270x270.png" alt="" /><!-- 고정 -->
						<?php
						$thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);

						if($thumb['src']) {
							$img_content = '<img src="'.$thumb['ori'].'" class="ofi thb" alt="'.$thumb['alt'].'" />';
						} else {
							//$img_content = '<span class="no_image" style="line-height:'.$board['bo_gallery_height'].'px;">no image</span>';
							$img_content = '<img src="/img/noimage.jpg" class="ofi thb bg" alt="'.$thumb['alt'].'" />';
						}

						echo $img_content;
						?><p class="over move"><span>view</span></p>
					</div>
					<div class="area"><div class="in_area">
						<?php if ($is_category && $list[$i]['ca_name']) { ?> <p class="cate">[<?php echo $list[$i]['ca_name'] ?>]</p> <?php } ?>

						<div class="title__area">
							<div class="tt title two"><?php echo $list[$i]['subject'] ?></div>
							<?php if (!empty($list[$i]['icon_new'])) {echo "<p class='icon new'></p>";} ?>
							<!--
							<?php if (isset($list[$i]['icon_hot'])) echo "<p class='icon hot'></p>"; ?>
							<?php if (isset($list[$i]['icon_new'])) echo rtrim($list[$i]['icon_new']);?>
							<?php if (isset($list[$i]['icon_file'])) echo "<p class='icon fil'>".rtrim($list[$i]['icon_file'])."</p>"; ?>
							<?php if (isset($list[$i]['icon_link'])) echo "<p class='icon link'>".rtrim($list[$i]['icon_link'])."</p>"; ?>
							<?php if (isset($list[$i]['icon_secret'])) echo "<p class='icon secre'>".rtrim($list[$i]['icon_secret'])."</p>"; ?>
							-->
						</div>

						<div class="tt txt two"><?=str_replace('&nbsp;','',strip_tags($list[$i]['wr_content']));?></div>
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

	<?php if ($list_href || $is_checkbox || $write_href) { ?>
	<div class="board_btn end">
		<ul class="board_user">
			<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="click write big">글쓰기</a></li><?php } ?>
		</ul>
	</div>
	<?php } ?>
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
<script>
$(function(){
	// 플러그인 이미지(bg 움직임) 오버
	$(".photo_list .list").iOverScript({
		btns : '.gall_cont', // 이벤트 class
		bg : '.over', // 활성화 class
		speed : 700 // 속도
	});
});
</script>
<iframe name="hidden" style="display:none;"></iframe>
<!-- } 게시판 목록 끝 -->
