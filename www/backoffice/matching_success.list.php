<?
	$sub_menu = '500100';
	include_once('./_common.php');

	include_once (G5_ADMIN_PATH.'/admin.head.php');

$g5['title'] = '거래 현황';

$allowed_search_fields = array(
	'TR.request_company_name' => 'TR.request_company_name',
	'M_req.mb_email' => 'M_req.mb_email',
	'C_req.TEL_NO' => 'C_req.TEL_NO',
	'TR.target_company_name' => 'TR.target_company_name',
	'TR.trade_type' => 'TR.trade_type',
);
$search_field = isset($allowed_search_fields[$sfl]) ? $allowed_search_fields[$sfl] : 'TR.request_company_name';

function normalize_company_phone($raw_phone)
{
	$raw = trim((string)$raw_phone);
	if ($raw === '') return '-';

	$digits = preg_replace('/\D+/', '', $raw);
	if ($digits === '') return '-';

	$len = strlen($digits);

	// 대표번호(예: 15445487 -> 1544-5487)
	if ($len === 8 && preg_match('/^(15|16|18)\d{6}$/', $digits)) {
		return substr($digits, 0, 4) . '-' . substr($digits, 4);
	}

	// 지역번호 02
	if (strpos($digits, '02') === 0) {
		if ($len === 9) return '02-' . substr($digits, 2, 3) . '-' . substr($digits, 5, 4);
		if ($len === 10) return '02-' . substr($digits, 2, 4) . '-' . substr($digits, 6, 4);
	}

	// 휴대폰 010/011/016/017/018/019
	if (preg_match('/^01[016789]/', $digits)) {
		if ($len === 10) return substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
		if ($len === 11) return substr($digits, 0, 3) . '-' . substr($digits, 3, 4) . '-' . substr($digits, 7, 4);
	}

	// 일반 지역번호/인터넷전화(031..., 070...)
	if ($len === 10) return substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
	if ($len === 11) return substr($digits, 0, 3) . '-' . substr($digits, 3, 4) . '-' . substr($digits, 7, 4);

	// 포맷 규칙을 확정하기 어려운 길이는 원본 숫자 그대로 노출
	return $digits;
}

$sql_common = " from trade_request TR ";
$sql_search = " where 1=1 ";

if ($stx) {
	$stx_sql = sql_escape_string($stx);
	$request_type_keyword = strtoupper(trim((string)$stx));
	$request_type_normalized = preg_replace('/\s+/', '', $request_type_keyword);
	switch ($search_field) {
		case 'TR.trade_type':
			if ($request_type_normalized !== '' && (strpos($request_type_normalized, 'TM') !== false || strpos($request_type_normalized, '티엠') !== false)) {
				$sql_search .= " and upper(trim(ifnull(TR.trade_type, ''))) = 'TM' ";
			} else if ($request_type_normalized !== '' && (strpos($request_type_normalized, 'BASIC') !== false || strpos($request_type_normalized, '기본') !== false || strpos($request_type_normalized, '일반') !== false)) {
				$sql_search .= " and upper(trim(ifnull(TR.trade_type, ''))) != 'TM' ";
			} else {
				$sql_search .= " and ifnull(TR.trade_type, '') like '%{$stx_sql}%' ";
			}
			break;
		case 'M_req.mb_email':
			$sql_search .= " and exists (
				select 1
				from g5_member M_req
				where M_req.mb_id = TR.request_mb_id
				  and M_req.mb_email like '%{$stx_sql}%'
			) ";
			break;
		case 'C_req.TEL_NO':
			$sql_search .= " and (
				exists (
					select 1
					from g5_member M_req
					where M_req.mb_id = TR.request_mb_id
					  and (
						ifnull(M_req.mb_hp, '') like '%{$stx_sql}%'
						or ifnull(M_req.mb_8, '') like '%{$stx_sql}%'
					  )
				)
				or exists (
					select 1
					from company C_req
					where C_req.BZNO = TR.request_bzno
					  and C_req.TEL_NO like '%{$stx_sql}%'
				)
			) ";
			break;
		default :
			$sql_search .= " and {$search_field} like '%{$stx_sql}%' ";
			break;
	}
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
		TR.request_mb_id,
		TR.request_company_name,
		TR.request_bzno,
		TR.target_company_name,
		TR.target_bzno,
		TR.target_kedcd,
		TR.message_content,
		TR.adm_chk,
		TR.adm_memo,
		TR.chat_room_id,
		'' as req_member_email,
		'' as req_member_phone,
		null as tgt_mb_no,
		'' as tgt_member_email,
		'' as tgt_member_phone,
		'' as req_company_phone,
		'' as tgt_company_phone
		$sql_common $sql_search order by TR.tr_no desc limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);
$rows_list = array();
for ($ri=0; $row=sql_fetch_array($result); $ri++) {
	$rows_list[] = $row;
}

$request_mb_ids = array();
$request_bznos = array();
$target_kedcds = array();
for ($ri = 0; $ri < count($rows_list); $ri++) {
	$request_mb_id = trim((string)$rows_list[$ri]['request_mb_id']);
	if ($request_mb_id !== '') {
		$request_mb_ids[$request_mb_id] = true;
	}

	$request_bzno = trim((string)$rows_list[$ri]['request_bzno']);
	if ($request_bzno !== '') {
		$request_bznos[$request_bzno] = true;
	}

	$target_kedcd = trim((string)$rows_list[$ri]['target_kedcd']);
	if ($target_kedcd !== '') {
		$target_kedcds[$target_kedcd] = true;
	}
}

$request_member_map = array();
if (count($request_mb_ids) > 0) {
	$request_mb_sql = array();
	foreach (array_keys($request_mb_ids) as $request_mb_id) {
		$request_mb_sql[] = "'" . sql_escape_string($request_mb_id) . "'";
	}

	$request_member_result = sql_query(" select mb_id, mb_email, mb_hp, mb_8
		from g5_member
		where mb_id in (" . implode(',', $request_mb_sql) . ") ");
	for ($ri = 0; $row = sql_fetch_array($request_member_result); $ri++) {
		$request_member_map[$row['mb_id']] = $row;
	}
}

$request_company_map = array();
if (count($request_bznos) > 0) {
	$request_bzno_sql = array();
	foreach (array_keys($request_bznos) as $request_bzno) {
		$request_bzno_sql[] = "'" . sql_escape_string($request_bzno) . "'";
	}

	$request_company_result = sql_query(" select BZNO, TEL_NO
		from company
		where BZNO in (" . implode(',', $request_bzno_sql) . ") ");
	for ($ri = 0; $row = sql_fetch_array($request_company_result); $ri++) {
		$request_company_map[$row['BZNO']] = $row;
	}
}

$target_company_map = array();
$target_kedcd_sql = array();
$target_member_map = array();
if (count($target_kedcds) > 0) {
	foreach (array_keys($target_kedcds) as $target_kedcd) {
		$target_kedcd_sql[] = "'" . sql_escape_string($target_kedcd) . "'";
	}

	$target_company_result = sql_query(" select KEDCD, TEL_NO
		from company
		where KEDCD in (" . implode(',', $target_kedcd_sql) . ") ");
	for ($ri = 0; $row = sql_fetch_array($target_company_result); $ri++) {
		$target_company_map[$row['KEDCD']] = $row;
	}

	$target_member_sql = " select
			MM.mb_company,
			MM.mb_no,
			MM.mb_email,
			MM.mb_hp,
			MM.mb_8
		from g5_member MM
		inner join (
			select mb_company, max(mb_no) as max_mb_no
			from g5_member
			where ifnull(mb_company, '') != ''
			  and mb_company in (" . implode(',', $target_kedcd_sql) . ")
			group by mb_company
		) MX on MM.mb_company = MX.mb_company and MM.mb_no = MX.max_mb_no ";
	$target_member_result = sql_query($target_member_sql);
	for ($ri = 0; $row = sql_fetch_array($target_member_result); $ri++) {
		$target_member_map[$row['mb_company']] = $row;
	}
}

for ($ri = 0; $ri < count($rows_list); $ri++) {
	$request_mb_id = trim((string)$rows_list[$ri]['request_mb_id']);
	if ($request_mb_id !== '' && isset($request_member_map[$request_mb_id])) {
		$rows_list[$ri]['req_member_email'] = $request_member_map[$request_mb_id]['mb_email'];
		$rows_list[$ri]['req_member_phone'] = trim((string)$request_member_map[$request_mb_id]['mb_hp']) ? $request_member_map[$request_mb_id]['mb_hp'] : $request_member_map[$request_mb_id]['mb_8'];
	}

	$request_bzno = trim((string)$rows_list[$ri]['request_bzno']);
	if ($request_bzno !== '' && isset($request_company_map[$request_bzno])) {
		$rows_list[$ri]['req_company_phone'] = $request_company_map[$request_bzno]['TEL_NO'];
	}

	$target_kedcd = trim((string)$rows_list[$ri]['target_kedcd']);
	if ($target_kedcd !== '' && isset($target_company_map[$target_kedcd])) {
		$rows_list[$ri]['tgt_company_phone'] = $target_company_map[$target_kedcd]['TEL_NO'];
	}

	if ($target_kedcd !== '' && isset($target_member_map[$target_kedcd])) {
		$rows_list[$ri]['tgt_mb_no'] = $target_member_map[$target_kedcd]['mb_no'];
		$rows_list[$ri]['tgt_member_email'] = $target_member_map[$target_kedcd]['mb_email'];
		$rows_list[$ri]['tgt_member_phone'] = trim((string)$target_member_map[$target_kedcd]['mb_hp']) ? $target_member_map[$target_kedcd]['mb_hp'] : $target_member_map[$target_kedcd]['mb_8'];
	}
}

$rnumber=($total_count-($page-1)*$rows)+1;

	?>
	  <style>
	.throu {text-decoration:line-through; text-decoration-color: red;}
	.matching-success-page {
		width: 100%;
	}
	.matching-success-toolbar {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 8px;
		min-width: 0 !important;
		width: 100%;
	}
	.matching-success-toolbar > * {
		flex: 0 0 auto;
	}
	.matching-success-toolbar .ov_listall,
	.matching-success-toolbar .btn_ov01 {
		margin: 0;
		white-space: nowrap;
	}
	.matching-success-excel {
		margin-left: auto;
		white-space: nowrap;
	}
	.matching-success-search {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 8px;
		min-width: 0 !important;
	}
	.matching-success-search select,
	.matching-success-search .frm_input {
		min-height: 38px;
	}
	.matching-success-search .frm_input {
		min-width: 220px;
	}
	.matching-success-search .btn_submit {
		width: auto !important;
		min-width: 68px;
		min-height: 38px;
		padding: 0 16px !important;
		background: #17a2b8 !important;
		color: #fff !important;
		text-indent: 0 !important;
		overflow: visible !important;
		border-radius: 4px;
	}
	.matching-success-table {
		overflow-x: auto;
		-webkit-overflow-scrolling: touch;
		width: 100%;
	}
	.matching-success-table table {
		min-width: 1480px;
	}
	.matching-success-table thead .group-head {
		font-weight: 700;
		text-align: center;
	}
	.matching-success-table thead .group-head--request {
		background: #dbeafe;
		color: #1d4ed8;
	}
	.matching-success-table thead .group-head--target {
		background: #dcfce7;
		color: #15803d;
	}
	.matching-success-table th,
	.matching-success-table td {
		white-space: nowrap;
	}
	.matching-success-table tbody td:nth-child(5),
	.matching-success-table tbody td:nth-child(6),
	.matching-success-table tbody td:nth-child(7),
	.matching-success-table tbody td:nth-child(8) {
		background: #f8fbff;
	}
	.matching-success-table tbody td:nth-child(9),
	.matching-success-table tbody td:nth-child(10),
	.matching-success-table tbody td:nth-child(11),
	.matching-success-table tbody td:nth-child(12) {
		background: #f6fcf7;
	}
	.matching-success-table td:nth-child(13) {
		white-space: normal;
		min-width: 200px;
		word-break: break-word;
	}
	.matching-company-label {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 42px;
		height: 22px;
		margin-right: 6px;
		padding: 0 8px;
		border-radius: 999px;
		font-size: 11px;
		font-weight: 700;
		line-height: 1;
		vertical-align: middle;
	}
	.matching-company-label--request {
		background: #dbeafe;
		color: #1d4ed8;
	}
	.matching-company-label--target {
		background: #dcfce7;
		color: #15803d;
	}
	.matching-success-table .btn,
	.matching-success-table #chk_k {
		white-space: nowrap;
	}
	@media (max-width: 991px) {
		.matching-success-page > h2 {
			font-size: 22px;
			margin-bottom: 14px;
		}
		.matching-success-toolbar {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			align-items: stretch;
			gap: 10px;
		}
		.matching-success-toolbar > * {
			min-width: 0;
		}
		.matching-success-toolbar .ov_listall,
		.matching-success-toolbar .btn_ov01,
		.matching-success-excel {
			width: 100%;
			max-width: 100%;
			margin: 0;
		}
		.matching-success-toolbar .ov_listall {
			grid-column: 1 / -1;
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 42px;
			padding: 10px 12px;
			text-align: center;
		}
		.matching-success-toolbar .btn_ov01 {
			display: flex;
			align-items: stretch;
			height: auto;
			min-height: 44px;
			line-height: 1.3;
			overflow: hidden;
		}
		.matching-success-toolbar .btn_ov01 .ov_txt,
		.matching-success-toolbar .btn_ov01 .ov_num {
			float: none;
			display: flex;
			align-items: center;
			justify-content: center;
			min-width: 0;
			padding: 10px 8px;
			text-align: center;
			white-space: normal;
			word-break: keep-all;
			font-size: 12px;
		}
		.matching-success-excel {
			grid-column: 1 / -1;
			min-height: 42px;
		}
		.matching-success-search {
			display: grid;
			grid-template-columns: 1fr;
			gap: 10px;
		}
		.matching-success-search select,
		.matching-success-search .frm_input,
		.matching-success-search .btn_submit {
			width: 100%;
			margin: 0 !important;
		}
		.matching-success-table {
			margin: 0 -10px;
			padding: 0 10px 10px;
			width: calc(100% + 20px);
		}
		.matching-success-table table {
			min-width: 1260px;
		}
		.matching-success-table th,
		.matching-success-table td {
			font-size: 12px;
			padding-left: 6px;
			padding-right: 6px;
		}
	}
	</style>
	<div class="matching-success-page">
	 <h2><?php echo $g5['title']; ?></h2>

<div class="local_ov01 local_ov matching-success-toolbar">
	    <?php if ($page > 1) {?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>" class="ov_listall">처음으로</a><?php } ?>
	    <span class="btn_ov01"><span class="ov_txt">전체 내용</span><span class="ov_num"> <?php echo $display_count; ?>건</span></span>
		<button type="button" class="btn btn-primary btn-sm matching-success-excel" id="exl_download" onclick="$('#fsearch').attr('action','./matching_excel.php').submit();$('#fsearch').attr('action','');"><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀 다운로드</button>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch matching-success-search" method="get">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="TR.request_company_name"<?php echo get_selected($sfl, "TR.request_company_name"); ?>>요청업체</option>
    <option value="M_req.mb_email"<?php echo get_selected($sfl, "M_req.mb_email"); ?>>이메일</option>
	<option value="C_req.TEL_NO"<?php echo get_selected($sfl, "C_req.TEL_NO"); ?>>연락처</option>
	<option value="TR.target_company_name"<?php echo get_selected($sfl, "TR.target_company_name"); ?>>대상업체</option>
	<option value="TR.trade_type"<?php echo get_selected($sfl, "TR.trade_type"); ?>>요청구분</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx"  class=" frm_input">
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="tbl_head01 tbl_wrap matching-success-table">
	    <table>
	    <caption><?php echo $g5['title']; ?> 목록</caption>
	    <thead>
	    <tr>
	        <th scope="col" rowspan="2">상태</th>
			<th scope="col" rowspan="2">요청 구분</th>
			<th scope="col" rowspan="2">요청 시간</th>
			<th scope="col" rowspan="2">TM 24시간</th>
			<th scope="col" colspan="4" class="group-head group-head--request">거래 요청 보낸 기업</th>
			<th scope="col" colspan="4" class="group-head group-head--target">거래 요청 받은 기업</th>
			<th scope="col" rowspan="2" style="width:200px">메세지</th>
			<th scope="col" rowspan="2">체크</th>
			<!--th scope="col">관리</th-->
	    </tr>
	    <tr>
	        <th scope="col">보낸 업체</th>
			<th scope="col">사업자등록번호</th>
			<th scope="col">연락처</th>
			<th scope="col">이메일</th>
	        <th scope="col">받은 업체</th>
	        <th scope="col">사업자등록번호</th>
			<th scope="col">연락처</th>
			<th scope="col">이메일</th>
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
	        <td class="td_center"><span class="matching-company-label matching-company-label--request">보낸</span><?=trim((string)$v['request_company_name']) ? $v['request_company_name'] : '-'?></td>
			<td class="td_center"><?=trim((string)$v['request_bzno']) ? licenseNum($v['request_bzno']) : '-'?></td>
			<td class="td_center"><?=normalize_company_phone(trim((string)$v['req_member_phone']) ? $v['req_member_phone'] : $v['req_company_phone'])?></td>
			<td class="td_center"><?=trim((string)$v['req_member_email']) ? $v['req_member_email'] : '-'?></td>
			<td class="td_center"><span class="matching-company-label matching-company-label--target">받은</span><?=trim((string)$v['target_company_name']) ? $v['target_company_name'] : '-'?></td>
			<td class="td_center"><?=trim((string)$v['target_bzno']) ? licenseNum($v['target_bzno']) : '-'?></td>
			<td class="td_center"><?=normalize_company_phone(trim((string)$v['tgt_member_phone']) ? $v['tgt_member_phone'] : $v['tgt_company_phone'])?></td>
			<td class="td_center"><?=trim((string)$v['tgt_member_email']) ? $v['tgt_member_email'] : '-'?></td>
			<td class="td_center"><?=nl2br(htmlspecialchars((string)$v['message_content'], ENT_QUOTES, 'UTF-8'))?></td>
			<td class="td_center">
			<?if($status!="거래진행중" && $status != "거래거절"){?>
				<span id="chk_k" class="<?=$adm_chk_class?>" idx="<?=$v['tr_no']?>" chk="<?=$adm_chk_text?>"><?=$adm_chk_text?></span>
				<?if(!(int)$v['tgt_mb_no']){?><br><a href="member_form.php?ADDKEDCD=<?=$v['target_kedcd']?>" target="_blank" class="btn btn-sm btn-warning text-light">회원등록</a><?}?>
			<?}?>
			</td>

        <?php /* legacy delete action removed */ ?>
    </tr>
    <?php
    }
    if ($i == 0) {
        echo '<tr><td colspan="14" class="empty_table">자료가 한건도 없습니다.</td></tr>';
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
</div>
	<?
	include_once (G5_ADMIN_PATH.'/admin.tail.php');
	?>
