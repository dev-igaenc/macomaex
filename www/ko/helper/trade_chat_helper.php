<?php
if (!defined('_GNUBOARD_')) exit;

/*
 * 거래 채팅 도메인 헬퍼.
 * 기업 pair 기준 room 식별 규칙과 권한/조회용 공통 함수를 모아둔다.
 */

include_once(__DIR__ . '/trade_chat_config_helper.php');
include_once(__DIR__ . '/trade_chat_stream_helper.php');

function trade_chat_normalize_bzno($bzno) {
	return preg_replace('/[^0-9]/', '', (string)$bzno);
}

function trade_chat_make_company_pair_key($bznoA, $bznoB) {
	$a = trade_chat_normalize_bzno($bznoA);
	$b = trade_chat_normalize_bzno($bznoB);

	if ($a === '' || $b === '') return '';

	$pair = array($a, $b);
	sort($pair, SORT_STRING);

	return $pair[0] . ':' . $pair[1];
}

function trade_chat_make_stream_user_id($mbId) {
	$mbId = trim((string)$mbId);
	if ($mbId === '') return '';

	return 'member:' . $mbId;
}

function trade_chat_make_stream_channel_id($companyPairKey) {
	$companyPairKey = trim((string)$companyPairKey);
	if ($companyPairKey === '') return '';

	return 'trade-' . substr(hash('sha256', $companyPairKey), 0, 32);
}

function trade_chat_write_event_log($eventType, $status, $payload = array(), $errorMessage = '') {
	$eventType = trim((string)$eventType);
	$status = trim((string)$status);
	if ($eventType === '' || $status === '') return false;

	$payload = is_array($payload) ? $payload : array('value' => $payload);
	$payloadJson = empty($payload)
		? '{}'
		: json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	if ($payloadJson === false) {
		$payloadJson = '{}';
	}

	$eventTypeSql = sql_escape_string($eventType);
	$statusSql = sql_escape_string($status);
	$payloadSql = sql_escape_string($payloadJson);
	$errorSql = sql_escape_string((string)$errorMessage);

	return sql_query("
		INSERT INTO trade_chat_event_log
		SET event_type='{$eventTypeSql}',
			event_status='{$statusSql}',
			payload_json='{$payloadSql}',
			error_message=" . ($errorSql === '' ? "NULL" : "'{$errorSql}'") . ",
			created_at=NOW()
	", false);
}

function trade_chat_get_request_row($trNo) {
	$trNo = (int)$trNo;
	if ($trNo < 1) return array();

	return sql_fetch("
		SELECT
			tr_no,
			request_mb_id,
			request_company_name,
			target_company_name,
			request_bzno,
			target_bzno,
			target_kedcd,
			approval_status,
			approved_at,
			chat_room_id,
			chat_open_triggered_at,
			chat_open_result
		FROM trade_request
		WHERE tr_no='{$trNo}'
		LIMIT 1
	");
}

function trade_chat_find_room_by_pair_key($companyPairKey) {
	$companyPairKey = trim((string)$companyPairKey);
	if ($companyPairKey === '') return array();

	return sql_fetch("
		SELECT *
		FROM trade_chat_room
		WHERE company_pair_key='" . sql_escape_string($companyPairKey) . "'
		LIMIT 1
	");
}

function trade_chat_find_room_by_tr_no($trNo) {
	$trNo = (int)$trNo;
	if ($trNo < 1) return array();

	return sql_fetch("
		SELECT R.*
		FROM trade_chat_room R
		WHERE R.source_tr_no='{$trNo}'
		   OR R.latest_tr_no='{$trNo}'
		ORDER BY R.room_id DESC
		LIMIT 1
	");
}

function trade_chat_member_company_bzno($member) {
	return trade_chat_normalize_bzno($member['mb_4'] ?? '');
}

function trade_chat_assert_member_access($room, $member) {
	$memberId = trim((string)($member['mb_id'] ?? ''));
	$memberBzno = trade_chat_member_company_bzno($member);

	if ($memberId === '' || $memberBzno === '') {
		return array(
			'success' => false,
			'message' => '회원 정보가 올바르지 않습니다.',
		);
	}

	if (empty($room['room_id'])) {
		return array(
			'success' => false,
			'message' => '채팅방 정보가 없습니다.',
		);
	}

	$requestBzno = trade_chat_normalize_bzno($room['request_bzno'] ?? '');
	$targetBzno = trade_chat_normalize_bzno($room['target_bzno'] ?? '');
	if ($memberBzno !== $requestBzno && $memberBzno !== $targetBzno) {
		return array(
			'success' => false,
			'message' => '채팅방 접근 권한이 없습니다.',
		);
	}

	if (($room['room_status'] ?? '') !== 'active') {
		return array(
			'success' => false,
			'message' => '활성 채팅방이 아닙니다.',
		);
	}

	return array(
		'success' => true,
		'stream_user_id' => trade_chat_make_stream_user_id($memberId),
	);
}

function trade_chat_sync_room_members($roomId) {
	$roomId = (int)$roomId;
	if ($roomId < 1) {
		return array(
			'success' => false,
			'message' => 'room_id가 올바르지 않습니다.',
		);
	}

	return array(
		'success' => false,
		'message' => 'Phase 1 스켈레톤 상태입니다.',
		'room_id' => $roomId,
	);
}

function trade_chat_open_or_attach_room($trNo, $member) {
	$requestRow = trade_chat_get_request_row($trNo);
	if (empty($requestRow['tr_no'])) {
		return array(
			'success' => false,
			'message' => '거래 요청을 찾을 수 없습니다.',
		);
	}

	$companyPairKey = trade_chat_make_company_pair_key(
		$requestRow['request_bzno'] ?? '',
		$requestRow['target_bzno'] ?? ''
	);
	if ($companyPairKey === '') {
		return array(
			'success' => false,
			'message' => '기업 식별 정보가 올바르지 않습니다.',
		);
	}

	$existingRoom = trade_chat_find_room_by_pair_key($companyPairKey);
	if (!empty($existingRoom['room_id'])) {
		return array(
			'success' => true,
			'action' => 'reuse',
			'room' => $existingRoom,
			'company_pair_key' => $companyPairKey,
		);
	}

	return array(
		'success' => false,
		'message' => 'Phase 1 스켈레톤 상태입니다.',
		'action' => 'create',
		'company_pair_key' => $companyPairKey,
		'stream_channel_id' => trade_chat_make_stream_channel_id($companyPairKey),
		'stream_user_id' => trade_chat_make_stream_user_id($member['mb_id'] ?? ''),
		'request_row' => $requestRow,
	);
}

function trade_chat_issue_user_token($room, $member) {
	$access = trade_chat_assert_member_access($room, $member);
	if (empty($access['success'])) {
		return $access;
	}

	return trade_chat_stream_create_token($access['stream_user_id']);
}
