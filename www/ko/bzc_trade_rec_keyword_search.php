<?php
include_once(__DIR__ . '/_common.php');
include_once(G5_PATH . '/ko/helper/company_list_ai_helper.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function bzc_trade_rec_search_response($status, $payload)
{
    http_response_code((int)$status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function bzc_trade_rec_search_normalize_query($value)
{
    $value = trim(preg_replace('/\s+/u', ' ', (string)$value));
    if ($value === '') return '';
    if (!preg_match('/^[\p{L}\p{N}\s·&()+,._\/-]+$/u', $value)) return '';
    $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    return ($length >= 2 && $length <= 30) ? $value : '';
}

function bzc_trade_rec_search_parse_keywords($keywords_json)
{
    $decoded = json_decode((string)$keywords_json, true);
    if (!is_array($decoded)) return array();

    $keywords = array();
    foreach ($decoded as $value) {
        $keyword = trim((string)$value);
        if ($keyword === '' || in_array($keyword, $keywords, true)) continue;
        $keywords[] = $keyword;
    }
    return $keywords;
}

function bzc_trade_rec_search_contains($haystack, $needle)
{
    if (function_exists('mb_stripos')) return mb_stripos((string)$haystack, (string)$needle, 0, 'UTF-8') !== false;
    return stripos((string)$haystack, (string)$needle) !== false;
}

function bzc_trade_rec_search_fetch_rows($source_where, $query_sql, $trade_type)
{
    // 추천 키워드와 업종명을 함께 검색해 사용자가 입력한 품목과 연결되는 후보를 찾는다.
    $industry_select = "COALESCE(k.k_code_name, k9.k_code_name, '') AS industry_name";
    $industry_joins = "LEFT JOIN kssc k ON k.k_code = r.c_partner_bzc
        LEFT JOIN kssc_9 k9 ON k9.k_code = r.c_partner_bzc";
    $query_where = "(r.c_partner_kw LIKE '%{$query_sql}%'
            OR k.k_code_name LIKE '%{$query_sql}%'
            OR k9.k_code_name LIKE '%{$query_sql}%')";
    $sql = "
        SELECT
            r.c_source_bzc,
            r.c_partner_bzc,
            r.c_partner_kw,
            r.c_trade_cnt,
            {$industry_select}
        FROM bzc_trade_rec r
        {$industry_joins}
        WHERE r.c_trade_type = " . (int)$trade_type . "
          AND ({$source_where})
          AND ({$query_where})
        ORDER BY r.c_trade_cnt DESC
        LIMIT 200
    ";
    $result = sql_query($sql, false);
    if (!$result) return false;

    $rows = array();
    while ($row = sql_fetch_array($result)) $rows[] = $row;
    return $rows;
}

function bzc_trade_rec_search_append_items(&$items, &$seen, $rows, $query, $match_type, $relevance_label, $max_add = 10)
{
    if (!is_array($rows)) return;
    $added = 0;
    foreach ($rows as $row) {
        $partner_bzc = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($row['c_partner_bzc'] ?? '')), 0, 10));
        if ($partner_bzc === '') continue;

        $labels = array();
        foreach (bzc_trade_rec_search_parse_keywords($row['c_partner_kw'] ?? '') as $keyword) {
            if (bzc_trade_rec_search_contains($keyword, $query)) $labels[] = $keyword;
        }
        $industry_name = trim((string)($row['industry_name'] ?? ''));
        if ($industry_name !== '' && bzc_trade_rec_search_contains($industry_name, $query)) $labels[] = $industry_name;

        foreach ($labels as $label) {
            $item_key = $partner_bzc . '|' . $label;
            if (isset($seen[$item_key])) continue;
            $seen[$item_key] = true;
            $items[] = array(
                'source_bzc' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($row['c_source_bzc'] ?? '')), 0, 10)),
                'partner_bzc' => $partner_bzc,
                'partner_cd' => simple_crypt($partner_bzc, 'e'),
                'keywords' => array($label),
                'label' => $label,
                'trade_cnt' => (int)($row['c_trade_cnt'] ?? 0),
                'match_type' => $match_type,
                'relevance_label' => $relevance_label,
                'selection_source' => 'custom',
            );
            $added++;
            if (count($items) >= 10 || $added >= (int)$max_add) return;
        }
    }
}

function bzc_trade_rec_search_sort_items(&$items)
{
    // 후보 수량을 단계별로 채운 뒤 화면에는 관련도 높은 순서와 같은 단계의 거래량 순서로 정렬한다.
    $priority = array('exact' => 1, 'prefix_5' => 2, 'prefix_4' => 3, 'all' => 4);
    usort($items, function ($a, $b) use ($priority) {
        $a_priority = $priority[(string)($a['match_type'] ?? '')] ?? 99;
        $b_priority = $priority[(string)($b['match_type'] ?? '')] ?? 99;
        if ($a_priority !== $b_priority) return $a_priority <=> $b_priority;

        $trade_compare = (int)($b['trade_cnt'] ?? 0) <=> (int)($a['trade_cnt'] ?? 0);
        if ($trade_compare !== 0) return $trade_compare;
        return strcmp((string)($a['label'] ?? ''), (string)($b['label'] ?? ''));
    });
}

$action = trim((string)($_POST['action'] ?? $_GET['action'] ?? 'search'));
$query = bzc_trade_rec_search_normalize_query($_POST['query'] ?? $_GET['query'] ?? '');
$source_bzc = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($_POST['bzc_cd'] ?? $_GET['bzc_cd'] ?? '')), 0, 10));
$trade_type = (int)($_POST['trade_type'] ?? $_GET['trade_type'] ?? 0);
$search_scope = trim((string)($_POST['scope'] ?? $_GET['scope'] ?? 'related')) === 'all' ? 'all' : 'related';
$trace_id = company_list_ai_normalize_trace_id($_POST['trace_id'] ?? $_GET['trace_id'] ?? '');

if (!in_array($trade_type, array(1, 2), true)) {
    bzc_trade_rec_search_response(422, array('ok' => false, 'error' => 'trade_type_required', 'items' => array()));
}
if ($source_bzc === '') {
    bzc_trade_rec_search_response(422, array('ok' => false, 'error' => 'bzc_cd_required', 'items' => array()));
}
if ($query === '') {
    bzc_trade_rec_search_response(422, array('ok' => false, 'error' => 'invalid_query', 'items' => array()));
}

if ($action === 'select') {
    $partner_cd = trim((string)($_POST['partner_cd'] ?? ''));
    $partner_bzc = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)simple_crypt($partner_cd, 'd')), 0, 10));
    $label = trim((string)($_POST['label'] ?? ''));
    $match_type = trim((string)($_POST['match_type'] ?? 'exact'));
    if ($partner_bzc === '' || $label === '') {
        bzc_trade_rec_search_response(422, array('ok' => false, 'error' => 'invalid_selection'));
    }

    bzc_trade_rec_search_response(200, array('ok' => true));
}

$query_sql = sql_real_escape_string($query);
$source_bzc_sql = sql_real_escape_string($source_bzc);
$items = array();
$seen = array();
$query_failed = false;

if ($search_scope === 'all') {
    // 사용자가 명시적으로 범위를 넓힌 경우에만 전체 업종에서 검색한다.
    $rows = bzc_trade_rec_search_fetch_rows("1=1", $query_sql, $trade_type);
    if ($rows === false) $query_failed = true;
    else bzc_trade_rec_search_append_items($items, $seen, $rows, $query, 'all', '다른 업종');
} else {
    // 관련도 높은 순서로 exact → 앞 5자리 → 앞 4자리 결과를 채워 최대 10개를 반환한다.
    $prefix_5 = substr($source_bzc, 0, 5);
    $prefix_4 = substr($source_bzc, 0, 4);
    $tiers = array(
        array("r.c_source_bzc = '{$source_bzc_sql}'", 'exact', '관련도 높음', 5),
        array("r.c_source_bzc LIKE '" . sql_real_escape_string($prefix_5 . '%') . "' AND r.c_source_bzc != '{$source_bzc_sql}'", 'prefix_5', '관련 업종', 3),
        array("r.c_source_bzc LIKE '" . sql_real_escape_string($prefix_4 . '%') . "' AND r.c_source_bzc NOT LIKE '" . sql_real_escape_string($prefix_5 . '%') . "'", 'prefix_4', '유사 업종', 2),
    );
    $tier_rows = array();
    foreach ($tiers as $idx => $tier) {
        $rows = bzc_trade_rec_search_fetch_rows($tier[0], $query_sql, $trade_type);
        if ($rows === false) {
            $query_failed = true;
            break;
        }
        $tier_rows[$idx] = $rows;
        bzc_trade_rec_search_append_items($items, $seen, $rows, $query, $tier[1], $tier[2], $tier[3]);
    }
    // 각 단계 권장 수량을 먼저 확보한 뒤 남은 자리는 관련도 높은 단계부터 다시 채운다.
    if (!$query_failed && count($items) < 10) {
        foreach ($tiers as $idx => $tier) {
            if (count($items) >= 10) break;
            bzc_trade_rec_search_append_items($items, $seen, $tier_rows[$idx] ?? array(), $query, $tier[1], $tier[2], 10 - count($items));
        }
    }
}

if ($query_failed) {
    company_list_ai_write_event_log('custom_keyword_search', 'fail', array(
        'action' => 'search_custom_keyword',
        'trace_id' => $trace_id,
        'stage' => 'keyword_search',
        'source' => 'bzc_trade_rec+kssc+kssc_9',
        'recommendation_mode' => 'keyword',
        'trade_type' => $trade_type,
        'source_bzc_cd' => $source_bzc,
        'match_type' => 'query_failed',
        'keywords' => $query,
        'error_code' => 'query_failed',
        'request_data' => array('query' => $query, 'search_scope' => $search_scope),
    ), '직접 입력 품목 검색 실패');
    bzc_trade_rec_search_response(500, array('ok' => false, 'error' => 'query_failed', 'items' => array()));
}

bzc_trade_rec_search_sort_items($items);
$match_types = array_values(array_unique(array_map(function ($item) {
    return (string)($item['match_type'] ?? '');
}, $items)));
$log_match_type = empty($match_types) ? 'no_match' : ($search_scope === 'all' ? 'all' : (count($match_types) > 1 ? 'related_mixed' : $match_types[0]));
$log_recorded = false;

bzc_trade_rec_search_response(200, array(
    'ok' => true,
    'query' => $query,
    'trade_type' => $trade_type,
    'search_scope' => $search_scope,
    'can_expand' => $search_scope === 'related' && empty($items),
    'limit' => 10,
    'trace_id' => $trace_id,
    'log_recorded' => $log_recorded,
    'items' => $items,
));
?>
