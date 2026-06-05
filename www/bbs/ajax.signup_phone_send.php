<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

header('Content-Type: application/json; charset=utf-8');

function macomaex_signup_phone_send_response($payload)
{
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    macomaex_signup_phone_send_response(array(
        'ok' => false,
        'message' => 'POST로 요청해 주세요.',
    ));
}

$phone = isset($_POST['mb_hp']) ? trim((string) $_POST['mb_hp']) : '';
if ($phone === '' && isset($_POST['phone'])) {
    $phone = trim((string) $_POST['phone']);
}

$result = macomaex_signup_phone_otp_send($phone);

$payload = array(
    'ok' => !empty($result['ok']),
    'message' => isset($result['message']) ? $result['message'] : '',
);

if (function_exists('macomaex_signup_sms_debug_allowed') && macomaex_signup_sms_debug_allowed()) {
    $payload['debug'] = isset($result['debug']) ? $result['debug'] : array();
}

macomaex_signup_phone_send_response($payload);
