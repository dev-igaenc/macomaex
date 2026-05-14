<?php
$is_cli = (PHP_SAPI === 'cli');
if ($is_cli) {
    // Avoid HTTPS redirect logic in the app bootstrap when invoked from CLI cron.
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
    if (!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = 'localhost';
    if (!isset($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = '/cron/tm_overdue_slack.php';
}

include_once(__DIR__ . '/../common.php');

if (!$is_cli) {
    header('Content-Type: text/plain; charset=utf-8');
}
date_default_timezone_set('Asia/Seoul');

if (!function_exists('tm_overdue_env')) {
    function tm_overdue_env($key)
    {
        if (function_exists('macomaex_env')) {
            return trim((string)macomaex_env($key, ''));
        }

        $value = getenv($key);
        if ($value === false || $value === null) {
            return '';
        }

        return trim((string)$value);
    }
}

$expected_token = tm_overdue_env('TM_OVERDUE_SLACK_TOKEN');
if ($expected_token === '' && defined('_tm_overdue_slack_token')) {
    $expected_token = trim((string)constant('_tm_overdue_slack_token'));
}

$slack_webhook = tm_overdue_env('TM_OVERDUE_SLACK_WEBHOOK');
if ($slack_webhook === '' && defined('_tm_overdue_slack_webhook')) {
    $slack_webhook = trim((string)constant('_tm_overdue_slack_webhook'));
}

$token = '';
if ($is_cli) {
    global $argv;
    if (isset($argv[1])) {
        $token = trim((string)$argv[1]);
    }
    foreach ((array)$argv as $arg) {
        $arg = trim((string)$arg);
        if (strpos($arg, '--token=') === 0) {
            $token = substr($arg, 8);
            break;
        }
        if (strpos($arg, 'token=') === 0) {
            $token = substr($arg, 6);
            break;
        }
    }
} else {
    $token = isset($_REQUEST['token']) ? trim((string)$_REQUEST['token']) : '';
}

if ($expected_token === '' || $token !== $expected_token) {
    if (!$is_cli) {
        http_response_code(403);
    }
    echo "FORBIDDEN";
    exit;
}

if ($slack_webhook === '') {
    if (!$is_cli) {
        http_response_code(500);
    }
    echo "SLACK_WEBHOOK_NOT_CONFIGURED";
    exit;
}

$where = " A.del != 'D'
    and A.receive_approval = 'N'
    and UPPER(TRIM(IFNULL(H.request_type, ''))) = 'TM'
    and COALESCE(H.created_at, A.regdate) <= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    and IFNULL(TRIM(A.adm_chk), '') = '' ";

$count_sql = "select count(*) as cnt
    from g5_matching A
    left join credit_history H on H.id = (
        select max(id) from credit_history
        where matching_no = A.no and tx_type='USE'
    )
    where {$where}";
$count_row = sql_fetch($count_sql);
$target_count = (int)$count_row['cnt'];

if ($target_count < 1) {
    echo "OK|NO_TARGET";
    exit;
}

$detail_sql = "select
        A.no,
        A.request_id,
        A.receive_id,
        A.KEDCD,
        A.regdate,
        COALESCE(H.created_at, A.regdate) as request_time,
        TIMESTAMPDIFF(HOUR, COALESCE(H.created_at, A.regdate), NOW()) as elapsed_hour,
        B.mb_name as request_name,
        C.mb_name as receive_name
    from g5_matching A
    left join g5_member B on A.request_id = B.mb_id
    left join g5_member C on A.receive_id = C.mb_id
    left join credit_history H on H.id = (
        select max(id) from credit_history
        where matching_no = A.no and tx_type='USE'
    )
    where {$where}
    order by COALESCE(H.created_at, A.regdate) asc
    limit 20";
$detail_result = sql_query($detail_sql);

$lines = array();
$ids = array();
for ($i = 0; $row = sql_fetch_array($detail_result); $i++) {
    $ids[] = (int)$row['no'];
    $request_name = trim((string)$row['request_name']);
    $receive_name = trim((string)$row['receive_name']);
    $company_name = trim((string)$row['KEDCD']);
    if ($request_name === '') $request_name = (string)$row['request_id'];
    if ($receive_name === '') $receive_name = (string)$row['receive_id'];

    $lines[] = sprintf(
        "%d) 매칭#%d\n- 요청자: %s\n- 수신자: %s\n- 수신기업: %s\n- 요청시각: %s",
        $i + 1,
        (int)$row['no'],
        $request_name,
        $receive_name,
        $company_name,
        (string)$row['request_time']
    );
}

$header = "[TM 24시간 미응답 알림]";
$summary = "- 기준시각: " . date('Y-m-d H:i:s');
$body = implode("\n", $lines);
if ($target_count > 20) {
    $body .= "\n... 외 " . ($target_count - 20) . "건";
}

$payload = json_encode(
    array('text' => $header . "\n" . $summary . "\n\n" . $body),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

if (!function_exists('curl_init')) {
    if (!$is_cli) {
        http_response_code(500);
    }
    echo "CURL_NOT_AVAILABLE";
    exit;
}

$ch = curl_init($slack_webhook);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
$response = curl_exec($ch);
$http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response === false || $http_code < 200 || $http_code >= 300) {
    if (!$is_cli) {
        http_response_code(500);
    }
    echo "SLACK_SEND_FAIL|" . $http_code . "|" . $curl_error;
    exit;
}

if (!empty($ids)) {
    $id_list = implode(',', array_map('intval', $ids));
    sql_query("update g5_matching set adm_chk='연락필요' where no in ({$id_list})");
}

echo "OK|SENT|" . $target_count;
exit;
