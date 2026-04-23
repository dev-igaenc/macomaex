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

	// Stream user id는 콜론(:)을 허용하지 않으므로 member_ prefix를 사용한다.
	return 'member_' . preg_replace('/[^a-z0-9@_. -]/i', '_', $mbId);
}

function trade_chat_make_stream_channel_id($companyPairKey) {
	$companyPairKey = trim((string)$companyPairKey);
	if ($companyPairKey === '') return '';

	return 'trade-' . substr(hash('sha256', $companyPairKey), 0, 32);
}

function trade_chat_normalize_channel_type($channelType) {
	$channelType = trim((string)$channelType);
	if ($channelType === '') return 'messaging';
	if (!preg_match('/^[a-z0-9_-]{1,30}$/', $channelType)) return 'messaging';
	return $channelType;
}

function trade_chat_is_valid_channel_id($channelId) {
	$channelId = trim((string)$channelId);
	return $channelId !== '' && preg_match('/^[a-z0-9@_. -]{1,128}$/i', $channelId);
}

function trade_chat_write_event_log($eventType, $status, $payload = array(), $errorMessage = '') {
	$eventType = trim((string)$eventType);
	$status = trim((string)$status);
	if ($eventType === '' || $status === '') return false;
	if (!in_array($status, array('success', 'fail', 'skip'), true)) {
		$status = 'fail';
	}
	if (!preg_match('/^[a-z0-9_.:-]{1,50}$/i', $eventType)) {
		$eventType = 'invalid_event_type';
	}

	$payload = is_array($payload) ? $payload : array('value' => $payload);
	$payloadJson = empty($payload)
		? '{}'
		: json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	if ($payloadJson === false) {
		$payloadJson = '{}';
	}

	$roomId = isset($payload['room_id']) ? (int)$payload['room_id'] : 0;
	$trNo = isset($payload['tr_no']) ? (int)$payload['tr_no'] : 0;
	$mbId = isset($payload['mb_id']) ? trim((string)$payload['mb_id']) : '';

	$eventTypeSql = sql_escape_string($eventType);
	$statusSql = sql_escape_string($status);
	$payloadSql = sql_escape_string($payloadJson);
	$errorSql = sql_escape_string((string)$errorMessage);
	$mbIdSql = sql_escape_string($mbId);

	return sql_query("
		INSERT INTO trade_chat_event_log
		SET room_id=" . ($roomId > 0 ? "'{$roomId}'" : "NULL") . ",
			tr_no=" . ($trNo > 0 ? "'{$trNo}'" : "NULL") . ",
			mb_id=" . ($mbIdSql === '' ? "NULL" : "'{$mbIdSql}'") . ",
			event_type='{$eventTypeSql}',
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

function trade_chat_find_room_by_id($roomId) {
	$roomId = (int)$roomId;
	if ($roomId < 1) return array();

	return sql_fetch("
		SELECT *
		FROM trade_chat_room
		WHERE room_id='{$roomId}'
		LIMIT 1
	");
}

function trade_chat_find_active_room_members($roomId) {
	$roomId = (int)$roomId;
	$members = array();
	if ($roomId < 1) return $members;

	$result = sql_query("
		SELECT *
		FROM trade_chat_room_member
		WHERE room_id='{$roomId}'
		  AND is_active=1
		ORDER BY room_member_id ASC
	", false);

	if ($result) {
		for ($i = 0; $row = sql_fetch_array($result); $i++) {
			$members[] = $row;
		}
	}

	return $members;
}

function trade_chat_update_request_chat_state($trNo, $roomId, $result) {
	$trNo = (int)$trNo;
	$roomId = (int)$roomId;
	$result = trim((string)$result);

	if ($trNo < 1 || !in_array($result, array('pending', 'success', 'fail'), true)) {
		return false;
	}

	return sql_query("
		UPDATE trade_request
		SET chat_room_id=" . ($roomId > 0 ? "'{$roomId}'" : "NULL") . ",
			chat_open_triggered_at=NOW(),
			chat_open_result='" . sql_escape_string($result) . "'
		WHERE tr_no='{$trNo}'
	", false);
}

function trade_chat_update_room_stream_state($roomId, $status, $channelCid = '', $errorCode = '', $errorMessage = '') {
	$roomId = (int)$roomId;
	$status = trim((string)$status);
	if ($roomId < 1 || !in_array($status, array('pending', 'active', 'closed', 'blocked'), true)) {
		return false;
	}

	$channelCid = trim((string)$channelCid);
	$errorCode = trim((string)$errorCode);
	$errorMessage = trim((string)$errorMessage);

	return sql_query("
		UPDATE trade_chat_room
		SET room_status='" . sql_escape_string($status) . "',
			stream_channel_cid=" . ($channelCid === '' ? "stream_channel_cid" : "'" . sql_escape_string($channelCid) . "'") . ",
			opened_at=" . ($status === 'active' ? "IFNULL(opened_at, NOW())" : "opened_at") . ",
			last_sync_at=NOW(),
			last_error_code=" . ($errorCode === '' ? "NULL" : "'" . sql_escape_string($errorCode) . "'") . ",
			last_error_message=" . ($errorMessage === '' ? "NULL" : "'" . sql_escape_string($errorMessage) . "'") . "
		WHERE room_id='{$roomId}'
	", false);
}

function trade_chat_update_room_requests_result($roomId, $result) {
	$roomId = (int)$roomId;
	$result = trim((string)$result);
	if ($roomId < 1 || !in_array($result, array('pending', 'success', 'fail'), true)) {
		return false;
	}

	return sql_query("
		UPDATE trade_request
		SET chat_open_result='" . sql_escape_string($result) . "'
		WHERE chat_room_id='{$roomId}'
	", false);
}

function trade_chat_fetch_member_by_id($mbId) {
	$mbId = trim((string)$mbId);
	if ($mbId === '') return array();

	return sql_fetch("
		SELECT mb_id, mb_4, mb_company, mb_name
		FROM g5_member
		WHERE mb_id='" . sql_escape_string($mbId) . "'
		LIMIT 1
	");
}

function trade_chat_upsert_room_member($roomId, $memberRow, $role) {
	$roomId = (int)$roomId;
	$mbId = trim((string)($memberRow['mb_id'] ?? ''));
	$companyBzno = trade_chat_normalize_bzno($memberRow['mb_4'] ?? '');
	$companyKedcd = trim((string)($memberRow['mb_company'] ?? ''));
	$streamUserId = trade_chat_make_stream_user_id($mbId);
	$role = trim((string)$role);

	if ($roomId < 1 || $mbId === '' || $companyBzno === '' || $streamUserId === '') {
		return false;
	}

	if (!in_array($role, array('requester', 'target', 'company_member'), true)) {
		$role = 'company_member';
	}

	return sql_query("
		INSERT INTO trade_chat_room_member
		SET room_id='{$roomId}',
			mb_id='" . sql_escape_string($mbId) . "',
			stream_user_id='" . sql_escape_string($streamUserId) . "',
			company_bzno='" . sql_escape_string($companyBzno) . "',
			company_kedcd=" . ($companyKedcd === '' ? "NULL" : "'" . sql_escape_string($companyKedcd) . "'") . ",
			member_role='" . sql_escape_string($role) . "',
			is_active=1,
			joined_at=NOW()
		ON DUPLICATE KEY UPDATE
			stream_user_id=VALUES(stream_user_id),
			company_bzno=VALUES(company_bzno),
			company_kedcd=VALUES(company_kedcd),
			member_role=VALUES(member_role),
			is_active=1,
			updated_at=NOW()
	", false);
}

function trade_chat_sync_room_members_for_request($roomId, $requestRow, $currentMember) {
	$roomId = (int)$roomId;
	if ($roomId < 1) {
		return array(
			'success' => false,
			'message' => 'room_id가 올바르지 않습니다.',
		);
	}

	$synced = 0;
	$skipped = array();

	$requestMember = trade_chat_fetch_member_by_id($requestRow['request_mb_id'] ?? '');
	if (!empty($requestMember['mb_id'])) {
		if (trade_chat_upsert_room_member($roomId, $requestMember, 'requester')) {
			$synced++;
		}
	} else {
		$skipped[] = 'requester_not_found';
	}

	if (!empty($currentMember['mb_id'])) {
		if (trade_chat_upsert_room_member($roomId, $currentMember, 'target')) {
			$synced++;
		}
	} else {
		$skipped[] = 'target_not_found';
	}

	return array(
		'success' => $synced > 0,
		'synced_count' => $synced,
		'skipped' => $skipped,
	);
}

function trade_chat_member_company_bzno($member) {
	return trade_chat_normalize_bzno($member['mb_4'] ?? '');
}

function trade_chat_assert_member_access($room, $member, $requireActive = true) {
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

	if ($requireActive && ($room['room_status'] ?? '') !== 'active') {
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

function trade_chat_retry_stream_room($roomId, $member) {
	$room = trade_chat_find_room_by_id($roomId);
	if (empty($room['room_id'])) {
		return array(
			'success' => false,
			'message' => '채팅방을 찾을 수 없습니다.',
		);
	}

	$access = trade_chat_assert_member_access($room, $member, false);
	if (empty($access['success'])) {
		return $access;
	}

	return trade_chat_sync_stream_room($room['room_id']);
}

function trade_chat_sync_stream_room($roomId) {
	$room = trade_chat_find_room_by_id($roomId);
	if (empty($room['room_id'])) {
		trade_chat_write_event_log('stream_room_sync', 'fail', array(
			'room_id' => (int)$roomId,
			'reason' => 'room_not_found',
		), '채팅방 정보를 찾을 수 없습니다.');

		return array(
			'success' => false,
			'message' => '채팅방 정보를 찾을 수 없습니다.',
		);
	}

	if (($room['room_status'] ?? '') === 'active' && !empty($room['stream_channel_cid'])) {
		trade_chat_update_room_requests_result($room['room_id'], 'success');
		trade_chat_write_event_log('stream_room_sync', 'skip', array(
			'room_id' => (int)$room['room_id'],
			'tr_no' => (int)($room['latest_tr_no'] ?? 0),
			'reason' => 'room_already_active',
			'stream_channel_cid' => $room['stream_channel_cid'],
		));

		return array(
			'success' => true,
			'action' => 'skip',
			'room' => $room,
		);
	}

	$members = trade_chat_find_active_room_members($room['room_id']);
	if (count($members) < 2) {
		$message = 'Stream 채널 동기화에는 활성 멤버가 2명 이상 필요합니다.';
		trade_chat_update_room_stream_state($room['room_id'], 'pending', '', 'members_not_enough', $message);
		trade_chat_write_event_log('stream_room_sync', 'skip', array(
			'room_id' => (int)$room['room_id'],
			'tr_no' => (int)($room['latest_tr_no'] ?? 0),
			'reason' => 'members_not_enough',
			'member_count' => count($members),
		), $message);

		return array(
			'success' => false,
			'action' => 'skip',
			'message' => $message,
			'member_count' => count($members),
		);
	}

	$streamUserIds = array();
	$upsertResults = array();
	foreach ($members as $memberRow) {
		$streamUserId = trim((string)($memberRow['stream_user_id'] ?? ''));
		$mbId = trim((string)($memberRow['mb_id'] ?? ''));
		if ($streamUserId === '' || $mbId === '') continue;

		$streamUserIds[] = $streamUserId;
		$upsert = trade_chat_stream_upsert_user($mbId, array(
			'name' => $mbId,
			'company_bzno' => $memberRow['company_bzno'] ?? '',
			'company_kedcd' => $memberRow['company_kedcd'] ?? '',
		));
		$upsertResults[] = $upsert;

		if (empty($upsert['success'])) {
			$message = $upsert['message'] ?? 'Stream user upsert 실패';
			trade_chat_update_room_stream_state($room['room_id'], 'pending', '', 'user_upsert_failed', $message);
			trade_chat_write_event_log('stream_user_upsert', 'fail', array(
				'room_id' => (int)$room['room_id'],
				'tr_no' => (int)($room['latest_tr_no'] ?? 0),
				'mb_id' => $mbId,
				'stream_user_id' => $streamUserId,
			), $message);

			return array(
				'success' => false,
				'action' => 'user_upsert',
				'message' => $message,
				'stream_user_id' => $streamUserId,
			);
		}
	}

	$streamUserIds = array_values(array_unique($streamUserIds));
	if (count($streamUserIds) < 2) {
		$message = 'Stream user id가 2개 이상 필요합니다.';
		trade_chat_update_room_stream_state($room['room_id'], 'pending', '', 'stream_users_not_enough', $message);
		trade_chat_write_event_log('stream_room_sync', 'skip', array(
			'room_id' => (int)$room['room_id'],
			'tr_no' => (int)($room['latest_tr_no'] ?? 0),
			'reason' => 'stream_users_not_enough',
			'stream_user_count' => count($streamUserIds),
		), $message);

		return array(
			'success' => false,
			'action' => 'skip',
			'message' => $message,
		);
	}

	$channelResult = trade_chat_stream_get_or_create_channel(
		$room['stream_channel_type'] ?? 'messaging',
		$room['stream_channel_id'] ?? '',
		array(
			'room_id' => (int)$room['room_id'],
			'company_pair_key' => $room['company_pair_key'] ?? '',
			'source_tr_no' => (int)($room['source_tr_no'] ?? 0),
			'latest_tr_no' => (int)($room['latest_tr_no'] ?? 0),
			'created_by_id' => $streamUserIds[0],
			'members' => $streamUserIds,
		)
	);

	if (empty($channelResult['success'])) {
		$message = $channelResult['message'] ?? 'Stream channel 생성 실패';
		trade_chat_update_room_stream_state($room['room_id'], 'pending', '', 'channel_sync_failed', $message);
		trade_chat_write_event_log('stream_channel_sync', 'fail', array(
			'room_id' => (int)$room['room_id'],
			'tr_no' => (int)($room['latest_tr_no'] ?? 0),
			'stream_channel_id' => $room['stream_channel_id'] ?? '',
			'members' => $streamUserIds,
		), $message);

		return array(
			'success' => false,
			'action' => 'channel_sync',
			'message' => $message,
		);
	}

	$channelCid = $channelResult['channel_cid'] ?? (($room['stream_channel_type'] ?? 'messaging') . ':' . ($room['stream_channel_id'] ?? ''));
	trade_chat_update_room_stream_state($room['room_id'], 'active', $channelCid);
	trade_chat_update_room_requests_result($room['room_id'], 'success');
	trade_chat_write_event_log('stream_room_sync', 'success', array(
		'room_id' => (int)$room['room_id'],
		'tr_no' => (int)($room['latest_tr_no'] ?? 0),
		'stream_channel_id' => $room['stream_channel_id'] ?? '',
		'stream_channel_cid' => $channelCid,
		'members' => $streamUserIds,
		'upsert_results' => $upsertResults,
		'channel_result' => $channelResult,
	));

	return array(
		'success' => true,
		'action' => 'sync',
		'room_id' => (int)$room['room_id'],
		'stream_channel_cid' => $channelCid,
		'members' => $streamUserIds,
	);
}

function trade_chat_open_or_attach_room($trNo, $member) {
	$requestRow = trade_chat_get_request_row($trNo);
	if (empty($requestRow['tr_no'])) {
		trade_chat_update_request_chat_state($trNo, 0, 'fail');
		trade_chat_write_event_log('room_open', 'fail', array(
			'tr_no' => (int)$trNo,
			'mb_id' => $member['mb_id'] ?? '',
			'reason' => 'request_not_found',
		), '거래 요청을 찾을 수 없습니다.');

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
		trade_chat_update_request_chat_state($requestRow['tr_no'], 0, 'fail');
		trade_chat_write_event_log('room_open', 'fail', array(
			'tr_no' => (int)$requestRow['tr_no'],
			'mb_id' => $member['mb_id'] ?? '',
			'reason' => 'invalid_company_pair',
		), '기업 식별 정보가 올바르지 않습니다.');

		return array(
			'success' => false,
			'message' => '기업 식별 정보가 올바르지 않습니다.',
		);
	}

	$existingRoom = trade_chat_find_room_by_pair_key($companyPairKey);
	if (!empty($existingRoom['room_id'])) {
		sql_query("
			UPDATE trade_chat_room
			SET latest_tr_no='" . (int)$requestRow['tr_no'] . "',
				updated_at=NOW()
			WHERE room_id='" . (int)$existingRoom['room_id'] . "'
		", false);

		$existingRoom = trade_chat_find_room_by_pair_key($companyPairKey);
		$roomId = (int)($existingRoom['room_id'] ?? 0);
		trade_chat_sync_room_members_for_request($roomId, $requestRow, $member);
		trade_chat_update_request_chat_state($requestRow['tr_no'], $roomId, 'pending');
		trade_chat_write_event_log('room_open', 'skip', array(
			'room_id' => $roomId,
			'tr_no' => (int)$requestRow['tr_no'],
			'mb_id' => $member['mb_id'] ?? '',
			'reason' => 'room_already_exists',
			'company_pair_key' => $companyPairKey,
		));

		return array(
			'success' => true,
			'action' => 'reuse',
			'room' => $existingRoom,
			'company_pair_key' => $companyPairKey,
		);
	}

	$settings = trade_chat_settings();
	$channelType = trade_chat_normalize_channel_type($settings['channel_type'] ?? 'messaging');
	$streamChannelId = trade_chat_make_stream_channel_id($companyPairKey);

	$inserted = sql_query("
		INSERT INTO trade_chat_room
		SET company_pair_key='" . sql_escape_string($companyPairKey) . "',
			request_bzno='" . sql_escape_string(trade_chat_normalize_bzno($requestRow['request_bzno'] ?? '')) . "',
			target_bzno='" . sql_escape_string(trade_chat_normalize_bzno($requestRow['target_bzno'] ?? '')) . "',
			request_company_name='" . sql_escape_string($requestRow['request_company_name'] ?? '') . "',
			target_company_name='" . sql_escape_string($requestRow['target_company_name'] ?? '') . "',
			stream_channel_type='" . sql_escape_string($channelType) . "',
			stream_channel_id='" . sql_escape_string($streamChannelId) . "',
			stream_channel_cid=NULL,
			source_tr_no='" . (int)$requestRow['tr_no'] . "',
			latest_tr_no='" . (int)$requestRow['tr_no'] . "',
			room_status='pending',
			opened_at=NULL,
			created_at=NOW()
		ON DUPLICATE KEY UPDATE
			latest_tr_no=VALUES(latest_tr_no),
			updated_at=NOW()
	", false);

	$room = trade_chat_find_room_by_pair_key($companyPairKey);
	if (!$inserted || empty($room['room_id'])) {
		trade_chat_update_request_chat_state($requestRow['tr_no'], 0, 'fail');
		trade_chat_write_event_log('room_open', 'fail', array(
			'tr_no' => (int)$requestRow['tr_no'],
			'mb_id' => $member['mb_id'] ?? '',
			'company_pair_key' => $companyPairKey,
			'stream_channel_id' => $streamChannelId,
		), '채팅방 row 생성에 실패했습니다.');

		return array(
			'success' => false,
			'message' => '채팅방 row 생성에 실패했습니다.',
			'action' => 'create',
			'company_pair_key' => $companyPairKey,
			'stream_channel_id' => $streamChannelId,
		);
	}

	$roomId = (int)$room['room_id'];
	$memberSync = trade_chat_sync_room_members_for_request($roomId, $requestRow, $member);
	trade_chat_update_request_chat_state($requestRow['tr_no'], $roomId, 'pending');
	trade_chat_write_event_log('room_open', 'success', array(
		'room_id' => $roomId,
		'tr_no' => (int)$requestRow['tr_no'],
		'mb_id' => $member['mb_id'] ?? '',
		'company_pair_key' => $companyPairKey,
		'stream_channel_id' => $streamChannelId,
		'room_status' => $room['room_status'] ?? 'pending',
		'member_sync' => $memberSync,
	));

	return array(
		'success' => true,
		'action' => 'create',
		'room' => $room,
		'company_pair_key' => $companyPairKey,
		'stream_channel_id' => $streamChannelId,
	);
}

function trade_chat_issue_user_token($room, $member) {
	$access = trade_chat_assert_member_access($room, $member);
	if (empty($access['success'])) {
		return $access;
	}

	return trade_chat_stream_create_token($access['stream_user_id']);
}
