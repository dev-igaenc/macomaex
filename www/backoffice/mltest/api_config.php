<?php
// ── .env 로드 (웹루트 상위 디렉토리 — 웹 접근 불가 영역) ─
if (!defined('MLTEST_PAGE_PASSWORD')) {
    $_envFile = dirname($_SERVER['DOCUMENT_ROOT']) . '/.env';
    $__e = [];
    if (file_exists($_envFile)) {
        foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
            $_line = trim($_line);
            if ($_line === '' || $_line[0] === '#') continue;
            $__pos = strpos($_line, '=');
            if ($__pos === false) continue;
            $__k = trim(substr($_line, 0, $__pos));
            $__v = trim(substr($_line, $__pos + 1));
            // 따옴표 제거 ("value" 또는 'value')
            if (strlen($__v) >= 2 && (($__v[0] === '"' && $__v[-1] === '"') || ($__v[0] === "'" && $__v[-1] === "'"))) {
                $__v = substr($__v, 1, -1);
            }
            $__e[$__k] = $__v;
        }
    }
    define('GEMINI_API_KEY',      $__e['GEMINI_API_KEY']      ?? '');
    define('EC2_BASE_URL',        $__e['ML_API_URL']          ?? '');
    define('ML_API_KEY',          $__e['ML_API_KEY']          ?? '');
    define('MLTEST_PAGE_PASSWORD',$__e['MLTEST_PAGE_PASSWORD']?? '');
    define('GEMINI_MODEL',         $__e['GEMINI_MODEL']         ?? 'gemini-2.5-flash');
    define('DEV_DB_HOST',         $__e['G5_DB_HOST']          ?? 'localhost');
    define('DEV_DB_USER',         $__e['G5_DB_USER']          ?? '');
    define('DEV_DB_PASS',         $__e['G5_DB_PASSWORD']      ?? '');
    define('DEV_DB_NAME',         $__e['G5_DB_NAME']          ?? 'testmacomaex');
    unset($_envFile, $__e, $_line, $__pos, $__k, $__v);
}

function ec2_post($endpoint, $json_payload, $timeout = 25) {
    $ch = curl_init(EC2_BASE_URL . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $json_payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-API-Key: ' . ML_API_KEY,
        ],
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_TIMEOUT         => $timeout,
        CURLOPT_CONNECTTIMEOUT  => 3,
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_SSL_VERIFYHOST  => false,
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || !$raw) return false;
    return $raw;
}

function ec2_post_parallel($endpoint1, $payload1, $endpoint2, $payload2, $timeout = 25) {
    $mh = curl_multi_init();
    $ch1 = curl_init(EC2_BASE_URL . $endpoint1);
    curl_setopt_array($ch1, [
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload1,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-API-Key: ' . ML_API_KEY],
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false,
    ]);
    $ch2 = curl_init(EC2_BASE_URL . $endpoint2);
    curl_setopt_array($ch2, [
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload2,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-API-Key: ' . ML_API_KEY],
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false,
    ]);
    curl_multi_add_handle($mh, $ch1);
    curl_multi_add_handle($mh, $ch2);
    $running = null;
    do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running > 0);
    $raw1 = curl_multi_getcontent($ch1); $code1 = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
    $raw2 = curl_multi_getcontent($ch2); $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_multi_remove_handle($mh, $ch1); curl_multi_remove_handle($mh, $ch2);
    curl_multi_close($mh);
    return [($code1 === 200 && $raw1) ? $raw1 : false, ($code2 === 200 && $raw2) ? $raw2 : false];
}
