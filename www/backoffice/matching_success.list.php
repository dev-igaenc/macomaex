<?
	$sub_menu = '500100';
	include_once('./_common.php');

	include_once (G5_ADMIN_PATH.'/admin.head.php');

$g5['title'] = '실시간매칭';

$sql_common = " from g5_matching A left join g5_member B on A.request_id=B.mb_id left join g5_member C on A.receive_id=C.mb_id";
$sql_search = " where del !='D' ";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common . $sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];
$display_count = 254;

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select *, C.mb_name as r_name  $sql_common $sql_search order by no desc limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);

$rnumber=($total_count-($page-1)*$rows)+1;

?>
  <style>
.throu {text-decoration:line-through; text-decoration-color: red;}
</style>
 <h2><?php echo $g5['title']; ?></h2>

<div class="local_ov01 local_ov">
    <?php if ($page > 1) {?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">처음으로</a><?php } ?>
    <span class="btn_ov01"><span class="ov_txt">전체 내용</span><span class="ov_num"> <?php echo $display_count; ?>건</span></span>
	&ensp;<button type="button" class="btn btn-primary btn-sm pull-right" id="exl_download" onclick="$('#fsearch').attr('action','./matching_excel.php').submit();$('#fsearch').attr('action','');"><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀 다운로드</button>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="B.mb_name"<?php echo get_selected($sfl, "B.mb_name"); ?>>매칭요청</option>
    <option value="B.mb_email"<?php echo get_selected($sfl, "B.mb_email"); ?>>이메일</option>
	<option value="B.mb_8"<?php echo get_selected($sfl, "B.mb_8"); ?>>연락처</option>
	<option value="C.mb_name"<?php echo get_selected($sfl, "C.mb_name"); ?>>매칭수락</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx"  class=" frm_input">
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">상태</th>
        <th scope="col">매칭요청</th>
		<th scope="col">사업자등록번호</th>
        <th scope="col">담당자이름</th>
		<th scope="col">직위</th>
		<th scope="col">이메일</th>
		<th scope="col">연락처</th>
        <th scope="col">매칭수락</th>
        <th scope="col">사업자등록번호</th>
		<th scope="col">담당자이름</th>
        <th scope="col">직위</th>
		<th scope="col">연락처</th>
		<th scope="col">이메일</th>
		<th scope="col" style="width:200px">메세지</th>
		<th scope="col">체크</th>
		<!--th scope="col">관리</th-->
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $v=sql_fetch_array($result); $i++) {
			$kind = $v['rc_kind'];
			$requestCon	= getUserInfo($v['request_id']);
			$recieveCon	= getUserInfo($v['receive_id']);

			$recieveCon2	= getComapyInfo($v['KEDCD']);
			$status = "";
			$statusClass = "";
			if($v['receive_approval'] == "N"){
				$status = "매칭요청중";
				$statusClass = "btn btn-sm bg-secondary text-secondary text-light";
			}else if($v['receive_approval'] == "Y"){
				$status = "매칭완료";
				$statusClass = "btn btn-sm bg-success text-light";
			}else if($v['receive_approval'] == "R"){
				$status = "매칭거절";
				$statusClass = "btn btn-sm bg-danger text-light";
			}

	
	?>
    <tr>
        <td class="td_id"><span class="<?=$statusClass?>"><?=$status?></span></td>
        <td class="td_center">
													<?=$requestCon['mb_name']?>
		</td>
		<td class="td_center"><?=$requestCon['mb_4']?></td>
		<td class="td_center"><?=$requestCon['mb_6']?></td>
		<td class="td_center"><?=$requestCon['mb_7']?></td>
		<td class="td_center"><?=$requestCon['mb_email']?></td>
		<td class="td_center"><?=$requestCon['mb_8']?></td>
		<td class="td_center"><?=$recieveCon2[ENP_NM]?></td>
		<td class="td_center"><?=licenseNum($recieveCon2[BZNO]);?></td>
		<td class="td_center"><?if($recieveCon['mb_6']){?><?=$recieveCon['mb_6']?><?}else{?>-<?}?></td>
		<td class="td_center"><?if($recieveCon['mb_7']){?><?=$recieveCon['mb_7']?><?}else{?>-<?}?></td>
		<td class="td_center"><?if($recieveCon['mb_8']){?><?=$recieveCon['mb_8']?><?}else{?>-<?}?></td>
		<td class="td_center"><?if($recieveCon['mb_email']){?><?=$recieveCon['mb_email']?><?}else{?>-<?}?></td>
		<td class="td_center"><?=nl2br($v['contents'])?></td>
		<td class="td_center">
		<?if($status!="매칭완료" && $status != "매칭거절"){?>
			<?if($v['adm_chk']){?><span id="chk_k" class="btn btn-sm btn-primary" idx="<?=$v['no']?>" chk="<?=$v['adm_chk']?>">연락완료</span><?}else{?><span id="chk_k" class="btn btn-sm btn-secondary"  idx="<?=$v['no']?>" chk="<?=$v['adm_chk']?>">연락필요</span><?}?>
			<?if(!$recieveCon['mb_no']){?><br><a href="member_form.php?ADDKEDCD=<?=$v['KEDCD']?>" target="_blank" class="btn btn-sm btn-warning text-light">회원등록</a><?}?>
		<?}?>
		</td>

        <!--td class="td_mng td_mng_l">
            <a href="./matching_success_update.php?w=d&amp;no=<?php echo $v[no]; ?>&amp;<?=$qstr?>" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($v['title']); ?> </span>삭제</a>
        </td-->
    </tr>
    <?php
    }
    if ($i == 0) {
        echo '<tr><td colspan="10" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
<script>
$("body").on("click","#chk_k",function(){
		$this=$(this);
		idx=$(this).attr("idx");
		chk=$(this).attr("chk");
		if(idx){
			$.post( "./admin_ajax.php",{
				type:'matching_admchk',
				idx:idx,
				chk:chk
			})
			.done(function(jqXHR) {
				if(jqXHR==500){
					alert("요청에 문제가 있습니다.");
				}else{
					if(jqXHR=="연락필요") {
						$this.removeClass("btn-primary").addClass("btn-secondary").text(jqXHR).attr("chk","");
					}else{
						$this.removeClass("btn-secondary").addClass("btn-primary").text(jqXHR).attr("chk","연락완료");
					}
				}
			});
		}
})
</script>
<?
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
