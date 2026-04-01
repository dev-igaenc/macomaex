<?php
include_once('./_common.php');
if ($is_admin != 'super') exit;
// 엑셀 다운로드 함수
function array_to_excel($headers, $rows, $filename){
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // UTF-8 with BOM
    fputcsv($out, $headers, "\t");
    foreach ($rows as $row) {
        fputcsv($out, $row, "\t");
    }
    fclose($out);
}

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
left join g5_member C on A.receive_id=C.mb_id
left join (
	select H1.matching_no, H1.request_type, H1.created_at
	from g5_matching_credit_history H1
	inner join (
		select matching_no, max(id) as max_id
		from g5_matching_credit_history
		where tx_type='USE' and matching_no is not null and matching_no != ''
		group by matching_no
	) H2 on H1.matching_no = H2.matching_no and H1.id = H2.max_id
) H on H.matching_no = A.no";

$sql_search = " where del !='D' ";
if ($stx) {
    $request_type_keyword = strtoupper(trim((string)$stx));
    $request_type_normalized = preg_replace('/\s+/', '', $request_type_keyword);
    $sql_search .= " and ( ";
    switch ($search_field) {
        case 'H.request_type':
            if ($request_type_normalized !== '' && (strpos($request_type_normalized, 'TM') !== false || strpos($request_type_normalized, '티엠') !== false)) {
                $sql_search .= " (upper(trim(ifnull(H.request_type, ''))) = 'TM') ";
            } else if ($request_type_normalized !== '' && (strpos($request_type_normalized, 'BASIC') !== false || strpos($request_type_normalized, '기본') !== false || strpos($request_type_normalized, '일반') !== false)) {
                $sql_search .= " (H.matching_no is null or upper(trim(ifnull(H.request_type, ''))) != 'TM') ";
            } else {
                $sql_search .= " (ifnull(H.request_type, '') like '%{$stx}%') ";
            }
            break;
        default :
            $sql_search .= " ({$search_field} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}
$sql = "select *,
		C.mb_name as r_name,
		COALESCE(H.created_at, A.regdate) as request_time,
		TIMESTAMPDIFF(HOUR, COALESCE(H.created_at, A.regdate), NOW()) as tm_elapsed_hour
		$sql_common $sql_search order by no desc";

$result = sql_query($sql);
$headers = array(
    '상태',
    '요청구분',
    'TM 24시간',
    '매칭요청',
    '사업자등록번호',
    '담당자이름',
    '직위',
    '이메일',
    '연락처',
    '매칭수락',
    '사업자등록번호',
    '담당자이름',
    '직위',
    '연락처',
    '이메일',
    '메세지',
    '요청시간',
);
$rows = array();

for ($i=0; $v=sql_fetch_array($result); $i++) {

			$kind = $v['rc_kind'];
			$requestCon	= getUserInfo($v['request_id']);
			$recieveCon	= getUserInfo($v['receive_id']);

			$recieveCon2	= getComapyInfo($v['KEDCD']);
			$status = "";
			$statusClass = "";
			$request_type = strtoupper(trim((string)$v['request_type']));
			if($request_type !== "TM") $request_type = "BASIC";
			$request_type_text = ($request_type === "TM") ? "TM 요청" : "일반 요청";
			$request_time = trim((string)$v['request_time']);
			if(!$request_time) $request_time = trim((string)$v['regdate']);
			if(!$request_time) $request_time = "-";
			$tm_elapsed_hour = (int)$v['tm_elapsed_hour'];
			$tm_24h_text = "-";
			if($request_type === "TM"){
				if($v['receive_approval'] == "N"){
					if($tm_elapsed_hour >= 24) $tm_24h_text = "24시간 경과";
					else $tm_24h_text = "24시간 이내 ({$tm_elapsed_hour}h)";
				}else{
					$tm_24h_text = "처리완료";
				}
			}
			if($v['receive_approval'] == "N"){
				$status = "매칭요청중";
				$statusClass = "btn btn-sm bg-secondary text-secondary text-light";
			}else if($v['receive_approval'] == "Y"){
				$status = "매칭완료";
				$statusClass = "btn btn-sm bg-success text-success text-light";
			}else if($v['receive_approval'] == "R"){
				$status = "매칭거절";
				$statusClass = "btn btn-sm bg-danger text-light";
			}

			$mb_6="-";
			if($recieveCon['mb_6']){
				$mb_6=$recieveCon['mb_6'];
			}

			$mb_7="-";
			if($recieveCon['mb_7']){
				$mb_7=$recieveCon['mb_7'];
			}

			$mb_email="-";
			if($recieveCon['mb_email']){
				$mb_email=$recieveCon['mb_email'];
			}

			$mb_8="-";
			if($recieveCon['mb_8']){
				$mb_8=$recieveCon['mb_8'];
			}


	$rows[] = array(
		$status,
		$request_type_text,
		$tm_24h_text,
		$requestCon['mb_name'],
		$requestCon['mb_4'],
		$requestCon['mb_6'],
		$requestCon['mb_7'],
		$requestCon['mb_email'],
		$requestCon['mb_8'],
		$recieveCon2['ENP_NM'],
		$recieveCon2['BZNO'],
		$mb_6,
		$mb_7,
		$mb_8,
		$mb_email,
		$v['contents'],
		$request_time,
	);
}

// 엑셀 다운로드
$dateString = date("Y-m-d", time());
array_to_excel($headers, $rows, '매칭리스트_'.$dateString);
exit;
?>
