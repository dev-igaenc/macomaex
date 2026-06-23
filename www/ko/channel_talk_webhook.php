<?php
include_once(__DIR__ . '/_common.php');
include_once(G5_LIB_PATH . '/channel_talk.lib.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    channel_talk_json_response(false, '허용되지 않은 요청 방식입니다.', 405);
}

$rawBody = file_get_contents('php://input');
if (!is_string($rawBody) || trim($rawBody) === '') {
    channel_talk_json_response(false, '웹훅 본문이 비어 있습니다.', 400);
}

$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    channel_talk_json_response(false, '웹훅 데이터가 올바르지 않습니다.', 400);
}

$entity = isset($payload['entity']) && is_array($payload['entity']) ? $payload['entity'] : array();
$personType = channel_talk_extract_person_type($entity);
$messageText = channel_talk_extract_message_text($entity);
$talkId = channel_talk_extract_talk_id_from_webhook($payload);

if ($messageText === '') {
    channel_talk_json_response(true, '텍스트 메시지가 없어 처리하지 않았습니다.', 200, array(
        'talk_id' => $talkId,
        'person_type' => $personType,
    ));
}

$meta = array(
    'page_url' => channel_talk_extract_page_url_from_webhook($payload),
    'referer_url' => channel_talk_resolve_referer_url($payload),
    'ip_address' => channel_talk_request_ip(),
    'user_agent' => channel_talk_request_user_agent(),
);

if ($personType === 'user') {
    $logId = channel_talk_insert_question_log($talkId, $messageText, $meta);
    channel_talk_json_response(true, '사용자 메시지 로그를 저장했습니다.', 200, array(
        'talk_id' => $talkId,
        'person_type' => $personType,
        'log_id' => $logId,
    ));
}

if ($personType === 'bot') {
    $updated = channel_talk_update_latest_answer_log($talkId, $messageText);
    channel_talk_json_response(true, $updated ? '봇 응답 로그를 반영했습니다.' : '매칭되는 미응답 로그가 없어 건너뛰었습니다.', 200, array(
        'talk_id' => $talkId,
        'person_type' => $personType,
        'updated' => $updated ? 'Y' : 'N',
    ));
}

channel_talk_json_response(true, '지원하지 않는 personType이라 처리하지 않았습니다.', 200, array(
    'talk_id' => $talkId,
    'person_type' => $personType,
));
