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

function trade_request_normalize_bzno($value) {
	return preg_replace('/[^0-9]/', '', (string)$value);
}

function trade_request_normalize_phone($value) {
	return preg_replace('/[^0-9]/', '', (string)$value);
}

function trade_request_fetch_kodata_company_json($bzno) {
	$bzno = trade_request_normalize_bzno($bzno);
	if (!preg_match('/^[0-9]{10}$/', $bzno)) {
		return array('success' => false, 'message' => '사업자등록번호 형식을 확인해 주세요.');
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
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
		return array('success' => false, 'message' => '기업 정보 조회 중 통신 오류가 발생했습니다.');
	}

	$parsed = function_exists('macomaex_parse_kodata_xml')
		? macomaex_parse_kodata_xml($raw, array('context' => 'trade_request_modal', 'bzno' => $bzno))
		: array('success' => false, 'error_message' => 'KODATA 파서 함수를 찾을 수 없습니다.');

	if (empty($parsed['success'])) {
		return array('success' => false, 'message' => $parsed['error_message'] ?? '기업 정보 파싱 중 오류가 발생했습니다.');
	}

	$xml = $parsed['xml'];
	if ((string)($xml->HEADER->errcd ?? '') !== '00') {
		return array('success' => false, 'message' => (string)($xml->HEADER->errctt ?? '기업 정보 조회에 실패했습니다.'));
	}

	return array(
		'success' => true,
		'json' => $parsed['json'],
	);
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

function trade_request_resolve_company_data_by_bzno($bzno, $options = array()) {
	$bzno = trade_request_normalize_bzno($bzno);
	// persist=false: 모달 확인용(미리보기만), persist=true: 최종 제출 성공 후 DB 확정 저장
	$persist = !empty($options['persist']);
	if (!preg_match('/^[0-9]{10}$/', $bzno)) {
		return array('success' => false, 'message' => '사업자등록번호 형식을 확인해 주세요.');
	}

	$payload = trade_request_member_company_data($bzno);
	if (!empty($payload['company'])) {
		return array('success' => true, 'company' => $payload, 'bzno' => $bzno);
	}

	$logRow = sql_fetch("SELECT * FROM company_api_log WHERE c_bizno='" . sql_escape_string($bzno) . "' ORDER BY c_datetime DESC LIMIT 1");
	$jsonText = trim((string)($logRow['c_content'] ?? ''));

	if ($jsonText === '') {
		$fetched = trade_request_fetch_kodata_company_json($bzno);
		if (empty($fetched['success'])) return $fetched;
		$jsonText = (string)$fetched['json'];
		if ($persist) {
			// 최종 저장 단계에서만 API 로그를 남긴다.
			$comInfo = json_decode($jsonText);
			$kedcd = trim((string)($comInfo->CONTENTS->ENPintr0000->kedcd ?? ''));
			sql_query("INSERT INTO company_api_log SET c_bizno='" . sql_escape_string($bzno) . "', c_kedcd='" . sql_escape_string($kedcd) . "', c_content='" . sql_escape_string($jsonText) . "', c_datetime=now(), c_ip='" . sql_escape_string($_SERVER['REMOTE_ADDR'] ?? '') . "'", false);
		}
	}

	if ($persist) {
		// 최종 저장 단계에서만 company 적재를 수행한다.
		trade_request_upsert_company_from_kodata_json($bzno, $jsonText);
		$payload = trade_request_member_company_data($bzno);
		if (!empty($payload['company'])) {
			return array('success' => true, 'company' => $payload, 'bzno' => $bzno);
		}
	}

	// 저장 전 단계에서는 DB 반영 없이 모달 출력용 payload만 구성한다.
	$payload = trade_request_company_payload_from_kodata_json($jsonText);
	if (empty($payload['company'])) {
		return array('success' => false, 'message' => '기업 정보를 불러오지 못했습니다. 잠시 후 다시 시도해 주세요.');
	}
	if (empty($payload['bzno'])) {
		$payload['bzno'] = licenseNum($bzno);
	}
	return array('success' => true, 'company' => $payload, 'bzno' => $bzno);
}
