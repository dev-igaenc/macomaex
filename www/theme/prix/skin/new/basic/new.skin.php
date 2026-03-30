<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 선택삭제으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

if ($is_admin) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$new_skin_url.'/style.css">', 0);
?>

<!-- 전체게시물 목록 시작 { -->
<form name="fnewlist" id="fnewlist" method="post" action="#" onsubmit="return fnew_submit(this);">
	<input type="hidden" name="sw"       value="move">
	<input type="hidden" name="view"     value="<?php echo $view; ?>">
	<input type="hidden" name="sfl"      value="<?php echo $sfl; ?>">
	<input type="hidden" name="stx"      value="<?php echo $stx; ?>">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
	<input type="hidden" name="page"     value="<?php echo $page; ?>">
	<input type="hidden" name="pressed"  value="">

	<!-- 게시판 페이지 정보 및 버튼 시작 { -->
	<div class="board_btn">
		<div class="total">Total <strong><?php echo number_format($total_count) ?></strong>건,  <strong><?php echo $page ?></strong> 페이지</div>

		<ul class="board_user">
			<?php if ($is_admin) { ?>
				<li><a href="<?php echo $admin_href ?>" class="click admin"><i class="fa fa-cog fa-spin fa-fw"></i> 관리자</a></li>
				<li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.title" class="click"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
			<?php } ?>
		</ul>
	</div>
	<!-- } 게시판 페이지 정보 및 버튼 끝 -->

	<div class="board_list">
		<table>
			<colgroup>
				<?php if ($is_admin) { ?><col width="7%"><?php } ?>
				<col width="15%">
				<col width="*">
				<col width="15%">
				<col width="7%">
			</colgroup>
			<thead>
			<tr>
				<?php if ($is_admin) { ?>
				<th scope="col" class="inp">
					<p class="checkIn noTxt">
						<input type="checkbox" id="all_chk">
						<label for="all_chk">현재 페이지 게시물 전체</label>
					</p>
				</th>
				<?php } ?>
				<th scope="col">게시판</th>
				<th scope="col">제목</th>
				<th scope="col">이름</th>
				<th scope="col">등록일</a></th>
			</tr>
			</thead>
			<tbody>
			<?php
			for ($i=0; $i<count($list); $i++) {
				$num = $total_count - ($page - 1) * $config['cf_page_rows'] - $i;
				$gr_subject = cut_str($list[$i]['gr_subject'], 20);
				$bo_subject = cut_str($list[$i]['bo_subject'], 20);
				$wr_subject = get_text(cut_str($list[$i]['wr_subject'], 80));
			?>
			<tr>
				<?php if ($is_admin) { ?>
				<td class="td_chk">
					<p class="checkIn noTxt">
						<input type="checkbox" name="chk_bn_id[]" value="<?php echo $i; ?>" id="chk_bn_id_<?php echo $i ?>" class="selec_chk">
						<label for="chk_bn_id_<?php echo $i ?>"><?php echo $num?></label>
					</p>
					<input type="hidden" name="bo_table[<?php echo $i; ?>]" value="<?php echo $list[$i]['bo_table']; ?>">
					<input type="hidden" name="wr_id[<?php echo $i; ?>]" value="<?php echo $list[$i]['wr_id']; ?>">
				</td>
				<?php } ?>
				<td class="td_num2"><a href="<?php echo get_pretty_url($list[$i]['bo_table']); ?>"><?php echo $bo_subject ?></a></td>

				<td class="td_subject"><div class="bo_tit"><a href="<?php echo $list[$i]['href'] ?>"><?php echo $wr_subject ?></a></div></td>
				<td class="btm"><?php echo $list[$i]['name'] ?></td>
				<td class="btm"><?php echo date("Y.m.d", strtotime($list[$i]['wr_datetime'])) ?></td>
			</tr>
			<?php } ?>
			<?php if ($i == 0) { echo '<tr class="noPost"><td colspan="'.$colspan.'">등록된 글이 없습니다.</td></tr>'; } ?>
			</tbody>
		</table>
	</div>

	<!-- 페이지 -->
	<?php echo $write_pages; ?>
	<!-- 페이지 -->

	<?php if ($is_admin) { ?>
	<div class="board_btn end">
		<ul class="board_user">
			<li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.title" class="click"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
		</ul>
	</div>
	<?php } ?>
</form>

<?php if ($is_admin) { ?>
<script>
$(function(){
    $('#all_chk').click(function(){
        $('[name="chk_bn_id[]"]').attr('checked', this.checked);
    });
});

function fnew_submit(f)
{
    f.pressed.value = document.pressed;

    var cnt = 0;
    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_bn_id[]" && f.elements[i].checked)
            cnt++;
    }

    if (!cnt) {
        alert(document.pressed+"할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if (!confirm("선택한 게시물을 정말 "+document.pressed+" 하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다")) {
        return false;
    }

    f.action = "./new_delete.php";

    return true;
}
</script>
<?php } ?>
<!-- } 전체게시물 목록 끝 -->