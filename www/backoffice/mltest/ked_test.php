<?php
// 운영서버 get_set.php를 프록시로 호출하는 테스트용 파일
header('Content-Type: text/plain; charset=UTF-8');

$comnum = preg_replace('/[^0-9]/', '', $_POST['comnum'] ?? '');
if (strlen($comnum) !== 10) {
    echo "500|올바른 사업자번호를 입력해 주세요.";
    exit;
}

$ch = curl_init('https://macomaex.com/get_set.php');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(['type' => 'main_comp_chk', 'comnum' => $comnum]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 35,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$result   = curl_exec($ch);
$curl_err = curl_error($ch);
curl_close($ch);

if (!$result || $curl_err) {
    echo "500|CURL_ERROR|운영서버 연결 실패: " . ($curl_err ?: '응답 없음');
    exit;
}

echo $result;
