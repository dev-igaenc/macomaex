<?php
include_once(__DIR__ . '/_common.php');
include_once(G5_PATH . '/ko/helper/company_list_ai_helper.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function bzc_trade_rec_json_response($payload)
{
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function bzc_trade_rec_parse_keywords($keywords_json)
{
    $decoded = json_decode((string)$keywords_json, true);
    if (!is_array($decoded)) return array();

    $keywords = array();
    foreach ($decoded as $value) {
        $keyword = trim((string)$value);
        if ($keyword === '') continue;
        if (in_array($keyword, $keywords, true)) continue;
        $keywords[] = $keyword;
    }

    return $keywords;
}

// [키워드 조회 조건 정규화]
// 기준 기업 업종코드와 거래 방향을 정리하고, 이후 추천 목록까지 같은 trace_id로 연결한다.
// 키워드 후보도 최종 추천과 같은 방향을 사용한다. 1=구매처, 2=판매처.
$bzc_cd = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($_GET['bzc_cd'] ?? '')), 0, 10));
$trade_type = (int)($_GET['trade_type'] ?? 1);
if (!in_array($trade_type, array(1, 2), true)) $trade_type = 1;
$trace_id = company_list_ai_normalize_trace_id($_GET['trace_id'] ?? '');
// 업종코드가 비어도 scope=all이면 성공한 예외 흐름으로 전체 업종 후보를 조회한다.
$keyword_scope = trim((string)($_GET['scope'] ?? 'related')) === 'all' ? 'all' : 'related';
$source_bzc_missing = (int)($_GET['source_bzc_missing'] ?? 0) ? 1 : 0;
$source_bzc_resolved = (int)($_GET['source_bzc_resolved'] ?? 0) ? 1 : 0;
$source_bzc_resolve_source = trim((string)($_GET['source_bzc_resolve_source'] ?? ''));
$fallback_reason = trim((string)($_GET['fallback_reason'] ?? ''));

if ($bzc_cd === '' && $keyword_scope !== 'all') {
    company_list_ai_write_event_log('keyword_lookup', 'fail', array(
        'action' => 'lookup_keywords',
        'trace_id' => $trace_id,
        'stage' => 'keyword_lookup',
        'source' => 'bzc_trade_rec',
        'recommendation_mode' => 'keyword',
        'trade_type' => $trade_type,
        'reason' => 'missing_bzc_cd',
        'error_code' => 'missing_bzc_cd',
        'keyword_scope' => $keyword_scope,
    ), '추천 키워드 조회에 업종코드가 필요합니다.');
    bzc_trade_rec_json_response(array(
        'ok' => false,
        'error' => 'bzc_cd is required',
        'items' => array(),
    ));
}

$bzc_cd_sql = sql_real_escape_string($bzc_cd);
$trade_type_sql = (int)$trade_type;

function bzc_trade_rec_fetch_rows($where_sql, $trade_type_sql)
{
    // [키워드 후보 조회]
    // 기준 업종 조건과 거래 방향이 같은 행을 거래량 순으로 조회한다.
    // c_partner_bzc는 EC2에 전달할 상대 업종코드, c_partner_kw는 화면에 표시할 키워드다.
    $sql = "
        SELECT c_source_bzc, c_partner_bzc, c_partner_kw, c_trade_cnt
        FROM bzc_trade_rec
        WHERE {$where_sql}
          AND c_trade_type = {$trade_type_sql}
        ORDER BY c_trade_cnt DESC
        LIMIT 15
    ";
    $result = sql_query($sql, false);
    if (!$result) return false;

    $rows = array();
    while ($row = sql_fetch_array($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function bzc_trade_rec_rows_have_keywords($rows)
{
    // 조회 행이 존재하더라도 c_partner_kw가 모두 비어 있으면 다음 fallback 단계로 이동한다.
    if (!is_array($rows) || empty($rows)) return false;

    foreach ($rows as $row) {
        if (!empty(bzc_trade_rec_parse_keywords($row['c_partner_kw'] ?? ''))) {
            return true;
        }
    }

    return false;
}

$match_type = 'exact';
if ($keyword_scope === 'all') {
    // 기준 업종코드가 없을 때는 전체 업종에서 거래량 기준 후보를 제시한다.
    $rows = bzc_trade_rec_fetch_rows("1=1", $trade_type_sql);
    $match_type = 'global_all';
    if ($rows === false) {
        company_list_ai_write_event_log('keyword_lookup', 'fail', array(
            'action' => 'lookup_keywords',
            'trace_id' => $trace_id,
            'stage' => 'keyword_lookup',
            'source' => 'bzc_trade_rec',
            'recommendation_mode' => 'keyword',
            'trade_type' => $trade_type,
            'source_bzc_cd' => $bzc_cd,
            'match_type' => $match_type,
            'keyword_scope' => $keyword_scope,
            'source_bzc_missing' => $source_bzc_missing,
            'source_bzc_resolved' => $source_bzc_resolved,
            'source_bzc_resolve_source' => $source_bzc_resolve_source,
            'fallback_reason' => $fallback_reason,
            'reason' => 'query_failed',
            'error_code' => 'query_failed',
        ), '전체 업종 추천 키워드 조회 실패');
        bzc_trade_rec_json_response(array(
            'ok' => false,
            'error' => 'all-scope query failed',
            'bzc_cd' => $bzc_cd,
            'trade_type' => $trade_type,
            'items' => array(),
        ));
    }
} else {
    // [1단계: 업종코드 완전 일치 조회]
    // exact는 기준 업종코드 전체가 완전히 같은 행을 뜻한다.
    // 예: 기준 코드가 C10710이면 먼저 c_source_bzc = 'C10710'만 조회한다.
    $rows = bzc_trade_rec_fetch_rows("c_source_bzc = '{$bzc_cd_sql}'", $trade_type_sql);
    if ($rows === false) {
        company_list_ai_write_event_log('keyword_lookup', 'fail', array(
            'action' => 'lookup_keywords',
            'trace_id' => $trace_id,
            'stage' => 'keyword_lookup',
            'source' => 'bzc_trade_rec',
            'recommendation_mode' => 'keyword',
            'trade_type' => $trade_type,
            'source_bzc_cd' => $bzc_cd,
            'match_type' => 'exact',
            'keyword_scope' => $keyword_scope,
            'reason' => 'query_failed',
            'error_code' => 'query_failed',
        ), '추천 키워드 exact 조회 실패');
        bzc_trade_rec_json_response(array(
            'ok' => false,
            'error' => 'query failed',
            'bzc_cd' => $bzc_cd,
            'trade_type' => $trade_type,
            'items' => array(),
        ));
    }

    if (!bzc_trade_rec_rows_have_keywords($rows) && strlen($bzc_cd) >= 5) {
        // [2단계: 앞 5자리 fallback]
        // 완전 일치 행에 사용할 키워드가 없으면 같은 5자리 분류로 범위를 넓힌다.
        // 예: C10710 -> c_source_bzc LIKE 'C1071%'
        $prefix_sql = sql_real_escape_string(substr($bzc_cd, 0, 5) . '%');
        $rows = bzc_trade_rec_fetch_rows("c_source_bzc LIKE '{$prefix_sql}'", $trade_type_sql);
        if ($rows === false) {
            company_list_ai_write_event_log('keyword_lookup', 'fail', array(
                'action' => 'lookup_keywords',
                'trace_id' => $trace_id,
                'stage' => 'keyword_lookup',
                'source' => 'bzc_trade_rec',
                'recommendation_mode' => 'keyword',
                'trade_type' => $trade_type,
                'source_bzc_cd' => $bzc_cd,
                'match_type' => 'prefix_5',
                'keyword_scope' => $keyword_scope,
                'reason' => 'query_failed',
                'error_code' => 'query_failed',
            ), '추천 키워드 5자리 fallback 조회 실패');
            bzc_trade_rec_json_response(array(
                'ok' => false,
                'error' => '5-digit fallback query failed',
                'bzc_cd' => $bzc_cd,
                'trade_type' => $trade_type,
                'items' => array(),
            ));
        }
        $match_type = 'prefix_5';
    }

    if (!bzc_trade_rec_rows_have_keywords($rows) && strlen($bzc_cd) >= 4) {
        // [3단계: 앞 4자리 fallback]
        // 5자리 범위에도 키워드가 없을 때 마지막으로 같은 4자리 분류까지 확장한다.
        // 예: C10710 -> c_source_bzc LIKE 'C107%'
        $prefix_sql = sql_real_escape_string(substr($bzc_cd, 0, 4) . '%');
        $rows = bzc_trade_rec_fetch_rows("c_source_bzc LIKE '{$prefix_sql}'", $trade_type_sql);
        if ($rows === false) {
            company_list_ai_write_event_log('keyword_lookup', 'fail', array(
                'action' => 'lookup_keywords',
                'trace_id' => $trace_id,
                'stage' => 'keyword_lookup',
                'source' => 'bzc_trade_rec',
                'recommendation_mode' => 'keyword',
                'trade_type' => $trade_type,
                'source_bzc_cd' => $bzc_cd,
                'match_type' => 'prefix_4',
                'keyword_scope' => $keyword_scope,
                'reason' => 'query_failed',
                'error_code' => 'query_failed',
            ), '추천 키워드 4자리 fallback 조회 실패');
            bzc_trade_rec_json_response(array(
                'ok' => false,
                'error' => '4-digit fallback query failed',
                'bzc_cd' => $bzc_cd,
                'trade_type' => $trade_type,
                'items' => array(),
            ));
        }
        $match_type = 'prefix_4';
    }
}

$items = array();
$seen_items = array();
$matched_source_bzc_cds = array();
$matched_keywords = array();
// [화면 표시용 키워드 후보 구성]
// 상대 업종코드+키워드가 같은 항목은 제거하고, 거래량 우선순위를 유지한 채 최대 15개만 반환한다.
foreach ($rows as $row) {
    $partner_bzc = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($row['c_partner_bzc'] ?? '')), 0, 10));
    if ($partner_bzc === '') continue;

    $keywords = bzc_trade_rec_parse_keywords($row['c_partner_kw'] ?? '');
    if (empty($keywords)) continue;

    foreach ($keywords as $keyword) {
        $item_key = $partner_bzc . '|' . $keyword;
        if (isset($seen_items[$item_key])) continue;
        $seen_items[$item_key] = true;
        $matched_source_bzc = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($row['c_source_bzc'] ?? '')), 0, 10));
        if ($matched_source_bzc !== '' && !in_array($matched_source_bzc, $matched_source_bzc_cds, true)) {
            $matched_source_bzc_cds[] = $matched_source_bzc;
        }
        if (!in_array($keyword, $matched_keywords, true)) $matched_keywords[] = $keyword;

        $items[] = array(
            'source_bzc' => $matched_source_bzc,
            'partner_bzc' => $partner_bzc,
            'partner_cd' => simple_crypt($partner_bzc, 'e'),
            'keywords' => array($keyword),
            'label' => $keyword,
            'trade_cnt' => (int)($row['c_trade_cnt'] ?? 0),
            // 사용자가 추천 버튼을 누를 때 실제 선택 키워드의 fallback 단계를 서버 로그까지 전달한다.
            'match_type' => $match_type,
            'relevance_label' => $match_type === 'global_all' ? '전체 업종' : '',
        );
        if (count($items) >= 15) break 2;
    }
}

if ($keyword_scope === 'all') {
    // 실패 로그가 아니라 성공 로그에 fallback 메타를 남겨 운영에서 API 성공+업종코드 누락을 구분한다.
    company_list_ai_write_event_log('keyword_lookup', 'success', array(
        'action' => 'lookup_keywords',
        'trace_id' => $trace_id,
        'stage' => 'keyword_lookup',
        'source' => 'bzc_trade_rec',
        'recommendation_mode' => 'keyword',
        'trade_type' => $trade_type,
        'source_bzc_cd' => $bzc_cd,
        'match_type' => $match_type,
        'keyword_scope' => $keyword_scope,
        'source_bzc_missing' => $source_bzc_missing,
        'source_bzc_resolved' => $source_bzc_resolved,
        'source_bzc_resolve_source' => $source_bzc_resolve_source,
        'fallback_reason' => $fallback_reason,
        'result_count' => count($items),
        'matched_source_bzc_cds' => implode(',', $matched_source_bzc_cds),
        'keywords' => implode(',', $matched_keywords),
    ), '업종코드 누락으로 전체 업종 기준 추천 키워드 조회');
}

bzc_trade_rec_json_response(array(
    'ok' => true,
    'bzc_cd' => $bzc_cd,
    'trade_type' => $trade_type,
    'keyword_scope' => $keyword_scope,
    'limit' => 15,
    'trace_id' => $trace_id,
    'match_type' => $match_type,
    'items' => $items,
));
?>
