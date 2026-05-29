<?php
if (!defined('_GNUBOARD_')) exit;

/*
 * my_matching.php 전용 서버 헬퍼.
 * 받은 요청 승인/거절 처리와 보낸 요청 상태 조회를 페이지 밖으로 분리해 관리한다.
 */

include_once(__DIR__ . '/trade_chat_helper.php');
include_once(__DIR__ . '/alimtalk_log_helper.php');

function my_matching_json_response($payload) {
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

function my_matching_text_response($text) {
	header('Content-Type: text/plain; charset=utf-8');
	echo (string)$text;
	exit;
}

function my_matching_csrf_session_key() {
	return 'ss_my_matching_csrf_token';
}

function my_matching_create_csrf_token() {
	$token = '';
	if (function_exists('random_bytes')) {
		try {
			$token = bin2hex(random_bytes(32));
		} catch (Throwable $e) {
			$token = '';
		}
	}

	if ($token === '') {
		if (function_exists('openssl_random_pseudo_bytes')) {
			$bytes = openssl_random_pseudo_bytes(32);
			if ($bytes !== false) {
				$token = bin2hex($bytes);
			}
		}
	} else {
		$token = '';
	}

	if ($token === '') {
		$token = md5(uniqid((string)mt_rand(), true));
	}

	set_session(my_matching_csrf_session_key(), $token);
	return $token;
}

function my_matching_verify_csrf_token($request) {
	$postedToken = isset($request['csrf_token']) ? trim((string)$request['csrf_token']) : '';
	$sessionToken = trim((string)get_session(my_matching_csrf_session_key()));

	if ($postedToken === '' || $sessionToken === '') {
		return false;
	}

	if (function_exists('hash_equals')) {
		return hash_equals($sessionToken, $postedToken);
	}

	return $sessionToken === $postedToken;
}

function my_matching_member_bzno($member) {
	return preg_replace('/[^0-9]/', '', (string)($member['mb_4'] ?? ''));
}

function my_matching_handle_trade_request_approval($member, $request) {
	include_once(__DIR__ . '/trade_chat_helper.php');

	if (empty($member['mb_id'])) {
		my_matching_text_response('501');
	}

	if (!my_matching_verify_csrf_token($request)) {
		my_matching_text_response('403');
	}

	$idx = isset($request['idx']) ? (int)$request['idx'] : 0;
	$typ = isset($request['typ']) ? trim((string)$request['typ']) : '';
	$memberBzno = my_matching_member_bzno($member);
	$nextStatus = '';

	if ($typ === 'accept') $nextStatus = '1';
	if ($typ === 'reject') $nextStatus = '2';

	if ($idx < 1 || $memberBzno === '' || $nextStatus === '') {
		my_matching_text_response('500');
	}

	$memberBznoSql = sql_escape_string($memberBzno);
	$targetRequest = sql_fetch("
		select tr_no, approval_status, request_mb_id
		from trade_request
		where tr_no='{$idx}'
		  and target_bzno='{$memberBznoSql}'
		limit 1
	");

	if (empty($targetRequest['tr_no'])) {
		my_matching_text_response('500');
	}

	if ($nextStatus === '1') {
		$updateSql = "
			update trade_request
			set approval_status='1',
				approved_at=now(),
				rejected_at=NULL
			where tr_no='{$idx}'
			  and target_bzno='{$memberBznoSql}'
		";
	} else {
		$updateSql = "
			update trade_request
			set approval_status='2',
				rejected_at=now(),
				approved_at=NULL
			where tr_no='{$idx}'
			  and target_bzno='{$memberBznoSql}'
		";
	}

	$updated = sql_query($updateSql, false);
	if (!$updated) {
		my_matching_text_response('500');
	}

	if ($nextStatus === '1' && (int)($targetRequest['approval_status'] ?? 0) !== 1) {
		$requestMemberId = trim((string)($targetRequest['request_mb_id'] ?? ''));
		$alimtalkLogPayload = array(
			'template_code' => 'accmatching',
			'tr_no' => $idx,
			'mb_id' => $member['mb_id'] ?? '',
			'reason' => '',
			'to' => '',
			'request_id' => '',
			'provider_status' => '',
			'error_code' => '',
		);
		$alimtalkLogStatus = 'skip';
		if ($requestMemberId !== '') {
			$requestMemberInfo = getUserInfo($requestMemberId);
			$requestHp = trim((string)($requestMemberInfo['mb_8'] ?? ''));
			if ($requestHp === '') {
				$alimtalkLogPayload['reason'] = 'requester_hp_empty';
			} elseif (!class_exists('NCP_SENS_AT')) {
				$alimtalkLogPayload['reason'] = 'alimtalk_class_missing';
			} else {
				$alimtalkLogPayload['to'] = $requestHp;
				$gw_msg = new NCP_SENS_AT();
				$gw_msg->templateCode = 'accmatching';
				$gw_msg->to = $requestHp;
				$sendRes = $gw_msg->send();
				$alimtalkLogStatus = 'fail';
				if (is_array($sendRes)) {
					$alimtalkLogPayload['request_id'] = trim((string)($sendRes['requestId'] ?? ''));
					$alimtalkLogPayload['provider_status'] = trim((string)($sendRes['statusName'] ?? $sendRes['requestStatusName'] ?? ''));
					$alimtalkLogPayload['error_code'] = trim((string)($sendRes['statusCode'] ?? $sendRes['errorCode'] ?? ''));
				}
				if (is_array($sendRes) && !empty($sendRes['requestId'])) {
					$alimtalkLogStatus = 'success';
				} else {
					$alimtalkLogPayload['reason'] = 'send_failed';
				}
			}
		} else {
			$alimtalkLogPayload['reason'] = 'request_member_id_empty';
		}
		alimtalk_write_event_log('accmatching', $alimtalkLogStatus, $alimtalkLogPayload);
	}

	if ($nextStatus === '1') {
		$chatRoomResult = trade_chat_open_or_attach_room($idx, $member);
		if (empty($chatRoomResult['success'])) {
			error_log('[trade_chat] room open failed: ' . json_encode(array(
				'tr_no' => $idx,
				'mb_id' => $member['mb_id'] ?? '',
				'message' => $chatRoomResult['message'] ?? '',
			), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		} else {
			$roomId = isset($chatRoomResult['room']['room_id']) ? (int)$chatRoomResult['room']['room_id'] : 0;
			if ($roomId > 0) {
				$streamSyncResult = trade_chat_sync_stream_room($roomId, $member);
				if (empty($streamSyncResult['success'])) {
					error_log('[trade_chat] stream sync failed: ' . json_encode(array(
						'tr_no' => $idx,
						'room_id' => $roomId,
						'mb_id' => $member['mb_id'] ?? '',
						'message' => $streamSyncResult['message'] ?? '',
					), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
				}
			}
		}
	}

	my_matching_text_response('1000|' . $nextStatus);
}

function my_matching_handle_sent_status($member) {
	if (empty($member['mb_id'])) {
		my_matching_json_response(array(
			'success' => false,
			'login' => false,
			'items' => array(),
		));
	}

	$memberBzno = my_matching_member_bzno($member);
	if ($memberBzno === '') {
		my_matching_json_response(array(
			'success' => false,
			'items' => array(),
		));
	}

	$memberBznoSql = sql_escape_string($memberBzno);
	$statusItems = array();
	$statusResult = sql_query("
		select tr_no, approval_status
		from trade_request
		where request_bzno='{$memberBznoSql}'
		order by tr_no desc
	", false);

	if ($statusResult) {
		for ($i = 0; $row = sql_fetch_array($statusResult); $i++) {
			$statusItems[] = array(
				'id' => (int)$row['tr_no'],
				'approval_status' => (int)$row['approval_status'],
			);
		}
	}

	my_matching_json_response(array(
		'success' => true,
		'items' => $statusItems,
	));
}

function my_matching_handle_trade_chat_retry($member, $request) {
	include_once(__DIR__ . '/trade_chat_helper.php');

	if (empty($member['mb_id'])) {
		my_matching_json_response(array(
			'success' => false,
			'login' => false,
			'message' => '로그인 후 이용해 주세요.',
		));
	}

	if (!my_matching_verify_csrf_token($request)) {
		my_matching_json_response(array(
			'success' => false,
			'message' => '요청 보안 토큰이 올바르지 않습니다.',
		));
	}

	$roomId = isset($request['room_id']) ? (int)$request['room_id'] : 0;
	if ($roomId < 1) {
		my_matching_json_response(array(
			'success' => false,
			'message' => '채팅방 정보가 올바르지 않습니다.',
		));
	}

	$result = trade_chat_retry_stream_room($roomId, $member);
	my_matching_json_response(array_merge(array(
		'success' => !empty($result['success']),
	), $result));
}
