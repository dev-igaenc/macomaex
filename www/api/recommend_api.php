<?php
/**
 * /api/recommend_api.php
 * EC2 ML 서버 프록시 — Vercel api/recommend.js 동등 기능을 PHP로 구현
 *
 * GET /api/recommend_api.php?action=recommend&kedcd=0008300204&bzc_cd=620100...
 * GET /api/recommend_api.php?action=analyze&src_kedcd=...&tgt_kedcd=...
 */
chdir(__DIR__ . '/../');
include_once(__DIR__ . '/../_common.php');
include_once(__DIR__ . '/api_config.php');

// ── 응답 헤더 ────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Cache-Control: no-store');

// ── 메서드 제한 ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// ── Rate Limit (세션, 분당) ──────────────────────────────────
$_rate_key = 'ml_rate_' . preg_replace('/[^0-9a-f:.]/', '', $_SERVER['REMOTE_ADDR']);
$_now = time();
if (!isset($_SESSION[$_rate_key])) $_SESSION[$_rate_key] = ['count' => 0, 'reset' => $_now + 60];
if ($_now > $_SESSION[$_rate_key]['reset']) $_SESSION[$_rate_key] = ['count' => 0, 'reset' => $_now + 60];
$_SESSION[$_rate_key]['count']++;
if ($_SESSION[$_rate_key]['count'] > API_RATE_LIMIT) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'recommend';


// ════════════════════════════════════════════════════════════
// analyze 액션 — 기업 쌍 상세 분석 (Gemini 포함)
// ════════════════════════════════════════════════════════════
if ($action === 'analyze') {
    $src = str_pad(preg_replace('/\D/', '', $_GET['src_kedcd'] ?? ''), 10, '0', STR_PAD_LEFT);
    $tgt = str_pad(preg_replace('/\D/', '', $_GET['tgt_kedcd'] ?? ''), 10, '0', STR_PAD_LEFT);
    $tt  = (int)($_GET['trade_type'] ?? 1);
    $ct  = preg_replace('/[^a-z0-9_]/', '', $_GET['candidate_type'] ?? 'chain2');

    if (strlen($src) !== 10 || preg_match('/^0+$/', $src) ||
        strlen($tgt) !== 10 || preg_match('/^0+$/', $tgt)) {
        http_response_code(400);
        echo json_encode(['error' => 'KEDCD 형식 오류']);
        exit;
    }

    $raw = ec2_post('/analyze', json_encode([
        'src_kedcd'      => $src,
        'tgt_kedcd'      => $tgt,
        'trade_type'     => $tt,
        'candidate_type' => $ct,
    ]));

    if ($raw === false) {
        http_response_code(502);
        echo json_encode(['error' => 'EC2 연결 실패']);
        exit;
    }
    echo $raw;
    exit;
}


// ════════════════════════════════════════════════════════════
// recommend 액션 — 거래처 추천 (buy + sell 병렬)
// ════════════════════════════════════════════════════════════
if ($action !== 'recommend') {
    http_response_code(400);
    echo json_encode(['error' => 'action은 recommend 또는 analyze 만 지원합니다.']);
    exit;
}

// ── kedcd 검증 ───────────────────────────────────────────────
$kedcd = str_pad(preg_replace('/\D/', '', $_GET['kedcd'] ?? ''), 10, '0', STR_PAD_LEFT);
if (strlen($kedcd) !== 10 || preg_match('/^0+$/', $kedcd)) {
    http_response_code(400);
    echo json_encode(['error' => 'kedcd는 10자리 숫자여야 합니다.']);
    exit;
}

// ── cold-start 파라미터 ──────────────────────────────────────
// KED API (get_set.php)에서 이미 가져온 값을 sessionStorage → JS → 여기로 전달
$bzc_cd     = substr(preg_replace('/[^A-Za-z0-9]/', '', $_GET['bzc_cd']     ?? ''), 0, 10);
$sido       = substr($_GET['sido']       ?? '', 0, 20);
$sze        = max(1, min(5, (int)($_GET['sze'] ?? 3) ?: 3));
$mpd        = substr($_GET['mpd']        ?? '', 0, 100);
$sales      = max(0, (int)($_GET['sales']      ?? 0));
$prev_sales = max(0, (int)($_GET['prev_sales'] ?? 0));
$asset      = max(0, (int)($_GET['asset']      ?? 0));
$debt       = max(0, (int)($_GET['debt']       ?? 0));
$estb_dt    = substr(preg_replace('/\D/', '', $_GET['estb_dt'] ?? ''), 0, 8);
$bad_cnt    = max(0, (int)($_GET['bad_cnt']    ?? 0));

// ── 비기업 이름 필터 (신용카드수취분, 현금영수증 등 KED 비기업 패턴 제거) ──
function _is_real_company(string $name): bool {
    if (mb_strlen($name) < 2) return false;
    static $bad = ['신용카드','현금영수증','수취분','발행분','간이과세','면세사업','소계','합계',
                   '직접수출','기타','불명','해당없음','개인','자가공급','내수','수출'];
    foreach ($bad as $p) {
        if (mb_strpos($name, $p) !== false) return false;
    }
    return true;
}

// ── 거래처 이름 (파이프 구분 → 비기업 필터 → 최대 10개) ─────
$cust_names = array_slice(
    array_values(array_filter(array_map('trim', explode('|', $_GET['cust_names'] ?? '')),
        '_is_real_company')),
    0, 10
);
$supp_names = array_slice(
    array_values(array_filter(array_map('trim', explode('|', $_GET['supp_names'] ?? '')),
        '_is_real_company')),
    0, 10
);

// ── EC2 요청 body 구성 ───────────────────────────────────────
$base = ['kedcd' => $kedcd, 'top_n' => 100];

// 값이 있는 파라미터는 bzc_cd 유무와 무관하게 모두 EC2에 전달
if ($bzc_cd)     $base['bzc_cd']     = $bzc_cd;
if ($sido)       $base['sido']       = $sido;
if ($sze)        $base['sze']        = $sze;
if ($mpd)        $base['mpd']        = $mpd;
if ($sales)      $base['sales']      = $sales;
if ($prev_sales) $base['prev_sales'] = $prev_sales;
if ($asset)      $base['asset']      = $asset;
if ($debt)       $base['debt']       = $debt;
if ($estb_dt)    $base['estb_dt']    = $estb_dt;
if ($bad_cnt)    $base['bad_cnt']    = $bad_cnt;
if ($cust_names || $supp_names) {
    $base['cust_names'] = $cust_names;
    $base['supp_names'] = $supp_names;
}

$buy_payload  = json_encode(array_merge($base, ['trade_type' => 1]));
$sell_payload = json_encode(array_merge($base, ['trade_type' => 2]));

// ── buy / sell 병렬 호출 ─────────────────────────────────────
list($buy_raw, $sell_raw) = ec2_post_parallel(
    '/recommend', $buy_payload,
    '/recommend', $sell_payload
);

$buy_data  = ($buy_raw  !== false) ? json_decode($buy_raw,  true) : null;
$sell_data = ($sell_raw !== false) ? json_decode($sell_raw, true) : null;

if (!$buy_data && !$sell_data) {
    http_response_code(502);
    echo json_encode(['error' => 'EC2 연결 실패', 'detail' => 'recommend service unavailable']);
    exit;
}

$buy_all  = isset($buy_data['results'])  ? $buy_data['results']  : [];
$sell_all = isset($sell_data['results']) ? $sell_data['results'] : [];

// ── 상위 3개 고정 + 나머지 셔플 후 45개 ─────────────────────
function _shuffle_slice($arr, $n) {
    $rest = array_slice($arr, 3);
    shuffle($rest);
    return array_slice($rest, 0, $n);
}

echo json_encode([
    'kedcd' => $kedcd,
    'buy' => [
        'top3'              => array_slice($buy_all, 0, 3),
        'list'              => _shuffle_slice($buy_all, 45),
        'total'             => count($buy_all),
        'refresh_remaining' => 5,
    ],
    'sell' => [
        'top3'              => array_slice($sell_all, 0, 3),
        'list'              => _shuffle_slice($sell_all, 45),
        'total'             => count($sell_all),
        'refresh_remaining' => 5,
    ],
    'buy_elapsed_ms'  => isset($buy_data['elapsed_ms'])  ? $buy_data['elapsed_ms']  : 0,
    'sell_elapsed_ms' => isset($sell_data['elapsed_ms']) ? $sell_data['elapsed_ms'] : 0,
], JSON_UNESCAPED_UNICODE);
