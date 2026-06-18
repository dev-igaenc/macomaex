<?php
include_once('./_common.php');
if ($is_admin != 'super') exit;
// 엑셀 다운로드 함수
require_once G5_PATH . '/lib/PHPExcel.php';

function excel_col_name($index) {
    $name = '';
    $index = (int)$index;
    while ($index > 0) {
        $mod = ($index - 1) % 26;
        $name = chr(65 + $mod) . $name;
        $index = (int)(($index - $mod) / 26);
    }
    return $name;
}

function array_to_excel($headers, $rows, $filename){
    $obj = new PHPExcel();
    $obj->setActiveSheetIndex(0);
    $sheet = $obj->getActiveSheet();
    $sheet->setTitle('거래 현황');

    for ($i = 0; $i < count($headers); $i++) {
        $col = excel_col_name($i + 1);
        $sheet->setCellValueExplicit($col . '1', (string)$headers[$i], PHPExcel_Cell_DataType::TYPE_STRING);
    }

    $row_no = 2;
    for ($r = 0; $r < count($rows); $r++) {
        $line = $rows[$r];
        for ($c = 0; $c < count($headers); $c++) {
            $col = excel_col_name($c + 1);
            $value = isset($line[$c]) ? (string)$line[$c] : '';
            $sheet->setCellValueExplicit($col . $row_no, $value, PHPExcel_Cell_DataType::TYPE_STRING);
        }
        $row_no++;
    }

    for ($c = 0; $c < count($headers); $c++) {
        $sheet->getColumnDimension(excel_col_name($c + 1))->setAutoSize(true);
    }

    $safe_filename = preg_replace('/[^a-zA-Z0-9_\-가-힣]/u', '_', (string)$filename);
    if (!$safe_filename) $safe_filename = 'trade_list';

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $safe_filename . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
    $writer->save('php://output');
}

$allowed_search_fields = array(
    'TR.request_company_name' => 'TR.request_company_name',
    'M_req.mb_email' => 'M_req.mb_email',
    'C_req.TEL_NO' => 'C_req.TEL_NO',
    'TR.target_company_name' => 'TR.target_company_name',
    'TR.trade_type' => 'TR.trade_type',
);
$search_field = isset($allowed_search_fields[$sfl]) ? $allowed_search_fields[$sfl] : 'TR.request_company_name';

$sql_common = " from trade_request TR ";

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
        case 'M_req.mb_email':
            $sql_search .= " exists (
                select 1
                from g5_member M_req
                where M_req.mb_id = TR.request_mb_id
                  and M_req.mb_email like '%{$stx_sql}%'
            ) ";
            break;
        case 'C_req.TEL_NO':
            $sql_search .= " (
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
            $sql_search .= " {$search_field} like '%{$stx_sql}%' ";
            break;
    }
}

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
        TR.request_mb_id
        $sql_common $sql_search order by TR.tr_no desc";

$result = sql_query($sql);
$rows_list = array();
for ($i = 0; $row = sql_fetch_array($result); $i++) {
    $rows_list[] = $row;
}

$request_mb_ids = array();
$request_bznos = array();
$target_kedcds = array();
for ($i = 0; $i < count($rows_list); $i++) {
    $request_mb_id = trim((string)$rows_list[$i]['request_mb_id']);
    if ($request_mb_id !== '') $request_mb_ids[$request_mb_id] = true;

    $request_bzno = trim((string)$rows_list[$i]['request_bzno']);
    if ($request_bzno !== '') $request_bznos[$request_bzno] = true;

    $target_kedcd = trim((string)$rows_list[$i]['target_kedcd']);
    if ($target_kedcd !== '') $target_kedcds[$target_kedcd] = true;
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
    for ($i = 0; $row = sql_fetch_array($request_member_result); $i++) {
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
    for ($i = 0; $row = sql_fetch_array($request_company_result); $i++) {
        $request_company_map[$row['BZNO']] = $row;
    }
}

$target_company_map = array();
$target_member_map = array();
if (count($target_kedcds) > 0) {
    $target_kedcd_sql = array();
    foreach (array_keys($target_kedcds) as $target_kedcd) {
        $target_kedcd_sql[] = "'" . sql_escape_string($target_kedcd) . "'";
    }

    $target_company_result = sql_query(" select KEDCD, TEL_NO
        from company
        where KEDCD in (" . implode(',', $target_kedcd_sql) . ") ");
    for ($i = 0; $row = sql_fetch_array($target_company_result); $i++) {
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
    for ($i = 0; $row = sql_fetch_array($target_member_result); $i++) {
        $target_member_map[$row['mb_company']] = $row;
    }
}

$headers = array(
    '상태',
    '요청 구분',
    '요청 시간',
    'TM 24시간',
    '거래 요청 보낸 업체',
    '사업자등록번호',
    '연락처',
    '이메일',
    '거래 요청 받은 업체',
    '사업자등록번호',
    '연락처',
    '이메일',
    '메세지',
    '체크',
);
$rows = array();

for ($i = 0; $i < count($rows_list); $i++) {
    $v = $rows_list[$i];
    $request_type = strtoupper(trim((string)$v['trade_type']));
    if($request_type !== "TM") $request_type = "BASIC";

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

    $status = "-";
    if((string)$v['approval_status'] === "0"){
        $status = "거래요청중";
    }else if((string)$v['approval_status'] === "1"){
        $status = "거래진행중";
    }else if((string)$v['approval_status'] === "2"){
        $status = "거래거절";
    }

    $tm_24h_text = "-";
    if($request_type === "TM"){
        if((string)$v['approval_status'] === "0"){
            if($tm_elapsed_hour >= 24) $tm_24h_text = "24시간 경과";
            else $tm_24h_text = "24시간 이내 {$tm_elapsed_hour}h";
        }else{
            $tm_24h_text = "처리완료";
        }
    }

    $adm_chk_text = trim((string)$v['adm_chk']);
    if(!$adm_chk_text) $adm_chk_text = "연락필요";

    $req_member_email = '';
    $req_member_phone = '';
    $request_mb_id = trim((string)$v['request_mb_id']);
    if ($request_mb_id !== '' && isset($request_member_map[$request_mb_id])) {
        $req_member_email = trim((string)$request_member_map[$request_mb_id]['mb_email']);
        $req_member_phone = trim((string)$request_member_map[$request_mb_id]['mb_hp']);
        if ($req_member_phone === '') $req_member_phone = trim((string)$request_member_map[$request_mb_id]['mb_8']);
    }

    $req_company_phone = '';
    $request_bzno = trim((string)$v['request_bzno']);
    if ($request_bzno !== '' && isset($request_company_map[$request_bzno])) {
        $req_company_phone = trim((string)$request_company_map[$request_bzno]['TEL_NO']);
    }

    $tgt_member_email = '';
    $tgt_member_phone = '';
    $target_kedcd = trim((string)$v['target_kedcd']);
    if ($target_kedcd !== '' && isset($target_member_map[$target_kedcd])) {
        $tgt_member_email = trim((string)$target_member_map[$target_kedcd]['mb_email']);
        $tgt_member_phone = trim((string)$target_member_map[$target_kedcd]['mb_hp']);
        if ($tgt_member_phone === '') $tgt_member_phone = trim((string)$target_member_map[$target_kedcd]['mb_8']);
    }

    $tgt_company_phone = '';
    if ($target_kedcd !== '' && isset($target_company_map[$target_kedcd])) {
        $tgt_company_phone = trim((string)$target_company_map[$target_kedcd]['TEL_NO']);
    }

    $rows[] = array(
        $status,
        $request_type,
        $request_time,
        $tm_24h_text,
        trim((string)$v['request_company_name']) ? $v['request_company_name'] : '-',
        trim((string)$v['request_bzno']) ? licenseNum($v['request_bzno']) : '-',
        trim((string)$req_member_phone) ? $req_member_phone : (trim((string)$req_company_phone) ? $req_company_phone : '-'),
        trim((string)$req_member_email) ? $req_member_email : '-',
        trim((string)$v['target_company_name']) ? $v['target_company_name'] : '-',
        trim((string)$v['target_bzno']) ? licenseNum($v['target_bzno']) : '-',
        trim((string)$tgt_member_phone) ? $tgt_member_phone : (trim((string)$tgt_company_phone) ? $tgt_company_phone : '-'),
        trim((string)$tgt_member_email) ? $tgt_member_email : '-',
        (string)$v['message_content'],
        $adm_chk_text,
    );
}

// 엑셀 다운로드
$dateString = date("Y-m-d", time());
array_to_excel($headers, $rows, '거래리스트_'.$dateString);
exit;
?>
