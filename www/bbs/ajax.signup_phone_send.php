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

// 일반/소셜 회원가입에서 공통으로 사용하는 인증번호 발송 처리입니다.
$result = macomaex_signup_phone_otp_send($phone);

$payload = array(
    'ok' => !empty($result['ok']),
    'message' => isset($result['message']) ? $result['message'] : '',
);

// 상세 알리고 오류는 허용된 테스트 IP에서만 노출합니다.
if (function_exists('macomaex_signup_sms_debug_allowed') && macomaex_signup_sms_debug_allowed()) {
    $payload['debug'] = isset($result['debug']) ? $result['debug'] : array();
}

macomaex_signup_phone_send_response($payload);
