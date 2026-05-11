<?
	$sub_menu = '500100';
	include_once('./_common.php');

	include_once (G5_ADMIN_PATH.'/admin.head.php');

$g5['title'] = '거래 현황';

$allowed_search_fields = array(
	'TR.request_company_name' => 'TR.request_company_name',
	'M_req.mb_email' => 'M_req.mb_email',
	'M_req.mb_8' => 'M_req.mb_8',
	'TR.target_company_name' => 'TR.target_company_name',
	'TR.trade_type' => 'TR.trade_type',
);
$search_field = isset($allowed_search_fields[$sfl]) ? $allowed_search_fields[$sfl] : 'TR.request_company_name';

$sql_common = " from trade_request TR
left join g5_member M_req on TR.request_mb_id = M_req.mb_id
left join (
	select MM.*
	from g5_member MM
	inner join (
		select mb_company, max(mb_no) as max_mb_no
		from g5_member
		where ifnull(mb_company, '') != ''
		group by mb_company
	) MX on MM.mb_company = MX.mb_company and MM.mb_no = MX.max_mb_no
) M_tgt on M_tgt.mb_company = TR.target_kedcd";
$sql_search = " where 1=1 ";

if ($stx) {
	$stx_sql = sql_escape_string($stx);
	$request_type_keyword = strtoupper(trim((string)$stx));
	$request_type_normalized = preg_replace('/\s+/', '', $request_type_keyword);
	$sql_search .= " and ( ";
	switch ($search_field) {
		case 'TR.trade_type':
			if ($request_type_normalized !== '' && (strpos($request_type_normalized, 'TM') !== false || strpos($request_type_normalized, '티엠') !== false)) {
				$sql_search .= " upper(trim(ifnull(TR.trade_type, ''))) = 'TM' ";
			} else if ($request_type_normalized !== '' && (strpos($request_type_normalized, 'BASIC') !== false || strpos($request_type_normalized, '기본') !== false || strpos($request_type_normalized, '일반') !== false)) {
				$sql_search .= " upper(trim(ifnull(TR.trade_type, ''))) != 'TM' ";
			} else {
				$sql_search .= " ifnull(TR.trade_type, '') like '%{$stx_sql}%' ";
			}
			break;
		default :
			$sql_search .= " ({$search_field} like '%{$stx_sql}%') ";
			break;
	}
	$sql_search .= " ) ";
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common . $sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];
$display_count = $total_count;

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select
		TR.tr_no,
		TR.created_at,
		TR.trade_type,
		TR.approval_status,
		TR.request_company_name,
		TR.request_bzno,
		TR.target_company_name,
		TR.target_bzno,
		TR.target_kedcd,
		TR.message_content,
		TR.adm_chk,
		TR.adm_memo,
		TR.chat_room_id,
		M_req.mb_6 as req_manager_name,
		M_req.mb_7 as req_manager_position,
		M_req.mb_email as req_manager_email,
		M_req.mb_8 as req_manager_phone,
		M_tgt.mb_no as tgt_mb_no,
		M_tgt.mb_6 as tgt_manager_name,
		M_tgt.mb_7 as tgt_manager_position,
		M_tgt.mb_8 as tgt_manager_phone,
		M_tgt.mb_email as tgt_manager_email
		$sql_common $sql_search order by TR.tr_no desc limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);
$rows_list = array();
for ($ri=0; $row=sql_fetch_array($result); $ri++) {
	$rows_list[] = $row;
}

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
    <option value="TR.request_company_name"<?php echo get_selected($sfl, "TR.request_company_name"); ?>>요청업체</option>
    <option value="M_req.mb_email"<?php echo get_selected($sfl, "M_req.mb_email"); ?>>이메일</option>
	<option value="M_req.mb_8"<?php echo get_selected($sfl, "M_req.mb_8"); ?>>연락처</option>
	<option value="TR.target_company_name"<?php echo get_selected($sfl, "TR.target_company_name"); ?>>대상업체</option>
	<option value="TR.trade_type"<?php echo get_selected($sfl, "TR.trade_type"); ?>>요청구분</option>
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
		<th scope="col">요청 구분</th>
		<th scope="col">요청 시간</th>
		<th scope="col">TM 24시간</th>
        <th scope="col">요청 업체</th>
		<th scope="col">사업자등록번호</th>
        <th scope="col">담당자 이름</th>
		<th scope="col">직위</th>
		<th scope="col">이메일</th>
		<th scope="col">연락처</th>
        <th scope="col">대상 업체</th>
        <th scope="col">사업자등록번호</th>
		<th scope="col">담당자 이름</th>
        <th scope="col">직위</th>
		<th scope="col">연락처</th>
		<th scope="col">이메일</th>
		<th scope="col" style="width:200px">메세지</th>
		<th scope="col">체크</th>
		<!--th scope="col">관리</th-->
    </tr>
    </thead>
    <tbody>
	    <?php for ($i=0; $i<count($rows_list); $i++) {
				$v = $rows_list[$i];
				$status = "";
				$statusClass = "";
				$request_type = strtoupper(trim((string)$v['trade_type']));
				if($request_type !== "TM") $request_type = "BASIC";
				$request_type_text = $request_type;
				$request_type_class = ($request_type === "TM") ? "btn btn-sm bg-primary text-light" : "btn btn-sm bg-secondary text-light";
				$request_time = trim((string)$v['created_at']);
				if(!$request_time) $request_time = "-";
				$tm_elapsed_hour = 0;
				if($request_time !== "-"){
					$req_ts = strtotime($request_time);
					if($req_ts !== false){
						$tm_elapsed_hour = (int)floor((time() - $req_ts) / 3600);
						if($tm_elapsed_hour < 0) $tm_elapsed_hour = 0;
					}
				}
				if((string)$v['approval_status'] === "0"){
					$status = "거래요청중";
					$statusClass = "btn btn-sm bg-secondary text-secondary text-light";
				}else if((string)$v['approval_status'] === "1"){
					$status = "거래진행중";
					$statusClass = "btn btn-sm bg-success text-light";
				}else if((string)$v['approval_status'] === "2"){
					$status = "거래거절";
					$statusClass = "btn btn-sm bg-danger text-light";
				}else{
					$status = "-";
					$statusClass = "btn btn-sm bg-light text-dark";
				}
				$tm_24h_text = "-";
				$tm_24h_class = "btn btn-sm bg-light text-dark";
				if($request_type === "TM"){
					if((string)$v['approval_status'] === "0"){
						if($tm_elapsed_hour >= 24){
							$tm_24h_text = "24시간 경과";
							$tm_24h_class = "btn btn-sm bg-danger text-light";
					}else{
						$tm_24h_text = "24시간 이내 {$tm_elapsed_hour}h";
						$tm_24h_class = "btn btn-sm bg-info text-light";
					}
				}else{
					$tm_24h_text = "처리완료";
					$tm_24h_class = "btn btn-sm bg-success text-light";
				}
			}
			$adm_chk_text = trim((string)$v['adm_chk']);
			if(!$adm_chk_text) $adm_chk_text = "연락필요";
			$adm_chk_class = "btn btn-sm btn-secondary";
			if($adm_chk_text == "진행중") $adm_chk_class = "btn btn-sm btn-info text-light";
			else if($adm_chk_text == "연락완료") $adm_chk_class = "btn btn-sm btn-primary";

	
		?>
	    <tr>
	        <td class="td_id"><span class="<?=$statusClass?>"><?=$status?></span></td>
			<td class="td_center"><span class="<?=$request_type_class?>"><?=$request_type_text?></span></td>
			<td class="td_center"><?=$request_time?></td>
			<td class="td_center"><span class="<?=$tm_24h_class?>"><?=$tm_24h_text?></span></td>
	        <td class="td_center">
														<?=trim((string)$v['request_company_name']) ? $v['request_company_name'] : '-'?>
			</td>
			<td class="td_center"><?=trim((string)$v['request_bzno']) ? licenseNum($v['request_bzno']) : '-'?></td>
			<td class="td_center"><?=trim((string)$v['req_manager_name']) ? $v['req_manager_name'] : '-'?></td>
			<td class="td_center"><?=trim((string)$v['req_manager_position']) ? $v['req_manager_position'] : '-'?></td>
			<td class="td_center"><?=trim((string)$v['req_manager_email']) ? $v['req_manager_email'] : '-'?></td>
			<td class="td_center"><?=trim((string)$v['req_manager_phone']) ? $v['req_manager_phone'] : '-'?></td>
			<td class="td_center"><?=trim((string)$v['target_company_name']) ? $v['target_company_name'] : '-'?></td>
			<td class="td_center"><?=trim((string)$v['target_bzno']) ? licenseNum($v['target_bzno']) : '-'?></td>
			<td class="td_center"><?=trim((string)$v['tgt_manager_name']) ? $v['tgt_manager_name'] : '-'?></td>
			<td class="td_center"><?=trim((string)$v['tgt_manager_position']) ? $v['tgt_manager_position'] : '-'?></td>
			<td class="td_center"><?=trim((string)$v['tgt_manager_phone']) ? $v['tgt_manager_phone'] : '-'?></td>
			<td class="td_center"><?=trim((string)$v['tgt_manager_email']) ? $v['tgt_manager_email'] : '-'?></td>
			<td class="td_center"><?=nl2br((string)$v['message_content'])?></td>
			<td class="td_center">
			<?if($status!="거래진행중" && $status != "거래거절"){?>
				<span id="chk_k" class="<?=$adm_chk_class?>" idx="<?=$v['tr_no']?>" chk="<?=$adm_chk_text?>"><?=$adm_chk_text?></span>
				<?if(!(int)$v['tgt_mb_no']){?><br><a href="member_form.php?ADDKEDCD=<?=$v['target_kedcd']?>" target="_blank" class="btn btn-sm btn-warning text-light">회원등록</a><?}?>
			<?}?>
			</td>

        <!--td class="td_mng td_mng_l">
            <a href="./matching_success_update.php?w=d&amp;no=<?php echo $v['no']; ?>&amp;<?=$qstr?>" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($v['title']); ?> </span>삭제</a>
        </td-->
    </tr>
    <?php
    }
    if ($i == 0) {
        echo '<tr><td colspan="18" class="empty_table">자료가 한건도 없습니다.</td></tr>';
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
					var nextClass = "btn btn-sm btn-secondary";
					if(jqXHR=="진행중") nextClass = "btn btn-sm btn-info text-light";
					else if(jqXHR=="연락완료") nextClass = "btn btn-sm btn-primary";
					$this.attr("class", nextClass).text(jqXHR).attr("chk", jqXHR);
				}
			});
		}
})
</script>
<?
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
