<?php
if (!defined('_GNUBOARD_')) exit;

/*
 * company_detail.php 전용 서버 헬퍼.
 * 거래 요청서 저장, 입력 검증, 크레딧 차감, 응답/로그 처리를 페이지 밖으로 분리해 관리한다.
 */

include_once(__DIR__ . '/trade_request_helper.php');
include_once(__DIR__ . '/alimtalk_log_helper.php');

function company_detail_trade_request_json_response($success, $message, $extra = array()) {
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(array_merge(array(
		'success' => $success,
		'message' => $message,
	), $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

function trade_request_compact_log_message($message, $maxLength = 255) {
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

function trade_request_log_directory_path() {
	return dirname(dirname(__DIR__)) . '/logs/trade_request';
}

function trade_request_log_file_path($dateYmd = '') {
	$dateYmd = preg_replace('/[^0-9]/', '', (string)$dateYmd);
	if ($dateYmd === '') {
		$dateYmd = date('Ymd');
	}

	return trade_request_log_directory_path() . '/trade_request_log_' . $dateYmd . '.php';
}

function trade_request_log_context_label($eventType, $status, $payload = array()) {
	$eventType = trim((string)$eventType);
	$status = trim((string)$status);
	$errorCategory = trim((string)($payload['error_category'] ?? ''));

	if ($eventType === 'submit' && $status === 'success') {
		return '거래 요청서 전송 완료';
	}
	if ($eventType === 'submit' && $status === 'fail') {
		if ($errorCategory === 'credit') return '크레딧 부족/차감 실패';
		if ($errorCategory === 'validation') return '입력값 검증 실패';
		if ($errorCategory === 'resource') return '필수 데이터 조회 실패';
		if ($errorCategory === 'system') return '시스템 처리 실패';
		return '거래 요청서 전송 실패';
	}
	if ($eventType === 'receiver_mail') {
		return '거래 요청 알림 메일 처리';
	}

	return '거래 요청 처리';
}

function trade_request_log_default_message($eventType, $status) {
	if ($eventType === 'submit' && $status === 'success') return '거래 요청서가 정상 저장되었습니다.';
	if ($eventType === 'submit' && $status === 'fail') return '거래 요청서 저장에 실패했습니다.';
	if ($eventType === 'receiver_mail' && $status === 'success') return '알림 메일 전송 상태를 기록했습니다.';
	if ($eventType === 'receiver_mail' && $status === 'fail') return '알림 메일 전송에 실패했습니다.';
	return '';
}

function company_detail_trade_request_write_daily_file_log($eventType, $status, $payload = array(), $message = '') {
	$payload = is_array($payload) ? $payload : array();
	$dir = trade_request_log_directory_path();
	if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
		return false;
	}

	$filePath = trade_request_log_file_path();
	$isNewFile = !file_exists($filePath);
	$requestUri = trim((string)($_SERVER['REQUEST_URI'] ?? ''));
	$loggedAt = date('Y-m-d H:i:s');
	$message = trade_request_compact_log_message($message, 500);
	if ($message === '') {
		$message = trade_request_log_default_message($eventType, $status);
	}

	$commonFields = array(
		'event_type' => trim((string)$eventType),
		'event_status' => trim((string)$status),
		'request_id' => trim((string)($payload['request_id'] ?? '')),
		'tr_no' => (int)($payload['tr_no'] ?? 0),
		'mb_id' => trim((string)($payload['mb_id'] ?? '')),
		'message' => $message,
		'context' => trade_request_log_context_label($eventType, $status, $payload),
		'request_uri' => $requestUri,
		'client_ip' => trim((string)($payload['client_ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''))),
		'user_agent' => trade_request_compact_log_message((string)($payload['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '')), 300),
	);

	$eventFields = array();
	if ($eventType === 'submit') {
		$eventFields = array(
			'request_bzno' => trim((string)($payload['request_bzno'] ?? '')),
			'target_kedcd' => trim((string)($payload['target_kedcd'] ?? '')),
			'target_bzno' => trim((string)($payload['target_bzno'] ?? '')),
			'trade_type' => trim((string)($payload['trade_type'] ?? '')),
			'trade_direction' => trim((string)($payload['trade_direction'] ?? '')),
			'credit_amount' => (int)($payload['credit_amount'] ?? 0),
			'credit_after' => (int)($payload['credit_after'] ?? 0),
			'remain_credit' => (int)($payload['remain_credit'] ?? 0),
			'plan_required' => !empty($payload['plan_required']) ? 'Y' : 'N',
			'purpose_count' => (int)($payload['purpose_count'] ?? 0),
			'error_code' => trim((string)($payload['error_code'] ?? '')),
			'error_category' => trim((string)($payload['error_category'] ?? '')),
			'stage' => trim((string)($payload['stage'] ?? '')),
		);
	} elseif ($eventType === 'receiver_mail') {
		$eventFields = array(
			'target_kedcd' => trim((string)($payload['target_kedcd'] ?? '')),
			'trade_type' => trim((string)($payload['trade_type'] ?? '')),
			'trade_direction' => trim((string)($payload['trade_direction'] ?? '')),
			'mail_attempted' => !empty($payload['mail_attempted']) ? 'Y' : 'N',
			'mail_sent' => !empty($payload['mail_sent']) ? 'Y' : 'N',
			'mail_recipient_type' => trim((string)($payload['mail_recipient_type'] ?? '')),
			'mail_reason' => trim((string)($payload['mail_reason'] ?? '')),
		);
	} else {
		$eventFields = array(
			'error_code' => trim((string)($payload['error_code'] ?? '')),
			'error_category' => trim((string)($payload['error_category'] ?? '')),
			'stage' => trim((string)($payload['stage'] ?? '')),
		);
	}

	$fields = array_merge($commonFields, $eventFields);

	$lines = array();
	if ($isNewFile) {
		$lines[] = "<?php exit; ?>";
		$lines[] = '';
	}
	$lines[] = '[' . $loggedAt . ']';
	foreach ($fields as $key => $value) {
		if ($value === '' || $value === null) continue;
		if ($value === 0 && !in_array($key, array('tr_no', 'credit_amount', 'credit_after', 'remain_credit', 'purpose_count'), true)) continue;
		$lines[] = $key . ': ' . $value;
	}
	$lines[] = '---';
	$lines[] = '';

	return @file_put_contents($filePath, implode(PHP_EOL, $lines), FILE_APPEND | LOCK_EX) !== false;
}

function company_detail_trade_request_classify_failure($errorCode) {
	$errorCode = trim((string)$errorCode);
	$map = array(
		'member_not_logged_in' => 'auth',
		'requester_bzno_empty' => 'validation',
		'requester_company_unresolved' => 'resource',
		'requester_company_persist_failed' => 'resource',
		'manager_info_missing' => 'validation',
		'manager_phone_invalid' => 'validation',
		'member_contact_missing' => 'validation',
		'member_phone_invalid' => 'validation',
		'invalid_target_kedcd' => 'validation',
		'invalid_trade_direction' => 'validation',
		'trade_purpose_empty' => 'validation',
		'desired_item_empty' => 'validation',
		'desired_item_too_long' => 'validation',
		'message_too_long' => 'validation',
		'invalid_trade_type' => 'validation',
		'invalid_trade_purpose' => 'validation',
		'mapped_trade_purpose_empty' => 'validation',
		'trade_purpose_json_encode_failed' => 'system',
		'target_company_not_found' => 'resource',
		'invalid_request_member_info' => 'resource',
		'same_company_request' => 'validation',
		'credit_not_enough' => 'credit',
		'trade_request_insert_failed' => 'system',
		'credit_consume_failed' => 'credit',
		'credit_used_update_failed' => 'system',
		'member_profile_update_failed' => 'system',
	);

	return $map[$errorCode] ?? 'unknown';
}

// 운영 로그는 식별자 중심으로만 남기고 원문 데이터는 최소화한다.
function company_detail_trade_request_log($stage, $data = array()) {
	$payload = array(
		'stage' => $stage,
		'uri' => $_SERVER['REQUEST_URI'] ?? '',
		'method' => $_SERVER['REQUEST_METHOD'] ?? '',
		'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
		'data' => $data,
	);

	error_log('[trade_request] ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function company_detail_trade_request_write_event_log($eventType, $status, $payload = array(), $message = '') {
	$eventType = trim((string)$eventType);
	$status = trim((string)$status);
	if ($eventType === '') $eventType = 'unknown';
	if (!in_array($status, array('success', 'fail', 'skip'), true)) {
		$status = 'fail';
	}

	company_detail_trade_request_log('event_' . $eventType . '_' . $status, $payload);
	company_detail_trade_request_write_daily_file_log($eventType, $status, $payload, $message);
}

function company_detail_trade_request_fail_response($message, $errorCode, $payload = array(), $extra = array()) {
	$errorCode = trim((string)$errorCode);
	if ($errorCode === '') $errorCode = 'unknown_error';

	$basePayload = is_array($payload) ? $payload : array();
	$basePayload['error_code'] = $errorCode;
	$basePayload['error_category'] = company_detail_trade_request_classify_failure($errorCode);
	company_detail_trade_request_write_event_log('submit', 'fail', $basePayload, $message);

	company_detail_trade_request_json_response(false, $message, array_merge(array(
		'error_code' => $errorCode,
		'error_category' => $basePayload['error_category'],
	), is_array($extra) ? $extra : array()));
}

function company_detail_trade_request_success_response($message, $payload = array(), $extra = array()) {
	$basePayload = is_array($payload) ? $payload : array();
	company_detail_trade_request_write_event_log('submit', 'success', $basePayload, $message);
	company_detail_trade_request_json_response(true, $message, is_array($extra) ? $extra : array());
}

function company_detail_format_phone_hyphen($phoneDigits) {
	$digits = preg_replace('/[^0-9]/', '', (string)$phoneDigits);
	if (strlen($digits) === 11) {
		return preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $digits);
	}
	return $digits;
}

function company_detail_trade_request_consume_credit($memberId, $trNo, $targetKedcd, $tradeType, $creditAmount) {
	$memberId = trim((string)$memberId);
	$trNo = (int)$trNo;
	$targetKedcd = trim((string)$targetKedcd);
	$tradeType = strtoupper(trim((string)$tradeType));
	$creditAmount = (int)$creditAmount;

	if ($memberId === '' || $trNo < 1 || $creditAmount < 1) {
		return array(
			'success' => false,
			'message' => '크레딧 차감 파라미터가 올바르지 않습니다.',
		);
	}

	$currentCredit = credit_ledger_get_balance($memberId);
	if ($currentCredit < $creditAmount) {
		return array(
			'success' => false,
			'message' => '요청 가능 크레딧이 부족합니다. (필요: ' . $creditAmount . ')',
			'plan_required' => true,
		);
	}

	$ledgerLast = credit_ledger_get_latest_row($memberId, true);
	$currentCredit = isset($ledgerLast['credit_after']) ? (int)$ledgerLast['credit_after'] : 0;
	if ($currentCredit < $creditAmount) {
		return array(
			'success' => false,
			'message' => '요청 가능 크레딧이 부족합니다. (필요: ' . $creditAmount . ')',
			'plan_required' => true,
		);
	}

	$creditAfter = $currentCredit - $creditAmount;
	$memo = addslashes($tradeType === 'TM' ? 'TM 서포트 거래 요청' : '일반 거래 요청');

	$historySql = "insert into credit_history set
		uid='" . sql_escape_string($memberId) . "',
		tx_type='USE',
		service_code='TRADE_REQUEST',
		credit_delta='-{$creditAmount}',
		credit_after='{$creditAfter}',
		order_no=NULL,
		tr_no='{$trNo}',
		target_kedcd='" . sql_escape_string($targetKedcd) . "',
		once_key=NULL,
		memo='{$memo}',
		created_at=now(),
		request_type='" . sql_escape_string($tradeType) . "'";

	$historyResult = sql_query($historySql, false);
	if (!$historyResult) {
		return array(
			'success' => false,
			'message' => '크레딧 이력 저장에 실패했습니다.',
		);
	}
	$useHistoryId = (int)sql_insert_id();
	// USE 1건을 FIFO LOT 차감으로 분해해 실제 사용 LOT를 기록한다.
	$lotUse = credit_lot_allocate_use($memberId, $useHistoryId, $creditAmount);
	if (empty($lotUse['ok'])) {
		$lotMsg = isset($lotUse['message']) ? (string)$lotUse['message'] : '';
		$isLotShortage = ($lotMsg === 'not enough lot remain');
		$userMessage = $isLotShortage
			? '가용 크레딧이 부족합니다. (필요: ' . $creditAmount . ')'
			: '크레딧 차감 처리 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.';
		return array(
			'success' => false,
			'message' => $userMessage,
			'debug_stage' => 'lot_allocate',
			'debug_reason' => $lotMsg,
			'debug_allocated' => isset($lotUse['allocated']) ? (int)$lotUse['allocated'] : 0,
			'debug_missing' => isset($lotUse['missing']) ? (int)$lotUse['missing'] : 0,
			'plan_required' => $isLotShortage,
		);
	}

	return array(
		'success' => true,
		'credit_after' => $creditAfter,
		'order_no' => '',
	);
}

function company_detail_trade_request_valid_email($email) {
	$email = trim((string)$email);
	if ($email === '') return '';

	if (function_exists('get_email_address')) {
		$email = get_email_address($email);
	}

	return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}

function company_detail_trade_request_plain_text($value) {
	$value = strip_tags((string)$value);
	$cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
	return trim($cleaned === null ? $value : $cleaned);
}

function company_detail_trade_request_send_receiver_mail($tradeRequestNo, $requestMemberId, $requestCompanyName, $targetCompany, $tradeDirection, $desiredItem, $messageContent, $tradeType, $requestBzno = '', $mappedPurposes = array()) {
	global $config;
	global $g5;

	include_once(G5_LIB_PATH . '/mailer.lib.php');
	include_once(G5_LIB_PATH . '/mail_preview_image.lib.php');

	$tradeRequestNo = (int)$tradeRequestNo;
	$requestMemberId = trim((string)$requestMemberId);
	$requestCompanyName = trim((string)$requestCompanyName);
	$requestBzno = preg_replace('/[^0-9]/', '', (string)$requestBzno);
	$tradeDirection = trim((string)$tradeDirection);
	$desiredItem = trim((string)$desiredItem);
	$messageContent = trim((string)$messageContent);
	$tradeType = trim((string)$tradeType);
	$targetKedcd = trim((string)($targetCompany['KEDCD'] ?? ''));
	$targetCompanyName = trim((string)($targetCompany['ENP_NM'] ?? ''));
	$companyEmail = company_detail_trade_request_valid_email($targetCompany['EMAIL'] ?? '');
	$subject = '[' . $config['cf_title'] . '] 거래 요청이 도착했습니다.';
	$mailLogBaseContext = array(
		'mail_type' => 'trade_request_receiver',
		'related_id' => (string)$requestMemberId,
	);

	$result = array(
		'attempted' => false,
		'sent' => false,
		'recipient_type' => '',
		'recipient_email' => '',
		'recipient_count' => 0,
		'sent_count' => 0,
		'reason' => '',
	);

	if ($tradeRequestNo < 1 || $targetKedcd === '') {
		$result['reason'] = 'invalid_trade_request_context';
		return $result;
	}

	$recipientMap = array();
	$receiverMembers = sql_query(" select mb_id, mb_name, mb_email
		from {$g5['member_table']}
		where mb_company = '" . sql_escape_string($targetKedcd) . "'
		  and (mb_leave_date = '' or mb_leave_date is null)
		order by mb_no asc ", false);

	if ($receiverMembers) {
		while ($receiverMember = sql_fetch_array($receiverMembers)) {
			$receiverMemberEmail = company_detail_trade_request_valid_email($receiverMember['mb_email'] ?? '');
			if ($receiverMemberEmail === '') {
				continue;
			}

			if (!isset($recipientMap[$receiverMemberEmail])) {
				$recipientMap[$receiverMemberEmail] = array(
					'email' => $receiverMemberEmail,
					'type' => 'member',
					'name' => trim((string)($receiverMember['mb_name'] ?? '')),
				);
			}
		}
	}

	if ($companyEmail !== '' && !isset($recipientMap[$companyEmail])) {
		$recipientMap[$companyEmail] = array(
			'email' => $companyEmail,
			'type' => 'company',
			'name' => $targetCompanyName,
		);
	}

	$recipientList = array_values($recipientMap);
	if (empty($recipientList)) {
		$result['reason'] = 'recipient_email_empty';
		if (function_exists('mailer_log_write')) {
			$skipContext = $mailLogBaseContext;
			$skipContext['recipient_route'] = 'member,company';
			$skipContext['skip_reason'] = 'recipient_email_empty';
			mailer_log_write($skipContext, '', $subject, 'SKIP', '대상 회사의 활성 회원 이메일과 회사 이메일이 모두 비어 있습니다.');
		}
		return $result;
	}

	$directionLabelMap = array(
		'P' => '구매',
		'S' => '판매',
		'C' => '협업',
	);
	$tradeDirectionLabel = isset($directionLabelMap[$tradeDirection]) ? $directionLabelMap[$tradeDirection] : $tradeDirection;
	$tradeTypeLabel = ($tradeType === 'TM') ? 'TM' : '기본';

	$purposeLabelMap = array(
		'BUY_PRODUCT' => '제품·상품 구매',
		'SOURCE_MATERIAL_PART' => '원료/부자재 소싱',
		'REQUEST_OEM_ODM' => 'OEM/ODM 생산 의뢰',
		'REQUEST_IMPORT_PROCUREMENT' => '수입/구매 대행 요청',
		'REQUEST_REGULAR_SUPPLY' => '정기 납품 요청',
		'OFFER_PRODUCT_SALE' => '제품·상품 판매 제안',
		'OFFER_OEM_ODM' => 'OEM/ODM 생산 제안',
		'OFFER_DISTRIBUTION_SALES' => '유통/판매 제휴 제안',
		'OFFER_EXPORT_AGENCY' => '수출 대행 제안',
		'OFFER_REGULAR_SALE' => '정기 공급 제안',
		'COLLAB_NEW_PRODUCT_DEVELOPMENT' => '신제품 공동개발',
		'COLLAB_MARKETING_PROMOTION' => '마케팅/프로모션 협업',
		'COLLAB_TECH_ALLIANCE_LICENSING' => '기술 제휴/라이선싱',
	);
	$tradePurposeLabels = array();
	if (is_array($mappedPurposes)) {
		foreach ($mappedPurposes as $purposeCode) {
			$purposeCode = trim((string)$purposeCode);
			if ($purposeCode === '') continue;
			$tradePurposeLabels[] = isset($purposeLabelMap[$purposeCode]) ? $purposeLabelMap[$purposeCode] : $purposeCode;
		}
	}
	$tradePurposeLabels = array_values(array_unique($tradePurposeLabels));

	$requestCompanyInfo = trade_request_member_company_data($requestBzno);
	$requestCompanyProfileName = trim((string)($requestCompanyInfo['company'] ?? ''));
	if ($requestCompanyProfileName === '') {
		$requestCompanyProfileName = $requestCompanyName;
	}
	$requestCompanyBizNo = trim((string)($requestCompanyInfo['bzno'] ?? ''));
	if ($requestCompanyBizNo === '' && $requestBzno !== '' && function_exists('licenseNum')) {
		$requestCompanyBizNo = (string)licenseNum($requestBzno);
	}
	$requestCompanyFounded = trim((string)($requestCompanyInfo['founded'] ?? ''));
	$requestCompanyEmployees = trim((string)($requestCompanyInfo['employees'] ?? ''));
	$requestCompanyCorpType = trim((string)($requestCompanyInfo['corpType'] ?? ''));
	$requestCompanyIndustry = trim((string)($requestCompanyInfo['industry'] ?? ''));
	$requestCompanyProduct = trim((string)($requestCompanyInfo['product'] ?? ''));
	$requestCompanyHomepage = trim((string)($requestCompanyInfo['homepage'] ?? ''));

	$messagePreviewLines = preg_split('/(?:\r\n|\r|\n)+|(?<=[\.\!\?])\s+/u', $messageContent, -1, PREG_SPLIT_NO_EMPTY);
	if (!is_array($messagePreviewLines)) {
		$messagePreviewLines = array();
	}
	$messagePreviewLines = array_values(array_filter(array_map('trim', $messagePreviewLines), static function ($line) {
		return $line !== '';
	}));
	if (empty($messagePreviewLines)) {
		$messagePreviewLines = array($messageContent);
	}
	$messagePreviewLines = array_slice($messagePreviewLines, 0, 3);
	while (count($messagePreviewLines) < 3) {
		$messagePreviewLines[] = $messagePreviewLines[count($messagePreviewLines) - 1];
	}
	$messagePreviewFirstLine = isset($messagePreviewLines[0]) ? trim((string)$messagePreviewLines[0]) : '';
	$messagePreviewBlurLines = array_values(array_filter(array_slice($messagePreviewLines, 1, 2), static function ($line) {
		return trim((string)$line) !== '';
	}));
	if (empty($messagePreviewBlurLines) && $messagePreviewFirstLine !== '') {
		$messagePreviewBlurLines = array($messagePreviewFirstLine);
	}

	$mailBaseUrl = function_exists('mail_preview_resolve_base_url')
		? mail_preview_resolve_base_url('https://macomaex.com')
		: 'https://macomaex.com';

	// 메일 발송 블러 이미지 생성
	$messagePreviewImageUrl = '';
	if (function_exists('mail_preview_generate_trade_request_blur_image')) {
		$generatedPreviewImage = mail_preview_generate_trade_request_blur_image(
			$messagePreviewBlurLines,
			$mailBaseUrl,
			array(
				'style' => 'gradient_reveal',
			)
		);
		if (!empty($generatedPreviewImage['url'])) {
			$messagePreviewImageUrl = (string)$generatedPreviewImage['url'];
		} else {
			company_detail_trade_request_log('mail_preview_generate_failed', array(
				'error' => (string)($generatedPreviewImage['error'] ?? ''),
				'path' => (string)($generatedPreviewImage['path'] ?? ''),
				'line_count' => count($messagePreviewBlurLines),
			));
		}
	}

	$mailDateLabel = date('Y년 m월 d일 H:i', defined('G5_SERVER_TIME') ? (int)G5_SERVER_TIME : time());

	$recipientTypes = array();
	$recipientEmails = array();
	$sentCount = 0;

	foreach ($recipientList as $recipient) {
		$recipientType = trim((string)($recipient['type'] ?? ''));
		$recipientEmail = trim((string)($recipient['email'] ?? ''));
		$receiverName = trim((string)($recipient['name'] ?? ''));
		if ($receiverName === '') {
			$receiverName = ($recipientType === 'company' && $targetCompanyName !== '') ? $targetCompanyName : '담당자';
		}

		$mailLogContext = $mailLogBaseContext;
		$mailLogContext['recipient_route'] = $recipientType;
		if (function_exists('mailer_prepare_tracking_context')) {
			$mailLogContext = mailer_prepare_tracking_context(1, $mailLogContext);
		}

		$trackToken = trim((string)($mailLogContext['track_token'] ?? ''));
		$tradeRequestActionUrl = 'https://macomaex.com';
		if ($trackToken !== '' && function_exists('mailer_tracking_click_url')) {
			$tradeRequestActionUrl = mailer_tracking_click_url($trackToken, 'https://macomaex.com');
		}
		$unsubscribeEmail = $recipientEmail;
		$unsubscribeUrl = $mailBaseUrl . '/email/unsubscribe?email=' . rawurlencode($unsubscribeEmail);

		ob_start();
		include G5_BBS_PATH . '/mail/trade_request_received_mail.php';
		$content = ob_get_clean();

		$result['attempted'] = true;
		$recipientTypes[] = $recipientType;
		$recipientEmails[] = $recipientEmail;

		if ((bool)mailer(
			$config['cf_admin_email_name'],
			$config['cf_admin_email'],
			$recipientEmail,
			$subject,
			$content,
			1,
			"",
			"",
			"",
			$mailLogContext
		)) {
			$sentCount++;
		}
	}

	$recipientTypes = array_values(array_unique(array_filter($recipientTypes)));
	$recipientEmails = array_values(array_unique(array_filter($recipientEmails)));
	$result['recipient_type'] = implode(',', $recipientTypes);
	$result['recipient_email'] = implode(',', $recipientEmails);
	$result['recipient_count'] = count($recipientEmails);
	$result['sent_count'] = $sentCount;
	$result['sent'] = ($result['recipient_count'] > 0 && $sentCount === $result['recipient_count']);

	if (!$result['sent']) {
		$result['reason'] = ($sentCount > 0) ? 'partial_mailer_failed' : 'mailer_failed';
	}

	return $result;
}

function company_detail_trade_request_profile_state($member) {
	$bzno = trade_request_normalize_bzno($member['mb_4'] ?? '');
	$managerName = trim((string)($member['mb_6'] ?? ''));
	$managerPhoneRaw = trade_request_normalize_phone($member['mb_hp'] ?? '');
	$managerPhone = $managerPhoneRaw;
	if (strlen($managerPhoneRaw) === 11) {
		$managerPhone = preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $managerPhoneRaw);
	}

	$companyData = array();
	if ($bzno !== '') {
		$companyData = trade_request_member_company_data($bzno);
	}

	return array(
		'bzno' => $bzno,
		'has_bzno' => $bzno !== '',
		'has_company' => !empty($companyData['company']),
		'company' => $companyData,
		'has_manager' => ($managerName !== '' && $managerPhoneRaw !== ''),
		'manager_name' => $managerName,
		'manager_phone' => $managerPhone,
	);
}

function company_detail_should_overwrite_member_company_name($currentValue, $memberId) {
	$currentValue = trim((string)$currentValue);
	$memberId = trim((string)$memberId);
	if ($currentValue === '') return true;
	if ($memberId !== '' && $currentValue === $memberId) return true;
	return false;
}

function company_detail_trade_request_click_client_ip() {
	return trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
}

function company_detail_trade_request_click_guest_id() {
	$sessionId = trim((string)session_id());
	if ($sessionId !== '') {
		return 'guest:' . $sessionId;
	}

	$fallbackSeed = company_detail_trade_request_click_client_ip() . '|' . trim((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
	return 'guest:' . substr(hash('sha256', $fallbackSeed), 0, 32);
}

function company_detail_handle_trade_request_click_hit($member, $request, $sourceType) {
	$sourceType = trim((string)$sourceType);
	if (!in_array($sourceType, array('category', 'recommend'), true)) {
		company_detail_trade_request_json_response(false, '유입 구분이 올바르지 않습니다.', array(
			'error_code' => 'invalid_source_type',
		));
	}

	$targetKedcd = preg_replace('/\D/', '', (string)($request['target_kedcd'] ?? ($request['kedcd'] ?? '')));
	if (strlen($targetKedcd) !== 10 || preg_match('/^0+$/', $targetKedcd)) {
		company_detail_trade_request_json_response(false, '대상 기업 정보가 올바르지 않습니다.', array(
			'error_code' => 'invalid_target_kedcd',
		));
	}

	$targetCompany = sql_fetch("
		SELECT KEDCD, ENP_NM, BZNO
		FROM company
		WHERE KEDCD = '" . sql_escape_string($targetKedcd) . "'
		LIMIT 1
	");
	$targetCompanyName = trim((string)($targetCompany['ENP_NM'] ?? ''));
	$targetBzno = preg_replace('/\D/', '', (string)($targetCompany['BZNO'] ?? ''));

	if (empty($targetCompany['KEDCD'])) {
		$targetCompanyName = trim((string)($request['target_company_name'] ?? ''));
		$targetBzno = preg_replace('/\D/', '', (string)($request['target_bzno'] ?? ''));
	}
	if ($targetCompanyName === '' && $targetBzno === '') {
		company_detail_trade_request_json_response(false, '대상 기업 정보를 찾지 못했습니다.', array(
			'error_code' => 'target_company_not_found',
		));
	}

	$memberId = trim((string)($member['mb_id'] ?? ''));
	$mbType = $memberId !== '' ? 'member' : 'guest';
	$mbId = $memberId !== '' ? $memberId : company_detail_trade_request_click_guest_id();
	$ipAddress = company_detail_trade_request_click_client_ip();

	$sql = "insert into trade_request_click_hit set
		target_kedcd='" . sql_escape_string($targetKedcd) . "',
		target_company_name='" . sql_escape_string($targetCompanyName) . "',
		target_bzno='" . sql_escape_string($targetBzno) . "',
		mb_type='" . sql_escape_string($mbType) . "',
		mb_id='" . sql_escape_string($mbId) . "',
		source_type='" . sql_escape_string($sourceType) . "',
		ip_address='" . sql_escape_string($ipAddress) . "',
		clicked_at=now(),
		clicked_date=curdate()";

	$result = sql_query($sql, false);
	if (!$result) {
		company_detail_trade_request_log('click_hit_insert_failed', array(
			'target_kedcd' => $targetKedcd,
			'mb_type' => $mbType,
			'mb_id' => $mbId,
			'source_type' => $sourceType,
		));
		company_detail_trade_request_json_response(false, '클릭 기록 저장에 실패했습니다.', array(
			'error_code' => 'click_hit_insert_failed',
		));
	}

	company_detail_trade_request_json_response(true, '클릭 기록이 저장되었습니다.');
}

function company_detail_handle_trade_request_verify_bzno($member, $request) {
	if (empty($member['mb_id'])) {
		company_detail_trade_request_json_response(false, '로그인 후 이용해 주세요.');
	}

	$bzno = trade_request_normalize_bzno($request['bzno'] ?? '');
	// 모달의 "사업자번호 확인"은 화면 표시용 확인만 수행하고 DB에는 저장하지 않는다.
	$resolved = trade_request_resolve_company_data_by_bzno($bzno, array('persist' => false));
	if (empty($resolved['success'])) {
		company_detail_trade_request_json_response(false, $resolved['message'] ?? '사업자번호 확인에 실패했습니다.');
	}

	company_detail_trade_request_json_response(true, '기업 정보를 확인했습니다.', array(
		'company' => $resolved['company'],
		'bzno' => licenseNum($resolved['bzno']),
	));
}

function company_detail_handle_trade_request_insert($member, $request) {
	global $g5;
	$defaultMessage = trade_request_default_message();
	$requestId = date('YmdHis') . '-' . substr(md5(uniqid('', true)), 0, 8);

	company_detail_trade_request_log('request_received', array(
		'request_id' => $requestId,
		'member_id' => $member['mb_id'] ?? '',
		'post_keys' => array_keys($request),
	));

	if (empty($member['mb_id'])) {
		company_detail_trade_request_fail_response('로그인 후 이용해 주세요.', 'member_not_logged_in', array(
			'request_id' => $requestId,
			'stage' => 'auth',
		), array(
			'login_required' => true,
		));
	}

	$memberBzno = trade_request_normalize_bzno($member['mb_4'] ?? '');
	$memberBznoStored = trim((string)($member['mb_4'] ?? ''));
	$inputBzno = trade_request_normalize_bzno($request['requester_bzno'] ?? '');
	$requesterBzno = $memberBzno !== '' ? $memberBzno : $inputBzno;
	if ($requesterBzno === '') {
		company_detail_trade_request_fail_response('사업자번호를 먼저 확인해 주세요.', 'requester_bzno_empty', array(
			'request_id' => $requestId,
			'mb_id' => trim((string)($member['mb_id'] ?? '')),
			'stage' => 'validation',
		));
	}

	// 최종 제출 전 1차 검증: 기업정보를 조회하되 아직 DB에 확정 저장하지 않는다.
	$resolvedCompany = trade_request_resolve_company_data_by_bzno($requesterBzno, array('persist' => false));
	if (empty($resolvedCompany['success'])) {
		company_detail_trade_request_fail_response($resolvedCompany['message'] ?? '기업 정보를 확인하지 못했습니다.', 'requester_company_unresolved', array(
			'request_id' => $requestId,
			'mb_id' => trim((string)($member['mb_id'] ?? '')),
			'request_bzno' => $requesterBzno,
			'stage' => 'resource',
		));
	}
	$resolvedCompanyName = trim((string)($resolvedCompany['company']['company'] ?? ''));
	$resolvedCompanyKedcd = trim((string)($resolvedCompany['company']['kedcd'] ?? ''));

	$managerNameCurrent = trim((string)($member['mb_6'] ?? ''));
	$managerPhoneCurrent = trade_request_normalize_phone($member['mb_hp'] ?? '');
	$memberNameCurrent = trim((string)($member['mb_name'] ?? ''));
	$memberNickCurrent = trim((string)($member['mb_nick'] ?? ''));
	if ($managerNameCurrent === '' || $managerPhoneCurrent === '') {
		company_detail_trade_request_fail_response('회원 정보에 이름과 휴대폰 번호가 없습니다. 회원정보를 확인해 주세요.', 'member_contact_missing', array(
			'request_id' => $requestId,
			'mb_id' => trim((string)($member['mb_id'] ?? '')),
			'request_bzno' => $requesterBzno,
			'has_manager_name' => $managerNameCurrent !== '',
			'has_phone' => $managerPhoneCurrent !== '',
			'stage' => 'validation',
		));
	}
	if (!preg_match('/^[0-9]{9,11}$/', $managerPhoneCurrent)) {
		company_detail_trade_request_fail_response('회원 휴대폰 번호 형식을 확인해 주세요.', 'member_phone_invalid', array(
			'request_id' => $requestId,
			'mb_id' => trim((string)($member['mb_id'] ?? '')),
			'request_bzno' => $requesterBzno,
			'stage' => 'validation',
		));
	}

	$targetKedcd = isset($request['target_kedcd']) ? preg_replace('/[^0-9]/', '', (string)$request['target_kedcd']) : '';
	$tradeDirectionRaw = isset($request['trade_direction']) ? trim((string)$request['trade_direction']) : '';
	$tradePurposeRaw = isset($request['trade_purpose']) ? $request['trade_purpose'] : array();
	$desiredItem = company_detail_trade_request_plain_text($request['desired_item'] ?? '');
	$messageContent = company_detail_trade_request_plain_text($request['message_content'] ?? '');
	$tradeTypeRaw = isset($request['trade_type']) ? trim((string)$request['trade_type']) : '';

	$directionMap = array(
		'buy' => 'P',
		'supply' => 'S',
		'collab' => 'C',
	);
	$purposeMap = array(
		'buy' => array(
			'buy1' => 'BUY_PRODUCT',
			'buy2' => 'SOURCE_MATERIAL_PART',
			'buy3' => 'REQUEST_OEM_ODM',
			'buy4' => 'REQUEST_IMPORT_PROCUREMENT',
			'buy5' => 'REQUEST_REGULAR_SUPPLY',
		),
		'supply' => array(
			'sup1' => 'OFFER_PRODUCT_SALE',
			'sup2' => 'OFFER_OEM_ODM',
			'sup3' => 'OFFER_DISTRIBUTION_SALES',
			'sup4' => 'OFFER_EXPORT_AGENCY',
			'sup5' => 'OFFER_REGULAR_SALE',
		),
		'collab' => array(
			'col1' => 'COLLAB_NEW_PRODUCT_DEVELOPMENT',
			'col2' => 'COLLAB_MARKETING_PROMOTION',
			'col3' => 'COLLAB_TECH_ALLIANCE_LICENSING',
		),
	);
	$tradeTypeMap = array(
		'normal' => array('type' => 'BASIC', 'amount' => 10),
		'tm' => array('type' => 'TM', 'amount' => 30),
	);

	if (!preg_match('/^\d{10}$/', $targetKedcd)) {
		company_detail_trade_request_fail_response('대상 기업 정보가 올바르지 않습니다.', 'invalid_target_kedcd', array(
			'request_id' => $requestId,
			'target_kedcd' => $targetKedcd,
			'stage' => 'validation',
		));
	}
	if (!isset($directionMap[$tradeDirectionRaw])) {
		company_detail_trade_request_fail_response('거래 방향을 다시 선택해 주세요.', 'invalid_trade_direction', array(
			'request_id' => $requestId,
			'stage' => 'validation',
		));
	}
	if (!is_array($tradePurposeRaw) || empty($tradePurposeRaw)) {
		company_detail_trade_request_fail_response('거래 목적을 1개 이상 선택해 주세요.', 'trade_purpose_empty', array(
			'request_id' => $requestId,
			'stage' => 'validation',
		));
	}
	if ($desiredItem === '') {
		company_detail_trade_request_fail_response('거래 희망 품목을 입력해 주세요.', 'desired_item_empty', array(
			'request_id' => $requestId,
			'stage' => 'validation',
		));
	}
	if (mb_strlen($desiredItem, 'UTF-8') > 255) {
		company_detail_trade_request_fail_response('거래 희망 품목은 255자 이내로 입력해 주세요.', 'desired_item_too_long', array(
			'request_id' => $requestId,
			'stage' => 'validation',
		));
	}
	if ($messageContent === '') {
		$messageContent = $defaultMessage;
	}
	if (mb_strlen($messageContent, 'UTF-8') > 500) {
		company_detail_trade_request_fail_response('추가 문의 사항은 500자 이내로 입력해 주세요.', 'message_too_long', array(
			'request_id' => $requestId,
			'stage' => 'validation',
		));
	}
	if (!isset($tradeTypeMap[$tradeTypeRaw])) {
		company_detail_trade_request_fail_response('거래 타입을 다시 선택해 주세요.', 'invalid_trade_type', array(
			'request_id' => $requestId,
			'trade_type' => $tradeTypeRaw,
			'stage' => 'validation',
		));
	}

	$targetCompany = sql_fetch("
		SELECT KEDCD, ENP_NM, BZNO, EMAIL, BZC_CD, ESTB_DT, LABORER_SUM, ENP_TYP, PD_NM, HPAGE_URL
		FROM company
		WHERE KEDCD = '" . sql_escape_string($targetKedcd) . "'
		LIMIT 1
	");
	if (empty($targetCompany['KEDCD']) || empty($targetCompany['BZNO'])) {
		company_detail_trade_request_fail_response('대상 기업 정보가 올바르지 않습니다.', 'target_company_not_found', array(
			'request_id' => $requestId,
			'target_kedcd' => $targetKedcd,
			'stage' => 'resource',
		));
	}

	$requestMemberId = trim((string)($member['mb_id'] ?? ''));
	$requestCompanyName = $resolvedCompanyName !== '' ? $resolvedCompanyName : trim((string)($member['mb_name'] ?? ''));
	// 최종 제출 단계에서는 사전 검증으로 확정한 사업자번호를 기준으로 처리한다.
	$requestBzno = $requesterBzno;
	$targetCompanyName = trim((string)($targetCompany['ENP_NM'] ?? ''));
	$targetBzno = preg_replace('/[^0-9]/', '', (string)($targetCompany['BZNO'] ?? ''));

	if ($requestMemberId === '' || $requestCompanyName === '' || $requestBzno === '') {
		company_detail_trade_request_fail_response('회원 정보가 올바르지 않습니다. 관리자에게 문의해 주세요.', 'invalid_request_member_info', array(
			'request_id' => $requestId,
			'mb_id' => $requestMemberId,
			'request_bzno' => $requestBzno,
			'stage' => 'resource',
		));
	}
	if ($requestBzno === $targetBzno) {
		company_detail_trade_request_fail_response('자기 자신의 기업에는 거래 요청을 보낼 수 없습니다.', 'same_company_request', array(
			'request_id' => $requestId,
			'request_bzno' => $requestBzno,
			'target_bzno' => $targetBzno,
			'stage' => 'validation',
		));
	}

	$mappedPurposes = array();
	$seenPurpose = array();
	foreach ($tradePurposeRaw as $purposeId) {
		$purposeId = trim((string)$purposeId);
		if ($purposeId === '' || isset($seenPurpose[$purposeId])) continue;
		$seenPurpose[$purposeId] = true;

		if (!isset($purposeMap[$tradeDirectionRaw][$purposeId])) {
			company_detail_trade_request_fail_response('거래 목적 데이터가 올바르지 않습니다.', 'invalid_trade_purpose', array(
				'request_id' => $requestId,
				'direction' => $tradeDirectionRaw,
				'purpose_id' => $purposeId,
				'stage' => 'validation',
			));
		}

		$mappedPurposes[] = $purposeMap[$tradeDirectionRaw][$purposeId];
	}
	if (empty($mappedPurposes)) {
		company_detail_trade_request_fail_response('거래 목적을 다시 선택해 주세요.', 'mapped_trade_purpose_empty', array(
			'request_id' => $requestId,
			'stage' => 'validation',
		));
	}

	$tradeDirection = $directionMap[$tradeDirectionRaw];
	$tradeType = $tradeTypeMap[$tradeTypeRaw]['type'];
	$creditAmount = (int)$tradeTypeMap[$tradeTypeRaw]['amount'];
	$tradePurposeJson = json_encode(array_values($mappedPurposes), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

	if ($tradePurposeJson === false) {
		company_detail_trade_request_fail_response('거래 목적 처리 중 오류가 발생했습니다.', 'trade_purpose_json_encode_failed', array(
			'request_id' => $requestId,
			'stage' => 'validation',
		));
	}

	$remainCredit = credit_ledger_get_balance($requestMemberId);
	if ($remainCredit < $creditAmount) {
		company_detail_trade_request_fail_response('매칭 가능 크레딧이 부족합니다. (필요: ' . $creditAmount . ')', 'credit_not_enough', array(
			'request_id' => $requestId,
			'mb_id' => $requestMemberId,
			'request_bzno' => $requestBzno,
			'target_kedcd' => $targetKedcd,
			'trade_type' => $tradeTypeMap[$tradeTypeRaw]['type'] ?? '',
			'credit_amount' => $creditAmount,
			'remain_credit' => $remainCredit,
			'stage' => 'credit_precheck',
			'plan_required' => true,
		), array(
			'plan_required' => true,
			'remain_credit' => $remainCredit,
		));
	}

	$insertSql = "insert into trade_request set
		request_mb_id='" . sql_escape_string($requestMemberId) . "',
		target_mb_id=NULL,
		recipient_status='pending_assignment',
		recipient_assigned_at=NULL,
		request_company_name='" . sql_escape_string($requestCompanyName) . "',
		request_member_name='" . sql_escape_string($managerNameCurrent) . "',
		target_company_name='" . sql_escape_string($targetCompanyName) . "',
		target_member_name=NULL,
		request_bzno='" . sql_escape_string($requestBzno) . "',
		target_bzno='" . sql_escape_string($targetBzno) . "',
		target_kedcd='" . sql_escape_string($targetKedcd) . "',
		trade_direction='" . sql_escape_string($tradeDirection) . "',
		trade_purpose='" . sql_escape_string($tradePurposeJson) . "',
		desired_item='" . sql_escape_string($desiredItem) . "',
		message_content='" . sql_escape_string($messageContent) . "',
		trade_type='" . sql_escape_string($tradeType) . "',
		credit_used=0,
		credit_amount={$creditAmount},
		approval_status=0,
		adm_chk=0,
		adm_memo=NULL";

	sql_query("START TRANSACTION");
	$insertResult = sql_query($insertSql, false);
	if (!$insertResult) {
		sql_query("ROLLBACK");
		company_detail_trade_request_fail_response('거래 요청서 저장 중 오류가 발생했습니다.', 'trade_request_insert_failed', array(
			'request_id' => $requestId,
			'mb_id' => $requestMemberId,
			'request_bzno' => $requestBzno,
			'member_id' => $requestMemberId,
			'target_kedcd' => $targetKedcd,
			'trade_type' => $tradeType,
			'trade_direction' => $tradeDirection,
			'stage' => 'insert',
		));
	}

	$insertId = sql_insert_id();
	$creditConsume = company_detail_trade_request_consume_credit($requestMemberId, $insertId, $targetKedcd, $tradeType, $creditAmount);
	if (empty($creditConsume['success'])) {
		sql_query("ROLLBACK");
		$planRequired = !empty($creditConsume['plan_required']);
		company_detail_trade_request_fail_response($creditConsume['message'] ?? '크레딧 차감 중 오류가 발생했습니다.', 'credit_consume_failed', array(
			'request_id' => $requestId,
			'tr_no' => (int)$insertId,
			'mb_id' => $requestMemberId,
			'request_bzno' => $requestBzno,
			'target_kedcd' => $targetKedcd,
			'trade_type' => $tradeType,
			'trade_direction' => $tradeDirection,
			'credit_amount' => $creditAmount,
			'stage' => 'credit_consume',
			'tr_no' => $insertId,
			'message' => $creditConsume['message'] ?? '',
			'debug_stage' => $creditConsume['debug_stage'] ?? '',
			'debug_reason' => $creditConsume['debug_reason'] ?? '',
			'debug_allocated' => $creditConsume['debug_allocated'] ?? null,
			'debug_missing' => $creditConsume['debug_missing'] ?? null,
			'plan_required' => $planRequired,
		), array(
			'tr_no' => $insertId,
			'plan_required' => $planRequired,
		));
	}

	$creditUsedUpdated = sql_query("update trade_request set credit_used=1 where tr_no='" . (int)$insertId . "'", false);
	if (!$creditUsedUpdated) {
		sql_query("ROLLBACK");
		company_detail_trade_request_fail_response('거래 요청 상태 갱신 중 오류가 발생했습니다.', 'credit_used_update_failed', array(
			'request_id' => $requestId,
			'tr_no' => $insertId,
			'member_id' => $requestMemberId,
			'mb_id' => $requestMemberId,
			'request_bzno' => $requestBzno,
			'target_kedcd' => $targetKedcd,
			'trade_type' => $tradeType,
			'trade_direction' => $tradeDirection,
			'credit_amount' => $creditAmount,
			'stage' => 'credit_mark_used',
		));
	}

	// 거래요청 insert + 크레딧 차감이 모두 성공한 이후에만 기업정보를 DB에 확정 저장한다.
	$resolvedCompanyPersist = trade_request_resolve_company_data_by_bzno($requesterBzno, array('persist' => true));
	if (empty($resolvedCompanyPersist['success'])) {
		company_detail_trade_request_log('requester_company_persist_failed', array(
			'tr_no' => $insertId,
			'bzno' => $requesterBzno,
			'message' => $resolvedCompanyPersist['message'] ?? '',
		));
		sql_query("ROLLBACK");
		company_detail_trade_request_fail_response($resolvedCompanyPersist['message'] ?? '기업 정보 저장 중 오류가 발생했습니다.', 'requester_company_persist_failed', array(
			'request_id' => $requestId,
			'tr_no' => $insertId,
			'member_id' => $member['mb_id'] ?? '',
			'bzno' => $requesterBzno,
			'stage' => 'company_persist',
		));
	} else {
		$persistedKedcd = trim((string)($resolvedCompanyPersist['company']['kedcd'] ?? ''));
		if ($persistedKedcd !== '') {
			$resolvedCompanyKedcd = $persistedKedcd;
		}
	}

	$needMemberUpdate = false;
	$memberUpdateSet = array();
	$requesterBznoFormatted = function_exists('licenseNum') ? licenseNum($requesterBzno) : $requesterBzno;
	// 1) 사업자번호는 확정 저장 시 000-00-00000 포맷으로 동기화한다.
	if ($requesterBzno !== '' && $memberBznoStored !== $requesterBznoFormatted) {
		$needMemberUpdate = true;
		$memberUpdateSet[] = "mb_4='" . sql_escape_string($requesterBznoFormatted) . "'";
	}
	// 2) 사업자번호 조회로 확정된 기업코드(KEDCD)를 회원 회사 코드로 저장한다.
	if ($resolvedCompanyKedcd !== '' && trim((string)($member['mb_company'] ?? '')) !== $resolvedCompanyKedcd) {
		$needMemberUpdate = true;
		$memberUpdateSet[] = "mb_company='" . sql_escape_string($resolvedCompanyKedcd) . "'";
	}
	// 3) 회원명/닉네임이 비어 있거나 기본값(=mb_id)인 경우 기업명 저장
	if ($requestCompanyName !== '' && company_detail_should_overwrite_member_company_name($memberNameCurrent, $requestMemberId)) {
		$needMemberUpdate = true;
		$memberUpdateSet[] = "mb_name='" . sql_escape_string($requestCompanyName) . "'";
	}
	if ($requestCompanyName !== '' && company_detail_should_overwrite_member_company_name($memberNickCurrent, $requestMemberId)) {
		$needMemberUpdate = true;
		$memberUpdateSet[] = "mb_nick='" . sql_escape_string($requestCompanyName) . "'";
	}
	if ($needMemberUpdate && !empty($memberUpdateSet)) {
		$mbId = sql_escape_string((string)$member['mb_id']);
		$memberUpdateSql = "UPDATE g5_member SET " . implode(', ', $memberUpdateSet) . " WHERE mb_id='{$mbId}'";
		$memberUpdateResult = sql_query($memberUpdateSql, false);
		if (!$memberUpdateResult) {
			sql_query("ROLLBACK");
			company_detail_trade_request_fail_response('회원 정보 저장 중 오류가 발생했습니다.', 'member_profile_update_failed', array(
				'request_id' => $requestId,
				'tr_no' => $insertId,
				'member_id' => $member['mb_id'] ?? '',
				'stage' => 'member_update',
			));
		}
	}

	sql_query("COMMIT");

	company_detail_trade_request_log('request_created', array(
		'tr_no' => $insertId,
		'target_kedcd' => $targetKedcd,
		'trade_direction' => $tradeDirection,
		'trade_type' => $tradeType,
		'credit_amount' => $creditAmount,
		'credit_after' => $creditConsume['credit_after'] ?? null,
	));

		$alimtalkResult = array(
			'attempted' => false,
			'sent' => false,
			'to' => '',
			'request_id' => '',
			'error_code' => '',
			'provider_status' => '',
			'recipient_count' => 0,
			'sent_count' => 0,
			'reason' => '',
		);
		$receiverPhoneMap = array();
		$receiverAlimtalkRows = sql_query(" select mb_8
			from {$g5['member_table']}
			where mb_company = '" . sql_escape_string($targetKedcd) . "'
			  and (mb_leave_date = '' or mb_leave_date is null)
			order by mb_no asc ", false);
		if ($receiverAlimtalkRows) {
			while ($receiverAlimtalk = sql_fetch_array($receiverAlimtalkRows)) {
				$receiverHpRaw = trim((string)($receiverAlimtalk['mb_8'] ?? ''));
				$receiverHpDigits = trade_request_normalize_phone($receiverHpRaw);
				if ($receiverHpDigits === '') {
					continue;
				}
				if (!isset($receiverPhoneMap[$receiverHpDigits])) {
					$receiverPhoneMap[$receiverHpDigits] = $receiverHpRaw !== ''
						? $receiverHpRaw
						: company_detail_format_phone_hyphen($receiverHpDigits);
				}
			}
		}

		$receiverPhones = array_values($receiverPhoneMap);
		$alimtalkResult['recipient_count'] = count($receiverPhones);
		if (empty($receiverPhones)) {
			$alimtalkResult['reason'] = 'receiver_hp_empty';
		} elseif (!class_exists('NCP_SENS_AT')) {
			$alimtalkResult['reason'] = 'alimtalk_class_missing';
		} else {
			$alimtalkResult['attempted'] = true;
			$alimtalkResult['to'] = implode(',', $receiverPhones);
			$requestIds = array();
			$providerStatuses = array();
			$errorCodes = array();
			$sentCount = 0;

			foreach ($receiverPhones as $receiverHp) {
				$gw_msg = new NCP_SENS_AT();
				$gw_msg->templateCode = 'reqmatching2';
				$gw_msg->to = $receiverHp;
				$sendRes = $gw_msg->send();
				if (is_array($sendRes)) {
					$requestId = trim((string)($sendRes['requestId'] ?? ''));
					$providerStatus = trim((string)($sendRes['statusName'] ?? $sendRes['requestStatusName'] ?? ''));
					$errorCode = trim((string)($sendRes['statusCode'] ?? $sendRes['errorCode'] ?? ''));
					if ($requestId !== '') {
						$requestIds[] = $requestId;
					}
					if ($providerStatus !== '') {
						$providerStatuses[] = $providerStatus;
					}
					if ($errorCode !== '') {
						$errorCodes[] = $errorCode;
					}
				}

				if (is_array($sendRes) && !empty($sendRes['requestId'])) {
					$sentCount++;
				}
			}

			$alimtalkResult['request_id'] = implode(',', array_values(array_unique($requestIds)));
			$alimtalkResult['provider_status'] = implode(',', array_values(array_unique($providerStatuses)));
			$alimtalkResult['error_code'] = implode(',', array_values(array_unique($errorCodes)));
			$alimtalkResult['sent_count'] = $sentCount;
			$alimtalkResult['sent'] = ($alimtalkResult['recipient_count'] > 0 && $sentCount === $alimtalkResult['recipient_count']);
			if (!$alimtalkResult['sent']) {
				$alimtalkResult['reason'] = ($sentCount > 0) ? 'partial_send_failed' : 'send_failed';
			}
		}
		company_detail_trade_request_log('receiver_alimtalk', array(
			'tr_no' => $insertId,
			'attempted' => $alimtalkResult['attempted'],
			'sent' => $alimtalkResult['sent'],
			'to' => $alimtalkResult['to'],
			'recipient_count' => $alimtalkResult['recipient_count'],
			'sent_count' => $alimtalkResult['sent_count'],
			'request_id' => $alimtalkResult['request_id'],
			'error_code' => $alimtalkResult['error_code'],
			'provider_status' => $alimtalkResult['provider_status'],
			'reason' => $alimtalkResult['reason'],
	));
	alimtalk_write_event_log(
		'reqmatching2',
		$alimtalkResult['sent'] ? 'success' : ($alimtalkResult['attempted'] ? 'fail' : 'skip'),
			array(
				'template_code' => 'reqmatching2',
				'tr_no' => $insertId,
				'mb_id' => $requestMemberId,
				'to' => isset($receiverPhones[0]) ? $receiverPhones[0] : '',
				'request_id' => $alimtalkResult['request_id'],
				'provider_status' => $alimtalkResult['provider_status'],
				'error_code' => $alimtalkResult['error_code'],
				'reason' => trim((string)$alimtalkResult['reason']) !== ''
					? trim((string)$alimtalkResult['reason']) . '|count=' . (int)$alimtalkResult['sent_count'] . '/' . (int)$alimtalkResult['recipient_count']
					: 'count=' . (int)$alimtalkResult['sent_count'] . '/' . (int)$alimtalkResult['recipient_count'],
			)
		);

	$mailResult = company_detail_trade_request_send_receiver_mail(
		$insertId,
		$requestMemberId,
		$requestCompanyName,
		$targetCompany,
		$tradeDirection,
		$desiredItem,
		$messageContent,
		$tradeType,
		$requestBzno,
		$mappedPurposes
	);

	company_detail_trade_request_log('receiver_mail', array(
		'tr_no' => $insertId,
		'attempted' => !empty($mailResult['attempted']),
		'sent' => !empty($mailResult['sent']),
		'recipient_type' => $mailResult['recipient_type'] ?? '',
		'recipient_email' => $mailResult['recipient_email'] ?? '',
		'recipient_count' => (int)($mailResult['recipient_count'] ?? 0),
		'sent_count' => (int)($mailResult['sent_count'] ?? 0),
		'reason' => $mailResult['reason'] ?? '',
	));

	company_detail_trade_request_write_event_log('receiver_mail', !empty($mailResult['sent']) ? 'success' : 'fail', array(
		'request_id' => $requestId,
		'tr_no' => (int)$insertId,
		'mb_id' => $requestMemberId,
		'target_kedcd' => $targetKedcd,
		'trade_type' => $tradeType,
		'trade_direction' => $tradeDirection,
		'mail_attempted' => !empty($mailResult['attempted']),
		'mail_sent' => !empty($mailResult['sent']),
		'mail_recipient_type' => $mailResult['recipient_type'] ?? '',
		'mail_reason' => trim((string)($mailResult['reason'] ?? '')) !== ''
			? trim((string)($mailResult['reason'] ?? '')) . '|count=' . (int)($mailResult['sent_count'] ?? 0) . '/' . (int)($mailResult['recipient_count'] ?? 0)
			: 'count=' . (int)($mailResult['sent_count'] ?? 0) . '/' . (int)($mailResult['recipient_count'] ?? 0),
	), $mailResult['reason'] ?? '');

	company_detail_trade_request_success_response('거래 요청서가 저장되었습니다.', array(
		'request_id' => $requestId,
		'tr_no' => (int)$insertId,
		'mb_id' => $requestMemberId,
		'request_bzno' => $requestBzno,
		'target_kedcd' => $targetKedcd,
		'target_bzno' => $targetBzno,
		'trade_type' => $tradeType,
		'trade_direction' => $tradeDirection,
		'credit_amount' => $creditAmount,
		'credit_after' => (int)($creditConsume['credit_after'] ?? 0),
		'remain_credit' => (int)($creditConsume['credit_after'] ?? 0),
		'purpose_count' => count($mappedPurposes),
		'stage' => 'completed',
	), array(
		'tr_no' => $insertId,
		'credit_after' => $creditConsume['credit_after'] ?? null,
		'credit_amount' => $creditAmount,
		'unlock_detail' => true,
		'request_id' => $requestId,
	));
}
