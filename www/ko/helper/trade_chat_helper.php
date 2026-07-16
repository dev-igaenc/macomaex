<?php
if (!defined('_GNUBOARD_')) exit;

/*
 * 거래 채팅 도메인 헬퍼.
 * 거래 요청 번호 기준 1:1 room 식별 규칙과 권한/조회용 공통 함수를 모아둔다.
 */

include_once(__DIR__ . '/trade_chat_config_helper.php');
include_once(__DIR__ . '/trade_chat_stream_helper.php');

// 사업자번호 비교/저장에 필요한 숫자만 남긴다.
function trade_chat_normalize_bzno($bzno) {
	return preg_replace('/[^0-9]/', '', (string)$bzno);
}

// 두 회사 사업자번호를 정렬해 기업 pair 기준 고유 키를 만든다.
function trade_chat_make_company_pair_key($bznoA, $bznoB) {
	$a = trade_chat_normalize_bzno($bznoA);
	$b = trade_chat_normalize_bzno($bznoB);

	if ($a === '' || $b === '') return '';

	$pair = array($a, $b);
	sort($pair, SORT_STRING);

	return $pair[0] . ':' . $pair[1];
}

// 회원 ID를 Stream에서 허용하는 안전한 user id 형식으로 변환한다.
function trade_chat_make_stream_user_id($mbId) {
	$mbId = trim((string)$mbId);
	if ($mbId === '') return '';

	// Stream user id는 콜론(:)을 허용하지 않으므로 member_ prefix를 사용한다.
	return 'member_' . preg_replace('/[^a-z0-9@_. -]/i', '_', $mbId);
}

// 거래 요청 하나당 분리할 고정 Stream channel id를 만든다.
function trade_chat_make_stream_channel_id($trNo) {
	$trNo = (int)$trNo;
	if ($trNo < 1) return '';

	return 'trade-' . $trNo;
}

// 로그/에러 문구를 한 줄 요약으로 정리해 DB 컬럼 길이와 가독성을 맞춘다.
function trade_chat_compact_log_message($message, $maxLength = 255) {
	$message = trim((string)$message);
	if ($message === '') return '';

	$message = preg_replace('/\s+/u', ' ', $message);
	$maxLength = (int)$maxLength;
	if ($maxLength < 1) return $message;

	if (function_exists('mb_strlen') && function_exists('mb_substr')) {
		if (mb_strlen($message, 'UTF-8') > $maxLength) {
			return mb_substr($message, 0, $maxLength - 1, 'UTF-8') . '…';
		}

		return $message;
	}

	if (strlen($message) > $maxLength) {
		return substr($message, 0, $maxLength - 3) . '...';
	}

	return $message;
}

// 내부 error_code를 운영자가 읽기 쉬운 한국어 메시지로 치환한다.
function trade_chat_error_summary($code, $fallback = '') {
	$code = trim((string)$code);
	$map = array(
		'room_not_found' => '채팅방 정보를 찾을 수 없습니다.',
		'room_already_active' => '이미 연결된 채팅방입니다.',
		'members_not_enough' => '채팅 멤버가 아직 충분하지 않습니다.',
		'user_upsert_failed' => '채팅 사용자 동기화에 실패했습니다.',
		'stream_users_not_enough' => '채팅 사용자 정보가 아직 충분하지 않습니다.',
		'channel_sync_failed' => '채팅 채널 동기화에 실패했습니다.',
		'request_not_found' => '거래 요청을 찾을 수 없습니다.',
		'invalid_room_members' => '채팅에 참여할 요청자 또는 수신 담당자 정보가 없습니다.',
		'invalid_company_pair' => '기업 식별 정보가 올바르지 않습니다.',
		'room_row_create_failed' => '채팅방 생성에 실패했습니다.',
		'invalid_room_id' => '채팅방 식별값이 올바르지 않습니다.',
		'invalid_member' => '회원 정보가 올바르지 않습니다.',
		'room_access_denied' => '채팅방 접근 권한이 없습니다.',
		'room_not_active' => '활성 채팅방이 아닙니다.',
		'empty_member_id' => '회원 ID가 비어 있습니다.',
		'stream_client_init_failed' => 'GetStream 연결을 초기화할 수 없습니다.',
		'invalid_channel_identifier' => '채널 식별 정보가 올바르지 않습니다.',
		'invalid_channel_id' => '채널 ID 형식이 올바르지 않습니다.',
		'missing_channel_creator' => '채널 생성자 정보가 없습니다.',
		'empty_stream_user_id' => '채팅 사용자 식별값이 비어 있습니다.',
		'stream_api_user_upsert_failed' => 'Stream 사용자 등록에 실패했습니다.',
		'stream_api_channel_sync_failed' => 'Stream 채널 동기화에 실패했습니다.',
		'stream_api_token_failed' => 'Stream 토큰 발급에 실패했습니다.',
	);

	if ($code !== '' && isset($map[$code])) {
		return $map[$code];
	}

	return trade_chat_compact_log_message($fallback);
}

// 요약 문구 외의 상세 원인은 payload에 붙여서 파일/DB 로그에서 같이 추적한다.
function trade_chat_attach_error_detail($payload, $code = '', $detailMessage = '') {
	$payload = is_array($payload) ? $payload : array();
	$code = trim((string)$code);
	$detailMessage = trade_chat_compact_log_message($detailMessage, 500);

	if ($code !== '') {
		$payload['error_code'] = $code;
	}

	if ($detailMessage !== '') {
		$payload['raw_error_message'] = $detailMessage;
	}

	return $payload;
}

// channel type을 허용 형식으로 정리해 잘못된 설정값이 흘러가지 않게 한다.
function trade_chat_normalize_channel_type($channelType) {
	$channelType = trim((string)$channelType);
	if ($channelType === '') return 'messaging';
	if (!preg_match('/^[a-z0-9_-]{1,30}$/', $channelType)) return 'messaging';
	return $channelType;
}

// Stream channel id로 사용할 수 있는 값인지 최종 검증한다.
function trade_chat_is_valid_channel_id($channelId) {
	$channelId = trim((string)$channelId);
	return $channelId !== '' && preg_match('/^[a-z0-9@_. -]{1,128}$/i', $channelId);
}

// 채팅 이벤트를 DB 로그와 파일 로그에 함께 남기는 공통 진입점이다.
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
	$errorSql = sql_escape_string(trade_chat_compact_log_message($errorMessage));
	$mbIdSql = sql_escape_string($mbId);

	// DB 로그와 파일 로그를 같은 entry point로 묶어서 추적 기준을 일치시킨다.
	$result = sql_query("
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

	trade_chat_write_daily_file_log($eventType, $status, $payload, $errorMessage);

	return $result;
}

// 일자별 채팅 파일 로그를 쌓아둘 디렉터리 경로를 반환한다.
function trade_chat_log_directory_path() {
	return dirname(dirname(__DIR__)) . '/logs/chat';
}

// 오늘 날짜 기준 또는 지정 날짜 기준의 채팅 파일 로그 경로를 만든다.
function trade_chat_log_file_path($dateYmd = '') {
	$dateYmd = preg_replace('/[^0-9]/', '', (string)$dateYmd);
	if ($dateYmd === '') {
		$dateYmd = date('Ymd');
	}

	return trade_chat_log_directory_path() . '/chat_log_' . $dateYmd . '.php';
}

// 파일 로그에서 event_type별 문맥을 빠르게 읽을 수 있게 짧은 설명을 만든다.
function trade_chat_log_context_label($eventType, $status, $payload = array()) {
	$eventType = trim((string)$eventType);
	$status = trim((string)$status);
	$reason = trim((string)($payload['reason'] ?? ''));

	$map = array(
		'room_open' => '거래 수락 후 채팅방 생성',
		'stream_room_sync' => 'GetStream 채널 동기화',
		'stream_channel_sync' => 'GetStream 채널 생성 또는 조회',
		'stream_user_upsert' => 'GetStream 사용자 동기화',
		'file_upload_attempt' => '첨부파일 업로드 시도',
		'file_scan_webhook' => '첨부파일 스캔 웹훅 수신',
		'file_scan_result' => '첨부파일 악성코드 스캔 결과',
		'file_scan_action' => '악성 파일 메시지 후속 조치',
		'message_policy_action' => '부적절 메시지 정책 차단',
	);

	$label = $map[$eventType] ?? '채팅 이벤트 처리';

	if ($eventType === 'stream_room_sync' && $status === 'skip' && $reason === 'room_already_active') {
		return '이미 활성화된 채팅방 동기화 확인';
	}

	if ($eventType === 'stream_room_sync' && $status === 'skip' && $reason !== '') {
		return 'GetStream 채널 동기화 건너뜀';
	}

	if ($eventType === 'room_open' && $status === 'skip') {
		return '기존 채팅방 재사용';
	}

	return $label;
}

// 성공/스킵 로그도 사람이 바로 읽을 수 있게 기본 message를 채운다.
function trade_chat_log_default_message($eventType, $status, $payload = array()) {
	$eventType = trim((string)$eventType);
	$status = trim((string)$status);
	$reason = trim((string)($payload['reason'] ?? ''));

	if ($status === 'success') {
		$map = array(
			'room_open' => '채팅방 생성 완료',
			'stream_room_sync' => '채팅 채널 동기화 완료',
			'stream_channel_sync' => '채팅 채널 생성 또는 조회 완료',
			'stream_user_upsert' => '채팅 사용자 동기화 완료',
			'file_upload_attempt' => '첨부파일 업로드 시도를 기록했습니다.',
			'file_scan_webhook' => '파일 스캔 웹훅 수신 처리 완료',
			'file_scan_result' => '첨부파일 보안 스캔 완료',
			'file_scan_action' => '보안 조치가 완료되었습니다.',
			'message_policy_action' => '메시지 정책 차단 처리 완료',
		);

		return $map[$eventType] ?? '채팅 작업이 완료되었습니다.';
	}

	if ($status === 'skip') {
		if ($eventType === 'room_open' && $reason === 'room_already_exists') {
			return '기존 채팅방을 재사용했습니다.';
		}
		if ($eventType === 'stream_room_sync' && $reason === 'room_already_active') {
			return '이미 활성화된 채팅방입니다.';
		}
		if ($eventType === 'stream_room_sync' && $reason === 'members_not_enough') {
			return '채팅 멤버가 아직 충분하지 않습니다.';
		}
		if ($eventType === 'stream_room_sync' && $reason === 'stream_users_not_enough') {
			return '채팅 사용자 정보가 아직 충분하지 않습니다.';
		}
		if ($eventType === 'file_scan_webhook' && $reason === 'unsupported_event') {
			return '지원하지 않는 웹훅 이벤트라 건너뛰었습니다.';
		}
		if ($eventType === 'file_upload_attempt' && $reason !== '') {
			return '첨부파일 업로드가 정책에 의해 차단되었습니다.';
		}
		if ($eventType === 'file_scan_webhook' && $reason === 'no_attachments') {
			return '첨부파일 없는 메시지라 스캔을 건너뛰었습니다.';
		}
		if ($eventType === 'file_scan_result' && $reason === 'scan_unavailable') {
			return '스캐너 미설치 상태로 스캔을 건너뛰었습니다.';
		}
		if ($eventType === 'file_scan_action' && $reason === 'action_not_required') {
			return '감염 파일이 없어 후속 조치를 건너뛰었습니다.';
		}
		if ($eventType === 'message_policy_action' && $reason === 'sql_like_message_detected') {
			return 'SQL 유사 메시지를 감지했습니다.';
		}

		return '채팅 작업을 건너뛰었습니다.';
	}

	return '';
}

// 초기 안정화용으로 사람이 읽기 쉬운 멀티라인 일자별 로그 파일에 append한다.
function trade_chat_write_daily_file_log($eventType, $status, $payload = array(), $errorMessage = '') {
	$payload = is_array($payload) ? $payload : array();
	$dir = trade_chat_log_directory_path();
	if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
		return false;
	}

	$filePath = trade_chat_log_file_path();
	$isNewFile = !file_exists($filePath);
	$requestUri = trim((string)($_SERVER['REQUEST_URI'] ?? ''));
	$loggedAt = date('Y-m-d H:i:s');
	$errorMessage = trade_chat_compact_log_message($errorMessage);
	if ($errorMessage === '') {
		$errorMessage = trade_chat_log_default_message($eventType, $status, $payload);
	}

	$fields = array(
		'event_type' => trim((string)$eventType),
		'event_status' => trim((string)$status),
		'stream_event_type' => trim((string)($payload['event_type'] ?? '')),
		'webhook_event_id' => trim((string)($payload['webhook_event_id'] ?? '')),
		'request_id' => trim((string)($payload['request_id'] ?? '')),
		'room_id' => (int)($payload['room_id'] ?? 0),
		'tr_no' => (int)($payload['tr_no'] ?? 0),
		'mb_id' => trim((string)($payload['mb_id'] ?? '')),
		'company_pair_key' => trim((string)($payload['company_pair_key'] ?? '')),
		'stream_channel_id' => trim((string)($payload['stream_channel_id'] ?? '')),
		'stream_channel_cid' => trim((string)($payload['stream_channel_cid'] ?? '')),
		'message_id' => trim((string)($payload['message_id'] ?? '')),
		'attachment_count' => (int)($payload['attachment_count'] ?? 0),
		'attachment_index' => (int)($payload['attachment_index'] ?? -1),
		'attachment_type' => trim((string)($payload['attachment_type'] ?? '')),
		'attachment_title' => trim((string)($payload['attachment_title'] ?? '')),
			'attachment_url' => trim((string)($payload['attachment_url'] ?? '')),
			'attempt_result' => trim((string)($payload['attempt_result'] ?? '')),
			'attempt_reason' => trim((string)($payload['attempt_reason'] ?? '')),
			'policy_expected' => trim((string)($payload['policy_expected'] ?? '')),
			'scan_status' => trim((string)($payload['scan_status'] ?? '')),
		'scanner' => trim((string)($payload['scanner'] ?? '')),
		'file_size' => (int)($payload['file_size'] ?? 0),
		'scan_total' => (int)($payload['scan_total'] ?? 0),
		'scan_clean' => (int)($payload['scan_clean'] ?? 0),
		'scan_infected' => (int)($payload['scan_infected'] ?? 0),
		'scan_unavailable' => (int)($payload['scan_unavailable'] ?? 0),
		'scan_error' => (int)($payload['scan_error'] ?? 0),
		'infected_count' => (int)($payload['infected_count'] ?? 0),
		'http_code' => (int)($payload['http_code'] ?? 0),
		'actor_user_id' => trim((string)($payload['actor_user_id'] ?? '')),
		'action_status' => trim((string)($payload['action_status'] ?? '')),
		'delete_status' => trim((string)($payload['delete_status'] ?? '')),
		'notice_status' => trim((string)($payload['notice_status'] ?? '')),
		'action_error_code' => trim((string)($payload['action_error_code'] ?? '')),
		'ui_action' => trim((string)($payload['ui_action'] ?? '')),
		'ui_context' => trim((string)($payload['ui_context'] ?? '')),
		'search_keyword' => trim((string)($payload['search_keyword'] ?? '')),
		'search_result_count' => (int)($payload['search_result_count'] ?? 0),
		'message' => $errorMessage,
		'context' => trade_chat_log_context_label($eventType, $status, $payload),
		'request_uri' => $requestUri,
		'client_ip' => trim((string)($payload['client_ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''))),
		'user_agent' => trade_chat_compact_log_message((string)($payload['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '')), 300),
	);

	if ($fields['company_pair_key'] === '' && !empty($payload['room_id'])) {
		$room = trade_chat_find_room_by_id((int)$payload['room_id']);
		if (!empty($room['company_pair_key'])) {
			$fields['company_pair_key'] = trim((string)$room['company_pair_key']);
		}
		if ($fields['stream_channel_id'] === '' && !empty($room['stream_channel_id'])) {
			$fields['stream_channel_id'] = trim((string)$room['stream_channel_id']);
		}
		if ($fields['stream_channel_cid'] === '' && !empty($room['stream_channel_cid'])) {
			$fields['stream_channel_cid'] = trim((string)$room['stream_channel_cid']);
		}
	}

	if (!empty($payload['error_code'])) {
		$fields['error_code'] = trim((string)$payload['error_code']);
	}

	if (!empty($payload['raw_error_message'])) {
		$fields['raw_error_message'] = trade_chat_compact_log_message($payload['raw_error_message'], 500);
	}

	if (!empty($payload['reason'])) {
		$fields['reason'] = trim((string)$payload['reason']);
	}

	// 초기 안정화 단계에서는 사람이 바로 열어 읽기 쉬운 멀티라인 포맷을 우선한다.
	$lines = array();
	if ($isNewFile) {
		$lines[] = "<?php exit; ?>";
		$lines[] = '';
	}

	$lines[] = '[' . $loggedAt . ']';
	$allowZeroKeys = array(
		'attachment_index',
		'attachment_count',
		'scan_total',
		'scan_clean',
		'scan_infected',
		'scan_unavailable',
		'scan_error',
		'infected_count',
		'http_code',
		'search_result_count',
	);
	foreach ($fields as $key => $value) {
		if ($value === '') continue;
		if ($value === 0 && !in_array($key, $allowZeroKeys, true)) continue;
		if ($key === 'attachment_index' && (int)$value < 0) continue;
		$lines[] = $key . ': ' . $value;
	}
	$lines[] = '---';
	$lines[] = '';

	$body = implode(PHP_EOL, $lines);
	return @file_put_contents($filePath, $body, FILE_APPEND | LOCK_EX) !== false;
}

function trade_chat_get_request_row($trNo) {
	$trNo = (int)$trNo;
	if ($trNo < 1) return array();

	return sql_fetch("
		SELECT
			tr_no,
			request_mb_id,
			target_mb_id,
			request_company_name,
			target_company_name,
			request_bzno,
			target_bzno,
			target_kedcd,
			recipient_status,
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

// 레거시 기업 pair key로 기존 채팅방이 있는지 찾는다. 신규 생성에서는 사용하지 않는다.
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

// 거래 번호 문맥으로 정확히 연결된 채팅방을 찾는다.
function trade_chat_find_room_by_tr_no($trNo) {
	$trNo = (int)$trNo;
	if ($trNo < 1) return array();

	return sql_fetch("
		SELECT R.*
		FROM trade_chat_room R
		WHERE R.tr_no='{$trNo}'
		   OR R.room_id=(SELECT chat_room_id FROM trade_request WHERE tr_no='{$trNo}' LIMIT 1)
		ORDER BY R.room_id DESC
		LIMIT 1
	");
}

// room_id 기준으로 채팅방 단건을 조회한다.
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

// 현재 활성 멤버만 조회해 Stream sync 대상 목록을 만든다.
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

// 거래 요청 row에 연결된 채팅방과 open 결과 상태를 기록한다.
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

// 채팅방의 Stream 연결 상태와 마지막 에러 상태를 room row에 반영한다.
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

// 같은 room을 바라보는 거래 요청 row들의 open 결과를 일괄 갱신한다.
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

// 회원 ID로 채팅 연결에 필요한 최소 회원 정보를 조회한다.
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

// room-member 매핑을 upsert해서 거래별 1:1 room 참여자를 고정 관리한다.
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

// 거래 요청 문맥으로 요청자와 지정된 수신 담당자만 room-member 테이블과 동기화한다.
function trade_chat_sync_room_members_for_request($roomId, $requestRow, $currentMember = array()) {
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

	$targetMember = trade_chat_fetch_member_by_id($requestRow['target_mb_id'] ?? '');
	if (!empty($targetMember['mb_id'])) {
		if (trade_chat_upsert_room_member($roomId, $targetMember, 'target')) {
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

// 현재 회원 row에서 채팅 권한 판별에 사용할 회사 사업자번호를 꺼낸다.
function trade_chat_member_company_bzno($member) {
	return trade_chat_normalize_bzno($member['mb_4'] ?? '');
}

// 현재 회원이 해당 거래별 room의 정확한 참여자인지와 활성 상태 요구 조건을 함께 검증한다.
function trade_chat_assert_member_access($room, $member, $requireActive = true) {
	$memberId = trim((string)($member['mb_id'] ?? ''));

	if ($memberId === '') {
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

	$roomMember = sql_fetch("SELECT room_member_id FROM trade_chat_room_member WHERE room_id='" . (int)$room['room_id'] . "' AND mb_id='" . sql_escape_string($memberId) . "' AND is_active=1 LIMIT 1");
	if (empty($roomMember['room_member_id'])) {
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

// room_id만 주어졌을 때 room의 최근 거래 문맥으로 멤버를 다시 맞춘다.
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

// 사용자가 수동 재시도했을 때 room 접근 권한을 확인하고 Stream sync를 다시 호출한다.
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

	return trade_chat_sync_stream_room($room['room_id'], $member);
}

// 로컬 room/member 상태를 기준으로 Stream user와 channel을 실제 활성 상태로 맞춘다.
function trade_chat_sync_stream_room($roomId, $member = array()) {
	$actorMbId = trim((string)($member['mb_id'] ?? ''));
	$room = trade_chat_find_room_by_id($roomId);
	if (empty($room['room_id'])) {
		$errorCode = 'room_not_found';
		$message = trade_chat_error_summary($errorCode);
		trade_chat_write_event_log('stream_room_sync', 'fail', trade_chat_attach_error_detail(array(
			'room_id' => (int)$roomId,
			'mb_id' => $actorMbId,
			'reason' => 'room_not_found',
		), $errorCode), $message);

		return array(
			'success' => false,
			'message' => $message,
		);
	}

	if (($room['room_status'] ?? '') === 'active' && !empty($room['stream_channel_cid'])) {
		trade_chat_update_room_requests_result($room['room_id'], 'success');
		trade_chat_write_event_log('stream_room_sync', 'skip', array(
			'room_id' => (int)$room['room_id'],
			'tr_no' => (int)($room['latest_tr_no'] ?? 0),
			'mb_id' => $actorMbId,
			'reason' => 'room_already_active',
			'stream_channel_cid' => $room['stream_channel_cid'],
		));

		return array(
			'success' => true,
			'action' => 'skip',
			'room' => $room,
		);
	}

	// Stream 채널은 양쪽 멤버가 모두 준비된 뒤에만 활성화한다.
	$members = trade_chat_find_active_room_members($room['room_id']);
	if (count($members) < 2) {
		$errorCode = 'members_not_enough';
		$message = trade_chat_error_summary($errorCode);
		trade_chat_update_room_stream_state($room['room_id'], 'pending', '', $errorCode, $message);
		trade_chat_write_event_log('stream_room_sync', 'skip', trade_chat_attach_error_detail(array(
			'room_id' => (int)$room['room_id'],
			'tr_no' => (int)($room['latest_tr_no'] ?? 0),
			'mb_id' => $actorMbId,
			'reason' => 'members_not_enough',
			'member_count' => count($members),
		), $errorCode), $message);

		return array(
			'success' => false,
			'action' => 'skip',
			'message' => $message,
			'member_count' => count($members),
		);
	}

	$streamUserIds = array();
	$upsertResults = array();
	// 로컬 멤버를 먼저 Stream user로 맞춘 뒤 채널 생성/재사용을 시도한다.
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
			$errorCode = trim((string)($upsert['error_code'] ?? 'user_upsert_failed'));
			$message = trade_chat_error_summary($errorCode, $upsert['message'] ?? '');
			$detailMessage = $upsert['detail_message'] ?? ($upsert['message'] ?? '');
			trade_chat_update_room_stream_state($room['room_id'], 'pending', '', $errorCode, $message);
			trade_chat_write_event_log('stream_user_upsert', 'fail', trade_chat_attach_error_detail(array(
				'room_id' => (int)$room['room_id'],
				'tr_no' => (int)($room['latest_tr_no'] ?? 0),
				'mb_id' => $mbId,
				'stream_user_id' => $streamUserId,
			), $errorCode, $detailMessage), $message);

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
		$errorCode = 'stream_users_not_enough';
		$message = trade_chat_error_summary($errorCode);
		trade_chat_update_room_stream_state($room['room_id'], 'pending', '', $errorCode, $message);
		trade_chat_write_event_log('stream_room_sync', 'skip', trade_chat_attach_error_detail(array(
			'room_id' => (int)$room['room_id'],
			'tr_no' => (int)($room['latest_tr_no'] ?? 0),
			'mb_id' => $actorMbId,
			'reason' => 'stream_users_not_enough',
			'stream_user_count' => count($streamUserIds),
		), $errorCode), $message);

		return array(
			'success' => false,
			'action' => 'skip',
			'message' => $message,
		);
	}

	// 기업 pair 기준 room 하나를 Stream channel 하나로 동기화한다.
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
		$errorCode = trim((string)($channelResult['error_code'] ?? 'channel_sync_failed'));
		$message = trade_chat_error_summary($errorCode, $channelResult['message'] ?? '');
		$detailMessage = $channelResult['detail_message'] ?? ($channelResult['message'] ?? '');
		trade_chat_update_room_stream_state($room['room_id'], 'pending', '', $errorCode, $message);
		trade_chat_write_event_log('stream_channel_sync', 'fail', trade_chat_attach_error_detail(array(
			'room_id' => (int)$room['room_id'],
			'tr_no' => (int)($room['latest_tr_no'] ?? 0),
			'mb_id' => $actorMbId,
			'stream_channel_id' => $room['stream_channel_id'] ?? '',
			'members' => $streamUserIds,
		), $errorCode, $detailMessage), $message);

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
		'mb_id' => $actorMbId,
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

// 거래 수락 시 거래 번호 기준 1:1 room을 생성하거나 같은 거래의 room을 재사용한다.
function trade_chat_open_or_attach_room($trNo, $member) {
	$requestRow = trade_chat_get_request_row($trNo);
	if (empty($requestRow['tr_no'])) {
		$errorCode = 'request_not_found';
		$message = trade_chat_error_summary($errorCode);
		trade_chat_update_request_chat_state($trNo, 0, 'fail');
		trade_chat_write_event_log('room_open', 'fail', trade_chat_attach_error_detail(array(
			'tr_no' => (int)$trNo,
			'mb_id' => $member['mb_id'] ?? '',
			'reason' => 'request_not_found',
		), $errorCode), $message);

		return array(
			'success' => false,
			'message' => $message,
		);
	}

	$requestMemberId = trim((string)($requestRow['request_mb_id'] ?? ''));
	$targetMemberId = trim((string)($requestRow['target_mb_id'] ?? ''));
	// 정책 전환 전 데이터는 담당자 값이 없으므로, 기존 수락 처리자만 레거시 호환 대상으로 사용한다.
	if ($targetMemberId === '' && ($requestRow['recipient_status'] ?? '') === 'legacy') {
		$targetMemberId = trim((string)($member['mb_id'] ?? ''));
		$requestRow['target_mb_id'] = $targetMemberId;
	}
	if ((int)($requestRow['approval_status'] ?? 0) !== 1 || $requestMemberId === '' || $targetMemberId === '') {
		$errorCode = 'invalid_room_members';
		$message = trade_chat_error_summary($errorCode);
		trade_chat_update_request_chat_state($requestRow['tr_no'], 0, 'fail');
		trade_chat_write_event_log('room_open', 'fail', trade_chat_attach_error_detail(array(
			'tr_no' => (int)$requestRow['tr_no'],
			'mb_id' => $member['mb_id'] ?? '',
			'reason' => 'invalid_room_members',
		), $errorCode), $message);

		return array(
			'success' => false,
			'message' => $message,
		);
	}

	$companyPairKey = trade_chat_make_company_pair_key(
		$requestRow['request_bzno'] ?? '',
		$requestRow['target_bzno'] ?? ''
	);
	$existingRoom = trade_chat_find_room_by_tr_no($requestRow['tr_no']);
	if (!empty($existingRoom['room_id'])) {
		$roomId = (int)($existingRoom['room_id'] ?? 0);
		$memberSync = trade_chat_sync_room_members_for_request($roomId, $requestRow, $member);
		trade_chat_update_request_chat_state($requestRow['tr_no'], $roomId, 'pending');
		$streamSync = trade_chat_sync_stream_room($roomId, $member);
		$existingRoom = trade_chat_find_room_by_id($roomId);
		trade_chat_write_event_log('room_open', 'skip', array(
			'room_id' => $roomId,
			'tr_no' => (int)$requestRow['tr_no'],
			'mb_id' => $member['mb_id'] ?? '',
			'reason' => 'room_already_exists_for_trade',
			'company_pair_key' => $companyPairKey,
			'member_sync' => $memberSync,
			'stream_sync' => $streamSync,
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
	$streamChannelId = trade_chat_make_stream_channel_id($requestRow['tr_no']);

	$inserted = sql_query("
		INSERT INTO trade_chat_room
		SET tr_no='" . (int)$requestRow['tr_no'] . "',
			company_pair_key='" . sql_escape_string($companyPairKey) . "',
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
		ON DUPLICATE KEY UPDATE updated_at=updated_at
	", false);

	$room = trade_chat_find_room_by_tr_no($requestRow['tr_no']);
	if (!$inserted || empty($room['room_id'])) {
		$errorCode = 'room_row_create_failed';
		$message = trade_chat_error_summary($errorCode);
		trade_chat_update_request_chat_state($requestRow['tr_no'], 0, 'fail');
		trade_chat_write_event_log('room_open', 'fail', trade_chat_attach_error_detail(array(
			'tr_no' => (int)$requestRow['tr_no'],
			'mb_id' => $member['mb_id'] ?? '',
			'company_pair_key' => $companyPairKey,
			'stream_channel_id' => $streamChannelId,
		), $errorCode), $message);

		return array(
			'success' => false,
			'message' => $message,
			'action' => 'create',
			'company_pair_key' => $companyPairKey,
			'stream_channel_id' => $streamChannelId,
		);
	}

	$roomId = (int)$room['room_id'];
	$memberSync = trade_chat_sync_room_members_for_request($roomId, $requestRow, $member);
	trade_chat_update_request_chat_state($requestRow['tr_no'], $roomId, 'pending');
	$streamSync = trade_chat_sync_stream_room($roomId, $member);
	$room = trade_chat_find_room_by_id($roomId);
	trade_chat_write_event_log('room_open', 'success', array(
		'room_id' => $roomId,
		'tr_no' => (int)$requestRow['tr_no'],
		'mb_id' => $member['mb_id'] ?? '',
		'company_pair_key' => $companyPairKey,
		'stream_channel_id' => $streamChannelId,
		'room_status' => $room['room_status'] ?? 'pending',
		'member_sync' => $memberSync,
		'stream_sync' => $streamSync,
	));

	return array(
		'success' => true,
		'action' => 'create',
		'room' => $room,
		'company_pair_key' => $companyPairKey,
		'stream_channel_id' => $streamChannelId,
	);
}

function trade_chat_make_room_initial($title) {
	$title = trim((string)$title);
	if ($title === '') return '채';

	if (function_exists('mb_substr')) {
		return mb_substr($title, 0, 1, 'UTF-8');
	}

	return substr($title, 0, 1);
}

// 현재 회원 기준으로 상대 회사명을 room 제목으로 만든다.
function trade_chat_make_room_title($room, $memberBzno) {
	$memberBzno = trade_chat_normalize_bzno($memberBzno);
	$requestBzno = trade_chat_normalize_bzno($room['request_bzno'] ?? '');
	$targetBzno = trade_chat_normalize_bzno($room['target_bzno'] ?? '');

	$title = '';
	if ($memberBzno !== '' && $memberBzno === $requestBzno) {
		$title = trim((string)($room['target_company_name'] ?? ''));
		if ($title === '') $title = $targetBzno;
	} else {
		$title = trim((string)($room['request_company_name'] ?? ''));
		if ($title === '') $title = $requestBzno;
	}

	if ($title === '') {
		$title = '채팅방 #' . (int)($room['room_id'] ?? 0);
	}

	return $title;
}

// room list에 보여줄 마지막 메시지 또는 상태 문구 preview를 만든다.
function trade_chat_make_room_preview($room, $member = array()) {
	$channelType = trim((string)($room['stream_channel_type'] ?? 'messaging'));
	$channelId = trim((string)($room['stream_channel_id'] ?? ''));
	$memberStreamUserId = trade_chat_make_stream_user_id($member['mb_id'] ?? '');
	if ($channelId !== '' && trade_chat_is_configured()) {
		$latestPreview = trade_chat_stream_get_latest_message_preview($channelType, $channelId, $memberStreamUserId);
		if ($latestPreview !== '') {
			return $latestPreview;
		}
	}

	$status = trim((string)($room['room_status'] ?? ''));
	if ($status === 'active') return '대화를 이어갈 수 있습니다.';
	if ($status === 'pending') return '채팅 준비중입니다.';
	if ($status === 'closed') return '종료된 채팅방입니다.';
	if ($status === 'blocked') return '접근이 제한된 채팅방입니다.';
	return '채팅방 정보를 확인해 주세요.';
}

// 현재 회원이 접근 가능한 room 목록을 모달 sidebar용 구조로 정리한다.
function trade_chat_list_member_rooms($member) {
	$memberId = trim((string)($member['mb_id'] ?? ''));
	$memberBzno = trade_chat_member_company_bzno($member);
	if ($memberId === '') return array();

	$memberIdSql = sql_escape_string($memberId);
	$rooms = array();

	$result = sql_query("
		SELECT R.*
		FROM trade_chat_room R
		INNER JOIN trade_chat_room_member RM
			ON RM.room_id=R.room_id
		   AND RM.mb_id='{$memberIdSql}'
		   AND RM.is_active=1
		ORDER BY FIELD(room_status, 'active', 'pending', 'blocked', 'closed'),
		         updated_at DESC,
		         room_id DESC
	", false);

	if ($result) {
		for ($i = 0; $row = sql_fetch_array($result); $i++) {
			$title = trade_chat_make_room_title($row, $memberBzno);
			$requestCompanyName = trim((string)($row['request_company_name'] ?? ''));
			$targetCompanyName = trim((string)($row['target_company_name'] ?? ''));
			$myCompanyName = $memberBzno === trade_chat_normalize_bzno($row['request_bzno'] ?? '')
				? $requestCompanyName
				: $targetCompanyName;
			$counterpartyCompanyName = $memberBzno === trade_chat_normalize_bzno($row['request_bzno'] ?? '')
				? $targetCompanyName
				: $requestCompanyName;
			$rooms[] = array(
				'roomId' => (int)$row['room_id'],
				'roomStatus' => trim((string)($row['room_status'] ?? '')),
				'title' => $title,
				'myCompanyName' => $myCompanyName !== '' ? $myCompanyName : '우리 회사',
				'counterpartyCompanyName' => $counterpartyCompanyName !== '' ? $counterpartyCompanyName : $title,
				'initial' => trade_chat_make_room_initial($title),
				'preview' => trade_chat_make_room_preview($row, $member),
				'updatedAt' => trim((string)($row['updated_at'] ?? '')),
				'channelType' => trim((string)($row['stream_channel_type'] ?? 'messaging')),
				'channelId' => trim((string)($row['stream_channel_id'] ?? '')),
				'channelCid' => trim((string)($row['stream_channel_cid'] ?? '')),
				'companyPairKey' => trim((string)($row['company_pair_key'] ?? '')),
				'latestTrNo' => (int)($row['latest_tr_no'] ?? 0),
			);
		}
	}

	return $rooms;
}

// 현재 회원의 Stream user token을 발급해 채팅 연결 bootstrap에 사용한다.
function trade_chat_issue_member_token($member) {
	$memberId = trim((string)($member['mb_id'] ?? ''));
	if ($memberId === '') {
		return array(
			'success' => false,
			'message' => '회원 ID가 없습니다.',
		);
	}

	$upsert = trade_chat_stream_upsert_user($memberId, array(
		'name' => trim((string)($member['mb_id'] ?? $memberId)),
		'company_bzno' => trade_chat_member_company_bzno($member),
		'company_kedcd' => trim((string)($member['mb_company'] ?? '')),
	));

	if (empty($upsert['success'])) {
		return array(
			'success' => false,
			'message' => $upsert['message'] ?? 'Stream user upsert에 실패했습니다.',
		);
	}

	return trade_chat_stream_create_token(trade_chat_make_stream_user_id($memberId));
}

// 모달 최초 진입 시 필요한 room list와 사용자 연결 정보를 한 번에 만든다.
function trade_chat_build_modal_bootstrap($member, $initialRoomId = 0) {
	$rooms = trade_chat_list_member_rooms($member);
	$initialRoomId = (int)$initialRoomId;
	$selectedRoomId = 0;

	foreach ($rooms as $room) {
		if ($initialRoomId > 0 && $room['roomId'] === $initialRoomId) {
			$selectedRoomId = $room['roomId'];
			break;
		}
	}

	if ($selectedRoomId < 1 && !empty($rooms[0]['roomId'])) {
		$selectedRoomId = (int)$rooms[0]['roomId'];
	}

	$settings = trade_chat_settings();
	$tokenResult = array(
		'success' => false,
		'token' => '',
		'expires_in' => 0,
	);

	if (!empty($rooms) && trade_chat_is_configured()) {
		$tokenResult = trade_chat_issue_member_token($member);
	}

	return array(
		'success' => true,
		'login' => !empty($member['mb_id']),
		'configured' => trade_chat_is_configured(),
		'apiKey' => (string)($settings['api_key'] ?? ''),
		'user' => array(
			'id' => trade_chat_make_stream_user_id($member['mb_id'] ?? ''),
			'name' => trim((string)($member['mb_id'] ?? '')),
		),
		'token' => (string)($tokenResult['token'] ?? ''),
		'tokenExpiresIn' => (int)($tokenResult['expires_in'] ?? 0),
		'rooms' => $rooms,
		'initialRoomId' => $selectedRoomId,
	);
}

// room payload용 token 발급 래퍼를 남겨 room별 재진입 시 같은 경로를 재사용한다.
function trade_chat_issue_user_token($room, $member) {
	$access = trade_chat_assert_member_access($room, $member);
	if (empty($access['success'])) {
		return $access;
	}

	return trade_chat_stream_create_token($access['stream_user_id']);
}

// 선택한 room 하나를 실제 채팅 연결에 사용할 payload로 구성한다.
function trade_chat_build_modal_room_payload($member, $roomId) {
	$roomId = (int)$roomId;
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

	$settings = trade_chat_settings();
	$response = array(
		'success' => true,
		'configured' => trade_chat_is_configured(),
		'roomId' => (int)$room['room_id'],
		'roomStatus' => trim((string)($room['room_status'] ?? '')),
		'channelType' => trim((string)($room['stream_channel_type'] ?? 'messaging')),
		'channelId' => trim((string)($room['stream_channel_id'] ?? '')),
		'channelCid' => trim((string)($room['stream_channel_cid'] ?? '')),
		'apiKey' => (string)($settings['api_key'] ?? ''),
		'user' => array(
			'id' => $access['stream_user_id'],
			'name' => trim((string)($member['mb_id'] ?? $access['stream_user_id'])),
		),
		'token' => '',
		'tokenExpiresIn' => 0,
	);

	if ($response['configured'] && $response['roomStatus'] === 'active') {
		$tokenResult = trade_chat_issue_user_token($room, $member);
		if (!empty($tokenResult['success']) && !empty($tokenResult['token'])) {
			$response['token'] = (string)$tokenResult['token'];
			$response['tokenExpiresIn'] = (int)($tokenResult['expires_in'] ?? 0);
		} else {
			$response['success'] = false;
			$response['message'] = $tokenResult['message'] ?? '채팅 접속 토큰을 발급할 수 없습니다.';
		}
	}

	return $response;
}
