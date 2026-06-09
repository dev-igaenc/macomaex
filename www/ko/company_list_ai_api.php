<?php
// AI 추천 목록/상세 공용 API
// - action=recommend   : src 기업 기준 buy/sell 추천 목록 조회
// - action=target_card : src 기업 기준 특정 tgt 기업 카드 1건 조회(상세 복원용)
include_once('./_common.php');
include_once(G5_PATH . '/ko/helper/company_list_ai_helper.php');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method Not Allowed'), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!function_exists('company_list_ai_api_response')) {
    // 공통 JSON 응답 헬퍼
    function company_list_ai_api_response($status, $payload)
    {
        http_response_code((int)$status);
        echo json_encode(is_array($payload) ? $payload : array(), JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('company_list_ai_api_normalize_kedcd')) {
    // kedcd를 10자리 숫자로 정규화
    function company_list_ai_api_normalize_kedcd($value)
    {
        $kedcd = str_pad(preg_replace('/\D/', '', (string)$value), 10, '0', STR_PAD_LEFT);
        if (strlen($kedcd) !== 10 || preg_match('/^0+$/', $kedcd)) {
            return '';
        }
        return $kedcd;
    }
}

if (!function_exists('company_list_ai_api_normalize_bizno')) {
    // bizno를 10자리 숫자로 정규화
    function company_list_ai_api_normalize_bizno($value)
    {
        $bizno = preg_replace('/\D/', '', (string)$value);
        return (strlen($bizno) === 10) ? $bizno : '';
    }
}

if (!function_exists('company_list_ai_api_extract_results')) {
    // recommend 호출 결과에서 results 배열만 추출
    function company_list_ai_api_extract_results($call_result)
    {
        if (!is_array($call_result) || empty($call_result['ok'])) {
            return array();
        }
        if (!isset($call_result['data']['results']) || !is_array($call_result['data']['results'])) {
            return array();
        }
        return $call_result['data']['results'];
    }
}

if (!function_exists('company_list_ai_api_find_target_item')) {
    // results 목록에서 target kedcd 1건을 찾아 표준 item 형태로 반환
    function company_list_ai_api_find_target_item($results, $target_kedcd)
    {
        if (!is_array($results) || $target_kedcd === '') {
            return null;
        }

        foreach ($results as $index => $row) {
            $item = company_list_ai_normalize_item($row, $index);
            if (!is_array($item)) continue;
            $item_kedcd = company_list_ai_api_normalize_kedcd(isset($item['kedcd']) ? $item['kedcd'] : '');
            if ($item_kedcd !== '' && $item_kedcd === $target_kedcd) {
                return $item;
            }
        }

        return null;
    }
}

if (!function_exists('company_list_ai_api_resolve_source_base_payload')) {
    // src 기업 식별값(kedcd/bizno)을 base_payload로 해석
    function company_list_ai_api_resolve_source_base_payload($kedcd_raw, $bizno_raw, $query, &$resolve_meta)
    {
        $resolve_meta = array();
        $kedcd = company_list_ai_api_normalize_kedcd($kedcd_raw);
        $bizno = company_list_ai_api_normalize_bizno($bizno_raw);

        if ($kedcd !== '') {
            $base_payload = company_list_ai_build_base_payload($query);
            $base_payload['kedcd'] = $kedcd;
            $resolve_meta = array(
                'source' => 'kedcd_direct',
                'kedcd' => $kedcd,
                'bizno' => $bizno,
            );
            return $base_payload;
        }

        if ($bizno !== '') {
            $resolved = company_list_ai_resolve_base_payload_by_bizno($bizno, true);
            if (empty($resolved['ok'])) {
                $status = isset($resolved['status']) ? (int)$resolved['status'] : 502;
                if ($status < 400) $status = 502;
                company_list_ai_api_response($status, array(
                    'error' => '사업자번호 기반 기업정보 조회 실패',
                    'detail' => isset($resolved['error']) ? $resolved['error'] : 'unknown',
                ));
            }

            $base_payload = isset($resolved['base_payload']) && is_array($resolved['base_payload']) ? $resolved['base_payload'] : array();
            $resolved_kedcd = isset($base_payload['kedcd']) ? (string)$base_payload['kedcd'] : '';
            $query_override = company_list_ai_build_base_payload(array_merge((array)$query, array('kedcd' => $resolved_kedcd)));
            $base_payload = array_merge($base_payload, $query_override);
            $kedcd_from_payload = company_list_ai_api_normalize_kedcd(isset($base_payload['kedcd']) ? $base_payload['kedcd'] : '');

            $resolve_meta = array(
                'source' => isset($resolved['source']) ? (string)$resolved['source'] : 'bizno_resolved',
                'cached_datetime' => isset($resolved['cached_datetime']) ? (string)$resolved['cached_datetime'] : '',
                'kedcd' => $kedcd_from_payload,
                'bizno' => $bizno,
            );
            return $base_payload;
        }

        company_list_ai_api_response(400, array('error' => 'kedcd(10자리) 또는 bizno(10자리)가 필요합니다.'));
    }
}

$action = isset($_GET['action']) ? trim((string)$_GET['action']) : 'recommend';
$trace_id = company_list_ai_normalize_trace_id($_GET['trace_id'] ?? '');
if ($action !== 'recommend' && $action !== 'target_card') {
    company_list_ai_api_response(400, array('error' => 'action은 recommend 또는 target_card만 지원합니다.'));
}

// [API 목록 추천]
// API 사용처에는 구매처/판매처를 함께 반환하되, 두 방향의 캐시 조회·EC2 호출·출력 로그는 각각 분리한다.
if ($action === 'recommend') {
    $kedcd_raw = isset($_GET['kedcd']) ? (string)$_GET['kedcd'] : '';
    $bizno_raw = isset($_GET['bizno']) ? (string)$_GET['bizno'] : (isset($_GET['comnum']) ? (string)$_GET['comnum'] : '');

    $resolve_meta = array();
    $base_payload = company_list_ai_api_resolve_source_base_payload($kedcd_raw, $bizno_raw, $_GET, $resolve_meta);

    $buy_payload = $base_payload;
    $buy_payload['trade_type'] = 1;
    $sell_payload = $base_payload;
    $sell_payload['trade_type'] = 2;

    $ml_log_bizno = isset($resolve_meta['bizno']) ? company_list_ai_api_normalize_bizno($resolve_meta['bizno']) : '';
    $ml_log_kedcd = isset($resolve_meta['kedcd']) ? company_list_ai_api_normalize_kedcd($resolve_meta['kedcd']) : '';

    $timeout_sec = 8;
    $trace_context = array('trace_id' => $trace_id, 'match_type' => 'full');

    // 목록은 화면 경로와 동일하게 buy/sell 각각 한 달 캐시를 우선 조회
    $buy_call = null;
    company_list_ai_write_event_log('recommend_request', 'success', array(
        'trace_id' => $trace_id,
        'stage' => 'request',
        'source' => 'company_ml_log',
        'recommendation_mode' => 'full',
        'bizno' => $ml_log_bizno,
        'kedcd' => $ml_log_kedcd,
        'trade_type' => 1,
        'request_data' => $buy_payload,
    ), 'API 추천 요청 조건 및 한 달 캐시 조회');
    $buy_cached_row = company_list_ai_pick_ml_log($ml_log_bizno, $ml_log_kedcd, 1, false, 1);
    $buy_cached_call = company_list_ai_restore_call_from_ml_log($buy_cached_row);
    if (is_array($buy_cached_call)) $buy_call = $buy_cached_call;
    else $buy_call = company_list_ai_call_recommend($buy_payload, $timeout_sec, $trace_context);
    if (!empty($buy_call['from_cache'])) {
        company_list_ai_write_event_log('recommend_response', 'success', array(
            'trace_id' => $trace_id,
            'stage' => 'response',
            'source' => 'company_ml_log',
            'recommendation_mode' => 'full',
            'bizno' => $ml_log_bizno,
            'kedcd' => $ml_log_kedcd,
            'trade_type' => 1,
            'from_cache' => 1,
            'result_count' => count((array)($buy_call['data']['results'] ?? array())),
            'response_data' => company_list_ai_summarize_recommend_results($buy_call['data']),
        ), 'API 한 달 추천 캐시 응답 사용');
    }

    $sell_call = null;
    company_list_ai_write_event_log('recommend_request', 'success', array(
        'trace_id' => $trace_id,
        'stage' => 'request',
        'source' => 'company_ml_log',
        'recommendation_mode' => 'full',
        'bizno' => $ml_log_bizno,
        'kedcd' => $ml_log_kedcd,
        'trade_type' => 2,
        'request_data' => $sell_payload,
    ), 'API 추천 요청 조건 및 한 달 캐시 조회');
    $sell_cached_row = company_list_ai_pick_ml_log($ml_log_bizno, $ml_log_kedcd, 2, false, 1);
    $sell_cached_call = company_list_ai_restore_call_from_ml_log($sell_cached_row);
    if (is_array($sell_cached_call)) $sell_call = $sell_cached_call;
    else $sell_call = company_list_ai_call_recommend($sell_payload, $timeout_sec, $trace_context);
    if (!empty($sell_call['from_cache'])) {
        company_list_ai_write_event_log('recommend_response', 'success', array(
            'trace_id' => $trace_id,
            'stage' => 'response',
            'source' => 'company_ml_log',
            'recommendation_mode' => 'full',
            'bizno' => $ml_log_bizno,
            'kedcd' => $ml_log_kedcd,
            'trade_type' => 2,
            'from_cache' => 1,
            'result_count' => count((array)($sell_call['data']['results'] ?? array())),
            'response_data' => company_list_ai_summarize_recommend_results($sell_call['data']),
        ), 'API 한 달 추천 캐시 응답 사용');
    }

    // 실호출 성공 시에만 company_ml_log 적재
    if (!empty($buy_call['ok']) && empty($buy_call['from_cache'])) {
        company_list_ai_save_ml_log($ml_log_bizno, $ml_log_kedcd, 1, $buy_call);
    }
    if (!empty($sell_call['ok']) && empty($sell_call['from_cache'])) {
        company_list_ai_save_ml_log($ml_log_bizno, $ml_log_kedcd, 2, $sell_call);
    }

    if (empty($buy_call['ok']) && empty($sell_call['ok'])) {
        company_list_ai_api_response(502, array(
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
        ));
    }

    $buy_results = company_list_ai_api_extract_results($buy_call);
    $sell_results = company_list_ai_api_extract_results($sell_call);

    $buy_elapsed_ms = isset($buy_call['data']['elapsed_ms']) && is_numeric($buy_call['data']['elapsed_ms'])
        ? (int)$buy_call['data']['elapsed_ms']
        : (isset($buy_call['elapsed_ms']) ? (int)$buy_call['elapsed_ms'] : 0);
    $sell_elapsed_ms = isset($sell_call['data']['elapsed_ms']) && is_numeric($sell_call['data']['elapsed_ms'])
        ? (int)$sell_call['data']['elapsed_ms']
        : (isset($sell_call['elapsed_ms']) ? (int)$sell_call['elapsed_ms'] : 0);

    $response = array(
        'kedcd' => isset($resolve_meta['kedcd']) ? (string)$resolve_meta['kedcd'] : '',
        'buy' => company_list_ai_build_bucket($buy_results, 45),
        'sell' => company_list_ai_build_bucket($sell_results, 45),
        'buy_elapsed_ms' => $buy_elapsed_ms,
        'sell_elapsed_ms' => $sell_elapsed_ms,
    );

    if ($ml_log_bizno !== '') $response['bizno'] = $ml_log_bizno;
    if (!empty($resolve_meta['source'])) $response['company_source'] = (string)$resolve_meta['source'];
    if (!empty($resolve_meta['cached_datetime'])) $response['company_cached_at'] = (string)$resolve_meta['cached_datetime'];

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

    // API 출력도 구매처/판매처를 분리해 trade_type별 요청-응답-출력 흐름을 완성한다.
    company_list_ai_write_event_log('recommend_card_output', !empty($buy_call['ok']) ? 'success' : 'fail', array(
        'trace_id' => $trace_id,
        'stage' => 'card_output',
        'source' => 'company_list_ai_api',
        'recommendation_mode' => 'full',
        'bizno' => $ml_log_bizno,
        'kedcd' => $ml_log_kedcd,
        'trade_type' => 1,
        'from_cache' => !empty($buy_call['from_cache']),
        'result_count' => count($buy_results),
        'error_code' => !empty($buy_call['ok']) ? '' : 'recommend_failed',
        'output_data' => company_list_ai_summarize_recommend_results(array('results' => $buy_results)),
    ), !empty($buy_call['ok']) ? 'API 구매처 추천 결과 출력 완료' : 'API 구매처 추천 결과 출력 실패');
    company_list_ai_write_event_log('recommend_card_output', !empty($sell_call['ok']) ? 'success' : 'fail', array(
        'trace_id' => $trace_id,
        'stage' => 'card_output',
        'source' => 'company_list_ai_api',
        'recommendation_mode' => 'full',
        'bizno' => $ml_log_bizno,
        'kedcd' => $ml_log_kedcd,
        'trade_type' => 2,
        'from_cache' => !empty($sell_call['from_cache']),
        'result_count' => count($sell_results),
        'error_code' => !empty($sell_call['ok']) ? '' : 'recommend_failed',
        'output_data' => company_list_ai_summarize_recommend_results(array('results' => $sell_results)),
    ), !empty($sell_call['ok']) ? 'API 판매처 추천 결과 출력 완료' : 'API 판매처 추천 결과 출력 실패');

    company_list_ai_api_response(200, $response);
}

// [API 상세 카드 복원]
// src 기업과 trade_type에 맞는 추천 결과에서 target 기업 1건만 찾아 상세 화면에 내려준다.
// 캐시에서 찾지 못한 경우에만 EC2를 호출하고, 요청/응답/출력 단계를 같은 trace_id로 기록한다.
$src_kedcd_raw = isset($_GET['src_kedcd']) ? (string)$_GET['src_kedcd'] : (isset($_GET['kedcd']) ? (string)$_GET['kedcd'] : '');
$bizno_raw = isset($_GET['bizno']) ? (string)$_GET['bizno'] : (isset($_GET['comnum']) ? (string)$_GET['comnum'] : '');
$target_kedcd = company_list_ai_api_normalize_kedcd(
    isset($_GET['tgt_kedcd']) ? (string)$_GET['tgt_kedcd'] : (isset($_GET['target_kedcd']) ? (string)$_GET['target_kedcd'] : '')
);
$trade_type = (isset($_GET['trade_type']) && (int)$_GET['trade_type'] === 2) ? 2 : 1;

if ($target_kedcd === '') {
    company_list_ai_api_response(400, array('error' => 'tgt_kedcd(10자리)가 필요합니다.'));
}

$resolve_meta = array();
$base_payload = company_list_ai_api_resolve_source_base_payload($src_kedcd_raw, $bizno_raw, $_GET, $resolve_meta);
$src_kedcd = isset($resolve_meta['kedcd']) ? company_list_ai_api_normalize_kedcd($resolve_meta['kedcd']) : '';
$ml_log_bizno = isset($resolve_meta['bizno']) ? company_list_ai_api_normalize_bizno($resolve_meta['bizno']) : '';
// EC2 추천 캐시 1개월만 재사용
$ml_cache_window_months = 1;

$cache_candidates = array(
    array('today_only' => true, 'source' => 'company_ml_log_today'),
    // 최신 캐시도 무제한이 아닌 1개월 이내만 허용
    array('today_only' => false, 'source' => 'company_ml_log_latest_1m'),
);
$checked_cache_sources = array();

company_list_ai_write_event_log('recommend_request', 'success', array(
    'trace_id' => $trace_id,
    'stage' => 'request',
    'source' => 'company_ml_log',
    'recommendation_mode' => 'full',
    'bizno' => $ml_log_bizno,
    'kedcd' => $src_kedcd,
    'trade_type' => $trade_type,
    'request_data' => array(
        'action' => 'target_card',
        'src_kedcd' => $src_kedcd,
        'tgt_kedcd' => $target_kedcd,
        'trade_type' => $trade_type,
        'cache_window_months' => $ml_cache_window_months,
    ),
), 'API 상세 카드 요청 조건 및 한 달 캐시 조회');

foreach ($cache_candidates as $candidate) {
    $today_only = !empty($candidate['today_only']);
    $cache_source = (string)$candidate['source'];
    // today_only=false 인 latest 조회도 1개월 제한을 적용
    $row = company_list_ai_pick_ml_log($ml_log_bizno, $src_kedcd, $trade_type, $today_only, $ml_cache_window_months);
    if (!is_array($row) || empty($row['c_content'])) continue;

    $checked_cache_sources[] = $cache_source;
    $call_result = company_list_ai_restore_call_from_ml_log($row);
    if (!is_array($call_result)) continue;

    // DB JSON(results)에서 target kedcd 1건 매핑
    $results = company_list_ai_api_extract_results($call_result);
    $found = company_list_ai_api_find_target_item($results, $target_kedcd);
    if (!is_array($found)) continue;

    company_list_ai_write_event_log('recommend_response', 'success', array(
        'trace_id' => $trace_id,
        'stage' => 'response',
        'source' => 'company_ml_log',
        'recommendation_mode' => 'full',
        'bizno' => $ml_log_bizno,
        'kedcd' => $src_kedcd,
        'trade_type' => $trade_type,
        'from_cache' => 1,
        'result_count' => 1,
        'response_data' => array('target_kedcd' => $target_kedcd, 'cache_source' => $cache_source),
    ), 'API 상세 카드 한 달 캐시 응답 사용');
    company_list_ai_write_event_log('recommend_card_output', 'success', array(
        'trace_id' => $trace_id,
        'stage' => 'card_output',
        'source' => 'company_list_ai_api',
        'recommendation_mode' => 'full',
        'bizno' => $ml_log_bizno,
        'kedcd' => $src_kedcd,
        'trade_type' => $trade_type,
        'from_cache' => 1,
        'result_count' => 1,
        'output_data' => array('target_kedcd' => $target_kedcd, 'item' => $found),
    ), 'API 상세 추천 카드 출력 완료');

    company_list_ai_api_response(200, array(
        'src_kedcd' => $src_kedcd,
        'tgt_kedcd' => $target_kedcd,
        'trade_type' => $trade_type,
        'item' => $found,
        'from_cache' => true,
        'cache_source' => $cache_source,
        'cache_datetime' => isset($row['c_datetime']) ? (string)$row['c_datetime'] : '',
    ));
}

// 캐시 미스 시에만 EC2 호출로 보강
$payload = $base_payload;
$payload['trade_type'] = $trade_type;
$live_call = company_list_ai_call_recommend($payload, 8, array(
    'trace_id' => $trace_id,
    'match_type' => 'target_card',
));
if (!empty($live_call['ok']) && empty($live_call['from_cache'])) {
    company_list_ai_save_ml_log($ml_log_bizno, $src_kedcd, $trade_type, $live_call);
}

if (empty($live_call['ok'])) {
    company_list_ai_api_response(502, array(
        'error' => isset($live_call['error']) ? $live_call['error'] : 'EC2 연결 실패',
        'src_kedcd' => $src_kedcd,
        'tgt_kedcd' => $target_kedcd,
        'trade_type' => $trade_type,
        'cache_checked' => $checked_cache_sources,
        'http_code' => isset($live_call['http_code']) ? (int)$live_call['http_code'] : 0,
    ));
}

$live_results = company_list_ai_api_extract_results($live_call);
$live_found = company_list_ai_api_find_target_item($live_results, $target_kedcd);
if (!is_array($live_found)) {
    company_list_ai_write_event_log('recommend_card_output', 'fail', array(
        'trace_id' => $trace_id,
        'stage' => 'card_output',
        'source' => 'company_list_ai_api',
        'recommendation_mode' => 'full',
        'bizno' => $ml_log_bizno,
        'kedcd' => $src_kedcd,
        'trade_type' => $trade_type,
        'result_count' => 0,
        'error_code' => 'target_not_found',
        'output_data' => array('target_kedcd' => $target_kedcd),
    ), '추천 결과에서 API 상세 카드 대상을 찾지 못했습니다.');
    company_list_ai_api_response(404, array(
        'error' => '추천 결과에서 대상 기업을 찾지 못했습니다.',
        'src_kedcd' => $src_kedcd,
        'tgt_kedcd' => $target_kedcd,
        'trade_type' => $trade_type,
        'result_total' => count($live_results),
        'cache_checked' => $checked_cache_sources,
    ));
}

company_list_ai_write_event_log('recommend_card_output', 'success', array(
    'trace_id' => $trace_id,
    'stage' => 'card_output',
    'source' => 'company_list_ai_api',
    'recommendation_mode' => 'full',
    'bizno' => $ml_log_bizno,
    'kedcd' => $src_kedcd,
    'trade_type' => $trade_type,
    'from_cache' => 0,
    'result_count' => 1,
    'output_data' => array('target_kedcd' => $target_kedcd, 'item' => $live_found),
), 'API 상세 추천 카드 출력 완료');

company_list_ai_api_response(200, array(
    'src_kedcd' => $src_kedcd,
    'tgt_kedcd' => $target_kedcd,
    'trade_type' => $trade_type,
    'item' => $live_found,
    'from_cache' => false,
    'cache_source' => '',
    'cache_datetime' => '',
));
