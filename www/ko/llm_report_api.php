<?php
/**
 * /ko/llm_report_api.php
 * LLM 기반 기업 추천 심층 분석 보고서 생성 API
 *
 * POST JSON body:
 * {
 *   "data": {
 *     source_enp_nm, source_bzc_cd, source_bzc_nm, source_sido, source_sze,
 *     source_mpd, source_sales, source_cust_names[], source_supp_names[],
 *     trade_type: 1|2,
 *     buy_top1: {...},
 *     sell_top1: {...}
 *   }
 * }
 */

set_time_limit(60);
ignore_user_abort(true);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// _common.php 상대경로 경고 출력(<br><b>...) 방지를 위해 루트로 이동 후 include
chdir(__DIR__ . '/../');
include_once(__DIR__ . '/../_common.php');
include_once(__DIR__ . '/helper/llm_report_trade_helper.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method Not Allowed'), JSON_UNESCAPED_UNICODE);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(array('error' => '잘못된 요청 형식입니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$d = isset($body['data']) && is_array($body['data']) ? $body['data'] : array();

// 추천 목록에서 선택한 컨텍스트를 상세 분석 요청까지 유지한다.
// 이 단계에서는 입력을 정규화만 하고, 리포트 내용 및 캐시 키 반영은 후속 단계에서 처리한다.
$d['selected_target_keywords'] = array_values(array_unique(array_filter(array_map('trim', is_array($d['selected_target_keywords'] ?? null) ? $d['selected_target_keywords'] : array()))));
$d['selected_partner_bzc_cds'] = array_values(array_unique(array_filter(array_map(function ($value) {
    return strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)$value), 0, 10));
}, is_array($d['selected_partner_bzc_cds'] ?? null) ? $d['selected_partner_bzc_cds'] : array()))));
$d['recommendation_mode'] = trim((string)($d['recommendation_mode'] ?? 'full'));
if (!in_array($d['recommendation_mode'], array('keyword', 'full'), true)) $d['recommendation_mode'] = 'full';
$d['trace_id'] = substr(preg_replace('/[^A-Za-z0-9_-]/', '', (string)($d['trace_id'] ?? '')), 0, 64);

$tradeType = (int)($d['trade_type'] ?? 1);
$isSell = ($tradeType === 2);
$rec = $isSell ? ($d['sell_top1'] ?? null) : ($d['buy_top1'] ?? null);
// 상세에서 trade_type에 맞는 추천 1건을 우선 사용하고, 없으면 반대 버킷으로 fallback
if (empty($rec)) {
    $rec = $isSell ? ($d['buy_top1'] ?? null) : ($d['sell_top1'] ?? null);
    $isSell = !$isSell;
}
if (empty($rec) || !is_array($rec)) {
    http_response_code(400);
    echo json_encode(array('error' => 'ML 추천 데이터가 없습니다. 기업 목록에서 추천 결과를 먼저 조회하세요.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$tradeLabel = $isSell ? '판매처' : '구매처';

function llm_env($name, $default = '') {
    if (function_exists('macomaex_env')) {
        return (string)macomaex_env($name, $default);
    }
    $v = getenv($name);
    if ($v === false || $v === null || $v === '') return (string)$default;
    return (string)$v;
}

function llm_report_compact_log_message($message, $maxLength = 255) {
    $message = trim((string)$message);
    if ($message === '') return '';

    $message = preg_replace('/\s+/u', ' ', $message);
    $maxLength = (int)$maxLength;
    if ($maxLength < 1) return $message;

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($message, 'UTF-8') > $maxLength) {
            return mb_substr($message, 0, $maxLength - 1, 'UTF-8') . '…';
        }
        return $message;
    }

    if (strlen($message) > $maxLength) {
        return substr($message, 0, $maxLength - 3) . '...';
    }
    return $message;
}

function llm_report_log_directory_path() {
    return dirname(__DIR__) . '/logs/llm_report';
}

function llm_report_log_file_path($dateYmd = '') {
    $dateYmd = preg_replace('/[^0-9]/', '', (string)$dateYmd);
    if ($dateYmd === '') $dateYmd = date('Ymd');
    return llm_report_log_directory_path() . '/llm_report_log_' . $dateYmd . '.php';
}

function llm_report_slack_webhook() {
    $webhook = trim((string)llm_env('LLM_REPORT_SLACK_WEBHOOK', ''));
    if ($webhook !== '') return $webhook;

    $webhook = trim((string)llm_env('SYSTEM_ERROR_SLACK_WEBHOOK', ''));
    if ($webhook !== '') return $webhook;

    $webhook = trim((string)llm_env('TM_OVERDUE_SLACK_WEBHOOK', ''));
    if ($webhook !== '') return $webhook;

    if (defined('_system_error_slack_webhook')) {
        $webhook = trim((string)_system_error_slack_webhook);
        if ($webhook !== '') return $webhook;
    }
    if (defined('_tm_overdue_slack_webhook')) {
        $webhook = trim((string)_tm_overdue_slack_webhook);
        if ($webhook !== '') return $webhook;
    }

    return '';
}

function llm_report_slack_notify($sourceKedcd, $targetKedcd, $tradeType, $onceKey, $payload = array()) {
    $payload = is_array($payload) ? $payload : array();
    $status = strtoupper(trim((string)($payload['status'] ?? 'ERROR')));
    $stage = trim((string)($payload['stage'] ?? ''));
    if ($status !== 'ERROR' || $stage === 'credit') return false;

    $webhook = llm_report_slack_webhook();
    if ($webhook === '' || !function_exists('curl_init')) return false;

    $sourceKedcd = preg_replace('/\D/', '', (string)$sourceKedcd);
    $targetKedcd = preg_replace('/\D/', '', (string)$targetKedcd);
    $tradeType = ((int)$tradeType === 2) ? 2 : 1;
    $tradeLabel = ($tradeType === 2) ? '판매처' : '구매처';
    $onceKey = trim((string)$onceKey);
    $provider = trim((string)($payload['provider'] ?? ''));
    $model = trim((string)($payload['model'] ?? ''));
    $httpCode = isset($payload['http_code']) ? (int)$payload['http_code'] : 0;
    $message = llm_report_compact_log_message((string)($payload['message'] ?? llm_report_log_default_message($status, $payload)), 500);
    $creditCharged = !empty($payload['credit_charged']) ? 'Y' : 'N';
    $uid = trim((string)($payload['uid'] ?? ($GLOBALS['member']['mb_id'] ?? '')));
    $host = trim((string)($_SERVER['HTTP_HOST'] ?? 'CLI'));
    $requestUri = trim((string)($_SERVER['REQUEST_URI'] ?? ''));

    $text = "[AI 분석 오류 알림]"
        . "\n- site: " . ($host !== '' ? $host : '-')
        . "\n- user: " . ($uid !== '' ? $uid : '-')
        . "\n- stage: " . ($stage !== '' ? $stage : '-')
        . "\n- provider: " . ($provider !== '' ? $provider : '-')
        . "\n- model: " . ($model !== '' ? $model : '-')
        . "\n- http_code: " . ($httpCode > 0 ? $httpCode : '-')
        . "\n- trade_type: " . $tradeLabel
        . "\n- source_kedcd: " . ($sourceKedcd !== '' ? $sourceKedcd : '-')
        . "\n- target_kedcd: " . ($targetKedcd !== '' ? $targetKedcd : '-')
        . "\n- credit_charged: " . $creditCharged
        . "\n- once_key: " . ($onceKey !== '' ? $onceKey : '-')
        . "\n- uri: " . ($requestUri !== '' ? $requestUri : '-')
        . "\n- message: " . ($message !== '' ? $message : '-');

    $body = json_encode(array('text' => $text), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($body === false) return false;

    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $response = curl_exec($ch);
    $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return !($response === false || $http_code < 200 || $http_code >= 300);
}

function llm_report_log_default_message($status, $payload = array()) {
    $status = strtoupper(trim((string)$status));
    $stage = trim((string)($payload['stage'] ?? ''));

    if ($status === 'SUCCESS') return 'AI 기업 분석 보고서 생성 완료';
    if ($stage === 'env') return 'LLM API 키 설정 오류';
    if ($stage === 'credit') return 'AI 분석 크레딧 처리 오류';
    if ($stage === 'gemini_connect' || $stage === 'openai_connect') return 'LLM 연결 실패';
    if ($stage === 'gemini_http' || $stage === 'openai_http') return 'LLM API 응답 오류';
    if ($stage === 'extract') return '보고서 텍스트 추출 실패';

    return 'AI 기업 분석 보고서 생성 중 오류';
}

function llm_report_write_daily_file_log($sourceKedcd, $targetKedcd, $tradeType, $onceKey, $status, $payload = array()) {
    $payload = is_array($payload) ? $payload : array('value' => $payload);
    $status = strtoupper(trim((string)$status));
    if (!in_array($status, array('SUCCESS', 'ERROR', 'SKIP'), true)) $status = 'ERROR';

    $dir = llm_report_log_directory_path();
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;
    }

    $filePath = llm_report_log_file_path();
    $isNewFile = !file_exists($filePath);
    $loggedAt = date('Y-m-d H:i:s');

    $report = (string)($payload['report'] ?? '');
    $payloadForFile = $payload;
    if (isset($payloadForFile['report'])) unset($payloadForFile['report']);
    $payloadJson = empty($payloadForFile)
        ? '{}'
        : json_encode($payloadForFile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payloadJson === false) $payloadJson = '{}';

    $message = llm_report_compact_log_message((string)($payload['message'] ?? ''), 300);
    if ($message === '') {
        $message = llm_report_log_default_message($status, $payload);
    }

    $reportLength = 0;
    if ($report !== '') {
        $reportLength = function_exists('mb_strlen')
            ? (int)mb_strlen($report, 'UTF-8')
            : (int)strlen($report);
    }

    $fields = array(
        'event_status' => $status,
        'stage' => trim((string)($payload['stage'] ?? '')),
        'source_kedcd' => preg_replace('/\D/', '', (string)$sourceKedcd),
        'target_kedcd' => preg_replace('/\D/', '', (string)$targetKedcd),
        'trade_type' => ((int)$tradeType === 2 ? 2 : 1),
        'once_key' => trim((string)$onceKey),
        'provider' => trim((string)($payload['provider'] ?? '')),
        'model' => trim((string)($payload['model'] ?? '')),
        'http_code' => isset($payload['http_code']) ? (int)$payload['http_code'] : '',
        'elapsed_ms' => isset($payload['elapsed_ms']) ? (int)$payload['elapsed_ms'] : '',
        'credit_charged' => isset($payload['credit_charged']) ? (int)!empty($payload['credit_charged']) : '',
        'reused_within_window' => isset($payload['reused_within_window']) ? (int)!empty($payload['reused_within_window']) : '',
        'report_length' => $reportLength > 0 ? $reportLength : '',
        'message' => $message,
        'request_uri' => trim((string)($_SERVER['REQUEST_URI'] ?? '')),
        'client_ip' => trim((string)($_SERVER['REMOTE_ADDR'] ?? '')),
        'user_agent' => llm_report_compact_log_message((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 300),
        'payload_json' => $payloadJson,
    );

    $allowZeroKeys = array('http_code', 'elapsed_ms', 'credit_charged', 'reused_within_window');

    $lines = array();
    if ($isNewFile) {
        $lines[] = "<?php exit; ?>";
        $lines[] = '';
    }
    $lines[] = '[' . $loggedAt . ']';
    foreach ($fields as $key => $value) {
        if ($value === '') continue;
        if ($value === 0 && !in_array($key, $allowZeroKeys, true)) continue;
        $lines[] = $key . ': ' . $value;
    }
    $lines[] = '---';
    $lines[] = '';

    $body = implode(PHP_EOL, $lines);
    return @file_put_contents($filePath, $body, FILE_APPEND | LOCK_EX) !== false;
}

// 동일 분석(회원/의뢰사/대상기업/거래방향) 재사용 판정용 키
function llm_report_build_once_key($memberId, $sourceKedcd, $targetKedcd, $tradeType) {
    $memberId = trim((string)$memberId);
    $sourceKedcd = preg_replace('/\D/', '', (string)$sourceKedcd);
    $targetKedcd = preg_replace('/\D/', '', (string)$targetKedcd);
    $tradeType = ((int)$tradeType === 2) ? 2 : 1;
    if ($memberId === '' || $sourceKedcd === '' || $targetKedcd === '') return '';
    $serviceCode = 'AI_COMPANY_REPORT';
    return hash('sha256', $serviceCode . ':' . $memberId . ':' . $sourceKedcd . ':' . $targetKedcd . ':' . $tradeType);
}

// 사용 이력이 최근 N개월 내 존재하면 재차감 없이 재조회 허용
function llm_report_has_recent_use($memberId, $onceKey, $months = 6) {
    $memberId = trim((string)$memberId);
    $onceKey = trim((string)$onceKey);
    $months = (int)$months;
    if ($months < 1) $months = 1;
    if ($memberId === '' || $onceKey === '') return false;
    $uidSql = sql_escape_string($memberId);
    $onceKeySql = sql_escape_string($onceKey);
    $serviceCodeSql = sql_escape_string('AI_COMPANY_REPORT');
    $row = sql_fetch("select id from credit_history where uid='{$uidSql}' and once_key='{$onceKeySql}' and tx_type='USE' and service_code='{$serviceCodeSql}' and created_at >= date_sub(now(), interval {$months} month) limit 1");
    return !empty($row['id']);
}

// 동시 요청 중복차감 방지용 DB lock 키
function llm_report_lock_name($onceKey) {
    $onceKey = preg_replace('/[^0-9A-Za-z]/', '', (string)$onceKey);
    if ($onceKey === '') return '';
    return 'mcm_llm_' . substr($onceKey, 0, 32);
}

function llm_report_lock_acquire($onceKey, $timeout = 0) {
    $lockName = llm_report_lock_name($onceKey);
    if ($lockName === '') return '';
    $timeout = (int)$timeout;
    if ($timeout < 0) $timeout = 0;
    $row = sql_fetch("select GET_LOCK('" . sql_escape_string($lockName) . "', {$timeout}) as lock_ok");
    if (isset($row['lock_ok']) && (int)$row['lock_ok'] === 1) return $lockName;
    return '';
}

function llm_report_lock_release($lockName) {
    $lockName = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$lockName);
    if ($lockName === '') return;
    sql_fetch("select RELEASE_LOCK('" . sql_escape_string($lockName) . "') as release_ok");
}

// AI 보고서 최초 1회만 1크레딧 차감하고, 동일 once_key는 캐시 hit로 간주한다.
function llm_report_consume_credit_once($memberId, $sourceKedcd, $targetKedcd, $tradeType, $creditCost = 1) {
    $memberId = trim((string)$memberId);
    $sourceKedcd = preg_replace('/\D/', '', (string)$sourceKedcd);
    $targetKedcd = preg_replace('/\D/', '', (string)$targetKedcd);
    $tradeType = ((int)$tradeType === 2) ? 2 : 1;
    $creditCost = (int)$creditCost;
    if ($creditCost < 1) $creditCost = 1;

    if ($memberId === '' || $sourceKedcd === '' || $targetKedcd === '') {
        return array(
            'success' => false,
            'message' => '분석 대상 기업 정보가 올바르지 않습니다.',
        );
    }

    $serviceCode = 'AI_COMPANY_REPORT';
    $serviceCodeSql = sql_escape_string($serviceCode);
    $uidSql = sql_escape_string($memberId);
    $targetKedcdSql = sql_escape_string($targetKedcd);
    $onceKey = llm_report_build_once_key($memberId, $sourceKedcd, $targetKedcd, $tradeType);
    $onceKeySql = sql_escape_string($onceKey);

    $lockName = llm_report_lock_acquire($onceKey, 3);
    if ($lockName === '') {
        return array(
            'success' => false,
            'message' => '요청이 많아 처리가 지연되고 있습니다. 잠시 후 다시 시도해 주세요.',
            'status' => 503,
        );
    }

    $already = sql_fetch("select id from credit_history where once_key='{$onceKeySql}' and tx_type='USE' and service_code='{$serviceCodeSql}' limit 1");
    if (!empty($already['id'])) {
        llm_report_lock_release($lockName);
        return array(
            'success' => true,
            'charged' => false,
            'once_key' => $onceKey,
            'credit_cost' => $creditCost,
        );
    }

    $remain = credit_ledger_get_balance($memberId);
    if ($remain < $creditCost) {
        llm_report_lock_release($lockName);
        return array(
            'success' => false,
            'message' => 'AI 분석 크레딧이 부족합니다. (필요: ' . $creditCost . ')',
            'status' => 402,
            'plan_required' => true,
        );
    }

    sql_query('START TRANSACTION');
    $ledgerLast = credit_ledger_get_latest_row($memberId, true);
    $currentCredit = isset($ledgerLast['credit_after']) ? (int)$ledgerLast['credit_after'] : 0;
    if ($currentCredit < $creditCost) {
        sql_query('ROLLBACK');
        llm_report_lock_release($lockName);
        return array(
            'success' => false,
            'message' => 'AI 분석 크레딧이 부족합니다. (필요: ' . $creditCost . ')',
            'status' => 402,
            'plan_required' => true,
        );
    }
    $creditAfter = $currentCredit - $creditCost;
    $memo = addslashes('AI 기업 분석');

    $historySql = "insert into credit_history set
        uid='{$uidSql}',
        tx_type='USE',
        service_code='{$serviceCodeSql}',
        credit_delta='-{$creditCost}',
        credit_after='{$creditAfter}',
        order_no=NULL,
        tr_no='',
        target_kedcd='{$targetKedcdSql}',
        once_key='{$onceKeySql}',
        memo='{$memo}',
        request_type=NULL,
        created_at=now()";
    $ins = sql_query($historySql, false);
    if (!$ins) {
        sql_query('ROLLBACK');
        llm_report_lock_release($lockName);
        return array(
            'success' => false,
            'message' => '크레딧 이력 저장에 실패했습니다.',
            'status' => 500,
        );
    }
    $useHistoryId = (int)sql_insert_id();
    // USE 1건을 FIFO LOT 차감으로 분해해 실제 사용 LOT를 기록한다.
    $lotUse = credit_lot_allocate_use($memberId, $useHistoryId, $creditCost);
    if (empty($lotUse['ok'])) {
        sql_query('ROLLBACK');
        llm_report_lock_release($lockName);
        $lotMsg = isset($lotUse['message']) ? (string)$lotUse['message'] : '';
        return array(
            'success' => false,
            'message' => ($lotMsg === 'not enough lot remain') ? 'AI 분석 크레딧이 부족합니다. (필요: ' . $creditCost . ')' : '크레딧 차감 처리 중 오류가 발생했습니다.',
            'status' => 500,
        );
    }

    sql_query('COMMIT');
    llm_report_lock_release($lockName);

    return array(
        'success' => true,
        'charged' => true,
        'once_key' => $onceKey,
        'credit_cost' => $creditCost,
        'credit_after' => $creditAfter,
    );
}

// LLM 실패 시 같은 요청에서 차감된 1크레딧을 즉시 복원한다.
function llm_report_refund_credit_once($memberId, $onceKey, $creditCost = 1) {
    $memberId = trim((string)$memberId);
    $onceKey = trim((string)$onceKey);
    $creditCost = (int)$creditCost;
    if ($creditCost < 1 || $memberId === '' || $onceKey === '') return;

    $serviceCodeSql = sql_escape_string('AI_COMPANY_REPORT');
    $uidSql = sql_escape_string($memberId);
    $onceKeySql = sql_escape_string($onceKey);

    $lockName = llm_report_lock_acquire($onceKey, 1);
    if ($lockName === '') return;

    sql_query('START TRANSACTION');
    $useRow = sql_fetch("select id from credit_history where uid='{$uidSql}' and once_key='{$onceKeySql}' and tx_type='USE' and service_code='{$serviceCodeSql}' limit 1");
    $useHistoryId = isset($useRow['id']) ? (int)$useRow['id'] : 0;
    if ($useHistoryId > 0) {
        // 실패 환불 시 기존 USE의 LOT 차감부터 원복한다.
        $release = credit_lot_release_use($useHistoryId);
        if (empty($release['ok'])) {
            sql_query('ROLLBACK');
            llm_report_lock_release($lockName);
            return;
        }
    }
    $alreadyAdjust = sql_fetch("select id from credit_history where uid='{$uidSql}' and once_key='{$onceKeySql}' and tx_type='ADJUST' and service_code='CREDIT_ADJUST' limit 1");
    if ($useHistoryId > 0 && empty($alreadyAdjust['id'])) {
        $ledgerLast = credit_ledger_get_latest_row($memberId, true);
        $currentCredit = isset($ledgerLast['credit_after']) ? (int)$ledgerLast['credit_after'] : 0;
        $creditAfter = $currentCredit + $creditCost;
        $memo = addslashes('AI 분석 실패 복원');
        sql_query("insert into credit_history set
            uid='{$uidSql}',
            tx_type='ADJUST',
            service_code='CREDIT_ADJUST',
            credit_delta='{$creditCost}',
            credit_after='{$creditAfter}',
            order_no=NULL,
            tr_no='',
            target_kedcd='',
            once_key='{$onceKeySql}',
            memo='{$memo}',
            request_type=NULL,
            created_at=now()", false);
    }
    sql_query('COMMIT');
    llm_report_lock_release($lockName);
}

function llm_report_find_bizno_by_kedcd($kedcd) {
    static $cache = array();
    $kedcd = preg_replace('/\D/', '', (string)$kedcd);
    if ($kedcd === '') return '';
    if (isset($cache[$kedcd])) return $cache[$kedcd];
    $kedcdSql = sql_escape_string($kedcd);
    // 로그 테이블 c_bizno를 채우기 위해 의뢰사 KEDCD -> BZNO 변환
    $row = sql_fetch("select BZNO from company where KEDCD='{$kedcdSql}' order by KEDCD limit 1");
    $bizno = preg_replace('/\D/', '', (string)($row['BZNO'] ?? ''));
    $cache[$kedcd] = $bizno;
    return $bizno;
}

// LLM 호출 결과(성공/실패)를 company_llm_log에 저장
function llm_report_log_write($sourceKedcd, $targetKedcd, $tradeType, $onceKey, $payload) {
    $sourceKedcd = preg_replace('/\D/', '', (string)$sourceKedcd);
    $targetKedcd = preg_replace('/\D/', '', (string)$targetKedcd);
    $tradeType = ((int)$tradeType === 2) ? 2 : 1;
    $onceKey = trim((string)$onceKey);
    $bizno = llm_report_find_bizno_by_kedcd($sourceKedcd);
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    // 성공/실패 공통 payload를 JSON 문자열로 저장
    $content = is_array($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string)$payload;
    if ($content === false || $content === null || $content === '') $content = '{}';

    $biznoSql = sql_escape_string($bizno);
    $sourceSql = sql_escape_string($sourceKedcd);
    $targetSql = sql_escape_string($targetKedcd);
    $onceKeySql = sql_escape_string($onceKey);
    $contentSql = sql_escape_string($content);
    $ipSql = sql_escape_string($ip);

    $result = sql_query("insert into company_llm_log set
        c_bizno='{$biznoSql}',
        c_kedcd='{$sourceSql}',
        c_target_kedcd='{$targetSql}',
        c_trade_type='{$tradeType}',
        c_once_key='{$onceKeySql}',
        c_content='{$contentSql}',
        c_datetime=now(),
        c_ip='{$ipSql}'", false);

    $status = strtoupper(trim((string)($payload['status'] ?? 'ERROR')));
    llm_report_write_daily_file_log($sourceKedcd, $targetKedcd, $tradeType, $onceKey, $status, is_array($payload) ? $payload : array());
    llm_report_slack_notify($sourceKedcd, $targetKedcd, $tradeType, $onceKey, is_array($payload) ? $payload : array());

    return $result;
}

function llm_report_extract_success_report_from_content($rawContent) {
    $rawContent = trim((string)$rawContent);
    if ($rawContent === '') return array();
    $decoded = json_decode($rawContent, true);
    if (!is_array($decoded)) return array();
    $status = strtoupper(trim((string)($decoded['status'] ?? '')));
    $report = trim((string)($decoded['report'] ?? ''));
    if ($status !== 'SUCCESS' || $report === '') return array();
    return array(
        'report' => $report,
        'provider' => trim((string)($decoded['provider'] ?? '')),
        'model' => trim((string)($decoded['model'] ?? '')),
        'elapsed_ms' => isset($decoded['elapsed_ms']) ? (int)$decoded['elapsed_ms'] : 0,
    );
}

function llm_report_pick_recent_cached_report($onceKey, $tradeType, $months = 1) {
    $onceKey = trim((string)$onceKey);
    $tradeType = ((int)$tradeType === 2) ? 2 : 1;
    $months = (int)$months;
    if ($months < 1) $months = 1;
    if ($onceKey === '') return array();

    $onceKeySql = sql_escape_string($onceKey);
    $result = sql_query("
        SELECT c_content
        FROM company_llm_log
        WHERE c_once_key = '{$onceKeySql}'
          AND c_trade_type = '{$tradeType}'
          AND c_datetime >= date_sub(now(), interval {$months} month)
        ORDER BY c_id DESC
        LIMIT 10
    ", false);
    if (!$result) return array();

    while ($row = sql_fetch_array($result)) {
        $picked = llm_report_extract_success_report_from_content($row['c_content'] ?? '');
        if (!empty($picked['report'])) return $picked;
    }
    return array();
}

function _sze_label($sze) {
    switch ((int)$sze) {
        case 1: return '소상공인';
        case 2:
        case 3: return '소기업';
        case 4:
        case 5: return '중기업';
        case 6: return '대기업';
        default:
            return ((string)$sze !== '' && (int)$sze > 0) ? ('규모(' . (int)$sze . ')') : '-';
    }
}

function _sze_with_sales($sze, $sales) {
    $label = _sze_label($sze);
    $s = (float)$sales;
    if ($s <= 0) return $label;
    $formatted = $s >= 100000000
        ? number_format($s / 100000000, 1) . '억원'
        : number_format($s) . '원';
    return $label . ' (매출 ' . $formatted . ')';
}

function _debt_label($debt_ratio_pct, $capital_negative) {
    if (!empty($capital_negative)) return '자본잠식 (자본총계 음수 — 재무 위험 최고 등급)';
    return ($debt_ratio_pct !== null && $debt_ratio_pct !== '') ? ($debt_ratio_pct . '%') : '-';
}

function _bzc_full($rec) {
    $nm = trim((string)($rec['bzc_nm'] ?? ''));
    $cd = trim((string)($rec['bzc_cd'] ?? ''));
    if ($nm !== '' && $cd !== '') return $nm . ' (' . $cd . ')';
    if ($nm !== '') return $nm;
    if ($cd !== '') return $cd;
    return '-';
}

function _candidate_type_desc($type) {
    $type = (string)$type;
    if (strpos($type, 'chain') === 0) return '공통 거래처 네트워크 연결';
    if (strpos($type, 'partner') === 0) return '동종 기업 실거래 검증 파트너';
    if ($type === 'industry') return '동일 업종 공급망 파트너';
    if ($type === 'neighbor') return '지역 기반 근접 파트너';
    return '거래 연결';
}

function _axis_desc() {
    return array(
        'supply_chain'       => '공급망 연결력',
        'financial_health'   => '재무 건전성',
        'network_overlap'    => '거래망 중복도',
        'market_power'       => '시장 영향력',
        'trade_diversity'    => '거래 다양성',
        'company_size'       => '기업 규모',
        'growth_trend'       => '성장 궤도',
        'trade_loyalty'      => '거래 충성도',
        'scale_match'        => '거래 규모 적합도',
        'accessibility'      => '지역 접근성',
        'industry_fit'       => '업종 적합도',
        'product_fit'        => '상품 적합도',
        'similarity'         => '거래 유사도',
        'activity'           => '거래 활성도',
        'geographic_fit'     => '지역 근접성',
        'stability_score'    => '거래 안정성',
        'network_centrality' => '네트워크 중심성',
    );
}

function _rec_radar_full($rec, $axisDesc) {
    if (empty($rec) || !is_array($rec) || empty($rec['radar']) || !is_array($rec['radar'])) {
        return '레이더 데이터 없음';
    }
    $sorted = $rec['radar'];
    arsort($sorted);
    $lines = array();
    foreach ($sorted as $axis => $val) {
        $desc = isset($axisDesc[$axis]) ? $axisDesc[$axis] : $axis;
        $mark = ((float)$val >= 70) ? '★' : (((float)$val >= 50) ? '◎' : '○');
        $lines[] = '- ' . $mark . ' ' . $desc . ': ' . $val . '/100';
    }
    return implode("\n", $lines);
}

$axisDesc = _axis_desc();

$srcNm    = (string)($d['source_enp_nm'] ?? '(의뢰사)');
$srcBzc   = _bzc_full(array(
    'bzc_nm' => (string)($d['source_bzc_nm'] ?? ''),
    'bzc_cd' => (string)($d['source_bzc_cd'] ?? ''),
));
$srcSido  = (string)($d['source_sido'] ?? '-');
$srcMpd   = (!empty($d['source_mpd']) && $d['source_mpd'] !== '-') ? (string)$d['source_mpd'] : '미공개';
$custList = (!empty($d['source_cust_names']) && is_array($d['source_cust_names'])) ? implode(', ', $d['source_cust_names']) : '미공개';
$suppList = (!empty($d['source_supp_names']) && is_array($d['source_supp_names'])) ? implode(', ', $d['source_supp_names']) : '미공개';
$srcSales = !empty($d['source_sales']) ? number_format((float)$d['source_sales']) . '원' : '미공개';
$srcSze   = _sze_with_sales($d['source_sze'] ?? 0, $d['source_sales'] ?? 0);

$recNmReal = (string)($rec['enp_nm'] ?? ($rec['kedcd'] ?? ''));
$recNm     = '해당 기업';
$recScore  = $rec['display_score'] ?? ($rec['score'] ?? '-');
$recType   = _candidate_type_desc($rec['candidate_type'] ?? '-');
$recRadar  = _rec_radar_full($rec, $axisDesc);
$recBzc    = _bzc_full($rec);
$recSze    = _sze_label($rec['sze'] ?? 0);
$recSido   = (string)($rec['sido'] ?? '-');
$recAge    = !empty($rec['company_age']) ? ((int)$rec['company_age'] . '년') : '-';
$recGrowth = (isset($rec['sales_growth_pct']) && $rec['sales_growth_pct'] !== null)
    ? (((float)$rec['sales_growth_pct'] >= 0 ? '+' : '') . $rec['sales_growth_pct'] . '%')
    : '-';
$recDebt   = _debt_label($rec['debt_ratio_pct'] ?? null, $rec['capital_negative'] ?? 0);
$recP2y    = (isset($rec['consec_profit_2y']) && $rec['consec_profit_2y'] !== null)
    ? ((int)$rec['consec_profit_2y'] ? '2년 연속 흑자' : '미확인')
    : '-';
$recKeywords = !empty($rec['keywords']) && is_array($rec['keywords']) ? implode(', ', $rec['keywords']) : '';
$srcKeywords = !empty($d['src_keywords']) && is_array($d['src_keywords']) ? implode(', ', $d['src_keywords']) : '';
$tradeDir = $isSell
    ? '의뢰사가 해당 기업에 판매할 수 있는 품목·서비스'
    : '의뢰사가 해당 기업으로부터 구매할 수 있는 품목·서비스';
$tradeAction = $isSell ? '어떤 제품·서비스를 판매할 수 있는지' : '어떤 품목·소재·서비스를 구매할 수 있는지';
$allowedTradeItems = llm_report_trade_allowed_items($d, $rec, $isSell);
$buyItemHint = !empty($allowedTradeItems['buy_items']) ? implode(', ', $allowedTradeItems['buy_items']) : '없음';
$sellItemHint = !empty($allowedTradeItems['sell_items']) ? implode(', ', $allowedTradeItems['sell_items']) : '없음';

$memberId = trim((string)($member['mb_id'] ?? ''));
if ($memberId === '') {
    http_response_code(401);
    echo json_encode(array('error' => '로그인 후 이용해 주세요.'), JSON_UNESCAPED_UNICODE);
    exit;
}

// 프론트 전달값이 비어도 회원사 KEDCD로 fallback
$sourceKedcd = preg_replace('/\D/', '', (string)($d['source_kedcd'] ?? ($member['mb_company'] ?? '')));
if ($sourceKedcd === '') {
    $sourceKedcd = preg_replace('/\D/', '', (string)($member['mb_company'] ?? ''));
}
$targetKedcd = preg_replace('/\D/', '', (string)($d['target_kedcd'] ?? ($rec['kedcd'] ?? '')));
if ($targetKedcd === '') {
    http_response_code(400);
    echo json_encode(array('error' => '추천 대상 기업 정보가 없어 분석을 진행할 수 없습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}
// 정책 분리:
// - 결과 재사용(LLM 재호출 생략): 1개월
// - 크레딧 재차감 면제: 2개월
$cacheWindowMonths = 1;
$creditWindowMonths = 2;
$analysisOnceKey = llm_report_build_once_key($memberId, $sourceKedcd, $targetKedcd, $tradeType);
if ($analysisOnceKey === '') {
    http_response_code(400);
    echo json_encode(array('error' => '분석 식별키 생성에 실패했습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

// 최근 1개월 내 동일 분석 성공 리포트가 있으면 LLM 재호출 없이 즉시 반환
$cachedReport = llm_report_pick_recent_cached_report($analysisOnceKey, $tradeType, $cacheWindowMonths);
if (!empty($cachedReport['report'])) {
    // 과거 캐시도 현재 원본 품목 통제 규칙으로 맞춰서 반환
    $cachedReportText = llm_report_trade_sanitize_report_json((string)$cachedReport['report'], $d, $rec, $isSell);
    llm_report_write_daily_file_log($sourceKedcd, $targetKedcd, $tradeType, $analysisOnceKey, 'SKIP', array(
        'stage' => 'cache_hit',
        'provider' => (string)($cachedReport['provider'] ?? ''),
        'model' => (string)($cachedReport['model'] ?? ''),
        'elapsed_ms' => isset($cachedReport['elapsed_ms']) ? (int)$cachedReport['elapsed_ms'] : 0,
        'credit_charged' => 0,
        'reused_within_window' => 1,
        'message' => '최근 성공 리포트 재사용으로 LLM 호출 생략',
    ));
    echo json_encode(array(
        'report' => $cachedReportText,
        'trade_type' => $tradeType,
        'trade_label' => $tradeLabel,
        'provider' => (string)($cachedReport['provider'] ?? ''),
        'model' => (string)($cachedReport['model'] ?? ''),
        'elapsed_ms' => isset($cachedReport['elapsed_ms']) ? (int)$cachedReport['elapsed_ms'] : 0,
        'cache_hit' => true,
        'credit_charged' => false,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$systemInstruction = <<<SYS
당신은 B2B 영업 현장 전문가입니다. 보고서 독자는 실제 구매·영업 담당자이며, "이 회사와 왜 거래가 되는가"를 직관적으로 이해하고 싶어합니다.

[데이터 경계 원칙 — 최우선 준수]
1. 보고서에 사용 가능한 정보는 아래 '제공 데이터' 항목뿐입니다.
2. '-' 또는 '미확인' 항목은 서술하지 않습니다.
3. 업종코드·업종명에서 해당 업종의 일반적 거래 품목과 공급망 역할 해석은 허용합니다. 단, 해당 기업의 특정 제품명·고객사·대표자 등 기업 고유 정보는 추론 작성 금지입니다.
4. 사전 학습 지식으로 알고 있는 해당 기업의 구체적 정보를 기재하지 않습니다.
5. 모든 서술은 제공된 데이터에 직접 근거해야 합니다.

[업종명 거래 언어 변환 — 필수]
업종명을 반드시 실제 거래 언어로 변환하여 표현합니다.
예) '그 외 기타 1차 철강 제조업' → '산업용 철강 원소재 및 가공소재 공급 기업'
    '응용 소프트웨어 개발 및 공급업' → '업무용 소프트웨어 개발·공급 기업'
    '화장품 제조업' → '화장품 OEM·ODM 및 완제품 공급 기업'
    '자동차 부품 제조업' → '자동차 부품·조립 가공 공급 기업'
    '생산설비 관련 제조업' → '생산설비·자동화 장비 공급 기업'
    '포장재 제조업' → '포장재·용기 공급 기업'

[거래 중심 서술 원칙]
이 보고서는 {$tradeLabel} 추천입니다.
핵심 질문: "{$tradeAction}"을 담당자가 즉시 이해할 수 있도록 설명합니다.
- {$tradeDir}를 보고서 전반에 걸쳐 구체적으로 서술합니다.
- '품목 키워드'가 제공된 경우 해당 키워드를 거래 가능 품목 서술의 우선 근거로 사용합니다. 없으면 업종코드로 추론합니다.
- 추천 사유를 업종명 중심으로 쓰지 말고, 실제 거래 품목 중심으로 씁니다.
- `buy_items`, `sell_items`에는 아래 제공된 '구매 가능 품목 원본값', '판매 가능 품목 원본값'에 있는 표현만 그대로 사용합니다.
- `buy_items`, `sell_items`에서 새로운 품목명이나 서비스명을 추론해서 만들지 않습니다.
- 원본값이 없으면 해당 배열은 비워 둘 수 있으며, 없는 품목을 억지로 만들지 않습니다.
- 구매 가능 품목은 이 거래에서 실제로 구매 수요가 맞닿는 품목·서비스로 정리하고, 판매 가능 품목은 실제로 공급·제공 가능한 품목·서비스로 정리합니다.
- '핵심 추천 사유' 첫 번째 항목은 반드시 구체적 품목명 또는 서비스명으로 시작합니다. 업종 설명으로 시작하지 않습니다.
- '핵심 추천 사유', '거래 연결 배경', '공급망·공정 연결성', '거래 시나리오'에서 품목명 또는 서비스명이 필요할 때도 가능한 한 위 원본값 표현을 그대로 재사용합니다.
- 'IT 시스템 구축 서비스', '솔루션 제공', '운영 지원'처럼 너무 넓은 표현만 단독으로 쓰지 말고, 가능한 한 서버 구축, 네트워크 설계, 보안 시스템 구축, 유지보수 계약, 금속구조물, 배전반 외함처럼 실제 거래 단위를 씁니다.
- 각 섹션은 충분한 분량(2~4문장 이상)으로 구체적으로 서술합니다. 한 줄 요약으로 끝내지 않습니다.
- 재무·업력·규모는 섹션 3에서 별도로 다루되, 거래 신뢰도 관점에서 항목별로 해석합니다.
- 업종명은 항상 **거래언어변환 표현** 형식으로 볼드 처리합니다. 그 외 볼드(**)는 사용하지 않습니다.
- 모든 문장은 '~입니다', '~습니다' 존댓말로 작성합니다.
- 추천 기업의 실명은 보고서 어디서도 직접 기재하지 말고 반드시 '해당 기업'으로 표기하세요.

[출력 규칙 — 최우선]
- 마크다운 보고서를 쓰지 말고 JSON만 출력합니다.
- 코드펜스 없이 순수 JSON 객체만 반환합니다.
- 키 이름을 바꾸거나 생략하지 않습니다.
- `buy_items`, `sell_items`는 위 원본값 표현만 그대로 사용합니다.
- 원본값에 없는 새 품목명은 절대 만들지 않습니다.
SYS;

$userContent = <<<USR
## ※ 사용 가능한 데이터 (이 목록 외 정보는 존재하지 않습니다)

---
## [의뢰사: {$srcNm}]

| 항목 | 값 |
|------|-----|
| 업종 | {$srcBzc} |
| 지역 | {$srcSido} |
| 규모 | {$srcSze} |
| 주력상품 | {$srcMpd} |
| 매출(당기) | {$srcSales} |
| 품목 키워드 | {$srcKeywords} |
| 기존 매입거래처 | {$custList} |
| 기존 매출거래처 | {$suppList} |

---
## [{$recNm} — ML 추천 {$tradeLabel} 1위]

| 항목 | 값 |
|------|-----|
| 업종 | {$recBzc} |
| 지역 | {$recSido} |
| 규모 | {$recSze} |
| 업력 | {$recAge} |
| 매출성장률 | {$recGrowth} |
| 부채비율 | {$recDebt} |
| 2년 연속흑자 | {$recP2y} |
| 추천 점수 | {$recScore} |
| 연결 방식 | {$recType} |
| 품목 키워드 | {$recKeywords} |
| 구매 가능 품목 참고 | {$buyItemHint} |
| 판매 가능 품목 참고 | {$sellItemHint} |
| 구매 가능 품목 원본값 | {$buyItemHint} |
| 판매 가능 품목 원본값 | {$sellItemHint} |

레이더 지표 (★ 강점 · ◎ 보통 · ○ 참고)
{$recRadar}

---
## [기존 보고서 형식 — 내용 흐름은 아래 형식을 유지]

# {$tradeLabel} 거래 가능성 분석

> 추천 유형: {$tradeLabel} | 점수: {$recScore} | 연결: {$recType}

---

## 거래 품목 요약

### 구매 가능 품목
(구매 가능 품목 원본값을 우선 사용하여 1줄 요약)

### 판매 가능 품목
(판매 가능 품목 원본값을 우선 사용하여 1줄 요약)

---

## 핵심 추천 사유
- ({$tradeAction} — 업종명을 거래 언어로 변환하여 구체적으로)
- (두 기업이 공급망·공정에서 연결되는 이유)
- (ML 연결 방식이 말해주는 실거래 연결 근거)

---

## 1. 어떤 {$tradeLabel}인가

### 1-1. 거래 언어로 본 기업 포지션
(업종명을 거래 언어로 변환: "이 기업은 **[거래언어변환]** 기업으로, [공급망 내 역할]을 담당합니다" 형태. 지역 거점의 물류·접근성 의미, 규모 기반 포지션 분석 포함. 3~4문장)

### 1-2. {$tradeAction}
({$tradeLabel} 관점에서 거래 가능한 구체적 품목·소재·서비스·공정을 품목 키워드·업종코드 기반으로 열거.
형태: "① [품목·소재] ② [가공·공정 연계] ③ [서비스·납품 형태]"
각 항목을 1~2문장으로 구체적으로 설명합니다.)

---

## 2. 왜 우리 회사와 연결되는가

### 2-1. 거래 연결 배경
(ML 연결 방식({$recType})이 말해주는 두 기업의 관계. 의뢰사 업종과 추천 기업 업종이 공급망에서 어떻게 만나는지 3~4문장으로 구체적으로 서술)

### 2-2. 공급망·공정 연결성
(두 기업이 실제 공급망에서 어느 단계에 위치하는지, 어떤 공정·거래 흐름으로 연결 가능한지 구체적으로 서술.
★ 레이더 강점 지표를 거래 연결 신뢰도 관점에서 항목별로 해석. 각 지표 1~2문장)

### 2-3. 거래 시나리오
(이 거래가 실제로 성사되었을 때의 구체적인 거래 흐름 2~3가지를 서술.
의뢰사가 얻는 실질적 이익과 추천 기업이 얻는 이익을 각각 명시)

---

## 3. 거래 신뢰도 분석

### 3-1. 거래 안정성
(업력·규모를 거래 파트너로서의 안정성 관점에서 해석. 2~3문장)

### 3-2. 재무 건전성
(매출성장률·부채비율·2년 연속흑자를 거래 리스크 관점에서 해석. 항목별로 2~3문장)

---

## 4. 실행 제안 및 감안할 사항

### 4-1. 첫 거래 접점
(구체적인 첫 접촉 방법·시작 포인트 2~3가지를 단계별로 제시)

### 4-2. 감안할 사항
(거래 시 주의해야 할 리스크 또는 보완 사항 2~3가지)

---
## [추가 요청 반영 규칙 — 위 기존 형식에 아래 내용을 추가 반영]

- 보고서 상단 또는 추천사유 영역에서 사용자가 바로 이해할 수 있도록 '구매 가능 품목', '판매 가능 품목'을 별도 항목으로 노출합니다.
- 추천사유에 업종명만 쓰지 말고 구매 품목과 판매 품목을 함께 반영합니다.
- 예: "귀사가 필요로 하는 알루미늄 판넬 및 배전반 외함을 공급할 수 있는 기업으로 분석되었습니다." 같은 방식으로 작성합니다.
- 목적은 사용자가 "왜 이 기업이 추천됐는지"를 업종이 아니라 실제 거래 품목 기준으로 바로 이해하게 만드는 것입니다.

---
## [JSON 출력 스키마 — 이 키 이름 그대로만 출력]

{
  "title": "{$tradeLabel} 거래 가능성 분석",
  "trade_label": "{$tradeLabel}",
  "score": "{$recScore}",
  "connection_type": "{$recType}",
  "buy_items": ["원본값1", "원본값2"],
  "sell_items": ["원본값1", "원본값2"],
  "reasons": [
    "귀사가 필요로 하는 [품목A], [품목B] 등을 공급/제공할 수 있는 기업으로 분석되었습니다.",
    "두 기업이 공급망·공정에서 어떻게 연결되는지 품목 중심으로 설명합니다.",
    "ML 연결 방식이 실거래 가능성과 어떻게 이어지는지 품목 또는 납품 형태를 포함해 설명합니다."
  ],
  "positioning": "거래 언어로 본 기업 포지션을 3~4문장으로 작성합니다.",
  "tradable_items_detail": "거래 가능한 구체적 품목·소재·서비스·공정을 3개 이상 포함해 설명합니다.",
  "connection_background": "거래 연결 배경을 실제 품목명 또는 서비스명을 포함해 3~4문장으로 설명합니다.",
  "supply_chain_fit": "공급망·공정 연결성과 레이더 강점 지표를 거래 연결 신뢰도 관점에서 설명합니다.",
  "trade_scenarios": [
    "거래 흐름 시나리오 1",
    "거래 흐름 시나리오 2"
  ],
  "stability_analysis": "업력·규모 기반 거래 안정성을 2~3문장으로 설명합니다.",
  "financial_analysis": "매출성장률·부채비율·2년 연속흑자를 거래 리스크 관점에서 2~3문장으로 설명합니다.",
  "first_touch_actions": [
    "첫 접촉 방법 1",
    "첫 접촉 방법 2"
  ],
  "considerations": [
    "감안할 사항 1",
    "감안할 사항 2"
  ]
}
USR;

// 시안 변수명(GEMINI_*)을 우선 사용하고, LLM_*는 하위 호환으로 유지
$provider = strtolower(trim(llm_env('LLM_PROVIDER', llm_env('LLM_PROVIDER_DEFAULT', ''))));
$model = trim(llm_env('LLM_MODEL', llm_env('GEMINI_MODEL', '')));
$apiKey = trim(llm_env('LLM_API_KEY', llm_env('GEMINI_API_KEY', '')));

if ($provider === '') {
    if ($apiKey !== '' || ($model !== '' && stripos($model, 'gemini') !== false)) $provider = 'gemini';
    else $provider = 'openai';
}
if ($model === '') {
    $model = ($provider === 'gemini')
        ? trim(llm_env('GEMINI_MODEL', 'gemini-3-flash-preview'))
        : trim(llm_env('OPENAI_MODEL', 'gpt-5-mini'));
}
if ($apiKey === '') {
    $apiKey = ($provider === 'gemini')
        ? trim(llm_env('GEMINI_API_KEY', ''))
        : trim(llm_env('OPENAI_API_KEY', ''));
}

if ($apiKey === '') {
    llm_report_log_write($sourceKedcd, $targetKedcd, $tradeType, $analysisOnceKey, array(
        'status' => 'ERROR',
        'stage' => 'env',
        'message' => 'LLM API KEY가 설정되지 않았습니다.',
    ));
    http_response_code(500);
    echo json_encode(array('error' => 'LLM API KEY가 설정되지 않았습니다. (LLM_API_KEY 또는 provider별 키 확인)'), JSON_UNESCAPED_UNICODE);
    exit;
}

$creditCost = 1;
$creditCharged = false;
$creditOnceKey = $analysisOnceKey;
$hasRecentUse = llm_report_has_recent_use($memberId, $analysisOnceKey, $creditWindowMonths);
// 2개월 이내 재조회는 크레딧 재차감 없이 그대로 분석을 진행
if (!$hasRecentUse) {
    // 첫 조회는 분석 시작 전에 잔액만 확인, 실제 차감은 분석 성공 직후에 진행
    $remainCredit = credit_ledger_get_balance($memberId);
    if ($remainCredit < $creditCost) {
        llm_report_log_write($sourceKedcd, $targetKedcd, $tradeType, $analysisOnceKey, array(
            'status' => 'ERROR',
            'stage' => 'credit',
            'message' => 'AI 분석 크레딧이 부족합니다. (필요: ' . $creditCost . ')',
            'plan_required' => true,
        ));
        http_response_code(402);
        echo json_encode(array(
            'error' => 'AI 분석 크레딧이 부족합니다. (필요: ' . $creditCost . ')',
            'plan_required' => true,
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$elapsedMs = 0;
$reportText = '';
$startMs = (int)(microtime(true) * 1000);

if ($provider === 'gemini') {
    $requestBody = array(
        'system_instruction' => array('parts' => array(array('text' => $systemInstruction))),
        'contents' => array(array('role' => 'user', 'parts' => array(array('text' => $userContent)))),
        'generationConfig' => array(
            'temperature' => 0.1,
            'maxOutputTokens' => 6000,
            'responseMimeType' => 'application/json',
            'responseSchema' => array(
                'type' => 'OBJECT',
                'required' => array(
                    'title',
                    'trade_label',
                    'score',
                    'connection_type',
                    'buy_items',
                    'sell_items',
                    'reasons',
                    'positioning',
                    'tradable_items_detail',
                    'connection_background',
                    'supply_chain_fit',
                    'trade_scenarios',
                    'stability_analysis',
                    'financial_analysis',
                    'first_touch_actions',
                    'considerations',
                ),
                'properties' => array(
                    'title' => array('type' => 'STRING'),
                    'trade_label' => array('type' => 'STRING'),
                    'score' => array('type' => 'STRING'),
                    'connection_type' => array('type' => 'STRING'),
                    'buy_items' => array('type' => 'ARRAY', 'items' => array('type' => 'STRING')),
                    'sell_items' => array('type' => 'ARRAY', 'items' => array('type' => 'STRING')),
                    'reasons' => array('type' => 'ARRAY', 'items' => array('type' => 'STRING')),
                    'positioning' => array('type' => 'STRING'),
                    'tradable_items_detail' => array('type' => 'STRING'),
                    'connection_background' => array('type' => 'STRING'),
                    'supply_chain_fit' => array('type' => 'STRING'),
                    'trade_scenarios' => array('type' => 'ARRAY', 'items' => array('type' => 'STRING')),
                    'stability_analysis' => array('type' => 'STRING'),
                    'financial_analysis' => array('type' => 'STRING'),
                    'first_touch_actions' => array('type' => 'ARRAY', 'items' => array('type' => 'STRING')),
                    'considerations' => array('type' => 'ARRAY', 'items' => array('type' => 'STRING')),
                ),
            ),
            'thinkingConfig' => array('thinkingBudget' => 0),
        ),
    );

    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/'
        . urlencode($model)
        . ':generateContent?key='
        . urlencode($apiKey);

    $payload = json_encode($requestBody, JSON_UNESCAPED_UNICODE);
    $raw = '';
    $code = 0;
    $curlErr = '';

    for ($attempt = 0; $attempt <= 2; $attempt++) {
        if ($attempt > 0) sleep(1 << ($attempt - 1));
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 50,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ));
        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if (!$curlErr && $raw && !in_array($code, array(429, 503), true)) break;
    }

    if ($curlErr || !$raw) {
        llm_report_log_write($sourceKedcd, $targetKedcd, $tradeType, $analysisOnceKey, array(
            'status' => 'ERROR',
            'stage' => 'gemini_connect',
            'provider' => $provider,
            'model' => $model,
            'credit_charged' => $creditCharged ? 1 : 0,
            'message' => 'LLM 연결 실패: ' . ($curlErr ?: '빈 응답'),
        ));
        http_response_code(502);
        echo json_encode(array('error' => 'LLM 연결 실패: ' . ($curlErr ?: '빈 응답')), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $resp = json_decode($raw, true);
    if ($code !== 200) {
        $errMsg = $resp['error']['message'] ?? '알 수 없는 오류';
        $hint = in_array($code, array(429, 503), true) ? ' (잠시 후 다시 시도해 주세요)' : '';
        llm_report_log_write($sourceKedcd, $targetKedcd, $tradeType, $analysisOnceKey, array(
            'status' => 'ERROR',
            'stage' => 'gemini_http',
            'provider' => $provider,
            'model' => $model,
            'http_code' => (int)$code,
            'credit_charged' => $creditCharged ? 1 : 0,
            'message' => 'LLM API 오류: ' . $errMsg,
        ));
        http_response_code(502);
        echo json_encode(array('error' => 'LLM API 오류 (' . $code . '): ' . $errMsg . $hint), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $parts = $resp['candidates'][0]['content']['parts'] ?? array();
    foreach ($parts as $part) {
        if (isset($part['text']) && empty($part['thought'])) {
            $reportText .= $part['text'];
        }
    }
    if ($reportText === '') {
        foreach ($parts as $part) {
            if (isset($part['text'])) $reportText .= $part['text'];
        }
    }
} else {
    // 향후 모델 교체를 위해 OpenAI 호환 엔드포인트도 유지
    $baseUrl = rtrim((string)llm_env('LLM_BASE_URL', llm_env('OPENAI_BASE_URL', 'https://api.openai.com/v1')), '/');
    $apiUrl = $baseUrl . '/chat/completions';
    $requestBody = array(
        'model' => $model,
        'temperature' => 0.1,
        'messages' => array(
            array('role' => 'system', 'content' => $systemInstruction),
            array('role' => 'user', 'content' => $userContent),
        ),
    );

    $payload = json_encode($requestBody, JSON_UNESCAPED_UNICODE);
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 50,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ));
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$raw) {
        llm_report_log_write($sourceKedcd, $targetKedcd, $tradeType, $analysisOnceKey, array(
            'status' => 'ERROR',
            'stage' => 'openai_connect',
            'provider' => $provider,
            'model' => $model,
            'credit_charged' => $creditCharged ? 1 : 0,
            'message' => 'LLM 연결 실패: ' . ($curlErr ?: '빈 응답'),
        ));
        http_response_code(502);
        echo json_encode(array('error' => 'LLM 연결 실패: ' . ($curlErr ?: '빈 응답')), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $resp = json_decode($raw, true);
    if ($code < 200 || $code >= 300) {
        $errMsg = $resp['error']['message'] ?? '알 수 없는 오류';
        llm_report_log_write($sourceKedcd, $targetKedcd, $tradeType, $analysisOnceKey, array(
            'status' => 'ERROR',
            'stage' => 'openai_http',
            'provider' => $provider,
            'model' => $model,
            'http_code' => (int)$code,
            'credit_charged' => $creditCharged ? 1 : 0,
            'message' => 'LLM API 오류: ' . $errMsg,
        ));
        http_response_code(502);
        echo json_encode(array('error' => 'LLM API 오류 (' . $code . '): ' . $errMsg), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $msg = $resp['choices'][0]['message']['content'] ?? '';
    if (is_array($msg)) {
        $chunks = array();
        foreach ($msg as $part) {
            if (is_array($part) && isset($part['text'])) $chunks[] = (string)$part['text'];
            else if (is_string($part)) $chunks[] = $part;
        }
        $reportText = implode("\n", $chunks);
    } else {
        $reportText = (string)$msg;
    }
}

$elapsedMs = (int)(microtime(true) * 1000) - $startMs;

if (trim($reportText) === '') {
    llm_report_log_write($sourceKedcd, $targetKedcd, $tradeType, $analysisOnceKey, array(
        'status' => 'ERROR',
        'stage' => 'extract',
        'provider' => $provider,
        'model' => $model,
        'credit_charged' => $creditCharged ? 1 : 0,
        'message' => '보고서 텍스트 추출 실패',
    ));
    http_response_code(502);
    echo json_encode(array('error' => '보고서 텍스트 추출 실패'), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($recNmReal !== '') {
    $reportText = str_replace($recNmReal, '해당 기업', $reportText);
}
$reportText = str_replace('__', '', $reportText);
$reportText = preg_replace('/\s*\(추론\)/', '', $reportText);
// 모델이 생성한 JSON에서 허용 품목과 핵심 거래 문장을 최종 보정한다.
$reportText = llm_report_trade_sanitize_report_json($reportText, $d, $rec, $isSell);
if (!$hasRecentUse) {
    $creditTx = llm_report_consume_credit_once($memberId, $sourceKedcd, $targetKedcd, $tradeType, $creditCost);
    if (empty($creditTx['success'])) {
        llm_report_log_write($sourceKedcd, $targetKedcd, $tradeType, $analysisOnceKey, array(
            'status' => 'ERROR',
            'stage' => 'credit',
            'provider' => $provider,
            'model' => $model,
            'elapsed_ms' => $elapsedMs,
            'credit_charged' => 0,
            'message' => (string)($creditTx['message'] ?? '크레딧 처리 중 오류가 발생했습니다.'),
            'plan_required' => !empty($creditTx['plan_required']),
        ));
        $status = isset($creditTx['status']) ? (int)$creditTx['status'] : 400;
        if ($status < 400 || $status > 599) $status = 400;
        http_response_code($status);
        echo json_encode(array(
            'error' => (string)($creditTx['message'] ?? '크레딧 처리 중 오류가 발생했습니다.'),
            'plan_required' => !empty($creditTx['plan_required']),
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }
    $creditCharged = !empty($creditTx['charged']);
    $creditOnceKey = (string)($creditTx['once_key'] ?? $analysisOnceKey);
}
// 최종 성공 결과를 company_llm_log에 남겨 상세 진입 시 즉시 재사용 가능하게 함
llm_report_log_write($sourceKedcd, $targetKedcd, $tradeType, $analysisOnceKey, array(
    'status' => 'SUCCESS',
    'provider' => $provider,
    'model' => $model,
    'trade_type' => $tradeType,
    'trade_label' => $tradeLabel,
    'elapsed_ms' => $elapsedMs,
    'credit_charged' => $creditCharged ? 1 : 0,
    'reused_within_window' => $hasRecentUse ? 1 : 0,
    'report' => $reportText,
));

echo json_encode(array(
    'report' => $reportText,
    'trade_type' => $tradeType,
    'trade_label' => $tradeLabel,
    'provider' => $provider,
    'model' => $model,
    'elapsed_ms' => $elapsedMs,
    'cache_hit' => false,
    'credit_charged' => $creditCharged,
    'once_key' => $creditOnceKey,
), JSON_UNESCAPED_UNICODE);
