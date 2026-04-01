<?
	$sub_menu = '500100';
	include_once('./_common.php');

	include_once (G5_ADMIN_PATH.'/admin.head.php');

$g5['title'] = '실시간매칭';

$allowed_search_fields = array(
	'B.mb_name' => 'B.mb_name',
	'B.mb_email' => 'B.mb_email',
	'B.mb_8' => 'B.mb_8',
	'C.mb_name' => 'C.mb_name',
	'H.request_type' => 'H.request_type',
);
$search_field = isset($allowed_search_fields[$sfl]) ? $allowed_search_fields[$sfl] : 'B.mb_name';

$sql_common = " from g5_matching A
left join g5_member B on A.request_id=B.mb_id
left join g5_member C on A.receive_id=C.mb_id";
$sql_search = " where del !='D' ";

if ($stx) {
	$request_type_keyword = strtoupper(trim((string)$stx));
	$request_type_normalized = preg_replace('/\s+/', '', $request_type_keyword);
	$sql_search .= " and ( ";
	switch ($search_field) {
		case 'H.request_type':
			$latest_tm_exists_sql = "exists (
				select 1
				from g5_matching_credit_history H1
				inner join (
					select matching_no, max(id) as max_id
					from g5_matching_credit_history
					where tx_type='USE' and matching_no is not null and matching_no != ''
					group by matching_no
				) H2 on H1.matching_no = H2.matching_no and H1.id = H2.max_id
				where H1.matching_no = A.no
				  and upper(trim(ifnull(H1.request_type, ''))) = 'TM'
			)";
			if ($request_type_normalized !== '' && (strpos($request_type_normalized, 'TM') !== false || strpos($request_type_normalized, '티엠') !== false)) {
				$sql_search .= " {$latest_tm_exists_sql} ";
			} else if ($request_type_normalized !== '' && (strpos($request_type_normalized, 'BASIC') !== false || strpos($request_type_normalized, '기본') !== false || strpos($request_type_normalized, '일반') !== false)) {
				$sql_search .= " not {$latest_tm_exists_sql} ";
			} else {
				$sql_search .= " exists (
					select 1
					from g5_matching_credit_history H1
					inner join (
						select matching_no, max(id) as max_id
						from g5_matching_credit_history
						where tx_type='USE' and matching_no is not null and matching_no != ''
						group by matching_no
					) H2 on H1.matching_no = H2.matching_no and H1.id = H2.max_id
					where H1.matching_no = A.no
					  and ifnull(H1.request_type, '') like '%{$stx}%'
				) ";
			}
			break;
		default :
			$sql_search .= " ({$search_field} like '%{$stx}%') ";
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

$sql = "select *,
		C.mb_name as r_name
		$sql_common $sql_search order by no desc limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);

// 현재 페이지 대상 매칭번호만 뽑아서 최신 USE 이력을 조회 (전체 이력 집계 쿼리 회피)
$rows_list = array();
$matching_no_list = array();
for ($ri=0; $row=sql_fetch_array($result); $ri++) {
	$rows_list[] = $row;
	$matching_no = isset($row['no']) ? (int)$row['no'] : 0;
	if($matching_no > 0) $matching_no_list[] = $matching_no;
}

$history_map = array();
if(!empty($matching_no_list)) {
	$matching_no_list = array_values(array_unique($matching_no_list));
	$matching_no_in = implode(',', $matching_no_list);
	if($matching_no_in !== '') {
		$history_sql = "select H1.matching_no, H1.request_type, H1.created_at
					from g5_matching_credit_history H1
					inner join (
						select matching_no, max(id) as max_id
						from g5_matching_credit_history
						where tx_type='USE' and matching_no in ({$matching_no_in})
						group by matching_no
					) H2 on H1.matching_no = H2.matching_no and H1.id = H2.max_id";
		$history_result = sql_query($history_sql);
		for ($hi=0; $hrow=sql_fetch_array($history_result); $hi++) {
			$history_map[(string)$hrow['matching_no']] = $hrow;
		}
	}
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
    <option value="B.mb_name"<?php echo get_selected($sfl, "B.mb_name"); ?>>매칭요청</option>
    <option value="B.mb_email"<?php echo get_selected($sfl, "B.mb_email"); ?>>이메일</option>
	<option value="B.mb_8"<?php echo get_selected($sfl, "B.mb_8"); ?>>연락처</option>
	<option value="C.mb_name"<?php echo get_selected($sfl, "C.mb_name"); ?>>매칭수락</option>
	<option value="H.request_type"<?php echo get_selected($sfl, "H.request_type"); ?>>요청구분</option>
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
		<th scope="col">요청구분</th>
		<th scope="col">요청시간</th>
		<th scope="col">TM 24시간</th>
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
    <?php for ($i=0; $i<count($rows_list); $i++) {
			$v = $rows_list[$i];
			$kind = $v['rc_kind'];
			$requestCon	= getUserInfo($v['request_id']);
			$recieveCon	= getUserInfo($v['receive_id']);

			$recieveCon2	= getComapyInfo($v['KEDCD']);
			$status = "";
			$statusClass = "";
			$history_info = isset($history_map[(string)$v['no']]) ? $history_map[(string)$v['no']] : array();
			$request_type = strtoupper(trim((string)(isset($history_info['request_type']) ? $history_info['request_type'] : '')));
			if($request_type !== "TM") $request_type = "BASIC";
			$request_type_text = ($request_type === "TM") ? "TM 요청" : "일반 요청";
			$request_type_class = ($request_type === "TM") ? "btn btn-sm bg-primary text-light" : "btn btn-sm bg-secondary text-light";
			$request_time = trim((string)(isset($history_info['created_at']) ? $history_info['created_at'] : ''));
			if(!$request_time) $request_time = trim((string)$v['regdate']);
			if(!$request_time) $request_time = "-";
			$tm_elapsed_hour = 0;
			if($request_time !== "-"){
				$req_ts = strtotime($request_time);
				if($req_ts !== false){
					$tm_elapsed_hour = (int)floor((time() - $req_ts) / 3600);
					if($tm_elapsed_hour < 0) $tm_elapsed_hour = 0;
				}
			}
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
			$tm_24h_text = "-";
			$tm_24h_class = "btn btn-sm bg-light text-dark";
			if($request_type === "TM"){
				if($v['receive_approval'] == "N"){
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
													<?=$requestCon['mb_name']?>
		</td>
		<td class="td_center"><?=$requestCon['mb_4']?></td>
		<td class="td_center"><?=$requestCon['mb_6']?></td>
		<td class="td_center"><?=$requestCon['mb_7']?></td>
		<td class="td_center"><?=$requestCon['mb_email']?></td>
		<td class="td_center"><?=$requestCon['mb_8']?></td>
		<td class="td_center"><?=$recieveCon2['ENP_NM']?></td>
		<td class="td_center"><?=licenseNum($recieveCon2['BZNO']);?></td>
		<td class="td_center"><?if($recieveCon['mb_6']){?><?=$recieveCon['mb_6']?><?}else{?>-<?}?></td>
		<td class="td_center"><?if($recieveCon['mb_7']){?><?=$recieveCon['mb_7']?><?}else{?>-<?}?></td>
		<td class="td_center"><?if($recieveCon['mb_8']){?><?=$recieveCon['mb_8']?><?}else{?>-<?}?></td>
		<td class="td_center"><?if($recieveCon['mb_email']){?><?=$recieveCon['mb_email']?><?}else{?>-<?}?></td>
		<td class="td_center"><?=nl2br($v['contents'])?></td>
		<td class="td_center">
		<?if($status!="매칭완료" && $status != "매칭거절"){?>
			<span id="chk_k" class="<?=$adm_chk_class?>" idx="<?=$v['no']?>" chk="<?=$adm_chk_text?>"><?=$adm_chk_text?></span>
			<?if(!$recieveCon['mb_no']){?><br><a href="member_form.php?ADDKEDCD=<?=$v['KEDCD']?>" target="_blank" class="btn btn-sm btn-warning text-light">회원등록</a><?}?>
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
