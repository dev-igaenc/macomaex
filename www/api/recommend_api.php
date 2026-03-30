<?php
chdir(__DIR__ . '/../');
include_once(__DIR__ . '/../_common.php');
include_once(__DIR__ . '/api_config.php');

// ============================================================
// 보안 헤더
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// ============================================================
// 1) 요청 방식 제한 (GET만 허용)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// ============================================================
// 2) IP 제한 (설정된 경우)
// ============================================================
if (API_ALLOWED_IPS !== '') {
    $allowed = array_map('trim', explode(',', API_ALLOWED_IPS));
    if (!in_array($_SERVER['REMOTE_ADDR'], $allowed)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
}

// ============================================================
// 3) Referer 체크 (외부 직접 호출 차단)
// ============================================================
if (!empty($_SERVER['HTTP_REFERER'])) {
    $ref_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    $my_host  = $_SERVER['HTTP_HOST'];
    if ($ref_host && $ref_host !== $my_host) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
}

// ============================================================
// 4) Rate Limit (세션 기반, 분당 제한)
// ============================================================
$rate_key = 'api_rate_' . $_SERVER['REMOTE_ADDR'];
$now = time();
if (!isset($_SESSION[$rate_key])) {
    $_SESSION[$rate_key] = ['count' => 0, 'reset' => $now + 60];
}
if ($now > $_SESSION[$rate_key]['reset']) {
    $_SESSION[$rate_key] = ['count' => 0, 'reset' => $now + 60];
}
$_SESSION[$rate_key]['count']++;
if ($_SESSION[$rate_key]['count'] > API_RATE_LIMIT) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}

// ============================================================
// 5) 입력값 검증
// ============================================================
$kedcd = isset($_GET['kedcd']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['kedcd']) : '';
$top_n = isset($_GET['top_n']) ? (int)$_GET['top_n'] : 50;

// kedcd 길이 제한
if (!$kedcd || strlen($kedcd) > 20) {
    http_response_code(400);
    echo json_encode(['error' => 'kedcd required (max 20 chars)']);
    exit;
}

// top_n 범위 제한
$top_n = max(1, min($top_n, 200));

// ============================================================
// 6) SQL은 이스케이프 적용 (그누보드 프레임워크 활용)
// ============================================================
$kedcd_safe = sql_escape_string($kedcd);

// 해당 기업 정보 조회
$sql = "SELECT BZC_CD, ENP_NM, SALES, LABORER_SUM FROM company WHERE KEDCD = '{$kedcd_safe}' LIMIT 1";
$row = sql_fetch($sql);

if (!$row['BZC_CD']) {
    http_response_code(404);
    echo json_encode(['error' => 'Company not found']);
    exit;
}

$bzc_cd   = sql_escape_string($row['BZC_CD']);
$enp_nm   = $row['ENP_NM'];
$my_sales = (int)$row['SALES'];

// ============================================================
// 7) industry_gap 조회
// ============================================================
// 판매처 gap
$sql_gap_sell = "
    SELECT norm_signed_sales_gap, norm_abs_sales_gap
    FROM industry_gap
    WHERE BZC_CD = '{$bzc_cd}' AND TXPL_CCD = '1'
    LIMIT 1
";
$gap_sell = sql_fetch($sql_gap_sell);

// 구매처 gap
$sql_gap_buy = "
    SELECT norm_signed_sales_gap, norm_abs_sales_gap
    FROM industry_gap
    WHERE BZC_CD = '{$bzc_cd}' AND TXPL_CCD = '2'
    LIMIT 1
";
$gap_buy = sql_fetch($sql_gap_buy);

// gap이 없으면 상위 업종코드로 fallback
if (!$gap_sell['norm_signed_sales_gap']) {
    $bzc_prefix = sql_escape_string(substr($bzc_cd, 0, 4));
    $sql_gap_sell = "
        SELECT norm_signed_sales_gap, norm_abs_sales_gap
        FROM industry_gap
        WHERE BZC_CD LIKE '{$bzc_prefix}%' AND TXPL_CCD = '1'
        ORDER BY ref_cnt DESC LIMIT 1
    ";
    $gap_sell = sql_fetch($sql_gap_sell);
}
if (!$gap_buy['norm_signed_sales_gap']) {
    $bzc_prefix = sql_escape_string(substr($bzc_cd, 0, 4));
    $sql_gap_buy = "
        SELECT norm_signed_sales_gap, norm_abs_sales_gap
        FROM industry_gap
        WHERE BZC_CD LIKE '{$bzc_prefix}%' AND TXPL_CCD = '2'
        ORDER BY ref_cnt DESC LIMIT 1
    ";
    $gap_buy = sql_fetch($sql_gap_buy);
}

// ============================================================
// 8) 규모 범위 계산
// ============================================================
function calc_sales_range($my_sales, $signed_gap, $abs_gap) {
    if ($my_sales <= 0) return [0, PHP_INT_MAX];

    $center_ratio = exp((float)$signed_gap);
    $center = $my_sales * $center_ratio;

    $spread = exp((float)$abs_gap * 2);
    $min_sales = max(0, (int)($center / $spread));
    $max_sales = (int)($center * $spread);

    return [$min_sales, $max_sales];
}

list($sell_min, $sell_max) = calc_sales_range(
    $my_sales,
    $gap_sell['norm_signed_sales_gap'] ?? -0.5,
    $gap_sell['norm_abs_sales_gap'] ?? 0.8
);
list($buy_min, $buy_max) = calc_sales_range(
    $my_sales,
    $gap_buy['norm_signed_sales_gap'] ?? -0.5,
    $gap_buy['norm_abs_sales_gap'] ?? 0.8
);

// ============================================================
// 9) 판매처/구매처 후보 추출
// ============================================================
$sell_min = (int)$sell_min;
$sell_max = (int)$sell_max;
$buy_min  = (int)$buy_min;
$buy_max  = (int)$buy_max;

$sql_sell = "
    SELECT DISTINCT c.KEDCD
    FROM company c
    JOIN bzc_trade_recommendation tr
      ON tr.partner_bzc = c.BZC_CD
    WHERE tr.source_bzc = '{$bzc_cd}'
      AND c.KEDCD <> '{$kedcd_safe}'
      AND c.ENP_SCD = '01'
      AND tr.score > 0
      AND c.SALES BETWEEN {$sell_min} AND {$sell_max}
    ORDER BY tr.score DESC
    LIMIT 3000
";
$result_sell = sql_query($sql_sell);
$cands_sell = [];
while ($r = sql_fetch_array($result_sell)) {
    $cands_sell[] = $r['KEDCD'];
}

$sql_buy = "
    SELECT DISTINCT c.KEDCD
    FROM company c
    JOIN bzc_trade_recommendation tr
      ON tr.partner_bzc = c.BZC_CD
    WHERE tr.source_bzc = '{$bzc_cd}'
      AND c.KEDCD <> '{$kedcd_safe}'
      AND c.ENP_SCD = '01'
      AND tr.score > 0
      AND c.SALES BETWEEN {$buy_min} AND {$buy_max}
    ORDER BY tr.score DESC
    LIMIT 3000
";
$result_buy = sql_query($sql_buy);
$cands_buy = [];
while ($r = sql_fetch_array($result_buy)) {
    $cands_buy[] = $r['KEDCD'];
}

$all_cands = array_values(array_unique(array_merge($cands_sell, $cands_buy)));

if (empty($all_cands)) {
    echo json_encode([
        'status' => 'ok',
        'kedcd' => $kedcd,
        'enp_nm' => $enp_nm,
        'bzc_cd' => $bzc_cd,
        'my_sales' => $my_sales,
        'candidate_count' => 0,
        'sellers' => [],
        'buyers' => [],
        'error' => 'No candidates found'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// 10) ML API 호출 (키는 config에서 로드)
// ============================================================
$payload = json_encode([
    'kedcd' => $kedcd,
    'candidates' => $all_cands,
    'top_n' => $top_n
]);

$ch = curl_init(ML_API_URL);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'X-API-Key: ' . ML_API_KEY
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 5,
    CURLOPT_CONNECTTIMEOUT => 2,
]);

$raw       = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err       = curl_error($ch);
curl_close($ch);

// ============================================================
// 11) 응답 (내부 정보 노출 차단)
// ============================================================
if (!$err && $http_code == 200 && $raw) {
    $ml_result = json_decode($raw, true);
    if ($ml_result && $ml_result['status'] === 'ok') {
        // 판매처 rank 점수
        $top_n_sell = count($ml_result['sellers']);
        foreach ($ml_result['sellers'] as $i => &$s) {
            $percentile = 1 - ($i / max($top_n_sell - 1, 1));
            $s['display_score'] = round($percentile * 100, 1);
        }
        unset($s);

        // 구매처 rank 점수
        $top_n_buy = count($ml_result['buyers']);
        foreach ($ml_result['buyers'] as $i => &$b) {
            $percentile = 1 - ($i / max($top_n_buy - 1, 1));
            $b['display_score'] = round($percentile * 100, 1);
        }
        unset($b);

        echo json_encode([
            'status' => 'ok',
            'kedcd' => $kedcd,
            'enp_nm' => $enp_nm,
            'bzc_cd' => $bzc_cd,
            'my_sales' => $my_sales,
            'candidate_count' => count($all_cands),
            'sellers' => $ml_result['sellers'],
            'buyers' => $ml_result['buyers'],
            'elapsed_ms' => $ml_result['elapsed_ms']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// 에러 시 내부 상세 정보 숨김
http_response_code(502);
echo json_encode([
    'status' => 'error',
    'error' => 'Recommendation service temporarily unavailable'
], JSON_UNESCAPED_UNICODE);
