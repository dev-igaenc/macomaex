<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 4;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;


// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>
<?php if ($is_checkbox) { ?>
<script>
	$( function() {
		$( "#list-move" ).sortable({
			handle: ".portlet-header",
			start: function(e, ui) {
				$(this).attr('data-previndex', ui.item.index()); // 기존 순서값을 data-previndex에 저장
			},
			update : function(e,ui){
				var f = document.fboardlist;
				f.target = "hidden";
				f.action = "<?php echo $board_skin_url; ?>/changeOrder.php";
				f.submit();
			},
			stop:function(event, ui){
				setTimeout(function(){
					location.reload();
				}, 10);

			}
		});
		$( "#list-move").disableSelection();
	});
</script>
<style>
	.ui-state-default{}

	.ui-state-default.ui-sortable-placeholder{visibility: visible !important; display:table-row; position: relative;}
	.ui-state-default.ui-sortable-placeholder:before{content: ""; background-color:#f1f1f1; border:1px dashed #000; display:block; position: absolute; left:0; right:0; top:0; bottom:0;}

	.ui-state-default.ui-sortable-helper{display:table;}
	@media only screen and (max-width : 840px){
		.ui-state-default.ui-sortable-placeholder{margin:-3rem 0; display:block;}
		.ui-state-default.ui-sortable-placeholder:before{top:3rem; bottom:3rem;}
	}
</style>
<?php } ?>

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
	<div class="board_list">
		<table>
			<colgroup>
				<?php if ($is_checkbox) { ?><col width="7%"><?php } ?>
				<col width="7%">
				<col width="*">
				<col width="15%">
				<col width="7%">
			</colgroup>
			<thead>
			<tr>
				<?php if ($is_checkbox) { ?>
				<th scope="col" class="inp">
					<p class="checkIn noTxt">
						<input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);">
						<label for="chkall">현재 페이지 게시물 전체</label>
					</p>
				</th>
				<?php } ?>
				<th scope="col">No</th>
				<th scope="col"><?=$lang_list_subject?></th>
				<th scope="col"><?=$lang_list_date?></th>
				<th scope="col"><?=$lang_list_hit?></a></th>
			</tr>
			</thead>
			<tbody id="list-move">
			<?php
			for ($i=0; $i<count($list); $i++) {
				if ($i%2==0) $lt_class = "even";
				else $lt_class = "";

				if($i == 0) {
					echo "<input type='hidden' name='biggest' value='".$list[$i]['wr_10']."'>";
				}
			?>
			<tr class="<?php if (!$list[$i]['is_notice']){?>ui-state-default <?php } ?><?php if ($list[$i]['is_notice']) echo "bo_notice"; ?> <?php echo $lt_class ?>" data-wr="<?=$list[$i]['wr_10']?>">
				<?php if ($is_checkbox) { ?>
				<td class="td_chk">
					<p class="checkIn noTxt">
						<input type="hidden" name="order_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>">
						<input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
						<label for="chk_wr_id_<?php echo $i ?>"><?php echo $list[$i]['subject'] ?></label>
					</p><br />
					<?php if (!$list[$i]['is_notice']){?><div class="portlet-header">이동</div><?php } ?>
				</td>
				<?php } ?>
				<td class="td_num2">
				<?php
				if ($list[$i]['is_notice']) // 공지사항
					echo '<strong class="notice_icon">'.$lang_list_notice.'</strong>';
				else if ($wr_id == $list[$i]['wr_id'])
					echo "<span class=\"bo_current\">열람중</span>";
				else
					echo $list[$i]['num'];
				?>
				</td>

				<td class="td_subject"><!-- style="padding-left:<?php echo $list[$i]['reply'] ? (strlen($list[$i]['wr_reply'])*10) : '0'; ?>px" -->
					<!-- <a href="<?php echo $list[$i]['ca_name_href'] ?>" class="bo_cate_link"></a> -->

					<div class="bo_tit">
						<a href="<?php echo $list[$i]['href'] ?>">
							<?php if ($is_category && $list[$i]['ca_name']) { ?>[<?php echo $list[$i]['ca_name'] ?>]<?php } ?> &nbsp;
							<?php echo $list[$i]['icon_reply'] ?>
							<?php
								if (isset($list[$i]['icon_secret'])) echo rtrim($list[$i]['icon_secret']);
							?>
							<?php echo $list[$i]['subject'] ?>
						</a>
						<?php if (!empty($list[$i]['icon_new'])) {echo "<p class='icon new'></p>";} ?>

						<?php
						//if ($list[$i]['icon_new']) echo "<span class=\"new_icon\">N<span class=\"sound_only\">새글</span></span>";
						// if ($list[$i]['file']['count']) { echo '<'.$list[$i]['file']['count'].'>'; }
						//if (isset($list[$i]['icon_hot'])) echo rtrim($list[$i]['icon_hot']);
						//if (isset($list[$i]['icon_file'])) echo rtrim($list[$i]['icon_file']);
						//if (isset($list[$i]['icon_link'])) echo rtrim($list[$i]['icon_link']);
						?>
						<?php if ($list[$i]['comment_cnt']) { ?><span class="sound_only">댓글</span><span class="cnt_cmt">[<?php echo $list[$i]['wr_comment']; ?>]</span><span class="sound_only">개</span><?php } ?>
					</div>
				</td>
				<td class="btm"><?php echo date("Y.m.d", strtotime($list[$i]['wr_datetime'])) ?></td>
				<td class="btm"><?php echo $list[$i]['wr_hit'] ?></td>
			</tr>
			<?php } ?>
			<?php if (count($list) == 0) { echo '<tr class="noPost"><td colspan="'.$colspan.'">'.$lang_list_no.'</td></tr>'; } ?>
			</tbody>
		</table>
	</div>
	<!--[e] List -->

	<?php if ($list_href || $is_checkbox || $write_href) { ?>
	<div class="board_btn end">
		<ul class="board_user">
			<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="click write big">글쓰기</a></li><?php } ?>
		</ul>
	</div>
	<?php } ?>

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
<iframe name="hidden" style="display:none;"></iframe>
<!-- } 게시판 목록 끝 -->
