<?php
include_once(__DIR__ . '/_common.php');
include_once(__DIR__ . '/helper/trade_chat_helper.php');

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(array('success' => false, 'message' => 'invalid_method'));
	exit;
}

if (empty($member['mb_id'])) {
	http_response_code(401);
	echo json_encode(array('success' => false, 'message' => 'not_logged_in'));
	exit;
}

// 화이트리스트 기반으로 허용된 UI 이벤트만 로그 적재 대상으로 받는다.
$eventTypeRaw = trim((string)($_POST['event_type'] ?? ''));
$roomId = (int)($_POST['room_id'] ?? 0);
$uiContext = trim((string)($_POST['ui_context'] ?? 'modal'));
$searchKeyword = trim((string)($_POST['search_keyword'] ?? ''));
$searchResultCount = (int)($_POST['search_result_count'] ?? -1);
$eventStatus = trim((string)($_POST['event_status'] ?? 'success'));
$fileName = trim((string)($_POST['file_name'] ?? ''));
$fileSize = (int)($_POST['file_size'] ?? 0);
$fileMime = trim((string)($_POST['file_mime'] ?? ''));
$attemptResult = trim((string)($_POST['attempt_result'] ?? ''));
$attemptReason = trim((string)($_POST['attempt_reason'] ?? ''));
$policyExpected = trim((string)($_POST['policy_expected'] ?? ''));

$allowedEventMap = array(
	'chat_room_enter' => 'enter',
	'chat_room_leave' => 'leave',
	'chat_room_search' => 'search',
	'file_upload_attempt' => 'file_upload',
);
if (!isset($allowedEventMap[$eventTypeRaw])) {
	http_response_code(400);
	echo json_encode(array('success' => false, 'message' => 'invalid_event_type'));
	exit;
}

if ($roomId < 1) {
	http_response_code(400);
	echo json_encode(array('success' => false, 'message' => 'invalid_room_id'));
	exit;
}

// room/member 권한 검증이 통과한 이벤트만 서버 로그에 남긴다.
$room = trade_chat_find_room_by_id($roomId);
if (empty($room['room_id'])) {
	http_response_code(404);
	echo json_encode(array('success' => false, 'message' => 'room_not_found'));
	exit;
}

$access = trade_chat_assert_member_access($room, $member, false);
if (empty($access['success'])) {
	http_response_code(403);
	echo json_encode(array('success' => false, 'message' => 'room_access_denied'));
	exit;
}

$payload = array(
	'room_id' => (int)$room['room_id'],
	'tr_no' => (int)($room['latest_tr_no'] ?? 0),
	'mb_id' => (string)$member['mb_id'],
	'company_pair_key' => trim((string)($room['company_pair_key'] ?? '')),
	'stream_channel_id' => trim((string)($room['stream_channel_id'] ?? '')),
	'stream_channel_cid' => trim((string)($room['stream_channel_cid'] ?? '')),
	'ui_action' => $allowedEventMap[$eventTypeRaw],
	'ui_context' => $uiContext,
);

if ($eventTypeRaw === 'chat_room_search') {
	// 검색 이벤트는 키워드/결과 건수를 함께 적재해 사용 패턴을 추적한다.
	$payload['search_keyword'] = $searchKeyword;
	if ($searchResultCount >= 0) {
		$payload['search_result_count'] = $searchResultCount;
	}
}

if ($eventTypeRaw === 'file_upload_attempt') {
	// 첨부 시도는 성공/차단과 무관하게 시도 자체 + 정책 원인까지 저장한다.
	$payload['attachment_title'] = $fileName;
	$payload['file_size'] = $fileSize;
	$payload['attachment_type'] = $fileMime;
	$payload['attempt_result'] = $attemptResult;
	$payload['attempt_reason'] = $attemptReason;
	if ($attemptReason !== '') {
		$payload['reason'] = $attemptReason;
	}
	if ($policyExpected !== '') {
		$payload['policy_expected'] = $policyExpected;
	}
}

if (!in_array($eventStatus, array('success', 'fail', 'skip'), true)) {
	$eventStatus = 'success';
}

trade_chat_write_event_log($eventTypeRaw, $eventStatus, $payload);

echo json_encode(array('success' => true));
exit;
