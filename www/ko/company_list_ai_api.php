<?php
// AI 추천 목록 페이지 전용 API
// - 입력: action=recommend, kedcd
// - 처리: EC2 /recommend buy/sell 호출
// - 출력: 화면 렌더용 정규화 JSON
include_once('./_common.php');
include_once(G5_PATH . '/ko/helper/company_list_ai_helper.php');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

// GET만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method Not Allowed'), JSON_UNESCAPED_UNICODE);
    exit;
}

// 현재는 recommend 액션만 지원
$action = isset($_GET['action']) ? trim((string)$_GET['action']) : 'recommend';
if ($action !== 'recommend') {
    http_response_code(400);
    echo json_encode(array('error' => 'action은 recommend만 지원합니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

// kedcd 직접 입력 또는 bizno 기반(Kodata/캐시) 둘 다 지원
$kedcd_raw = isset($_GET['kedcd']) ? (string)$_GET['kedcd'] : '';
$kedcd = str_pad(preg_replace('/\D/', '', $kedcd_raw), 10, '0', STR_PAD_LEFT);
$has_valid_kedcd = (strlen($kedcd) === 10 && !preg_match('/^0+$/', $kedcd));

$bizno_raw = isset($_GET['bizno']) ? (string)$_GET['bizno'] : (isset($_GET['comnum']) ? (string)$_GET['comnum'] : '');
$bizno = preg_replace('/\D/', '', $bizno_raw);

$base_payload = array();
$resolve_meta = array();

if ($has_valid_kedcd) {
    $base_payload = company_list_ai_build_base_payload($_GET);
    $base_payload['kedcd'] = $kedcd;
    $resolve_meta = array('source' => 'kedcd_direct');
} else if (strlen($bizno) === 10) {
    // 사업자번호 진입: company_api_log 캐시 우선, 필요 시 Kodata fallback
    $resolved = company_list_ai_resolve_base_payload_by_bizno($bizno, true);
    if (empty($resolved['ok'])) {
        $status = isset($resolved['status']) ? (int)$resolved['status'] : 502;
        if ($status < 400) $status = 502;
        http_response_code($status);
        echo json_encode(
            array(
                'error' => '사업자번호 기반 기업정보 조회 실패',
                'detail' => isset($resolved['error']) ? $resolved['error'] : 'unknown',
            ),
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }

    $base_payload = isset($resolved['base_payload']) && is_array($resolved['base_payload']) ? $resolved['base_payload'] : array();
    $resolved_kedcd = isset($base_payload['kedcd']) ? (string)$base_payload['kedcd'] : '';
    $query_override = company_list_ai_build_base_payload(array_merge($_GET, array('kedcd' => $resolved_kedcd)));
    $base_payload = array_merge($base_payload, $query_override);
    $kedcd = isset($base_payload['kedcd']) ? (string)$base_payload['kedcd'] : '';
    $resolve_meta = $resolved;
} else {
    http_response_code(400);
    echo json_encode(array('error' => 'kedcd(10자리) 또는 bizno(10자리)가 필요합니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$buy_payload = $base_payload;
$buy_payload['trade_type'] = 1;

$sell_payload = $base_payload;
$sell_payload['trade_type'] = 2;

// 추천 탭 전환 지연을 줄이기 위해 buy/sell을 선호출로 모두 조회
$timeout_sec = 8;
$buy_call = company_list_ai_call_recommend($buy_payload, $timeout_sec);
$sell_call = company_list_ai_call_recommend($sell_payload, $timeout_sec);

// 둘 다 실패하면 502
if (empty($buy_call['ok']) && empty($sell_call['ok'])) {
    http_response_code(502);
    echo json_encode(
        array(
            'error' => 'EC2 연결 실패',
            'detail' => array(
                'buy' => array(
                    'message' => isset($buy_call['error']) ? $buy_call['error'] : 'unknown',
                    'http_code' => isset($buy_call['http_code']) ? (int)$buy_call['http_code'] : 0,
                ),
                'sell' => array(
                    'message' => isset($sell_call['error']) ? $sell_call['error'] : 'unknown',
                    'http_code' => isset($sell_call['http_code']) ? (int)$sell_call['http_code'] : 0,
                ),
            ),
        ),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$buy_results = (isset($buy_call['ok']) && $buy_call['ok'] && isset($buy_call['data']['results']) && is_array($buy_call['data']['results']))
    ? $buy_call['data']['results']
    : array();
$sell_results = (isset($sell_call['ok']) && $sell_call['ok'] && isset($sell_call['data']['results']) && is_array($sell_call['data']['results']))
    ? $sell_call['data']['results']
    : array();

$buy_elapsed_ms = isset($buy_call['data']['elapsed_ms']) && is_numeric($buy_call['data']['elapsed_ms'])
    ? (int)$buy_call['data']['elapsed_ms']
    : (isset($buy_call['elapsed_ms']) ? (int)$buy_call['elapsed_ms'] : 0);

$sell_elapsed_ms = isset($sell_call['data']['elapsed_ms']) && is_numeric($sell_call['data']['elapsed_ms'])
    ? (int)$sell_call['data']['elapsed_ms']
    : (isset($sell_call['elapsed_ms']) ? (int)$sell_call['elapsed_ms'] : 0);

// 프론트에서 쓰는 응답 구조
$response = array(
    'kedcd' => $kedcd,
    'buy' => company_list_ai_build_bucket($buy_results, 45),
    'sell' => company_list_ai_build_bucket($sell_results, 45),
    'buy_elapsed_ms' => $buy_elapsed_ms,
    'sell_elapsed_ms' => $sell_elapsed_ms,
);

if ($bizno !== '') {
    $response['bizno'] = $bizno;
}
if (!empty($resolve_meta['source'])) {
    $response['company_source'] = (string)$resolve_meta['source'];
}
if (!empty($resolve_meta['cached_datetime'])) {
    $response['company_cached_at'] = (string)$resolve_meta['cached_datetime'];
}

// 한쪽만 실패한 경우도 화면은 동작하도록 partial_failure로 전달
if (empty($buy_call['ok']) || empty($sell_call['ok'])) {
    $response['partial_failure'] = array();
    if (empty($buy_call['ok'])) {
        $response['partial_failure']['buy'] = array(
            'message' => isset($buy_call['error']) ? $buy_call['error'] : 'unknown',
            'http_code' => isset($buy_call['http_code']) ? (int)$buy_call['http_code'] : 0,
        );
    }
    if (empty($sell_call['ok'])) {
        $response['partial_failure']['sell'] = array(
            'message' => isset($sell_call['error']) ? $sell_call['error'] : 'unknown',
            'http_code' => isset($sell_call['http_code']) ? (int)$sell_call['http_code'] : 0,
        );
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
