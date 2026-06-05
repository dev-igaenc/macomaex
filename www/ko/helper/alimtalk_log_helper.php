<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('alimtalk_log_compact_message')) {
	function alimtalk_log_compact_message($message, $maxLength = 300) {
		$message = trim((string)$message);
		$message = preg_replace('/\s+/', ' ', $message);
		$maxLength = (int)$maxLength;
		if ($maxLength < 1 || $message === '') return $message;

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
}

if (!function_exists('alimtalk_log_mask_phone')) {
	function alimtalk_log_mask_phone($phone) {
		$digits = preg_replace('/[^0-9]/', '', (string)$phone);
		$len = strlen($digits);
		if ($len < 7) return '';
		if ($len <= 7) return substr($digits, 0, 3) . '***';
		return substr($digits, 0, 3) . str_repeat('*', max(1, $len - 7)) . substr($digits, -4);
	}
}

if (!function_exists('alimtalk_log_directory_path')) {
	function alimtalk_log_directory_path() {
		return dirname(dirname(__DIR__)) . '/logs/alimtalk';
	}
}

if (!function_exists('alimtalk_log_file_path')) {
	function alimtalk_log_file_path($dateYmd = '') {
		$dateYmd = preg_replace('/[^0-9]/', '', (string)$dateYmd);
		if ($dateYmd === '') $dateYmd = date('Ymd');
		return alimtalk_log_directory_path() . '/alimtalk_log_' . $dateYmd . '.php';
	}
}

if (!function_exists('alimtalk_log_default_message')) {
	function alimtalk_log_default_message($eventType, $status) {
		$eventType = trim((string)$eventType);
		$status = trim((string)$status);

		if ($status === 'success') {
			if ($eventType === 'signup2') return '회원가입 알림톡 발송 성공';
			if ($eventType === 'reqmatching2') return '거래요청 수신 알림톡 발송 성공';
			if ($eventType === 'accmatching2') return '거래수락 알림톡 발송 성공';
			return '알림톡 발송 성공';
		}
		if ($status === 'skip') {
			return '알림톡 발송 조건 미충족으로 건너뜀';
		}
		return '알림톡 발송 실패';
	}
}

if (!function_exists('alimtalk_write_event_log')) {
	function alimtalk_write_event_log($eventType, $status, $payload = array(), $message = '') {
		$eventType = trim((string)$eventType);
		$status = trim((string)$status);
		if ($eventType === '' || $status === '') return false;
		if (!in_array($status, array('success', 'fail', 'skip'), true)) $status = 'fail';
		if (!preg_match('/^[a-z0-9_.:-]{1,50}$/i', $eventType)) $eventType = 'invalid_event_type';

		$payload = is_array($payload) ? $payload : array('value' => $payload);
		$dir = alimtalk_log_directory_path();
		if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
			return false;
		}

		$filePath = alimtalk_log_file_path();
		$isNewFile = !file_exists($filePath);
		$loggedAt = date('Y-m-d H:i:s');
		$message = alimtalk_log_compact_message($message, 500);
		if ($message === '') {
			$message = alimtalk_log_default_message($eventType, $status);
		}

		$fields = array(
			'event_type' => $eventType,
			'event_status' => $status,
			'template_code' => trim((string)($payload['template_code'] ?? $eventType)),
			'tr_no' => (int)($payload['tr_no'] ?? 0),
			'mb_id' => trim((string)($payload['mb_id'] ?? '')),
			'to_masked' => alimtalk_log_mask_phone((string)($payload['to'] ?? '')),
			'request_id' => trim((string)($payload['request_id'] ?? '')),
			'message_id' => trim((string)($payload['message_id'] ?? '')),
			'provider_status' => trim((string)($payload['provider_status'] ?? '')),
			'error_code' => trim((string)($payload['error_code'] ?? '')),
			'reason' => trim((string)($payload['reason'] ?? '')),
			'message' => $message,
			'request_uri' => trim((string)($_SERVER['REQUEST_URI'] ?? '')),
			'client_ip' => trim((string)($_SERVER['REMOTE_ADDR'] ?? '')),
			'user_agent' => alimtalk_log_compact_message((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 300),
		);

		$allowZeroKeys = array('tr_no');
		$lines = array();
		if ($isNewFile) {
			$lines[] = "<?php exit; ?>";
			$lines[] = '';
		}
		$lines[] = '[' . $loggedAt . ']';
		foreach ($fields as $key => $value) {
			if ($value === '') continue;
			if ($value === 0 && !in_array($key, $allowZeroKeys, true)) continue;
			$lines[] = $key . ': ' . $value;
		}
		$lines[] = '---';
		$lines[] = '';

		$body = implode(PHP_EOL, $lines);
		return @file_put_contents($filePath, $body, FILE_APPEND | LOCK_EX) !== false;
	}
}
