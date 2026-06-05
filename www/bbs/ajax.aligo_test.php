<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

header('Content-Type: application/json; charset=utf-8');

// 알리고 연동 상태를 확인하기 위한 테스트 전용 JSON 엔드포인트입니다.
function macomaex_aligo_test_response($payload)
{
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function macomaex_aligo_test_client_ip()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : '';

    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && trim((string) $_SERVER['HTTP_X_FORWARDED_FOR']) !== '') {
        $parts = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);
        $forwarded_ip = trim((string) $parts[0]);
        if ($forwarded_ip !== '') {
            $ip = $forwarded_ip;
        }
    }

    return $ip;
}

$allowed_ips = array(
    '1.226.85.230',
    '222.107.227.177',
    '222.122.39.49',
    '127.0.0.1',
    '::1',
);

$client_ip = macomaex_aligo_test_client_ip();
if (!in_array($client_ip, $allowed_ips, true)) {
    macomaex_aligo_test_response(array(
        'ok' => false,
        'message' => '허용되지 않은 접근입니다.',
        'client_ip' => $client_ip,
    ));
}

$mode = isset($_REQUEST['mode']) ? trim((string) $_REQUEST['mode']) : '';
$config = macomaex_aligo_config();

// .env 설정값의 존재 여부만 마스킹하여 확인합니다.
if ($mode === 'config') {
    macomaex_aligo_test_response(array(
        'ok' => $config['ready'],
        'message' => $config['ready'] ? '알리고 설정값이 준비되었습니다.' : '알리고 설정값이 누락되었습니다.',
        'config' => array(
            'user_id' => $config['user_id'] !== '' ? 'OK' : 'MISSING',
            'api_key' => $config['api_key'] !== '' ? 'OK('.strlen($config['api_key']).')' : 'MISSING',
            'sender' => $config['sender'] !== '' ? $config['sender'] : 'MISSING',
        ),
        'client_ip' => $client_ip,
    ));
}

// key/user_id 인증과 문자 잔여건수를 실제 알리고 API로 확인합니다.
if ($mode === 'remain') {
    $result = macomaex_aligo_remain();
    macomaex_aligo_test_response(array(
        'ok' => !empty($result['ok']),
        'message' => isset($result['message']) ? $result['message'] : '',
        'debug' => isset($result['debug']) ? $result['debug'] : array(),
        'client_ip' => $client_ip,
    ));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    macomaex_aligo_test_response(array(
        'ok' => false,
        'message' => 'POST로 요청해 주세요.',
        'client_ip' => $client_ip,
    ));
}

$receiver = isset($_POST['receiver']) ? trim((string) $_POST['receiver']) : '';
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '[매커멕스] 문자 발송 테스트입니다.';

// 테스트 번호로 실제 SMS를 발송하고 알리고 응답을 반환합니다.
$result = macomaex_aligo_send_sms($receiver, $message);

macomaex_aligo_test_response(array(
    'ok' => !empty($result['ok']),
    'message' => isset($result['message']) ? $result['message'] : '',
    'debug' => isset($result['debug']) ? $result['debug'] : array(),
    'client_ip' => $client_ip,
));
