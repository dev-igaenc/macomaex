<?php
/**
 * api/recommend.php — Cafe24용 B2B 거래처 추천 API (PHP → EC2 HTTPS)
 *
 * 엔드포인트:
 *   GET  ?action=recommend&kedcd=1868101689  → 구매/판매 각 100개 추천
 *   GET  ?action=refresh&trade_type=1        → 랜덤 12개 재추출
 *   GET  ?action=top3&trade_type=1           → Top 3
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if (!function_exists('macomaex_env')) {
    $common_path = __DIR__ . '/../_common.php';
    if (is_file($common_path)) {
        include_once($common_path);
    }
}

if (!function_exists('api_load_env_file')) {
    function api_load_env_file($env_path)
    {
        static $loaded = [];
        if (isset($loaded[$env_path])) {
            return;
        }
        $loaded[$env_path] = true;

        if (!is_file($env_path) || !is_readable($env_path)) {
            return;
        }

        $scanner = defined('INI_SCANNER_RAW') ? INI_SCANNER_RAW : INI_SCANNER_NORMAL;
        $values = @parse_ini_file($env_path, false, $scanner);
        if (!is_array($values)) {
            return;
        }

        foreach ($values as $name => $value) {
            if (getenv($name) !== false) {
                continue;
            }
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            @putenv($name . '=' . $value);
        }
    }
}

// _common.php 상대경로 이슈가 있어도 root/.env 를 직접 로드해서 값 확보
api_load_env_file(dirname(dirname(__DIR__)) . '/.env');

if (!function_exists('api_env')) {
    function api_env($name, $default = '')
    {
        if (function_exists('macomaex_env')) {
            return macomaex_env($name, $default);
        }

        $value = getenv($name);
        if ($value !== false && $value !== '') {
            return $value;
        }

        return $default;
    }
}

// ── EC2 설정 ─────────────────────────────────────────────────────
define('EC2_URL',     rtrim((string)api_env('ML_API_URL', ''), '/'));
define('EC2_API_KEY', (string)api_env('ML_API_KEY', ''));
define('TOP_N',       100);
define('DISPLAY_N',   12);
define('MAX_REFRESH', 5);

// ── EC2 API 호출 ─────────────────────────────────────────────────
function call_ec2($endpoint, $data) {
    if (EC2_URL === '' || EC2_API_KEY === '') {
        return ['error' => 'ML API 환경변수 미설정', 'http_code' => 500];
    }

    $url = EC2_URL . $endpoint;
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST            => true,
        CURLOPT_POSTFIELDS      => json_encode($data),
        CURLOPT_HTTPHEADER      => [
            'Content-Type: application/json',
            'X-API-Key: ' . EC2_API_KEY,
        ],
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_TIMEOUT         => 60,
        CURLOPT_CONNECTTIMEOUT  => 10,
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_SSL_VERIFYHOST  => false,
    ]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error     = curl_error($ch);
    curl_close($ch);

    if ($error)          return ['error' => 'EC2 연결 실패: ' . $error, 'http_code' => 0];
    if ($http_code !== 200) return ['error' => 'EC2 응답 오류', 'http_code' => $http_code, 'body' => $response];

    $decoded = json_decode($response, true);
    if (!$decoded)       return ['error' => 'JSON 파싱 실패', 'body' => substr($response, 0, 200)];
    return $decoded;
}

// ── 랜덤 추출 ────────────────────────────────────────────────────
function pick_random($results, $n) {
    if (count($results) <= $n) return array_values($results);
    $keys = array_keys($results);
    shuffle($keys);
    $picked = [];
    for ($i = 0; $i < $n; $i++) $picked[] = $results[$keys[$i]];
    return $picked;
}

// ── 라우팅 ───────────────────────────────────────────────────────
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'recommend':
        $kedcd = str_pad(preg_replace('/\D/', '', $_GET['kedcd'] ?? ''), 10, '0', STR_PAD_LEFT);
        if (strlen($kedcd) !== 10) { echo json_encode(['error' => 'KEDCD/bizno 형식 오류']); exit; }

        // 콜드스타트 파라미터 수집
        $params = [
            'kedcd'      => $kedcd,
            'top_n'      => TOP_N,
        ];
        foreach (['bzc_cd','sido','sze','mpd','sales','prev_sales','asset','debt','estb_dt','bad_cnt','cust_names','supp_names'] as $k) {
            if (!empty($_GET[$k])) $params[$k] = $_GET[$k];
        }

        $buy  = call_ec2('/recommend', array_merge($params, ['trade_type' => 1]));
        $sell = call_ec2('/recommend', array_merge($params, ['trade_type' => 2]));

        $_SESSION['rec_kedcd']       = $kedcd;
        $_SESSION['rec_buy']         = $buy['results']  ?? [];
        $_SESSION['rec_sell']        = $sell['results'] ?? [];
        $_SESSION['rec_buy_refresh'] = 0;
        $_SESSION['rec_sell_refresh']= 0;

        $buy_all  = $_SESSION['rec_buy'];
        $sell_all = $_SESSION['rec_sell'];

        $buy_rest  = array_slice($buy_all,  3);
        $sell_rest = array_slice($sell_all, 3);
        shuffle($buy_rest);  shuffle($sell_rest);

        echo json_encode([
            'kedcd' => $buy['kedcd'] ?? $kedcd,
            'buy'  => ['top3' => array_slice($buy_all, 0, 3),  'list' => array_slice($buy_rest,  0, DISPLAY_N), 'total' => count($buy_all),  'refresh_remaining' => MAX_REFRESH],
            'sell' => ['top3' => array_slice($sell_all, 0, 3), 'list' => array_slice($sell_rest, 0, DISPLAY_N), 'total' => count($sell_all), 'refresh_remaining' => MAX_REFRESH],
            'buy_elapsed_ms'  => $buy['elapsed_ms']  ?? 0,
            'sell_elapsed_ms' => $sell['elapsed_ms'] ?? 0,
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'refresh':
        $trade_type = intval($_GET['trade_type'] ?? 1);
        $key        = $trade_type === 2 ? 'sell' : 'buy';
        $ref_key    = "rec_{$key}_refresh";

        if (!isset($_SESSION["rec_{$key}"])) { echo json_encode(['error' => '추천 결과 없음. recommend 먼저 호출하세요.']); exit; }

        $count = $_SESSION[$ref_key] ?? 0;
        if ($count >= MAX_REFRESH) { echo json_encode(['error' => '리프레시 횟수 초과', 'refresh_remaining' => 0]); exit; }

        $rest = array_slice($_SESSION["rec_{$key}"], 3);
        shuffle($rest);
        $_SESSION[$ref_key] = $count + 1;

        echo json_encode([
            'trade_type'       => $trade_type,
            'list'             => array_slice($rest, 0, DISPLAY_N),
            'refresh_count'    => $count + 1,
            'refresh_remaining'=> MAX_REFRESH - $count - 1,
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'top3':
        $trade_type = intval($_GET['trade_type'] ?? 1);
        $key        = $trade_type === 2 ? 'sell' : 'buy';
        if (!isset($_SESSION["rec_{$key}"])) { echo json_encode(['error' => '추천 결과 없음']); exit; }
        echo json_encode(['trade_type' => $trade_type, 'top3' => array_slice($_SESSION["rec_{$key}"], 0, 3)], JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['error' => 'action 필요', 'available' => ['recommend','refresh','top3']]);
}
