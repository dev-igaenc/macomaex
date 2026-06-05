<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

header('Content-Type: application/json; charset=utf-8');

function macomaex_signup_phone_verify_response($payload)
{
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    macomaex_signup_phone_verify_response(array(
        'ok' => false,
        'message' => 'POST로 요청해 주세요.',
    ));
}

$phone = isset($_POST['mb_hp']) ? trim((string) $_POST['mb_hp']) : '';
if ($phone === '' && isset($_POST['phone'])) {
    $phone = trim((string) $_POST['phone']);
}

$code = isset($_POST['code']) ? trim((string) $_POST['code']) : '';
if ($code === '' && isset($_POST['cert_code'])) {
    $code = trim((string) $_POST['cert_code']);
}

// 발송된 휴대폰 번호와 6자리 인증번호를 세션 기준으로 검증합니다.
$result = macomaex_signup_phone_otp_verify($phone, $code);

macomaex_signup_phone_verify_response(array(
    'ok' => !empty($result['ok']),
    'message' => isset($result['message']) ? $result['message'] : '',
));
