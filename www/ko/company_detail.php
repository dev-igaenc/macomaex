<?php
include_once("./_common.php");

$favorite_css_ver = file_exists(__DIR__ . '/css/favorite.css') ? filemtime(__DIR__ . '/css/favorite.css') : time();
$favorite_js_ver = file_exists(__DIR__ . '/js/favorite.js') ? filemtime(__DIR__ . '/js/favorite.js') : time();
$defaultMessage = '담당자님, 안녕하세요. 귀사를 확인하고 연락드립니다. 신규 거래처를 검토 중이며, 귀사와 적합한 경우 거래 진행을 희망합니다. 상세 조건은 협의를 통해 논의하고자 합니다.';

// badge 퍼센트 포맷 변환
function format_badge_percent_detail($value) {
	if ($value === null || $value === '') return '';
	$str = trim((string)$value);
	if ($str === '') return '';
	if (!is_numeric($str)) return $str;

	$num = (float)$str;
	if ($num == (int)$num) return (string)((int)$num);
	return rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
}

// ajax 요청 응답 JSON 형태로 내려주는 함수
function trade_request_json_response($success, $message, $extra = array()) {
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(array_merge(array(
		'success' => $success,
		'message' => $message,
	), $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

// 로그용
function trade_request_log($stage, $data = array()) {
	$payload = array(
		'stage' => $stage,
		'uri' => $_SERVER['REQUEST_URI'] ?? '',
		'method' => $_SERVER['REQUEST_METHOD'] ?? '',
		'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
		'data' => $data,
	);

	error_log('[trade_request] ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function trade_request_company_payload($companyRow, $industryName = '') {
	$founded = '';
	$establishedAt = trim((string)($companyRow['ESTB_DT'] ?? ''));
	if ($establishedAt !== '' && strlen($establishedAt) >= 4) {
		$founded = substr($establishedAt, 0, 4) . '년';
	}

	$companyType = '';
	if (!empty($companyRow['ENP_TYP']) && isset($GLOBALS['enp_typt'][$companyRow['ENP_TYP']])) {
		$companyType = $GLOBALS['enp_typt'][$companyRow['ENP_TYP']];
	}

	return array(
		'company' => trim((string)($companyRow['ENP_NM'] ?? '')),
		'bzno' => !empty($companyRow['BZNO']) ? licenseNum($companyRow['BZNO']) : '',
		'founded' => $founded,
		'employees' => !empty($companyRow['LABORER_SUM']) ? number_format((float)$companyRow['LABORER_SUM']) . '명' : '',
		'corpType' => $companyType,
		'industry' => trim((string)$industryName),
		'product' => trim((string)($companyRow['PD_NM'] ?? '')),
		'homepage' => trim((string)($companyRow['HPAGE_URL'] ?? '')),
	);
}

// 상세 페이지 해제 상태에서 값이 비어 있으면 공통 대체 문구로 보여준다.
function trade_request_display_value($value) {
	$value = trim((string)$value);
	return ($value !== '') ? $value : '정보 준비중';
}

// 거래 요청 크레딧 차감 + 이력 적재 공통 처리
function trade_request_consume_credit($memberId, $trNo, $targetKedcd, $tradeType, $creditAmount) {
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
	$memoPrefix = ($tradeType === 'TM') ? '거래 요청(TM)' : '거래 요청(BASIC)';
	$memo = addslashes($memoPrefix . " 사용({$creditAmount} 차감)");

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'trade_request_insert') {
	trade_request_log('request_received', array(
		'member_id' => $member['mb_id'] ?? '',
		'post_keys' => array_keys($_POST),
	));

	if (empty($member['mb_id'])) {
		trade_request_log('validation_failed', array('reason' => 'member_not_logged_in'));
		trade_request_json_response(false, '로그인 후 이용해 주세요.');
	}

	$targetKedcd = isset($_POST['target_kedcd']) ? preg_replace('/[^0-9]/', '', (string)$_POST['target_kedcd']) : '';
	$tradeDirectionRaw = isset($_POST['trade_direction']) ? trim((string)$_POST['trade_direction']) : '';
	$tradePurposeRaw = isset($_POST['trade_purpose']) ? $_POST['trade_purpose'] : array();
	$desiredItem = isset($_POST['desired_item']) ? trim((string)$_POST['desired_item']) : '';
	$messageContent = isset($_POST['message_content']) ? trim((string)$_POST['message_content']) : '';
	$tradeTypeRaw = isset($_POST['trade_type']) ? trim((string)$_POST['trade_type']) : '';

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
		trade_request_log('validation_failed', array('reason' => 'invalid_target_kedcd', 'target_kedcd' => $targetKedcd));
		trade_request_json_response(false, '대상 기업 정보가 올바르지 않습니다.');
	}
	if (!isset($directionMap[$tradeDirectionRaw])) {
		trade_request_log('validation_failed', array('reason' => 'invalid_trade_direction', 'trade_direction' => $tradeDirectionRaw));
		trade_request_json_response(false, '거래 방향을 다시 선택해 주세요.');
	}
	if (!is_array($tradePurposeRaw) || empty($tradePurposeRaw)) {
		trade_request_log('validation_failed', array('reason' => 'trade_purpose_empty'));
		trade_request_json_response(false, '거래 목적을 1개 이상 선택해 주세요.');
	}
	if ($desiredItem === '') {
		trade_request_log('validation_failed', array('reason' => 'desired_item_empty'));
		trade_request_json_response(false, '거래 희망 품목을 입력해 주세요.');
	}
	if (mb_strlen($desiredItem, 'UTF-8') > 255) {
		trade_request_log('validation_failed', array('reason' => 'desired_item_too_long'));
		trade_request_json_response(false, '거래 희망 품목은 255자 이내로 입력해 주세요.');
	}
	if ($messageContent === '') {
		$messageContent = $defaultMessage;
	}
	if (mb_strlen($messageContent, 'UTF-8') > 500) {
		trade_request_log('validation_failed', array('reason' => 'message_too_long'));
		trade_request_json_response(false, '추가 문의 사항은 500자 이내로 입력해 주세요.');
	}
	if (!isset($tradeTypeMap[$tradeTypeRaw])) {
		trade_request_log('validation_failed', array('reason' => 'invalid_trade_type', 'trade_type' => $tradeTypeRaw));
		trade_request_json_response(false, '거래 타입을 다시 선택해 주세요.');
	}

	$targetCompany = sql_fetch("
		SELECT KEDCD, ENP_NM, BZNO
		FROM company
		WHERE KEDCD = '" . sql_escape_string($targetKedcd) . "'
		LIMIT 1
	");
	if (empty($targetCompany['KEDCD']) || empty($targetCompany['BZNO'])) {
		trade_request_log('validation_failed', array('reason' => 'target_company_not_found', 'target_kedcd' => $targetKedcd));
		trade_request_json_response(false, '대상 기업 정보가 올바르지 않습니다.');
	}

	$requestMemberId = trim((string)($member['mb_id'] ?? ''));
	$requestCompanyName = trim((string)($member['mb_name'] ?? ''));
	$requestBzno = preg_replace('/[^0-9]/', '', (string)($member['mb_4'] ?? ''));
	$targetCompanyName = trim((string)($targetCompany['ENP_NM'] ?? ''));
	$targetBzno = preg_replace('/[^0-9]/', '', (string)($targetCompany['BZNO'] ?? ''));

	if ($requestMemberId === '' || $requestCompanyName === '' || $requestBzno === '') {
		trade_request_log('validation_failed', array(
			'reason' => 'invalid_request_member_info',
			'request_mb_id' => $requestMemberId,
			'request_company_name' => $requestCompanyName,
			'request_bzno' => $requestBzno,
		));
		trade_request_json_response(false, '회원 정보가 올바르지 않습니다. 관리자에게 문의해 주세요.');
	}
	if ($requestBzno === $targetBzno) {
		trade_request_log('validation_failed', array('reason' => 'same_company_request'));
		trade_request_json_response(false, '자기 자신의 기업에는 거래 요청을 보낼 수 없습니다.');
	}

	$mappedPurposes = array();
	$seenPurpose = array();
	foreach ($tradePurposeRaw as $purposeId) {
		$purposeId = trim((string)$purposeId);
		if ($purposeId === '' || isset($seenPurpose[$purposeId])) continue;
		$seenPurpose[$purposeId] = true;

		if (!isset($purposeMap[$tradeDirectionRaw][$purposeId])) {
			trade_request_log('validation_failed', array(
				'reason' => 'invalid_trade_purpose',
				'direction' => $tradeDirectionRaw,
				'purpose_id' => $purposeId,
			));
			trade_request_json_response(false, '거래 목적 데이터가 올바르지 않습니다.');
		}

		$mappedPurposes[] = $purposeMap[$tradeDirectionRaw][$purposeId];
	}
	if (empty($mappedPurposes)) {
		trade_request_log('validation_failed', array('reason' => 'mapped_trade_purpose_empty'));
		trade_request_json_response(false, '거래 목적을 다시 선택해 주세요.');
	}

	$tradeDirection = $directionMap[$tradeDirectionRaw];
	$tradeType = $tradeTypeMap[$tradeTypeRaw]['type'];
	$creditAmount = (int)$tradeTypeMap[$tradeTypeRaw]['amount'];
	$tradePurposeJson = json_encode(array_values($mappedPurposes), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

	if ($tradePurposeJson === false) {
		trade_request_log('validation_failed', array('reason' => 'trade_purpose_json_encode_failed'));
		trade_request_json_response(false, '거래 목적 처리 중 오류가 발생했습니다.');
	}

	$creditStatus = sql_fetch("select uid, matching_able_cnt from g5_membership_status where uid='" . sql_escape_string($requestMemberId) . "' order by no desc limit 1");
	$remainCredit = isset($creditStatus['matching_able_cnt']) ? (int)$creditStatus['matching_able_cnt'] : 0;
	if (empty($creditStatus['uid']) || $remainCredit < $creditAmount) {
		trade_request_log('validation_failed', array(
			'reason' => 'credit_not_enough',
			'remain_credit' => $remainCredit,
			'required' => $creditAmount,
		));
		trade_request_json_response(false, '매칭 가능 크레딧이 부족합니다. (필요: ' . $creditAmount . ')', array(
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
		trade_request_log('insert_failed', array('sql' => $insertSql));
		trade_request_json_response(false, '거래 요청서 저장 중 오류가 발생했습니다.');
	}

	$insertId = sql_insert_id();
	$creditConsume = trade_request_consume_credit($requestMemberId, $insertId, $targetKedcd, $tradeType, $creditAmount);
	if (empty($creditConsume['success'])) {
		trade_request_log('credit_consume_failed', array(
			'tr_no' => $insertId,
			'message' => $creditConsume['message'] ?? '',
		));
		trade_request_json_response(false, $creditConsume['message'] ?? '크레딧 차감 중 오류가 발생했습니다.', array(
			'tr_no' => $insertId,
			'plan_required' => !empty($creditConsume['plan_required']),
		));
	}

	sql_query("update trade_request set credit_used=1 where tr_no='" . (int)$insertId . "'", false);

	trade_request_log('request_created', array(
		'tr_no' => $insertId,
		'target_kedcd' => $targetKedcd,
		'trade_direction' => $tradeDirection,
		'trade_type' => $tradeType,
		'credit_amount' => $creditAmount,
		'credit_after' => $creditConsume['credit_after'] ?? null,
	));

	trade_request_json_response(true, '거래 요청서가 저장되었습니다.', array(
		'tr_no' => $insertId,
		'credit_after' => $creditConsume['credit_after'] ?? null,
		'credit_amount' => $creditAmount,
		'unlock_detail' => true,
	));
}

// 진입 시 암호화 기업코드 복호화 후 조회하기 위함
$cd = isset($_GET['cd']) ? trim((string)$_GET['cd']) : '';
$kedcd = $cd ? simple_crypt($cd, 'd') : '';

// 기업코드 값 최소 검증
if (!preg_match('/^\d{10}$/', $kedcd)) {
	alert('잘못된 접근입니다.', '/ko/matching_list.php');
}

// 대상 기업 기본 정보 조회
$company = sql_fetch("
	SELECT
		KEDCD,
		ENP_NM,
		BZNO,
		BZC_CD,
		ESTB_DT,
		LABORER_SUM,
		ENP_TYP,
		PD_NM,
		LOC_ADDRA,
		LOC_ADDRB,
		HPAGE_URL
	FROM company
	WHERE KEDCD = '{$kedcd}'
");

if (empty($company['KEDCD'])) {
	alert('기업 정보를 찾을 수 없습니다.', '/ko/matching_list.php');
}

$favorite_company = array();
if (!empty($member['mb_id'])) {
	$favorite_company = sql_fetch("
		SELECT ms_id
		FROM g5_favorite
		WHERE mb_id = '" . sql_escape_string($member['mb_id']) . "'
		  AND KEDCD = '" . sql_escape_string($company['KEDCD']) . "'
	");
}

// 기업의 업종코드(BZC_CD) 기준 업종명 조회
$industry = array();
if (!empty($company['BZC_CD'])) {
	$industry = sql_fetch("
		SELECT k_code, k_code_name
		FROM kssc
		WHERE k_code = '{$company['BZC_CD']}'
	");
}

// 해당 기업코드 기준 성장성 ~ 재무상태 조회
$score = sql_fetch("
	SELECT
		KEDCD,
		total_grade_22,
		growth_score_5,
		profit_score_5,
		stability_score_5,
		badge_growth,
		badge_stability,
		badge_venture,
		age_score_5,
		emp_score_5,
		final_total_score_5
	FROM company_score_final_dedup
	WHERE KEDCD = '{$kedcd}'
");

// 설립일자(ESTB_DT) : YYYYMMDD -> YYYY년 변환
$foundedYear = '';
if (!empty($company['ESTB_DT']) && strlen($company['ESTB_DT']) >= 4) {
	$foundedYear = substr($company['ESTB_DT'], 0, 4) . '년';
}

// extend/user.config 의 $enp_typt 활용 -> 기업유형
$companyType = '';
if (!empty($company['ENP_TYP']) && isset($enp_typt[$company['ENP_TYP']])) {
	$companyType = $enp_typt[$company['ENP_TYP']];
}

// 거래 요청서 기본 안내 문구는 PHP/JS에서 공통으로 사용
$defaultMessage = '담당자님, 안녕하세요. 귀사를 확인하고 연락드립니다. 신규 거래처를 검토 중이며, 귀사와 적합한 경우 거래 진행을 희망합니다. 상세 조건은 협의를 통해 논의하고자 합니다.';

// 현재 로그인 회원이 이 기업에 대해 크레딧 사용 완료한 거래 요청이 있으면 마스킹 해제
$isDetailUnlocked = false;
if (!empty($member['mb_id'])) {
	$unlockRow = sql_fetch("
		SELECT tr_no
		FROM trade_request
		WHERE request_mb_id = '" . sql_escape_string($member['mb_id']) . "'
		  AND target_kedcd = '" . sql_escape_string($company['KEDCD']) . "'
		  AND credit_used = 1
		ORDER BY tr_no DESC
		LIMIT 1
	");

	$isDetailUnlocked = !empty($unlockRow['tr_no']);
}

$companyTitleDisplay = trade_request_display_value($company['ENP_NM'] ?? '');
$companyAddressDisplay = trade_request_display_value(trim(($company['LOC_ADDRA'] ?? '') . ' ' . ($company['LOC_ADDRB'] ?? '')));
$companyHomepageDisplay = trade_request_display_value($company['HPAGE_URL'] ?? '');

$requesterCompanyData = array();
if (!empty($member['mb_id'])) {
	$requesterBzno = preg_replace('/[^0-9]/', '', (string)($member['mb_4'] ?? ''));
	if ($requesterBzno !== '') {
		$requesterCompany = sql_fetch("
			SELECT
				KEDCD,
				ENP_NM,
				BZNO,
				BZC_CD,
				ESTB_DT,
				LABORER_SUM,
				ENP_TYP,
				PD_NM,
				HPAGE_URL
			FROM company
			WHERE BZNO = '" . sql_escape_string($requesterBzno) . "'
			LIMIT 1
		");
		if (!empty($requesterCompany['KEDCD'])) {
			$requesterIndustry = sql_fetch("
				SELECT k_code_name
				FROM kssc
				WHERE k_code = '" . sql_escape_string($requesterCompany['BZC_CD']) . "'
				LIMIT 1
			");
			$requesterCompanyData = trade_request_company_payload($requesterCompany, $requesterIndustry['k_code_name'] ?? '');
		}
	}
}

// 프론트 렌더링과 마스킹 분기에 필요한 상세 페이지 데이터 구조를 한 번에 정리해 JS로 내려준다.
$companyDetailData = array(
	'kedcd' => $company['KEDCD'] ?? '',
	'title' => $company['ENP_NM'] ?? '',
	'title_display' => $companyTitleDisplay,
	'sub' => $industry['k_code_name'] ?? '',
	'tags' => array(),
	'founded' => $foundedYear,
	'employees' => !empty($company['LABORER_SUM']) ? number_format((float)$company['LABORER_SUM']) . '명' : '',
	'corp_type' => $companyType,
	'product_line' => $company['PD_NM'] ?? '',
	'credit' => $score['total_grade_22'] ?? '',
	'bzno' => $company['BZNO'] ?? '',
	'defaultRequestMessage' => $defaultMessage,
	'address' => trim(($company['LOC_ADDRA'] ?? '') . ' ' . ($company['LOC_ADDRB'] ?? '')),
	'address_display' => $companyAddressDisplay,
	'homepage' => $company['HPAGE_URL'] ?? '',
	'homepage_display' => $companyHomepageDisplay,
	'is_unlocked' => $isDetailUnlocked,
	'badge_growth' => format_badge_percent_detail($score['badge_growth'] ?? null),
	'badge_stability' => format_badge_percent_detail($score['badge_stability'] ?? null),
	'badge_venture' => strtoupper(trim((string)($score['badge_venture'] ?? ''))),
	'grow' => isset($score['growth_score_5']) ? (float)$score['growth_score_5'] : 0,
	'profit' => isset($score['profit_score_5']) ? (float)$score['profit_score_5'] : 0,
	'stable' => isset($score['stability_score_5']) ? (float)$score['stability_score_5'] : 0,
	'ageScore' => isset($score['age_score_5']) ? (float)$score['age_score_5'] : 0,
	'empScore' => isset($score['emp_score_5']) ? (float)$score['emp_score_5'] : 0,
	'score' => isset($score['final_total_score_5']) ? (float)$score['final_total_score_5'] : 0,
);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>기업 상세 — 매커멕스</title>
	<script>try { if (window.self !== window.top) document.documentElement.classList.add('in-iframe'); } catch(e) {}</script>
	<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="css/design-tokens.css" />
	<link rel="stylesheet" href="css/badges.css" />
	<link rel="stylesheet" href="css/favorite.css?ver=<?php echo $favorite_css_ver; ?>">
	<link rel="stylesheet" href="css/confirm-modal.css">
	<link rel="stylesheet" href="css/share.css">
	<link rel="stylesheet" href="css/verify-modal.css" />
	<link rel="stylesheet" href="css/ai-loading.css" />
	<link rel="stylesheet" href="css/company-detail-left.css" />
	<link rel="stylesheet" href="css/company-detail.css" />
	<link rel="stylesheet" href="css/match-request-modal.css" />
	<link rel="stylesheet" href="css/top-nav.css" />
</head>
<body>
<div id="detailWrap">

	<!-- Header -->
	<div id="site-header" data-variant="search"></div>
	<script>
	// In iframe mode, prevent header.js from injecting header and setting body paddingTop
	if (document.documentElement.classList.contains('in-iframe')) {
		var _sh = document.getElementById('site-header');
		if (_sh) _sh.removeAttribute('id');
	}
	</script>
	<script src="js/header.js"></script>
	<script>
	if (document.documentElement.classList.contains('in-iframe')) {
		document.body.style.paddingTop = '0';
		document.documentElement.style.setProperty('--header-h', '0px');
		if (new URLSearchParams(window.location.search).get('nocta') === '1') {
			document.documentElement.classList.add('no-cta');
		}
	}
	</script>


	<!-- Verify modal -->
	<div class="modal_verify_overlay" id="modal_verify_overlay" aria-hidden="true">
		<div class="modal_verify" role="dialog" aria-labelledby="modal_verify_company" aria-modal="true">
			<button type="button" class="modal_verify_close" id="modal_verify_btn_close" aria-label="닫기">&times;</button>
			<div class="modal_verify_icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
			</div>
			<h2 class="modal_verify_company" id="modal_verify_company">(주)이가이엔씨</h2>
			<p class="modal_verify_text">귀사는 데이터베이스 및 온라인 정보 제공업<br>법인기업으로 확인되었습니다.</p>
			<p class="modal_verify_text2">본 서비스는 귀사와 동일·유사 업종의 실제 거래 데이터를 분석해 적합한 거래처를 추천해드립니다.</p>
			<hr class="modal_verify_hr">
			<p class="modal_verify_guide">추천 결과를 확인해보세요.</p>
			<button type="button" class="modal_verify_btn" id="modal_verify_btn_ai">
				<span class="sparkle" aria-hidden="true"><svg viewBox="0 0 24 24"><use href="#icon-magic-wand"/></svg></span>
				거래처 확인하기
			</button>
		</div>
	</div>
	<!-- AI guide modal -->
	<div class="modal_verify_overlay" id="modal_ai_guide_overlay" aria-hidden="true">
		<div class="modal_verify" role="dialog" aria-labelledby="modal_ai_guide_title" aria-modal="true">
			<button type="button" class="modal_verify_close" id="modal_ai_guide_btn_close" aria-label="닫기">&times;</button>
			<div class="modal_verify_icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M12 17v-5M12 8h.01M4 12a8 8 0 1 0 16 0 8 8 0 1 0-16 0z"/></svg>
			</div>
			<h2 class="modal_verify_company" id="modal_ai_guide_title">AI 추천 안내</h2>
			<p class="modal_verify_text" id="modal_ai_guide_text">사업자 번호를 입력하고 AI 추천받기를 눌러주세요.</p>
			<p class="modal_verify_text2" id="modal_ai_guide_text2">AI 분석을 통해 업종/거래 데이터를 기반으로 적합한 거래처 판매처를 추천해드립니다.</p>
			<button type="button" class="modal_verify_btn" id="modal_ai_guide_btn_ok">확인했어요</button>
		</div>
	</div>

	<div class="page-wrap">
		<nav class="top-nav">
			<a class="btn-back" href="javascript:history.back();"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg> 돌아가기</a>
			<div class="top-nav-actions">
				<button type="button" class="btn-match-cta-top" id="openTypeModal2">거래 요청하기</button>
				<button type="button" class="btn-favorite<?php echo !empty($favorite_company['ms_id']) ? ' is-active' : ''; ?>" data-favorite data-kedcd="<?php echo htmlspecialchars($company['KEDCD'], ENT_QUOTES, 'UTF-8'); ?>" title="관심기업 등록" aria-label="관심기업 등록">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
				</button>
				<button type="button" class="btn-topnav-icon" data-share title="공유하기" aria-label="공유하기">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
				</button>
			</div>
		</nav>

		<div class="layout">
			<!-- Left column: 기업 핵심 정보 -->
			<div class="card" id="leftCard"></div>

			<!-- Right column: 5각형 레이더 차트 -->
			<div class="right-stack" id="rightStack">
				<div class="right-section-card">
					<div class="right-section-title">기업 분석</div>
					<div class="detail-radar-card" id="detailRadarCard"></div>
				</div>
			</div>
		</div>
	</div>

	<?php include __DIR__ . '/modal/match_request_modal.php'; ?>

	<!-- floating bar removed -->

	<!-- AI loading overlay (inline from ai_loading.php) -->
	<div class="ai-loading_overlay" id="aiLoadingOverlay">
		<div class="ai-loading_card">
			<div class="ai-loading_spinner"></div>
			<h3 class="ai-loading_title">AI가 거래처를 분석하고 있습니다</h3>
			<div class="ai-loading_steps">
				<div class="ai-loading_step" data-step="0">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>업종 연관성 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="1">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>거래 네트워크 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="2">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>지역 근접성 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="3">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>규모 적합도 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="4">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>재무 건전성 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="5">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>기업 신뢰도 분석 중...</span>
				</div>
			</div>
			<div class="ai-loading_bar"><div class="ai-loading_bar_fill" id="aiLoadingBarFill"></div></div>
		</div>
	</div>
</div>

<!-- SVG icon definitions -->
<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
	<symbol id="icon-magic-wand" viewBox="0 0 24 24"><path fill="currentColor" d="M16.8 2.2l1.1 2.3 2.3 1.1-2.3 1.1-1.1 2.3-1.1-2.3-2.3-1.1 2.3-1.1 1.1-2.3zm3.2 2.8.6 1.1 1.1.6-1.1.6-.6 1.1-.6-1.1-1.1-.6 1.1-.6.6-1.1zM7.4 17.4 16.1 8.7l2.6 2.6-8.7 8.7-2.6-2.6zm-1 2.4 1.3 1.3-3.1 1 .8-2.9z"/></symbol>
</svg>

<script>
window.COMPANY_DETAIL_DATA = <?php echo json_encode($companyDetailData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
window.MATCH_REQUEST_CONTEXT = {
	endpoint: "/ko/company_detail.php",
	sourcePage: "company_detail",
	defaultRequestMessage: <?php echo json_encode($defaultMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	requesterCompany: <?php echo json_encode($requesterCompanyData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	target: {
		kedcd: <?php echo json_encode($company['KEDCD'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		company: <?php echo json_encode($company['ENP_NM'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		bzno: <?php echo json_encode(licenseNum($company['BZNO'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		founded: <?php echo json_encode($foundedYear, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		employees: <?php echo json_encode(!empty($company['LABORER_SUM']) ? number_format((float)$company['LABORER_SUM']) . '명' : '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		corpType: <?php echo json_encode($companyType, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		industry: <?php echo json_encode($industry['k_code_name'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		product: <?php echo json_encode($company['PD_NM'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
	}
};
</script>
<script src="js/badges.js"></script>
<script src="js/company-detail-left.js"></script>
<script src="js/company-detail.js"></script>
<script src="js/ai-loading.js"></script>
<script src="js/match-request-modal.js"></script>
<script src="js/favorite.js?ver=<?php echo $favorite_js_ver; ?>"></script>
<script src="js/share.js"></script>
<script src="js/confirm-modal.js"></script>
<script src="js/verify-modal.js"></script>
<!-- <script src="inc/auth-toggle.js"></script> -->
</body>
</html>
