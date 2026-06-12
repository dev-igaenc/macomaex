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
		'kedcd' => trim((string)($companyRow['KEDCD'] ?? '')),
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

function trade_request_company_payload_from_kodata_json($jsonText, $expectedBzno = '') {
	$comInfo = json_decode((string)$jsonText);
	$intr = macomaex_kodata_find_intr($comInfo, $expectedBzno);
	if (!is_object($intr)) return array();

	$founded = '';
	$estbDt = macomaex_kodata_scalar($intr->estb_dt ?? '');
	if ($estbDt !== '' && strlen($estbDt) >= 4) {
		$founded = substr($estbDt, 0, 4) . '년';
	}

	$corpType = '';
	$enpTyp = macomaex_kodata_scalar($intr->enp_typ ?? '');
	if ($enpTyp !== '' && isset($GLOBALS['enp_typt'][$enpTyp])) {
		$corpType = $GLOBALS['enp_typt'][$enpTyp];
	}

	$bzno = trade_request_normalize_bzno(macomaex_kodata_scalar($intr->bzno ?? ''));
	if ($bzno === '') {
		$bzno = trade_request_normalize_bzno(macomaex_kodata_scalar($intr->bsno ?? ''));
	}
	$companyName = macomaex_kodata_scalar($intr->enp_nm_trd ?? '');
	if ($companyName === '') $companyName = macomaex_kodata_scalar($intr->enp_nm ?? '');
	$laborerSum = macomaex_kodata_scalar($intr->laborer_sum ?? '');

	return array(
		'kedcd' => macomaex_kodata_scalar($intr->kedcd ?? ''),
		'company' => $companyName,
		'bzno' => $bzno !== '' ? licenseNum($bzno) : '',
		'founded' => $founded,
		'employees' => $laborerSum !== '' ? number_format((float)$laborerSum) . '명' : '',
		'corpType' => $corpType,
		'industry' => macomaex_kodata_scalar($intr->bzc_nm ?? ''),
		'product' => macomaex_kodata_scalar($intr->mpd ?? ''),
		'homepage' => macomaex_kodata_scalar($intr->hpage_url ?? ''),
	);
}

// 로그인 회원의 사업자번호로 우리 회사 정보를 조회해 공통 payload로 반환
function trade_request_member_company_data($memberBzno, &$diagnostics = null) {
	$memberBzno = preg_replace('/[^0-9]/', '', (string)$memberBzno);
	$diagnostics = array(
		'status' => 'miss',
		'reason' => 'company_not_found',
	);
	if ($memberBzno === '') {
		$diagnostics['status'] = 'fail';
		$diagnostics['reason'] = 'invalid_bzno';
		return array();
	}

	$companyResult = sql_query("
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
	", false);
	if (!$companyResult) {
		$diagnostics['status'] = 'fail';
		$diagnostics['reason'] = 'company_query_failed';
		return array();
	}

	$company = sql_fetch_array($companyResult);
	if (empty($company['KEDCD'])) return array();
	$diagnostics['status'] = 'hit';
	$diagnostics['reason'] = 'company_found';

	$industry = sql_fetch("
		SELECT k_code_name
		FROM kssc
		WHERE k_code = '" . sql_escape_string($company['BZC_CD']) . "'
		LIMIT 1
	", false);

	return trade_request_company_payload($company, $industry['k_code_name'] ?? '');
}

function trade_request_normalize_bzno($value) {
	return preg_replace('/[^0-9]/', '', (string)$value);
}

function trade_request_normalize_phone($value) {
	return preg_replace('/[^0-9]/', '', (string)$value);
}

function trade_request_bzno_resolve_log($priority, $source, $status, $bzno, $context = array()) {
	$payload = array_merge(array(
		'request_id' => trim((string)($GLOBALS['trade_request_bzno_resolve_request_id'] ?? '')),
		'priority' => (int)$priority,
		'source' => trim((string)$source),
		'status' => trim((string)$status),
		'bzno' => trade_request_normalize_bzno($bzno),
	), is_array($context) ? $context : array());

	error_log('[trade_request_bzno_resolve] ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

// 거래 요청용 기업정보를 최근 캐시, KODATA, 전체 캐시, company 테이블 순서로 조회한다.
function trade_request_resolve_company_data_by_bzno($bzno, $options = array()) {
	$bzno = trade_request_normalize_bzno($bzno);
	$persist = !empty($options['persist']);
	$resolved = macomaex_kodata_resolve_company($bzno, array(
		'persist_company' => $persist,
		'context' => 'trade_request_modal',
		'connect_timeout' => 3,
		'timeout' => 10,
	));
	if (empty($resolved['success'])) {
		trade_request_bzno_resolve_log(0, 'all_sources', 'fail', $bzno, array(
			'persist' => $persist,
			'reason' => $resolved['error_code'] ?? 'all_fallbacks_exhausted',
		));
		return array(
			'success' => false,
			'message' => $resolved['message'] ?? '입력하신 사업자번호로 기업 정보를 확인하지 못했습니다. 사업자번호를 다시 확인하거나 잠시 후 다시 시도해 주세요.',
		);
	}

	$source = trim((string)($resolved['source'] ?? ''));
	$payload = array();
	if ($persist || $source === 'company') {
		$payload = trade_request_member_company_data($bzno);
	} else if (!empty($resolved['json'])) {
		$payload = trade_request_company_payload_from_kodata_json($resolved['json'], $bzno);
		if (empty($payload['bzno'])) $payload['bzno'] = licenseNum($bzno);
	}
	if (empty($payload['company'])) {
		return array('success' => false, 'message' => '기업 정보 응답을 화면에 표시하지 못했습니다.');
	}

	trade_request_bzno_resolve_log(0, $source, 'success', $bzno, array('persist' => $persist));
	return array(
		'success' => true,
		'company' => $payload,
		'bzno' => $bzno,
		'source' => $source,
		'cache_saved' => $resolved['cache_saved'] ?? null,
		'fallback_error_code' => $resolved['fallback_error_code'] ?? '',
	);
}
