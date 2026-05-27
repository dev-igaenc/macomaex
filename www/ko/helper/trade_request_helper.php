<?php
if (!defined('_GNUBOARD_')) exit;

// 거래 요청 모달과 저장 로직에서 공통으로 쓰는 기본 문구
function trade_request_default_message() {
    return "안녕하세요. 귀사와의 거래 및 협업 가능성을 검토하고자 문의드립니다.\n상호 검토 가능한 분야가 있을 것으로 판단되며, 가능하실 경우\n채팅을 통해 상세 내용 협의를 진행하고 싶습니다.";
}

// company 테이블 row를 거래 요청 모달에서 쓰는 공통 payload 구조로 정리
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

// 로그인 회원의 사업자번호로 우리 회사 정보를 조회해 공통 payload로 반환
function trade_request_member_company_data($memberBzno) {
	$memberBzno = preg_replace('/[^0-9]/', '', (string)$memberBzno);
	if ($memberBzno === '') return array();

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
			HPAGE_URL
		FROM company
		WHERE BZNO = '" . sql_escape_string($memberBzno) . "'
		LIMIT 1
	");
	if (empty($company['KEDCD'])) return array();

	$industry = sql_fetch("
		SELECT k_code_name
		FROM kssc
		WHERE k_code = '" . sql_escape_string($company['BZC_CD']) . "'
		LIMIT 1
	");

	return trade_request_company_payload($company, $industry['k_code_name'] ?? '');
}
