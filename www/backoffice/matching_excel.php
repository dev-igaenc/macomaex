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
        M_req.mb_6 as req_manager_name,
        M_req.mb_7 as req_manager_position,
        M_req.mb_email as req_manager_email,
        M_req.mb_8 as req_manager_phone,
        M_tgt.mb_6 as tgt_manager_name,
        M_tgt.mb_7 as tgt_manager_position,
        M_tgt.mb_8 as tgt_manager_phone,
        M_tgt.mb_email as tgt_manager_email
        $sql_common $sql_search order by TR.tr_no desc";

$result = sql_query($sql);
$headers = array(
    '상태',
    '요청 구분',
    '요청 시간',
    'TM 24시간',
    '요청 업체',
    '사업자등록번호',
    '담당자이름',
    '직위',
    '이메일',
    '연락처',
    '대상 업체',
    '사업자등록번호',
    '담당자이름',
    '직위',
    '연락처',
    '이메일',
    '메세지',
    '체크',
);
$rows = array();

for ($i=0; $v=sql_fetch_array($result); $i++) {
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

    $rows[] = array(
        $status,
        $request_type,
        $request_time,
        $tm_24h_text,
        trim((string)$v['request_company_name']) ? $v['request_company_name'] : '-',
        trim((string)$v['request_bzno']) ? licenseNum($v['request_bzno']) : '-',
        trim((string)$v['req_manager_name']) ? $v['req_manager_name'] : '-',
        trim((string)$v['req_manager_position']) ? $v['req_manager_position'] : '-',
        trim((string)$v['req_manager_email']) ? $v['req_manager_email'] : '-',
        trim((string)$v['req_manager_phone']) ? $v['req_manager_phone'] : '-',
        trim((string)$v['target_company_name']) ? $v['target_company_name'] : '-',
        trim((string)$v['target_bzno']) ? licenseNum($v['target_bzno']) : '-',
        trim((string)$v['tgt_manager_name']) ? $v['tgt_manager_name'] : '-',
        trim((string)$v['tgt_manager_position']) ? $v['tgt_manager_position'] : '-',
        trim((string)$v['tgt_manager_phone']) ? $v['tgt_manager_phone'] : '-',
        trim((string)$v['tgt_manager_email']) ? $v['tgt_manager_email'] : '-',
        (string)$v['message_content'],
        $adm_chk_text,
    );
}

// 엑셀 다운로드
$dateString = date("Y-m-d", time());
array_to_excel($headers, $rows, '거래리스트_'.$dateString);
exit;
?>
