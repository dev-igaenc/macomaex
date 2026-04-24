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

// kedcd(10자리 숫자) 검증
$kedcd_raw = isset($_GET['kedcd']) ? (string)$_GET['kedcd'] : '';
$kedcd = str_pad(preg_replace('/\D/', '', $kedcd_raw), 10, '0', STR_PAD_LEFT);
if (strlen($kedcd) !== 10 || preg_match('/^0+$/', $kedcd)) {
    http_response_code(400);
    echo json_encode(array('error' => 'kedcd는 10자리 숫자여야 합니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

// 공통 payload 구성
$base_payload = company_list_ai_build_base_payload($_GET);
$base_payload['kedcd'] = $kedcd;

$buy_payload = $base_payload;
$buy_payload['trade_type'] = 1;

$sell_payload = $base_payload;
$sell_payload['trade_type'] = 2;

// buy/sell 각각 호출
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
