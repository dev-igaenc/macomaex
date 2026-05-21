<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('membership_refund_slack_env')) {
	function membership_refund_slack_env($key, $default = '') {
		$key = trim((string)$key);
		if ($key === '') return (string)$default;
		if (function_exists('macomaex_env')) {
			return trim((string)macomaex_env($key, $default));
		}
		$value = getenv($key);
		if ($value === false || $value === null) return (string)$default;
		return trim((string)$value);
	}
}

if (!function_exists('membership_refund_slack_webhook')) {
	function membership_refund_slack_webhook() {
		$webhook = membership_refund_slack_env('REFUND_REQUEST_SLACK_WEBHOOK', '');
		if ($webhook !== '') return $webhook;
		if (defined('_refund_request_slack_webhook')) {
			$webhook = trim((string)constant('_refund_request_slack_webhook'));
			if ($webhook !== '') return $webhook;
		}

		$webhook = membership_refund_slack_env('TM_OVERDUE_SLACK_WEBHOOK', '');
		if ($webhook !== '') return $webhook;
		if (defined('_tm_overdue_slack_webhook')) {
			$webhook = trim((string)constant('_tm_overdue_slack_webhook'));
			if ($webhook !== '') return $webhook;
		}

		return '';
	}
}

if (!function_exists('membership_refund_slack_notify')) {
	function membership_refund_slack_notify($payload) {
		if (!is_array($payload)) return false;
		$webhook = membership_refund_slack_webhook();
		if ($webhook === '') return false;
		if (!function_exists('curl_init')) return false;

		$order_no = trim((string)(isset($payload['order_no']) ? $payload['order_no'] : ''));
		$uid = trim((string)(isset($payload['uid']) ? $payload['uid'] : ''));
		$company = trim((string)(isset($payload['company']) ? $payload['company'] : ''));
		$plan = trim((string)(isset($payload['plan']) ? $payload['plan'] : ''));
		$credit = (int)(isset($payload['credit']) ? $payload['credit'] : 0);
		$amount = (int)(isset($payload['amount']) ? $payload['amount'] : 0);
		$requested_at = trim((string)(isset($payload['requested_at']) ? $payload['requested_at'] : ''));

		$text = "[환불요청 접수]\n"
			. "- 요청시각: " . ($requested_at !== '' ? $requested_at : date('Y-m-d H:i:s')) . "\n"
			. "- 사용자ID: " . ($uid !== '' ? $uid : '-') . "\n"
			. "- 회사명: " . ($company !== '' ? $company : '-') . "\n"
			. "- 주문번호: " . ($order_no !== '' ? $order_no : '-') . "\n"
			. "- 플랜: " . ($plan !== '' ? $plan : '-') . "\n"
			. "- 결제금액: " . ($amount > 0 ? number_format($amount) . "원" : '-') . "\n"
			. "- 충전크레딧: " . ($credit > 0 ? number_format($credit) . " 크레딧" : '-');

		$json_flags = 0;
		if (defined('JSON_UNESCAPED_UNICODE')) $json_flags |= JSON_UNESCAPED_UNICODE;
		if (defined('JSON_UNESCAPED_SLASHES')) $json_flags |= JSON_UNESCAPED_SLASHES;
		$body = json_encode(array('text' => $text), $json_flags);
		if ($body === false) return false;

		$ch = curl_init($webhook);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 6);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		$response = curl_exec($ch);
		$http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_error($ch);
		curl_close($ch);

		return !($response === false || $http_code < 200 || $http_code >= 300);
	}
}
