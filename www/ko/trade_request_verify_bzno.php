<?php
$tradeRequestVerifyResponded = false;
$tradeRequestVerifyRequestId = date('YmdHis') . '-' . substr(md5(uniqid('', true)), 0, 8);

ob_start();

function trade_request_verify_endpoint_log($stage, $context = array()) {
	global $tradeRequestVerifyRequestId;

	$payload = array_merge(array(
		'request_id' => $tradeRequestVerifyRequestId,
		'stage' => trim((string)$stage),
		'uri' => trim((string)($_SERVER['REQUEST_URI'] ?? '')),
		'method' => trim((string)($_SERVER['REQUEST_METHOD'] ?? '')),
		'ip' => trim((string)($_SERVER['REMOTE_ADDR'] ?? '')),
	), is_array($context) ? $context : array());

	error_log('[trade_request_verify_endpoint] ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function trade_request_verify_endpoint_response($success, $message, $extra = array()) {
	global $tradeRequestVerifyResponded, $tradeRequestVerifyRequestId;

	$tradeRequestVerifyResponded = true;
	while (ob_get_level() > 0) {
		ob_end_clean();
	}

	if (!headers_sent()) {
		http_response_code(200);
		header('Content-Type: application/json; charset=utf-8');
		header('X-Content-Type-Options: nosniff');
		header('Cache-Control: no-store');
		header('X-Request-ID: ' . $tradeRequestVerifyRequestId);
	}

	$payload = array_merge(array(
		'success' => (bool)$success,
		'message' => (string)$message,
		'request_id' => $tradeRequestVerifyRequestId,
	), is_array($extra) ? $extra : array());
	$json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	if ($json === false) {
		$json = '{"success":false,"message":"사업자번호 확인 중 오류가 발생했습니다.","request_id":"' . $tradeRequestVerifyRequestId . '"}';
	}

	echo $json;
	exit;
}

register_shutdown_function(function () {
	global $tradeRequestVerifyResponded;

	if ($tradeRequestVerifyResponded) return;

	$error = error_get_last();
	$fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
	if (is_array($error) && in_array((int)($error['type'] ?? 0), $fatalTypes, true)) {
		trade_request_verify_endpoint_log('fatal_error', array(
			'error_type' => (int)($error['type'] ?? 0),
			'error_message' => trim((string)($error['message'] ?? '')),
			'error_file' => trim((string)($error['file'] ?? '')),
			'error_line' => (int)($error['line'] ?? 0),
		));
	} else {
		trade_request_verify_endpoint_log('unexpected_shutdown', array(
			'last_error_type' => (int)($error['type'] ?? 0),
			'last_error_message' => trim((string)($error['message'] ?? '')),
		));
	}

	trade_request_verify_endpoint_response(false, '사업자번호 확인 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.');
});

try {
	include_once(__DIR__ . '/_common.php');
	include_once(__DIR__ . '/helper/trade_request_helper.php');

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		trade_request_verify_endpoint_response(false, '잘못된 요청입니다.');
	}

	$action = trim((string)($_POST['action'] ?? ''));
	if (empty($member['mb_id'])) {
		trade_request_verify_endpoint_response(false, '로그인 후 이용해 주세요.', array('login_required' => true));
	}

	if ($action === 'client_error') {
		trade_request_verify_endpoint_log('client_error', array(
			'client_request_id' => trim((string)($_POST['client_request_id'] ?? '')),
			'source_page' => trim((string)($_POST['source_page'] ?? '')),
			'error_type' => trim((string)($_POST['error_type'] ?? '')),
			'http_status' => (int)($_POST['http_status'] ?? 0),
			'content_type' => substr(trim((string)($_POST['content_type'] ?? '')), 0, 120),
			'error_message' => substr(trim((string)($_POST['error_message'] ?? '')), 0, 300),
			'response_preview' => substr(trim((string)($_POST['response_preview'] ?? '')), 0, 500),
		));
		trade_request_verify_endpoint_response(true, '오류 로그를 기록했습니다.');
	}

	if ($action !== 'trade_request_verify_bzno') {
		trade_request_verify_endpoint_response(false, '잘못된 요청입니다.');
	}

	$bzno = trade_request_normalize_bzno($_POST['bzno'] ?? '');
	if (!preg_match('/^[0-9]{10}$/', $bzno)) {
		trade_request_verify_endpoint_response(false, '사업자등록번호 10자리를 입력해 주세요.');
	}

	$GLOBALS['trade_request_bzno_resolve_request_id'] = trim((string)($_POST['client_request_id'] ?? ''));
	if ($GLOBALS['trade_request_bzno_resolve_request_id'] === '') {
		$GLOBALS['trade_request_bzno_resolve_request_id'] = $tradeRequestVerifyRequestId;
	}
	$resolved = trade_request_resolve_company_data_by_bzno($bzno, array('persist' => false));
	if (empty($resolved['success'])) {
		trade_request_verify_endpoint_response(false, $resolved['message'] ?? '사업자번호 확인에 실패했습니다.');
	}

	trade_request_verify_endpoint_response(true, '기업 정보를 확인했습니다.', array(
		'company' => $resolved['company'],
		'bzno' => licenseNum($resolved['bzno']),
	));
} catch (Throwable $e) {
	trade_request_verify_endpoint_log('throwable', array(
		'exception_class' => get_class($e),
		'error_message' => $e->getMessage(),
		'error_file' => $e->getFile(),
		'error_line' => $e->getLine(),
	));
	trade_request_verify_endpoint_response(false, '사업자번호 확인 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.');
}
