<?php
if (!defined('_GNUBOARD_')) exit;

/*
 * company_detail.php 전용 서버 헬퍼.
 * 거래 요청서 저장, 입력 검증, 크레딧 차감, 응답/로그 처리를 페이지 밖으로 분리해 관리한다.
 */

include_once(__DIR__ . '/trade_request_helper.php');

function company_detail_trade_request_json_response($success, $message, $extra = array()) {
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(array_merge(array(
		'success' => $success,
		'message' => $message,
	), $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
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

	$status = sql_fetch("select * from g5_membership_status where uid='" . sql_escape_string($memberId) . "' order by no desc limit 1");
	$currentCredit = isset($status['matching_able_cnt']) ? (int)$status['matching_able_cnt'] : 0;
	if (empty($status['uid']) || $currentCredit < $creditAmount) {
		return array(
			'success' => false,
			'message' => '매칭 가능 크레딧이 부족합니다. (필요: ' . $creditAmount . ')',
			'plan_required' => true,
		);
	}

	$deductSql = "update g5_membership_status set matching_able_cnt=matching_able_cnt-{$creditAmount} where uid='" . sql_escape_string($memberId) . "' and matching_able_cnt >= {$creditAmount}";
	$deductResult = sql_query($deductSql, false);
	$affected = sql_fetch("select row_count() as cnt");
	if (!$deductResult || (int)$affected['cnt'] < 1) {
		return array(
			'success' => false,
			'message' => '매칭 가능 크레딧이 부족합니다. (필요: ' . $creditAmount . ')',
			'plan_required' => true,
		);
	}

	$afterStatus = sql_fetch("select * from g5_membership_status where uid='" . sql_escape_string($memberId) . "' order by no desc limit 1");
	$creditAfter = isset($afterStatus['matching_able_cnt']) ? (int)$afterStatus['matching_able_cnt'] : 0;
	$memo = addslashes($tradeType === 'TM' ? 'TM 서포트 거래 요청' : '일반 거래 요청');

	$historySql = "insert into g5_matching_credit_history set
		uid='" . sql_escape_string($memberId) . "',
		tx_type='USE',
		service_code='TRADE_REQUEST',
		credit_delta='-{$creditAmount}',
		credit_after='{$creditAfter}',
		order_no=NULL,
		matching_no='{$trNo}',
		target_kedcd='" . sql_escape_string($targetKedcd) . "',
		once_key=NULL,
		memo='{$memo}',
		created_at=now(),
		request_type='" . sql_escape_string($tradeType) . "'";

	$historyResult = sql_query($historySql, false);
	if (!$historyResult) {
		sql_query("update g5_membership_status set matching_able_cnt=matching_able_cnt+{$creditAmount} where uid='" . sql_escape_string($memberId) . "'", false);

		return array(
			'success' => false,
			'message' => '크레딧 이력 저장에 실패했습니다.',
		);
	}

	if (!empty($afterStatus['order_no'])) {
		sql_query("update g5_membership_status_history set matching_able_cnt='{$creditAfter}' where uid='" . sql_escape_string($memberId) . "' and order_no='" . sql_escape_string($afterStatus['order_no']) . "'", false);
	}

	return array(
		'success' => true,
		'credit_after' => $creditAfter,
		'order_no' => $afterStatus['order_no'] ?? '',
	);
}

function company_detail_handle_trade_request_insert($member, $request) {
	$defaultMessage = trade_request_default_message();

	company_detail_trade_request_log('request_received', array(
		'member_id' => $member['mb_id'] ?? '',
		'post_keys' => array_keys($request),
	));

	if (empty($member['mb_id'])) {
		company_detail_trade_request_log('validation_failed', array('reason' => 'member_not_logged_in'));
		company_detail_trade_request_json_response(false, '로그인 후 이용해 주세요.');
	}

	$targetKedcd = isset($request['target_kedcd']) ? preg_replace('/[^0-9]/', '', (string)$request['target_kedcd']) : '';
	$tradeDirectionRaw = isset($request['trade_direction']) ? trim((string)$request['trade_direction']) : '';
	$tradePurposeRaw = isset($request['trade_purpose']) ? $request['trade_purpose'] : array();
	$desiredItem = isset($request['desired_item']) ? trim((string)$request['desired_item']) : '';
	$messageContent = isset($request['message_content']) ? trim((string)$request['message_content']) : '';
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
		company_detail_trade_request_log('validation_failed', array('reason' => 'invalid_target_kedcd', 'target_kedcd' => $targetKedcd));
		company_detail_trade_request_json_response(false, '대상 기업 정보가 올바르지 않습니다.');
	}
	if (!isset($directionMap[$tradeDirectionRaw])) {
		company_detail_trade_request_log('validation_failed', array('reason' => 'invalid_trade_direction', 'trade_direction' => $tradeDirectionRaw));
		company_detail_trade_request_json_response(false, '거래 방향을 다시 선택해 주세요.');
	}
	if (!is_array($tradePurposeRaw) || empty($tradePurposeRaw)) {
		company_detail_trade_request_log('validation_failed', array('reason' => 'trade_purpose_empty'));
		company_detail_trade_request_json_response(false, '거래 목적을 1개 이상 선택해 주세요.');
	}
	if ($desiredItem === '') {
		company_detail_trade_request_log('validation_failed', array('reason' => 'desired_item_empty'));
		company_detail_trade_request_json_response(false, '거래 희망 품목을 입력해 주세요.');
	}
	if (mb_strlen($desiredItem, 'UTF-8') > 255) {
		company_detail_trade_request_log('validation_failed', array('reason' => 'desired_item_too_long'));
		company_detail_trade_request_json_response(false, '거래 희망 품목은 255자 이내로 입력해 주세요.');
	}
	if ($messageContent === '') {
		$messageContent = $defaultMessage;
	}
	if (mb_strlen($messageContent, 'UTF-8') > 500) {
		company_detail_trade_request_log('validation_failed', array('reason' => 'message_too_long'));
		company_detail_trade_request_json_response(false, '추가 문의 사항은 500자 이내로 입력해 주세요.');
	}
	if (!isset($tradeTypeMap[$tradeTypeRaw])) {
		company_detail_trade_request_log('validation_failed', array('reason' => 'invalid_trade_type', 'trade_type' => $tradeTypeRaw));
		company_detail_trade_request_json_response(false, '거래 타입을 다시 선택해 주세요.');
	}

	$targetCompany = sql_fetch("
		SELECT KEDCD, ENP_NM, BZNO
		FROM company
		WHERE KEDCD = '" . sql_escape_string($targetKedcd) . "'
		LIMIT 1
	");
	if (empty($targetCompany['KEDCD']) || empty($targetCompany['BZNO'])) {
		company_detail_trade_request_log('validation_failed', array('reason' => 'target_company_not_found', 'target_kedcd' => $targetKedcd));
		company_detail_trade_request_json_response(false, '대상 기업 정보가 올바르지 않습니다.');
	}

	$requestMemberId = trim((string)($member['mb_id'] ?? ''));
	$requestCompanyName = trim((string)($member['mb_name'] ?? ''));
	$requestBzno = preg_replace('/[^0-9]/', '', (string)($member['mb_4'] ?? ''));
	$targetCompanyName = trim((string)($targetCompany['ENP_NM'] ?? ''));
	$targetBzno = preg_replace('/[^0-9]/', '', (string)($targetCompany['BZNO'] ?? ''));

	if ($requestMemberId === '' || $requestCompanyName === '' || $requestBzno === '') {
		company_detail_trade_request_log('validation_failed', array(
			'reason' => 'invalid_request_member_info',
			'request_mb_id' => $requestMemberId,
			'request_company_name' => $requestCompanyName,
			'request_bzno' => $requestBzno,
		));
		company_detail_trade_request_json_response(false, '회원 정보가 올바르지 않습니다. 관리자에게 문의해 주세요.');
	}
	if ($requestBzno === $targetBzno) {
		company_detail_trade_request_log('validation_failed', array('reason' => 'same_company_request'));
		company_detail_trade_request_json_response(false, '자기 자신의 기업에는 거래 요청을 보낼 수 없습니다.');
	}

	$mappedPurposes = array();
	$seenPurpose = array();
	foreach ($tradePurposeRaw as $purposeId) {
		$purposeId = trim((string)$purposeId);
		if ($purposeId === '' || isset($seenPurpose[$purposeId])) continue;
		$seenPurpose[$purposeId] = true;

		if (!isset($purposeMap[$tradeDirectionRaw][$purposeId])) {
			company_detail_trade_request_log('validation_failed', array(
				'reason' => 'invalid_trade_purpose',
				'direction' => $tradeDirectionRaw,
				'purpose_id' => $purposeId,
			));
			company_detail_trade_request_json_response(false, '거래 목적 데이터가 올바르지 않습니다.');
		}

		$mappedPurposes[] = $purposeMap[$tradeDirectionRaw][$purposeId];
	}
	if (empty($mappedPurposes)) {
		company_detail_trade_request_log('validation_failed', array('reason' => 'mapped_trade_purpose_empty'));
		company_detail_trade_request_json_response(false, '거래 목적을 다시 선택해 주세요.');
	}

	$tradeDirection = $directionMap[$tradeDirectionRaw];
	$tradeType = $tradeTypeMap[$tradeTypeRaw]['type'];
	$creditAmount = (int)$tradeTypeMap[$tradeTypeRaw]['amount'];
	$tradePurposeJson = json_encode(array_values($mappedPurposes), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

	if ($tradePurposeJson === false) {
		company_detail_trade_request_log('validation_failed', array('reason' => 'trade_purpose_json_encode_failed'));
		company_detail_trade_request_json_response(false, '거래 목적 처리 중 오류가 발생했습니다.');
	}

	$creditStatus = sql_fetch("select uid, matching_able_cnt from g5_membership_status where uid='" . sql_escape_string($requestMemberId) . "' order by no desc limit 1");
	$remainCredit = isset($creditStatus['matching_able_cnt']) ? (int)$creditStatus['matching_able_cnt'] : 0;
	if (empty($creditStatus['uid']) || $remainCredit < $creditAmount) {
		company_detail_trade_request_log('validation_failed', array(
			'reason' => 'credit_not_enough',
			'remain_credit' => $remainCredit,
			'required' => $creditAmount,
		));
		company_detail_trade_request_json_response(false, '매칭 가능 크레딧이 부족합니다. (필요: ' . $creditAmount . ')', array(
			'plan_required' => true,
			'remain_credit' => $remainCredit,
		));
	}

	$insertSql = "insert into trade_request set
		request_mb_id='" . sql_escape_string($requestMemberId) . "',
		request_company_name='" . sql_escape_string($requestCompanyName) . "',
		target_company_name='" . sql_escape_string($targetCompanyName) . "',
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

	$insertResult = sql_query($insertSql, false);
	if (!$insertResult) {
		company_detail_trade_request_log('insert_failed', array(
			'member_id' => $requestMemberId,
			'target_kedcd' => $targetKedcd,
			'trade_type' => $tradeType,
		));
		company_detail_trade_request_json_response(false, '거래 요청서 저장 중 오류가 발생했습니다.');
	}

	$insertId = sql_insert_id();
	$creditConsume = company_detail_trade_request_consume_credit($requestMemberId, $insertId, $targetKedcd, $tradeType, $creditAmount);
	if (empty($creditConsume['success'])) {
		sql_query("delete from trade_request where tr_no='" . (int)$insertId . "' and credit_used=0", false);
		company_detail_trade_request_log('credit_consume_failed', array(
			'tr_no' => $insertId,
			'message' => $creditConsume['message'] ?? '',
		));
		company_detail_trade_request_json_response(false, $creditConsume['message'] ?? '크레딧 차감 중 오류가 발생했습니다.', array(
			'tr_no' => $insertId,
			'plan_required' => !empty($creditConsume['plan_required']),
		));
	}

	$creditUsedUpdated = sql_query("update trade_request set credit_used=1 where tr_no='" . (int)$insertId . "'", false);
	if (!$creditUsedUpdated) {
		company_detail_trade_request_log('credit_used_update_failed', array(
			'tr_no' => $insertId,
			'member_id' => $requestMemberId,
		));
		company_detail_trade_request_json_response(false, '거래 요청 상태 갱신 중 오류가 발생했습니다.');
	}

	company_detail_trade_request_log('request_created', array(
		'tr_no' => $insertId,
		'target_kedcd' => $targetKedcd,
		'trade_direction' => $tradeDirection,
		'trade_type' => $tradeType,
		'credit_amount' => $creditAmount,
		'credit_after' => $creditConsume['credit_after'] ?? null,
	));

	company_detail_trade_request_json_response(true, '거래 요청서가 저장되었습니다.', array(
		'tr_no' => $insertId,
		'credit_after' => $creditConsume['credit_after'] ?? null,
		'credit_amount' => $creditAmount,
		'unlock_detail' => true,
	));
}
