<?php
include_once(__DIR__ . '/_common.php');
include_once(__DIR__ . '/helper/trade_chat_helper.php');

header('Content-Type: application/json; charset=utf-8');

function trade_chat_webhook_response($success, $message, $statusCode = 200, $extra = array()) {
	http_response_code((int)$statusCode);
	$payload = array_merge(array(
		'success' => (bool)$success,
		'message' => (string)$message,
	), is_array($extra) ? $extra : array());
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

function trade_chat_webhook_attachment_url($attachment) {
	if (!is_array($attachment)) return '';
	$candidates = array(
		$attachment['asset_url'] ?? '',
		$attachment['image_url'] ?? '',
		$attachment['thumb_url'] ?? '',
		$attachment['url'] ?? '',
	);
	foreach ($candidates as $candidate) {
		$url = trim((string)$candidate);
		if ($url !== '') return $url;
	}
	return '';
}

function trade_chat_webhook_safe_http_url($url) {
	$url = trim((string)$url);
	if ($url === '') return false;
	$parts = @parse_url($url);
	if (!is_array($parts)) return false;
	$scheme = strtolower((string)($parts['scheme'] ?? ''));
	if ($scheme !== 'http' && $scheme !== 'https') return false;
	return true;
}

function trade_chat_webhook_download_file($url, $maxBytes = 110000000) {
	if (!trade_chat_webhook_safe_http_url($url)) {
		return array('success' => false, 'message' => '허용되지 않는 첨부 URL입니다.');
	}

	$tmpFile = @tempnam(sys_get_temp_dir(), 'trade-chat-scan-');
	if (!is_string($tmpFile) || $tmpFile === '') {
		return array('success' => false, 'message' => '임시 파일을 생성할 수 없습니다.');
	}

	$fp = @fopen($tmpFile, 'wb');
	if (!$fp) {
		@unlink($tmpFile);
		return array('success' => false, 'message' => '임시 파일 핸들을 열 수 없습니다.');
	}

	$ch = @curl_init($url);
	if (!$ch) {
		@fclose($fp);
		@unlink($tmpFile);
		return array('success' => false, 'message' => '첨부 다운로드를 초기화할 수 없습니다.');
	}

	$downloaded = 0;
	@curl_setopt_array($ch, array(
		CURLOPT_FILE => $fp,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 3,
		CURLOPT_TIMEOUT => 25,
		CURLOPT_CONNECTTIMEOUT => 8,
		CURLOPT_NOPROGRESS => false,
		CURLOPT_PROGRESSFUNCTION => function ($resource, $downloadTotal, $downloadNow) use (&$downloaded, $maxBytes) {
			$downloaded = (int)$downloadNow;
			if ($downloadNow > $maxBytes) {
				return 1;
			}
			return 0;
		},
	));

	$ok = @curl_exec($ch);
	$httpCode = (int)@curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$curlError = trim((string)@curl_error($ch));
	@curl_close($ch);
	@fclose($fp);

	if ($ok === false || $httpCode < 200 || $httpCode >= 300) {
		@unlink($tmpFile);
		return array(
			'success' => false,
			'message' => '첨부 다운로드에 실패했습니다.',
			'http_code' => $httpCode,
			'detail' => $curlError,
		);
	}

	$fileSize = @filesize($tmpFile);
	$fileSize = is_int($fileSize) ? $fileSize : 0;
	if ($fileSize < 1) {
		@unlink($tmpFile);
		return array('success' => false, 'message' => '다운로드된 파일이 비어 있습니다.');
	}
	if ($fileSize > $maxBytes) {
		@unlink($tmpFile);
		return array('success' => false, 'message' => '스캔 가능한 파일 크기를 초과했습니다.');
	}

	return array(
		'success' => true,
		'path' => $tmpFile,
		'size' => $fileSize,
	);
}

function trade_chat_webhook_scan_file($filePath) {
	$filePath = trim((string)$filePath);
	if ($filePath === '' || !is_file($filePath)) {
		return array('status' => 'error', 'message' => '스캔 파일 경로가 올바르지 않습니다.');
	}

	$scanner = '';
	$scannerType = '';
	$clamdscan = trim((string)@shell_exec('command -v clamdscan 2>/dev/null'));
	if ($clamdscan !== '') {
		$scanner = $clamdscan . ' --no-summary --fdpass ';
		$scannerType = 'clamdscan';
	} else {
		$clamscan = trim((string)@shell_exec('command -v clamscan 2>/dev/null'));
		if ($clamscan !== '') {
			$scanner = $clamscan . ' --no-summary ';
			$scannerType = 'clamscan';
		}
	}

	if ($scanner === '') {
		return array(
			'status' => 'unavailable',
			'message' => 'ClamAV 스캐너가 설치되어 있지 않습니다.',
		);
	}

	$output = array();
	$returnCode = 2;
	@exec($scanner . escapeshellarg($filePath) . ' 2>&1', $output, $returnCode);
	$outputText = trim((string)implode("\n", $output));

	if ($returnCode === 0) {
		return array(
			'status' => 'clean',
			'scanner' => $scannerType,
			'message' => '악성코드 미검출',
			'raw_output' => $outputText,
		);
	}
	if ($returnCode === 1 || stripos($outputText, 'FOUND') !== false) {
		return array(
			'status' => 'infected',
			'scanner' => $scannerType,
			'message' => '악성코드 의심 파일이 탐지되었습니다.',
			'raw_output' => $outputText,
		);
	}

	return array(
		'status' => 'error',
		'scanner' => $scannerType,
		'message' => '악성코드 스캔 실행에 실패했습니다.',
		'raw_output' => $outputText,
		'return_code' => $returnCode,
	);
}

function trade_chat_webhook_event_channel_type($eventData) {
	if (!is_array($eventData)) return 'messaging';
	$channelType = trim((string)($eventData['channel_type'] ?? $eventData['channelType'] ?? ''));
	if ($channelType === '') {
		$cid = trim((string)($eventData['cid'] ?? ''));
		if (strpos($cid, ':') !== false) {
			$channelType = trim((string)substr($cid, 0, strpos($cid, ':')));
		}
	}
	return trade_chat_normalize_channel_type($channelType);
}

function trade_chat_webhook_event_channel_id($eventData) {
	if (!is_array($eventData)) return '';
	$channelId = trim((string)($eventData['channel_id'] ?? $eventData['channelID'] ?? ''));
	if ($channelId === '') {
		$cid = trim((string)($eventData['cid'] ?? ''));
		if (strpos($cid, ':') !== false) {
			$channelId = trim((string)substr($cid, strpos($cid, ':') + 1));
		}
	}
	return $channelId;
}

function trade_chat_webhook_find_room_id($channelType, $channelId) {
	$channelType = trade_chat_normalize_channel_type($channelType);
	$channelId = trim((string)$channelId);
	if ($channelType === '' || $channelId === '') return 0;

	$row = sql_fetch("
		SELECT room_id
		FROM trade_chat_room
		WHERE stream_channel_type='" . sql_escape_string($channelType) . "'
		  AND stream_channel_id='" . sql_escape_string($channelId) . "'
		LIMIT 1
	");
	return isset($row['room_id']) ? (int)$row['room_id'] : 0;
}

function trade_chat_webhook_actor_user_id($eventData, $message) {
	$candidate = trim((string)($message['user']['id'] ?? ''));
	if ($candidate !== '') return $candidate;
	$candidate = trim((string)($eventData['user']['id'] ?? ''));
	if ($candidate !== '') return $candidate;
	return trade_chat_make_stream_user_id('security_bot');
}

function trade_chat_webhook_request_meta_payload() {
	$forwardedFor = trim((string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
	$clientIp = '';
	if ($forwardedFor !== '') {
		$parts = explode(',', $forwardedFor);
		$clientIp = trim((string)($parts[0] ?? ''));
	}
	if ($clientIp === '') {
		$clientIp = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
	}

	return array(
		'client_ip' => $clientIp,
		'user_agent' => trim((string)($_SERVER['HTTP_USER_AGENT'] ?? '')),
		'request_id' => trim((string)($_SERVER['HTTP_X_REQUEST_ID'] ?? $_SERVER['HTTP_X_AMZN_TRACE_ID'] ?? '')),
	);
}

if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
	trade_chat_webhook_response(false, '허용되지 않은 요청 방식입니다.', 405);
}

$requestMeta = trade_chat_webhook_request_meta_payload();

if (!function_exists('trade_chat_stream_require_sdk') || !trade_chat_stream_require_sdk()) {
	trade_chat_write_event_log('file_scan_webhook', 'fail', array_merge($requestMeta, array(
		'reason' => 'sdk_unavailable',
	)), 'GetStream SDK를 불러올 수 없습니다.');
	trade_chat_webhook_response(false, 'SDK를 불러올 수 없습니다.', 500);
}

$rawBody = file_get_contents('php://input');
if (!is_string($rawBody) || trim($rawBody) === '') {
	trade_chat_write_event_log('file_scan_webhook', 'fail', array_merge($requestMeta, array(
		'reason' => 'empty_body',
	)), '웹훅 본문이 비어 있습니다.');
	trade_chat_webhook_response(false, '웹훅 본문이 비어 있습니다.', 400);
}

$signature = trim((string)($_SERVER['HTTP_X_SIGNATURE'] ?? ''));
$webhookSecret = trim((string)trade_chat_config('GETSTREAM_WEBHOOK_SECRET', ''));
if ($webhookSecret === '' || $signature === '') {
	trade_chat_write_event_log('file_scan_webhook', 'fail', array_merge($requestMeta, array(
		'reason' => 'missing_signature_or_secret',
	)), '웹훅 시크릿 또는 서명이 없습니다.');
	trade_chat_webhook_response(false, '웹훅 인증에 실패했습니다.', 401);
}

$verified = \GetStream\Generated\Webhook::verifySignature($rawBody, $signature, $webhookSecret);
if (!$verified) {
	trade_chat_write_event_log('file_scan_webhook', 'fail', array_merge($requestMeta, array(
		'reason' => 'invalid_signature',
	)), '웹훅 서명 검증에 실패했습니다.');
	trade_chat_webhook_response(false, '웹훅 서명 검증에 실패했습니다.', 401);
}

$eventType = \GetStream\Generated\Webhook::getEventType($rawBody);
$eventData = json_decode($rawBody, true);
if (!is_array($eventData)) {
	trade_chat_write_event_log('file_scan_webhook', 'fail', array_merge($requestMeta, array(
		'reason' => 'invalid_json',
		'event_type' => (string)$eventType,
	)), '웹훅 JSON 파싱에 실패했습니다.');
	trade_chat_webhook_response(false, '웹훅 데이터가 올바르지 않습니다.', 400);
}

$webhookEventId = trim((string)($eventData['id'] ?? $eventData['event_id'] ?? ''));
$eventMeta = array_merge($requestMeta, array(
	'event_type' => (string)$eventType,
	'webhook_event_id' => $webhookEventId,
));

if ($eventType !== 'message.new') {
	trade_chat_write_event_log('file_scan_webhook', 'skip', array_merge($eventMeta, array(
		'reason' => 'unsupported_event',
	)));
	trade_chat_webhook_response(true, '처리 대상 이벤트가 아닙니다.', 200);
}

$message = isset($eventData['message']) && is_array($eventData['message']) ? $eventData['message'] : array();
$attachments = isset($message['attachments']) && is_array($message['attachments']) ? $message['attachments'] : array();
$attachmentCount = count($attachments);
$channelType = trade_chat_webhook_event_channel_type($eventData);
$channelId = trade_chat_webhook_event_channel_id($eventData);
$roomId = trade_chat_webhook_find_room_id($channelType, $channelId);
$messageId = trim((string)($message['id'] ?? $eventData['message_id'] ?? ''));
if ($attachmentCount < 1) {
	trade_chat_write_event_log('file_scan_webhook', 'skip', array_merge($eventMeta, array(
		'reason' => 'no_attachments',
		'room_id' => $roomId,
		'message_id' => $messageId,
		'stream_channel_id' => $channelId,
		'stream_channel_cid' => trim((string)($eventData['cid'] ?? '')),
	)));
	trade_chat_webhook_response(true, '첨부파일 없는 메시지입니다.', 200);
}

trade_chat_write_event_log('file_scan_webhook', 'success', array_merge($eventMeta, array(
	'room_id' => $roomId,
	'message_id' => $messageId,
	'stream_channel_id' => $channelId,
	'stream_channel_cid' => trim((string)($eventData['cid'] ?? '')),
	'attachment_count' => $attachmentCount,
)));

$scanSummary = array(
	'total' => 0,
	'clean' => 0,
	'infected' => 0,
	'unavailable' => 0,
	'error' => 0,
);

foreach ($attachments as $index => $attachment) {
	$fileUrl = trade_chat_webhook_attachment_url($attachment);
	if ($fileUrl === '') {
		$scanSummary['error']++;
		trade_chat_write_event_log('file_scan_result', 'fail', array_merge($eventMeta, array(
			'reason' => 'missing_attachment_url',
			'room_id' => $roomId,
			'message_id' => $messageId,
			'stream_channel_id' => $channelId,
			'stream_channel_cid' => trim((string)($eventData['cid'] ?? '')),
			'attachment_index' => (int)$index,
		)), '첨부 URL이 비어 있습니다.');
		continue;
	}

	$scanSummary['total']++;
	$download = trade_chat_webhook_download_file($fileUrl);
	if (empty($download['success'])) {
		$scanSummary['error']++;
		trade_chat_write_event_log('file_scan_result', 'fail', array_merge($eventMeta, array(
			'reason' => 'download_failed',
			'room_id' => $roomId,
			'message_id' => $messageId,
			'stream_channel_id' => $channelId,
			'stream_channel_cid' => trim((string)($eventData['cid'] ?? '')),
			'attachment_index' => (int)$index,
			'attachment_url' => $fileUrl,
			'http_code' => (int)($download['http_code'] ?? 0),
		)), (string)($download['message'] ?? '첨부 다운로드 실패'));
		continue;
	}

	$filePath = (string)($download['path'] ?? '');
	$scanResult = trade_chat_webhook_scan_file($filePath);
	@unlink($filePath);

	$scanStatus = trim((string)($scanResult['status'] ?? 'error'));
	if (!isset($scanSummary[$scanStatus])) {
		$scanStatus = 'error';
	}
	$scanSummary[$scanStatus]++;

	$logStatus = ($scanStatus === 'clean') ? 'success' : (($scanStatus === 'unavailable') ? 'skip' : 'fail');
	trade_chat_write_event_log('file_scan_result', $logStatus, array_merge($eventMeta, array(
		'reason' => 'scan_' . $scanStatus,
		'room_id' => $roomId,
		'message_id' => $messageId,
		'stream_channel_id' => $channelId,
		'stream_channel_cid' => trim((string)($eventData['cid'] ?? '')),
		'attachment_index' => (int)$index,
		'attachment_url' => $fileUrl,
		'attachment_type' => trim((string)($attachment['type'] ?? '')),
		'attachment_title' => trim((string)($attachment['title'] ?? '')),
		'scan_status' => $scanStatus,
		'scanner' => trim((string)($scanResult['scanner'] ?? '')),
		'file_size' => (int)($download['size'] ?? 0),
	)), (string)($scanResult['message'] ?? ''));
}

if ($scanSummary['infected'] > 0 && $messageId !== '' && $channelId !== '') {
	$streamClient = trade_chat_stream_client();
	$actorUserId = trade_chat_webhook_actor_user_id($eventData, $message);
	$deleteOk = false;
	$deleteError = '';
	$deleteStatus = 'not_started';
	$noticeStatus = 'not_started';
	$actionErrorCode = '';
	$scanActionPayload = array(
		'room_id' => $roomId,
		'message_id' => $messageId,
		'stream_channel_id' => $channelId,
		'stream_channel_cid' => trim((string)($eventData['cid'] ?? '')),
		'infected_count' => (int)$scanSummary['infected'],
		'scan_total' => (int)$scanSummary['total'],
		'scan_clean' => (int)$scanSummary['clean'],
		'scan_infected' => (int)$scanSummary['infected'],
		'scan_unavailable' => (int)$scanSummary['unavailable'],
		'scan_error' => (int)$scanSummary['error'],
		'actor_user_id' => $actorUserId,
	);

	if ($streamClient) {
		try {
			$deleteResponse = $streamClient->deleteMessage($messageId, false, $actorUserId, false);
			$deleteOk = $deleteResponse->isSuccessful();
			if (!$deleteOk) {
				$deleteError = 'Stream 메시지 삭제 응답이 성공이 아닙니다.';
				$deleteStatus = 'api_failed';
				$actionErrorCode = 'delete_api_failed';
			} else {
				$deleteStatus = 'success';
			}
		} catch (\Throwable $e) {
			$deleteError = $e->getMessage();
			$deleteStatus = 'exception';
			$actionErrorCode = 'delete_exception';
		}

		if ($deleteOk) {
			try {
				$noticeText = '보안 정책에 따라 악성코드 의심 첨부파일 메시지가 차단되었습니다.';
				$streamClient->sendMessage(
					$channelType,
					$channelId,
					new \GetStream\GeneratedModels\SendMessageRequest(
						message: new \GetStream\GeneratedModels\MessageRequest(
							text: $noticeText,
							type: 'system',
							silent: true,
							userID: $actorUserId,
						),
					),
				);
				$noticeStatus = 'success';
			} catch (\Throwable $e) {
				$noticeStatus = 'failed';
				$actionErrorCode = 'notice_send_failed';
				trade_chat_write_event_log('file_scan_action', 'fail', array_merge($eventMeta, $scanActionPayload, array(
					'reason' => 'action_notice_send_failed',
					'action_status' => 'message_deleted_notice_failed',
					'delete_status' => $deleteStatus,
					'notice_status' => $noticeStatus,
					'action_error_code' => $actionErrorCode,
				)), '삭제 후 안내 메시지 전송 실패: ' . $e->getMessage());
			}
		} else {
			$noticeStatus = 'skipped';
		}
	} else {
		$deleteError = 'Stream client 초기화 실패';
		$deleteStatus = 'client_init_failed';
		$noticeStatus = 'skipped';
		$actionErrorCode = 'stream_client_init_failed';
	}

	trade_chat_write_event_log('file_scan_action', $deleteOk ? 'success' : 'fail', array_merge($eventMeta, $scanActionPayload, array(
		'reason' => $deleteOk ? 'action_infected_message_deleted' : 'action_delete_failed',
		'action_status' => $deleteOk ? 'message_deleted' : 'delete_failed',
		'delete_status' => $deleteStatus,
		'notice_status' => $noticeStatus,
		'action_error_code' => $actionErrorCode,
	)), $deleteOk ? '악성코드 의심 메시지를 삭제했습니다.' : ('메시지 삭제 실패: ' . $deleteError));
} elseif ($scanSummary['infected'] > 0) {
	trade_chat_write_event_log('file_scan_action', 'fail', array_merge($eventMeta, array(
		'reason' => 'action_target_missing',
		'room_id' => $roomId,
		'message_id' => $messageId,
		'stream_channel_id' => $channelId,
		'stream_channel_cid' => trim((string)($eventData['cid'] ?? '')),
		'infected_count' => (int)$scanSummary['infected'],
		'scan_total' => (int)$scanSummary['total'],
		'scan_clean' => (int)$scanSummary['clean'],
		'scan_infected' => (int)$scanSummary['infected'],
		'scan_unavailable' => (int)$scanSummary['unavailable'],
		'scan_error' => (int)$scanSummary['error'],
		'action_status' => 'delete_failed',
		'delete_status' => 'target_missing',
		'notice_status' => 'skipped',
		'action_error_code' => 'action_target_missing',
	)), '감염 파일이 탐지되었지만 message_id 또는 channel_id가 없어 후속 조치를 수행하지 못했습니다.');
} else {
	trade_chat_write_event_log('file_scan_action', 'skip', array_merge($eventMeta, array(
		'reason' => 'action_not_required',
		'room_id' => $roomId,
		'message_id' => $messageId,
		'stream_channel_id' => $channelId,
		'stream_channel_cid' => trim((string)($eventData['cid'] ?? '')),
		'infected_count' => 0,
		'scan_total' => (int)$scanSummary['total'],
		'scan_clean' => (int)$scanSummary['clean'],
		'scan_infected' => 0,
		'scan_unavailable' => (int)$scanSummary['unavailable'],
		'scan_error' => (int)$scanSummary['error'],
		'action_status' => 'no_action_needed',
		'delete_status' => 'skipped',
		'notice_status' => 'skipped',
		'action_error_code' => '',
	)), '감염 파일이 없어 후속 조치를 수행하지 않았습니다.');
}

trade_chat_webhook_response(true, '웹훅 수신 및 파일 스캔 처리 완료', 200, array(
	'scan_summary' => $scanSummary,
));
