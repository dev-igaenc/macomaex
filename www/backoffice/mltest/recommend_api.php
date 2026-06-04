<?php
ini_set('display_errors', '0');
ob_start();

include_once(__DIR__ . '/api_config.php');

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); echo json_encode(['error' => 'Method Not Allowed']); exit;
}

$action = $_GET['action'] ?? 'recommend';

if ($action === 'analyze') {
    $src = str_pad(preg_replace('/\D/', '', $_GET['src_kedcd'] ?? ''), 10, '0', STR_PAD_LEFT);
    $tgt = str_pad(preg_replace('/\D/', '', $_GET['tgt_kedcd'] ?? ''), 10, '0', STR_PAD_LEFT);
    $raw = ec2_post('/analyze', json_encode([
        'src_kedcd'  => $src, 'tgt_kedcd' => $tgt,
        'trade_type' => (int)($_GET['trade_type'] ?? 1),
        'candidate_type' => preg_replace('/[^a-z0-9_]/', '', $_GET['candidate_type'] ?? 'chain2'),
    ]));
    echo $raw !== false ? $raw : json_encode(['error' => 'EC2 연결 실패']);
    exit;
}

$kedcd = str_pad(preg_replace('/\D/', '', $_GET['kedcd'] ?? ''), 10, '0', STR_PAD_LEFT);
$bizno = substr(preg_replace('/\D/', '', $_GET['bizno'] ?? ''), 0, 10);
if (strlen($kedcd) !== 10 || preg_match('/^0+$/', $kedcd)) {
    http_response_code(400); echo json_encode(['error' => 'kedcd 오류']); exit;
}

$bzc_cd     = substr(preg_replace('/[^A-Za-z0-9]/', '', $_GET['bzc_cd'] ?? ''), 0, 10);
$sido       = substr($_GET['sido'] ?? '', 0, 20);
$sze        = max(1, min(5, (int)($_GET['sze'] ?? 3) ?: 3));
$mpd        = substr($_GET['mpd'] ?? '', 0, 100);
$sales      = max(0, (int)($_GET['sales'] ?? 0));
$prev_sales = max(0, (int)($_GET['prev_sales'] ?? 0));
$asset      = max(0, (int)($_GET['asset'] ?? 0));
$debt       = max(0, (int)($_GET['debt'] ?? 0));
$estb_dt    = substr(preg_replace('/\D/', '', $_GET['estb_dt'] ?? ''), 0, 8);
$bad_cnt    = max(0, (int)($_GET['bad_cnt'] ?? 0));
function _is_real_company(string $name): bool {
    if (mb_strlen($name) < 2) return false;
    static $bad = ['신용카드','현금영수증','수취분','발행분','간이과세','면세사업','소계','합계',
                   '직접수출','기타','불명','해당없음','개인','자가공급','내수','수출'];
    foreach ($bad as $p) { if (mb_strpos($name, $p) !== false) return false; }
    return true;
}
$cust_names = array_slice(array_values(array_filter(array_map('trim', explode('|', $_GET['cust_names'] ?? '')), '_is_real_company')), 0, 10);
$supp_names = array_slice(array_values(array_filter(array_map('trim', explode('|', $_GET['supp_names'] ?? '')), '_is_real_company')), 0, 10);

$base = ['kedcd' => $kedcd, 'top_n' => 100];
if ($bizno && $bizno !== $kedcd) $base['bizno'] = $bizno;
if ($bzc_cd)     $base['bzc_cd']     = $bzc_cd;

// 선택된 partner 업종코드 + 가중치 (키워드 선택 수 기반)
$filter_bzc_raw     = trim($_GET['filter_bzc_cds']     ?? '');
$filter_weight_raw  = trim($_GET['filter_bzc_weights'] ?? '');
if ($filter_bzc_raw) {
    $fbzcs = array_slice(array_values(array_filter(array_map(
        fn($v) => substr(preg_replace('/[^A-Za-z0-9]/', '', trim($v)), 0, 10),
        explode(',', $filter_bzc_raw)
    ))), 0, 50);
    if ($fbzcs) {
        $fweights = array_map('intval', explode(',', $filter_weight_raw));
        $fweights = array_slice($fweights, 0, count($fbzcs));
        while (count($fweights) < count($fbzcs)) $fweights[] = 1;
        $bzc_weighted = [];
        foreach ($fbzcs as $i => $bzc) {
            $bzc_weighted[] = ['bzc' => $bzc, 'weight' => max(1, $fweights[$i])];
        }
        $base['filter_bzc_cds']     = $fbzcs;
        $base['filter_bzc_weights'] = array_column($bzc_weighted, 'weight');
    }
}
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

$req_trade_type = (int)($_GET['trade_type'] ?? 0);
if ($req_trade_type === 1) {
    $buy_raw  = ec2_post('/recommend', json_encode(array_merge($base, ['trade_type' => 1])), 60);
    $sell_raw = false;
} elseif ($req_trade_type === 2) {
    $buy_raw  = false;
    $sell_raw = ec2_post('/recommend', json_encode(array_merge($base, ['trade_type' => 2])), 60);
} else {
    list($buy_raw, $sell_raw) = ec2_post_parallel(
        '/recommend', json_encode(array_merge($base, ['trade_type' => 1])),
        '/recommend', json_encode(array_merge($base, ['trade_type' => 2]))
    );
}

$buy_data  = $buy_raw  ? json_decode($buy_raw,  true) : null;
$sell_data = $sell_raw ? json_decode($sell_raw, true) : null;

if (!$buy_data && !$sell_data) {
    $ch = curl_init(EC2_BASE_URL . '/health');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>5, CURLOPT_CONNECTTIMEOUT=>3,
        CURLOPT_SSL_VERIFYPEER=>false, CURLOPT_SSL_VERIFYHOST=>false]);
    $hr = curl_exec($ch);
    $diag = [
        'curl_error'  => curl_error($ch),
        'http_code'   => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'url'         => EC2_BASE_URL . '/health',
        'health_body' => $hr ?: false,
        'buy_raw_len' => strlen((string)$buy_raw),
        'sell_raw_len'=> strlen((string)$sell_raw),
        'buy_raw_preview' => $buy_raw ? substr($buy_raw, 0, 200) : null,
    ];
    curl_close($ch);
    http_response_code(502);
    echo json_encode(['error' => 'EC2 연결 실패', 'diag' => $diag], JSON_UNESCAPED_UNICODE);
    exit;
}

$buy_all  = $buy_data['results']  ?? [];
$sell_all = $sell_data['results'] ?? [];

function _shuffle_slice($arr, $n) { $r = array_slice($arr, 3); shuffle($r); return array_slice($r, 0, $n); }

$out = json_encode([
    'kedcd' => $kedcd,
    'buy'  => ['top3' => array_slice($buy_all,  0, 3), 'list' => _shuffle_slice($buy_all,  45), 'total' => count($buy_all),  'refresh_remaining' => 5],
    'sell' => ['top3' => array_slice($sell_all, 0, 3), 'list' => _shuffle_slice($sell_all, 45), 'total' => count($sell_all), 'refresh_remaining' => 5],
    'buy_elapsed_ms'  => $buy_data['elapsed_ms']  ?? 0,
    'sell_elapsed_ms' => $sell_data['elapsed_ms'] ?? 0,
    'coldstart'    => $buy_data['coldstart'] ?? false,
    'src_keywords' => $buy_data['src_keywords'] ?? $sell_data['src_keywords'] ?? [],
], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
if ($out === false) {
    http_response_code(500);
    echo json_encode(['error' => 'JSON 인코딩 실패: ' . json_last_error_msg()]);
} else {
    echo $out;
}
