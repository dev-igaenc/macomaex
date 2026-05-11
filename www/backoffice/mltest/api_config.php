<?php
// EC2 ML 서버 설정 (mltest 전용 — 운영 api_config.php와 별개)
if (!function_exists('macomaex_env')) {
    $common_path = __DIR__ . '/../../_common.php';
    if (is_file($common_path)) {
        include_once($common_path);
    }
}

if (!function_exists('mltest_load_env_file')) {
    function mltest_load_env_file($env_path)
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
mltest_load_env_file(dirname(dirname(dirname(__DIR__))) . '/.env');

if (!function_exists('mltest_env')) {
    function mltest_env($name, $default = '')
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

$ml_base_url = mltest_env('MLTEST_API_URL', mltest_env('ML_API_URL', ''));
$ml_api_key  = mltest_env('MLTEST_API_KEY', mltest_env('ML_API_KEY', ''));

if (!defined('EC2_BASE_URL')) define('EC2_BASE_URL', rtrim((string)$ml_base_url, '/'));
if (!defined('ML_API_KEY'))   define('ML_API_KEY',   (string)$ml_api_key);

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
