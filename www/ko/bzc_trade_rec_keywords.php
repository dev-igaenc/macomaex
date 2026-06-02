<?php
include_once(__DIR__ . '/_common.php');

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

$bzc_cd = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($_GET['bzc_cd'] ?? '')), 0, 10));
$trade_type = (int)($_GET['trade_type'] ?? 1);
if (!in_array($trade_type, array(1, 2), true)) $trade_type = 1;

if ($bzc_cd === '') {
    bzc_trade_rec_json_response(array(
        'ok' => false,
        'error' => 'bzc_cd is required',
        'items' => array(),
    ));
}

$bzc_cd_sql = sql_real_escape_string($bzc_cd);
$trade_type_sql = (int)$trade_type;

$sql = "
    SELECT c_partner_bzc, c_partner_kw, c_trade_cnt
    FROM bzc_trade_rec
    WHERE c_source_bzc = '{$bzc_cd_sql}'
      AND c_trade_type = {$trade_type_sql}
      AND c_trade_cnt >= 15
    ORDER BY c_trade_cnt DESC
    LIMIT 15
";
$result = sql_query($sql, false);

if (!$result) {
    bzc_trade_rec_json_response(array(
        'ok' => false,
        'error' => 'query failed',
        'bzc_cd' => $bzc_cd,
        'trade_type' => $trade_type,
        'items' => array(),
    ));
}

$items = array();
while ($row = sql_fetch_array($result)) {
    $partner_bzc = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($row['c_partner_bzc'] ?? '')), 0, 10));
    if ($partner_bzc === '') continue;

    $keywords = bzc_trade_rec_parse_keywords($row['c_partner_kw'] ?? '');
    if (empty($keywords)) continue;

    $items[] = array(
        'partner_bzc' => $partner_bzc,
        'keywords' => $keywords,
        'label' => implode(', ', $keywords),
        'trade_cnt' => (int)($row['c_trade_cnt'] ?? 0),
    );
}

bzc_trade_rec_json_response(array(
    'ok' => true,
    'bzc_cd' => $bzc_cd,
    'trade_type' => $trade_type,
    'limit' => 15,
    'min_trade_cnt' => 15,
    'items' => $items,
));
?>
