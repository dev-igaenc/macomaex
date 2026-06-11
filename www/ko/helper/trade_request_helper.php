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

function trade_request_company_payload_from_kodata_json($jsonText) {
	$comInfo = json_decode((string)$jsonText);
	if (!is_object($comInfo) || empty($comInfo->CONTENTS->ENPintr0000)) return array();
	$intr = $comInfo->CONTENTS->ENPintr0000;

	$founded = '';
	$estbDt = trim((string)($intr->estb_dt ?? ''));
	if ($estbDt !== '' && strlen($estbDt) >= 4) {
		$founded = substr($estbDt, 0, 4) . '년';
	}

	$corpType = '';
	$enpTyp = trim((string)($intr->enp_typ ?? ''));
	if ($enpTyp !== '' && isset($GLOBALS['enp_typt'][$enpTyp])) {
		$corpType = $GLOBALS['enp_typt'][$enpTyp];
	}

	$bzno = trade_request_normalize_bzno((string)($intr->bzno ?? ''));
	if ($bzno === '') {
		$bzno = trade_request_normalize_bzno((string)($intr->bsno ?? ''));
	}

	return array(
		'kedcd' => trim((string)($intr->kedcd ?? '')),
		'company' => trim((string)($intr->enp_nm_trd ?? $intr->enp_nm ?? '')),
		'bzno' => $bzno !== '' ? licenseNum($bzno) : '',
		'founded' => $founded,
		'employees' => !empty($intr->laborer_sum) ? number_format((float)$intr->laborer_sum) . '명' : '',
		'corpType' => $corpType,
		'industry' => trim((string)($intr->bzc_nm ?? '')),
		'product' => trim((string)($intr->mpd ?? '')),
		'homepage' => trim((string)($intr->hpage_url ?? '')),
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

function trade_request_fetch_kodata_company_json($bzno) {
	$bzno = trade_request_normalize_bzno($bzno);
	if (!preg_match('/^[0-9]{10}$/', $bzno)) {
		return array('success' => false, 'error_code' => 'invalid_bzno', 'message' => '사업자등록번호 형식을 확인해 주세요.');
	}

	$tokenUrl = "https://218.55.37.214:6059/enpinfo?userid=igaenconl&process=S&bzno={$bzno}&pidagryn=Y&jmno=ig001&format=XML&Length=10000";
	$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)';
	$cookieNm = G5_DATA_PATH . '/cookie_trade_request.txt';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $tokenUrl);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieNm);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieNm);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	// 외부 API가 지연되어도 현재 요청 안에서 3, 4순위 fallback을 수행할 수 있게 제한한다.
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$raw = curl_exec($ch);
	$curlError = curl_error($ch);
	$curlErrno = curl_errno($ch);
	curl_close($ch);

	if ($raw === false) {
		if (function_exists('macomaex_kodata_log')) {
			macomaex_kodata_log('curl_failed', array(
				'context' => 'trade_request_modal',
				'bzno' => $bzno,
				'curl_errno' => $curlErrno,
				'curl_error' => $curlError,
			));
		}
		return array(
			'success' => false,
			'error_code' => 'kodata_curl_failed',
			'curl_errno' => $curlErrno,
			'curl_error' => $curlError,
			'message' => '기업 정보 조회 중 통신 오류가 발생했습니다.',
		);
	}

	$parsed = function_exists('macomaex_parse_kodata_xml')
		? macomaex_parse_kodata_xml($raw, array('context' => 'trade_request_modal', 'bzno' => $bzno))
		: array('success' => false, 'error_message' => 'KODATA 파서 함수를 찾을 수 없습니다.');

	if (empty($parsed['success'])) {
		return array(
			'success' => false,
			'error_code' => $parsed['error_code'] ?? 'kodata_parse_failed',
			'message' => $parsed['error_message'] ?? '기업 정보 파싱 중 오류가 발생했습니다.',
		);
	}

	$xml = $parsed['xml'];
	if ((string)($xml->HEADER->errcd ?? '') !== '00') {
		return array(
			'success' => false,
			'error_code' => 'kodata_response_error',
			'kodata_error_code' => (string)($xml->HEADER->errcd ?? ''),
			'message' => (string)($xml->HEADER->errctt ?? '기업 정보 조회에 실패했습니다.'),
		);
	}

	return array(
		'success' => true,
		'json' => $parsed['json'],
	);
}

// 사업자번호의 최신 정상 KODATA 캐시를 조회한다.
// recentOnly=true이면 최근 1개월 이내 캐시만 대상으로 하며, 손상된 JSON은 건너뛴다.
function trade_request_find_company_api_log($bzno, $recentOnly = false, &$diagnostics = null) {
	$bzno = trade_request_normalize_bzno($bzno);
	$diagnostics = array(
		'status' => 'miss',
		'reason' => 'cache_not_found',
		'rows_scanned' => 0,
	);
	if (!preg_match('/^[0-9]{10}$/', $bzno)) {
		$diagnostics['status'] = 'fail';
		$diagnostics['reason'] = 'invalid_bzno';
		return array();
	}

	$recentCondition = $recentOnly
		? " AND c_datetime >= date_sub(now(), interval 1 month)"
		: '';

	$result = sql_query("
		SELECT *
		FROM company_api_log
		WHERE c_bizno='" . sql_escape_string($bzno) . "'
			AND c_content!=''
			{$recentCondition}
		ORDER BY c_datetime DESC
	", false);
	if (!$result) {
		$diagnostics['status'] = 'fail';
		$diagnostics['reason'] = 'cache_query_failed';
		return array();
	}

	while ($row = sql_fetch_array($result)) {
		$diagnostics['rows_scanned']++;
		$payload = trade_request_company_payload_from_kodata_json($row['c_content'] ?? '');
		if (!empty($payload['company'])) {
			$diagnostics['status'] = 'hit';
			$diagnostics['reason'] = 'valid_cache_found';
			return $row;
		}
	}

	if ($diagnostics['rows_scanned'] > 0) {
		$diagnostics['reason'] = 'valid_cache_not_found';
	}
	return array();
}

// KODATA 실시간 조회 결과가 모달에서 사용할 수 있는 정상 데이터인 경우 캐시에 저장한다.
function trade_request_save_company_api_log($bzno, $jsonText) {
	$bzno = trade_request_normalize_bzno($bzno);
	$jsonText = trim((string)$jsonText);
	if (!preg_match('/^[0-9]{10}$/', $bzno) || $jsonText === '') return false;

	$payload = trade_request_company_payload_from_kodata_json($jsonText);
	if (empty($payload['company'])) return false;

	return (bool)sql_query("INSERT INTO company_api_log SET c_bizno='" . sql_escape_string($bzno) . "', c_kedcd='" . sql_escape_string($payload['kedcd'] ?? '') . "', c_content='" . sql_escape_string($jsonText) . "', c_datetime=now(), c_ip='" . sql_escape_string($_SERVER['REMOTE_ADDR'] ?? '') . "'", false);
}

function trade_request_upsert_company_from_kodata_json($bzno, $jsonText) {
	$bzno = trade_request_normalize_bzno($bzno);
	$jsonText = (string)$jsonText;
	$comInfo = json_decode($jsonText);
	if (!is_object($comInfo) || empty($comInfo->CONTENTS->ENPintr0000)) {
		return false;
	}

	$intr = $comInfo->CONTENTS->ENPintr0000;
	$kedcd = trim((string)($intr->kedcd ?? ''));
	if ($kedcd === '') return false;

	$bzcCd = trim((string)($intr->bzc_cd ?? ''));
	$estbDt = trim((string)($intr->estb_dt ?? ''));
	$laborerSum = trim((string)($intr->laborer_sum ?? ''));
	$enpTyp = trim((string)($intr->enp_typ ?? ''));
	$pdNm = trim((string)($intr->mpd ?? ''));
	$hpageUrl = trim((string)($intr->hpage_url ?? ''));
	$enpNm = addslashes(trim((string)($intr->enp_nm ?? '')));
	$enpNmTrd = addslashes(trim((string)($intr->enp_nm_trd ?? '')));
	$locAddra = addslashes(trim((string)($intr->loc_addra ?? '')));
	$locAddrb = addslashes(trim((string)($intr->loc_addrb ?? '')));

	if ($enpNmTrd === '') $enpNmTrd = $enpNm;

	$exists = sql_fetch("SELECT KEDCD FROM company WHERE BZNO='" . sql_escape_string($bzno) . "' LIMIT 1");
	if (!empty($exists['KEDCD'])) return true;

	$sql = "INSERT INTO company SET
		KEDCD='" . sql_escape_string($kedcd) . "',
		ENP_NM='{$enpNm}',
		ENP_NM_TRD='{$enpNmTrd}',
		BZNO='" . sql_escape_string($bzno) . "',
		BZC_CD='" . sql_escape_string($bzcCd) . "',
		ESTB_DT='" . sql_escape_string($estbDt) . "',
		LABORER_SUM='" . sql_escape_string($laborerSum) . "',
		ENP_TYP='" . sql_escape_string($enpTyp) . "',
		PD_NM='{$pdNm}',
		HPAGE_URL='" . sql_escape_string($hpageUrl) . "',
		LOC_ADDRA='{$locAddra}',
		LOC_ADDRB='{$locAddrb}',
		ext_1='API 입력'";

	return (bool)sql_query($sql, false);
}

// KODATA 또는 캐시 JSON을 공통 응답으로 변환하고, 최종 제출 단계에서는 company 테이블에 적재한다.
function trade_request_resolve_company_data_from_kodata_json($bzno, $jsonText, $persist, $source) {
	if ($persist) {
		if (!trade_request_upsert_company_from_kodata_json($bzno, $jsonText)) {
			return array('success' => false, 'message' => '기업 정보 저장에 실패했습니다. 잠시 후 다시 시도해 주세요.');
		}

		$payload = trade_request_member_company_data($bzno);
		if (empty($payload['company'])) {
			return array('success' => false, 'message' => '저장된 기업 정보를 확인하지 못했습니다. 잠시 후 다시 시도해 주세요.');
		}
	} else {
		$payload = trade_request_company_payload_from_kodata_json($jsonText);
		if (empty($payload['company'])) return array();
		if (empty($payload['bzno'])) {
			$payload['bzno'] = licenseNum($bzno);
		}
	}

	return array(
		'success' => true,
		'company' => $payload,
		'bzno' => $bzno,
		'source' => $source,
	);
}

// 거래 요청용 기업정보를 최근 캐시, KODATA, 전체 캐시, company 테이블 순서로 조회한다.
function trade_request_resolve_company_data_by_bzno($bzno, $options = array()) {
	$bzno = trade_request_normalize_bzno($bzno);
	// persist=false: 모달 확인용(미리보기만), persist=true: 최종 제출 성공 후 DB 확정 저장
	$persist = !empty($options['persist']);
	if (!preg_match('/^[0-9]{10}$/', $bzno)) {
		return array('success' => false, 'message' => '사업자등록번호 형식을 확인해 주세요.');
	}

	// 1) 최근 1개월 이내 KODATA 캐시
	$recentDiagnostics = array();
	$recentLog = trade_request_find_company_api_log($bzno, true, $recentDiagnostics);
	$recentJson = trim((string)($recentLog['c_content'] ?? ''));
	if ($recentJson !== '') {
		$resolved = trade_request_resolve_company_data_from_kodata_json($bzno, $recentJson, $persist, 'company_api_log_1m');
		if (!empty($resolved['success'])) {
			trade_request_bzno_resolve_log(1, 'company_api_log_1m', 'success', $bzno, array(
				'persist' => $persist,
				'rows_scanned' => (int)($recentDiagnostics['rows_scanned'] ?? 0),
			));
			return $resolved;
		}
		$recentDiagnostics['status'] = 'fail';
		$recentDiagnostics['reason'] = 'cache_payload_resolve_failed';
	}
	trade_request_bzno_resolve_log(1, 'company_api_log_1m', $recentDiagnostics['status'] ?? 'miss', $bzno, array(
		'persist' => $persist,
		'reason' => $recentDiagnostics['reason'] ?? 'cache_not_found',
		'rows_scanned' => (int)($recentDiagnostics['rows_scanned'] ?? 0),
	));

	// 2) 최근 캐시가 없거나 사용할 수 없으면 KODATA 최신 정보 조회
	$fetched = trade_request_fetch_kodata_company_json($bzno);
	if (!empty($fetched['success'])) {
		$fetchedJson = trim((string)($fetched['json'] ?? ''));
		// 모달 확인 단계에서도 즉시 캐시해 최종 제출 시 외부 API 재호출을 방지한다.
		$cacheSaved = trade_request_save_company_api_log($bzno, $fetchedJson);
		$resolved = trade_request_resolve_company_data_from_kodata_json($bzno, $fetchedJson, $persist, 'kodata_live');
		if (!empty($resolved['success'])) {
			trade_request_bzno_resolve_log(2, 'kodata_live', 'success', $bzno, array(
				'persist' => $persist,
				'cache_saved' => $cacheSaved,
			));
			return $resolved;
		}
		trade_request_bzno_resolve_log(2, 'kodata_live', 'fail', $bzno, array(
			'persist' => $persist,
			'reason' => 'kodata_payload_resolve_failed',
			'cache_saved' => $cacheSaved,
		));
	} else {
		trade_request_bzno_resolve_log(2, 'kodata_live', 'fail', $bzno, array(
			'persist' => $persist,
			'reason' => $fetched['error_code'] ?? 'kodata_request_failed',
			'kodata_error_code' => $fetched['kodata_error_code'] ?? '',
			'curl_errno' => (int)($fetched['curl_errno'] ?? 0),
			'error_message' => $fetched['message'] ?? '',
		));
	}

	// 3) KODATA 조회 실패 시 기간 제한 없는 최신 정상 캐시
	$latestDiagnostics = array();
	$latestLog = trade_request_find_company_api_log($bzno, false, $latestDiagnostics);
	$latestJson = trim((string)($latestLog['c_content'] ?? ''));
	if ($latestJson !== '') {
		$resolved = trade_request_resolve_company_data_from_kodata_json($bzno, $latestJson, $persist, 'company_api_log_latest');
		if (!empty($resolved['success'])) {
			trade_request_bzno_resolve_log(3, 'company_api_log_latest', 'success', $bzno, array(
				'persist' => $persist,
				'rows_scanned' => (int)($latestDiagnostics['rows_scanned'] ?? 0),
			));
			return $resolved;
		}
		$latestDiagnostics['status'] = 'fail';
		$latestDiagnostics['reason'] = 'cache_payload_resolve_failed';
	}
	trade_request_bzno_resolve_log(3, 'company_api_log_latest', $latestDiagnostics['status'] ?? 'miss', $bzno, array(
		'persist' => $persist,
		'reason' => $latestDiagnostics['reason'] ?? 'cache_not_found',
		'rows_scanned' => (int)($latestDiagnostics['rows_scanned'] ?? 0),
	));

	// 4) 캐시까지 없으면 company 테이블을 마지막 수단으로 사용
	$companyDiagnostics = array();
	$payload = trade_request_member_company_data($bzno, $companyDiagnostics);
	if (!empty($payload['company'])) {
		trade_request_bzno_resolve_log(4, 'company', 'success', $bzno, array('persist' => $persist));
		return array('success' => true, 'company' => $payload, 'bzno' => $bzno, 'source' => 'company');
	}

	trade_request_bzno_resolve_log(4, 'company', $companyDiagnostics['status'] ?? 'miss', $bzno, array(
		'persist' => $persist,
		'reason' => $companyDiagnostics['reason'] ?? 'company_not_found',
	));
	trade_request_bzno_resolve_log(0, 'all_sources', 'fail', $bzno, array(
		'persist' => $persist,
		'reason' => 'all_fallbacks_exhausted',
	));

	return array(
		'success' => false,
		'message' => '입력하신 사업자번호로 기업 정보를 확인하지 못했습니다. 사업자번호를 다시 확인하거나 잠시 후 다시 시도해 주세요.',
	);
}
