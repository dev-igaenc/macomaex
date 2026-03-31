<?php
$sub_menu = '100000';
include_once('./_common.php');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$audit_table = function_exists('admin_audit_table_name') ? admin_audit_table_name() : (G5_TABLE_PREFIX . 'admin_audit_log');
if (!function_exists('admin_audit_ensure_table') || !admin_audit_ensure_table()) {
    alert('감사로그 테이블이 없어 CSV를 내려받을 수 없습니다.');
}

$allowed_sfl = array(
    'al_action' => '액션',
    'mb_id' => '관리자ID',
    'al_ip' => 'IP',
    'al_uri' => '요청URI',
    'al_message' => '메시지'
);

$sfl = isset($_GET['sfl']) ? trim((string)$_GET['sfl']) : 'al_action';
if (!isset($allowed_sfl[$sfl])) {
    $sfl = 'al_action';
}
$stx = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$result_filter = isset($_GET['result']) ? strtoupper(trim((string)$_GET['result'])) : '';
if (!in_array($result_filter, array('', 'SUCCESS', 'FAIL', 'INFO'))) {
    $result_filter = '';
}
$date_from = isset($_GET['date_from']) ? preg_replace('/[^0-9\-]/', '', (string)$_GET['date_from']) : date('Y-m-d', strtotime('-7 days'));
$date_to = isset($_GET['date_to']) ? preg_replace('/[^0-9\-]/', '', (string)$_GET['date_to']) : date('Y-m-d');

$sql_search = " where 1=1 ";
if ($result_filter) {
    $sql_search .= " and al_result = '" . sql_real_escape_string($result_filter) . "' ";
}
if ($stx !== '') {
    $sql_search .= " and {$sfl} like '%" . sql_real_escape_string($stx) . "%' ";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
    $sql_search .= " and al_datetime >= '" . sql_real_escape_string($date_from) . " 00:00:00' ";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $sql_search .= " and al_datetime <= '" . sql_real_escape_string($date_to) . " 23:59:59' ";
}

$sql = " select
            al_id, al_datetime, mb_id, mb_name, mb_level,
            al_action, al_result, al_message, al_target, al_ip, al_method, al_uri, al_payload
         from {$audit_table}
         {$sql_search}
         order by al_id desc ";
$result = sql_query($sql, false);

$count_row = sql_fetch(" select count(*) as cnt from {$audit_table} {$sql_search} ");
$download_count = isset($count_row['cnt']) ? (int)$count_row['cnt'] : 0;
$query_ok = (bool)$result;

if (function_exists('admin_audit_log')) {
    $audit_stx = function_exists('admin_audit_cut') ? admin_audit_cut($stx, 120) : substr($stx, 0, 120);
    $audit_payload = array(
        'csv_type' => 'audit_log',
        'filters' => array(
            'date_from' => $date_from,
            'date_to' => $date_to,
            'result' => $result_filter,
            'sfl' => $sfl,
            'stx' => $audit_stx
        ),
        'download_count' => $download_count
    );

    admin_audit_log(
        'admin_csv_download:audit_log',
        'table:' . $audit_table,
        $query_ok ? 'SUCCESS' : 'FAIL',
        $query_ok ? '감사로그 CSV 다운로드' : '감사로그 CSV 조회 실패',
        $audit_payload
    );
}

if (!function_exists('audit_csv_cell')) {
    function audit_csv_cell($value, $max_len = 5000)
    {
        $value = trim((string)$value);
        $value = str_replace(array("\r\n", "\r", "\n"), ' ', $value);
        $max_len = (int)$max_len;
        if ($max_len > 0) {
            if (function_exists('mb_substr')) {
                $value = mb_substr($value, 0, $max_len, 'UTF-8');
            } else {
                $value = substr($value, 0, $max_len);
            }
        }

        if ($value !== '' && preg_match('/^[=\+\-@]/', $value)) {
            $value = "'" . $value;
        }

        return $value;
    }
}

if (!function_exists('audit_csv_datetime_cell')) {
    function audit_csv_datetime_cell($value)
    {
        $value = audit_csv_cell($value, 30);
        if ($value === '') {
            return '';
        }

        return "'" . $value;
    }
}

$filename = 'audit_log_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');
if ($out === false) {
    exit;
}

fputs($out, "\xEF\xBB\xBF");
fputcsv($out, array(
    '번호', '일시', '관리자ID', '관리자명', '관리자레벨',
    '액션', '결과', '메시지', '대상', 'IP', '메서드', '요청URI', '상세'
));

if ($result) {
    for ($i = 0; $row = sql_fetch_array($result); $i++) {
        fputcsv($out, array(
            (int)$row['al_id'],
            audit_csv_datetime_cell($row['al_datetime']),
            audit_csv_cell($row['mb_id'], 40),
            audit_csv_cell($row['mb_name'], 80),
            audit_csv_cell($row['mb_level'], 10),
            audit_csv_cell($row['al_action'], 180),
            audit_csv_cell($row['al_result'], 20),
            audit_csv_cell($row['al_message'], 500),
            audit_csv_cell($row['al_target'], 180),
            audit_csv_cell($row['al_ip'], 45),
            audit_csv_cell($row['al_method'], 10),
            audit_csv_cell($row['al_uri'], 400),
            audit_csv_cell($row['al_payload'], 5000)
        ));
    }
}

fclose($out);
exit;
