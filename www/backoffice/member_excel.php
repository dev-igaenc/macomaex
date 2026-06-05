<?php
$sub_menu = "200110";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

function member_excel_output($rows, $filename)
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Cache-Control: max-age=0');

    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");

    foreach ($rows as $row) {
        fputcsv($out, $row);
    }

    fclose($out);
    exit;
}

function member_excel_text_cell($value)
{
    $value = (string) $value;
    $escaped = str_replace('"', '""', $value);

    return '="' . $escaped . '"';
}

$sql_common = " from {$g5['member_table']} ";

$sql_search = " where (mb_level < 10 and mb_level > 1)";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_tel':
        case 'mb_hp':
            $sql_search .= " ({$sfl} like '%{$stx}') ";
            break;
        case 'mb_4':
        case 'mb_8':
            $stx2 = str_replace("-", "", $stx);
            $sql_search .= " (replace({$sfl},'-','') like '%{$stx2}%') ";
            break;
        default:
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst = "mb_datetime";
    $sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";
$sql = " select * {$sql_common} {$sql_search} {$sql_order} ";
$result = sql_query($sql);

$excel_rows = array(
    array('아이디', '법인명', '사업자등록번호', '담당자 이름', '연락처', '가입날짜')
);

for ($i = 0; $row = sql_fetch_array($result); $i++) {
    $biz_number_raw = trim((string) $row['mb_4']);
    $biz_number = preg_replace('/[^0-9]/', '', $biz_number_raw);
    $display_biz_number = $biz_number !== '' ? $biz_number_raw : '-';
    $display_company_name = $biz_number !== '' ? trim((string) $row['mb_name']) : '-';
    $display_phone = trim((string) $row['mb_8']);
    $display_joined_at = $row['mb_leave_date'] ? '탈퇴함' : trim((string) $row['mb_datetime']);

    if ($display_company_name === '') {
        $display_company_name = '-';
    }

    if ($display_phone === '') {
        $display_phone = '-';
    }

    if ($display_joined_at === '') {
        $display_joined_at = '-';
    }

    $excel_rows[] = array(
        member_excel_text_cell(trim((string) $row['mb_id'])),
        $display_company_name,
        member_excel_text_cell($display_biz_number),
        trim((string) $row['mb_6']),
        member_excel_text_cell($display_phone),
        member_excel_text_cell($display_joined_at),
    );
}

$date_string = date("Y-m-d", time());
member_excel_output($excel_rows, '회원리스트_' . $date_string);
