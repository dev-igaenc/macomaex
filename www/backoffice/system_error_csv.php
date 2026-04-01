<?php
$sub_menu = '100000';
include_once('./_common.php');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$error_table = function_exists('system_error_log_table_name') ? system_error_log_table_name() : (G5_TABLE_PREFIX . 'system_error_log');
if (!function_exists('system_error_log_table_exists') || !system_error_log_table_exists()) {
    alert('에러 로그 테이블이 없어 CSV를 내려받을 수 없습니다.');
}

$allowed_sfl = array(
    'el_message' => '메시지',
    'el_file' => '파일',
    'el_request_uri' => '요청URI',
    'el_mb_id' => '관리자ID',
    'el_fingerprint' => '지문'
);

$err_sfl = isset($_GET['sfl']) ? trim((string)$_GET['sfl']) : 'el_message';
if (!isset($allowed_sfl[$err_sfl])) {
    $err_sfl = 'el_message';
}
$err_stx = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$err_level = isset($_GET['level']) ? strtoupper(trim((string)$_GET['level'])) : '';
$err_type = isset($_GET['type']) ? strtolower(trim((string)$_GET['type'])) : '';
$err_date_from = isset($_GET['date_from']) ? preg_replace('/[^0-9\-]/', '', (string)$_GET['date_from']) : date('Y-m-d', strtotime('-7 days'));
$err_date_to = isset($_GET['date_to']) ? preg_replace('/[^0-9\-]/', '', (string)$_GET['date_to']) : date('Y-m-d');

$err_where = " where 1=1 ";
if ($err_stx !== '') {
    $err_where .= " and {$err_sfl} like '%" . sql_real_escape_string($err_stx) . "%' ";
}
if ($err_level !== '') {
    $err_where .= " and el_level = '" . sql_real_escape_string($err_level) . "' ";
}
if ($err_type !== '') {
    $err_where .= " and el_type = '" . sql_real_escape_string($err_type) . "' ";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $err_date_from)) {
    $err_where .= " and el_datetime >= '" . sql_real_escape_string($err_date_from) . " 00:00:00' ";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $err_date_to)) {
    $err_where .= " and el_datetime <= '" . sql_real_escape_string($err_date_to) . " 23:59:59' ";
}

$sql = " select
            el_id, el_datetime, el_type, el_level, el_message, el_file, el_line,
            el_request_method, el_request_uri, el_mb_id, el_ip, el_fingerprint, el_payload
         from {$error_table}
         {$err_where}
         order by el_id desc ";
$result = sql_query($sql, false);

$count_row = sql_fetch(" select count(*) as cnt from {$error_table} {$err_where} ");
$download_count = isset($count_row['cnt']) ? (int)$count_row['cnt'] : 0;
$query_ok = (bool)$result;

if (function_exists('admin_audit_log')) {
    $audit_payload = array(
        'csv_type' => 'system_error',
        'filters' => array(
            'date_from' => $err_date_from,
            'date_to' => $err_date_to,
            'type' => $err_type,
            'level' => $err_level,
            'sfl' => $err_sfl,
            'stx' => function_exists('admin_audit_cut') ? admin_audit_cut($err_stx, 120) : substr($err_stx, 0, 120)
        ),
        'download_count' => $download_count
    );

    admin_audit_log(
        'admin_csv_download:system_error',
        'table:' . $error_table,
        $query_ok ? 'SUCCESS' : 'FAIL',
        $query_ok ? '시스템에러 CSV 다운로드' : '시스템에러 CSV 조회 실패',
        $audit_payload
    );
}

if (!function_exists('system_error_csv_cell')) {
    function system_error_csv_cell($value, $max_len = 5000)
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

if (!function_exists('system_error_csv_datetime_cell')) {
    function system_error_csv_datetime_cell($value)
    {
        $value = system_error_csv_cell($value, 30);
        if ($value === '') {
            return '';
        }

        return "'" . $value;
    }
}

$filename = 'system_error_log_' . date('Ymd_His') . '.csv';
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
    '번호', '일시', '타입', '레벨', '메시지', '파일', '라인',
    '요청메서드', '요청URI', '관리자ID', 'IP', 'Fingerprint', '상세'
));

if ($result) {
    for ($i = 0; $row = sql_fetch_array($result); $i++) {
        fputcsv($out, array(
            (int)$row['el_id'],
            system_error_csv_datetime_cell($row['el_datetime']),
            system_error_csv_cell($row['el_type'], 30),
            system_error_csv_cell($row['el_level'], 20),
            system_error_csv_cell($row['el_message'], 500),
            system_error_csv_cell($row['el_file'], 300),
            (int)$row['el_line'],
            system_error_csv_cell($row['el_request_method'], 10),
            system_error_csv_cell($row['el_request_uri'], 400),
            system_error_csv_cell($row['el_mb_id'], 40),
            system_error_csv_cell($row['el_ip'], 45),
            system_error_csv_cell($row['el_fingerprint'], 50),
            system_error_csv_cell($row['el_payload'], 5000)
        ));
    }
}

fclose($out);
exit;
