<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('macomaex_kodata_normalize_bzno')) {
	function macomaex_kodata_normalize_bzno($value) {
		return preg_replace('/[^0-9]/', '', (string)$value);
	}
}

if (!function_exists('macomaex_kodata_build_url')) {
	function macomaex_kodata_build_url($bzno) {
		$bzno = macomaex_kodata_normalize_bzno($bzno);
		$template = function_exists('macomaex_env')
			? trim((string)macomaex_env('KODATA_ENPINFO_URL_TEMPLATE', ''))
			: '';
		if ($template === '') {
			$template = 'https://218.55.37.214:6059/enpinfo?userid=igaenconl&process=S&bzno={bizno}&pidagryn=Y&jmno=ig001&format=XML&Length=10000';
		}

		return str_replace('{bizno}', rawurlencode($bzno), $template);
	}
}

if (!function_exists('macomaex_kodata_company_from_json')) {
	function macomaex_kodata_company_from_json($json_text, $fallback_bzno = '') {
		$decoded = json_decode((string)$json_text);
		if (!is_object($decoded) || empty($decoded->CONTENTS->ENPintr0000)) return array();

		$intr = $decoded->CONTENTS->ENPintr0000;
		$bzno = macomaex_kodata_normalize_bzno((string)($intr->bzno ?? $intr->bsno ?? $fallback_bzno));
		$name = trim((string)($intr->enp_nm_trd ?? ''));
		if ($name === '') $name = trim((string)($intr->enp_nm ?? ''));

		return array(
			'KEDCD' => trim((string)($intr->kedcd ?? '')),
			'ENP_NM' => trim((string)($intr->enp_nm ?? '')),
			'ENP_NM_TRD' => $name,
			'BZNO' => $bzno,
			'BZC_CD' => trim((string)($intr->bzc_cd ?? '')),
			'ENP_TYP' => trim((string)($intr->enp_typ ?? '')),
			'ESTB_DT' => trim((string)($intr->estb_dt ?? '')),
			'LABORER_SUM' => trim((string)($intr->laborer_sum ?? '')),
			'PD_NM' => trim((string)($intr->mpd ?? '')),
			'HPAGE_URL' => trim((string)($intr->hpage_url ?? '')),
			'LOC_ADDRA' => trim((string)($intr->loc_addra ?? '')),
			'LOC_ADDRB' => trim((string)($intr->loc_addrb ?? '')),
		);
	}
}

if (!function_exists('macomaex_kodata_find_cache')) {
	function macomaex_kodata_find_cache($bzno, $recent_only = false) {
		$bzno = macomaex_kodata_normalize_bzno($bzno);
		if (!preg_match('/^[0-9]{10}$/', $bzno)) return array();

		$recent_sql = $recent_only ? " and c_datetime >= date_sub(now(), interval 1 month)" : '';
		$result = sql_query(
			"select * from company_api_log
			 where c_bizno='" . sql_escape_string($bzno) . "'
			   and c_content!=''{$recent_sql}
			 order by c_datetime desc
			 limit 20",
			false
		);
		if (!$result) return array();

		while ($row = sql_fetch_array($result)) {
			if (!empty(macomaex_kodata_company_from_json($row['c_content'] ?? '', $bzno))) return $row;
		}
		return array();
	}
}

if (!function_exists('macomaex_kodata_save_cache')) {
	function macomaex_kodata_save_cache($bzno, $json_text) {
		$bzno = macomaex_kodata_normalize_bzno($bzno);
		$company = macomaex_kodata_company_from_json($json_text, $bzno);
		if (!preg_match('/^[0-9]{10}$/', $bzno) || empty($company['KEDCD'])) return false;

		return (bool)sql_query(
			"insert company_api_log set
			 c_bizno='" . sql_escape_string($bzno) . "',
			 c_kedcd='" . sql_escape_string($company['KEDCD']) . "',
			 c_content='" . sql_escape_string((string)$json_text) . "',
			 c_datetime=now(),
			 c_ip='" . sql_escape_string($_SERVER['REMOTE_ADDR'] ?? '') . "'",
			false
		);
	}
}

if (!function_exists('macomaex_kodata_fetch_live')) {
	function macomaex_kodata_fetch_live($bzno, $context = '') {
		$bzno = macomaex_kodata_normalize_bzno($bzno);
		if (!preg_match('/^[0-9]{10}$/', $bzno)) {
			return array('success' => false, 'error_code' => 'INVALID_BIZNO', 'message' => '사업자등록번호 형식을 확인해 주세요.');
		}

		$connect_timeout = function_exists('macomaex_env') ? (int)macomaex_env('KODATA_CONNECT_TIMEOUT', 3) : 3;
		$timeout = function_exists('macomaex_env') ? (int)macomaex_env('KODATA_TIMEOUT', 30) : 30;
		if ($connect_timeout < 1) $connect_timeout = 3;
		if ($timeout < $connect_timeout) $timeout = max(30, $connect_timeout);

		$cookie_path = defined('G5_DATA_PATH') ? G5_DATA_PATH . '/kodata_cookie.txt' : sys_get_temp_dir() . '/kodata_cookie.txt';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, macomaex_kodata_build_url($bzno));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_path);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_path);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$raw = curl_exec($ch);
		$curl_error = curl_error($ch);
		$curl_errno = curl_errno($ch);
		curl_close($ch);

		if ($raw === false) {
			if (function_exists('macomaex_kodata_log')) {
				macomaex_kodata_log('curl_failed', array(
					'context' => (string)$context,
					'bzno' => $bzno,
					'curl_errno' => $curl_errno,
					'curl_error' => $curl_error,
				));
			}
			return array(
				'success' => false,
				'error_code' => 'CURL_ERROR',
				'curl_errno' => $curl_errno,
				'curl_error' => $curl_error,
				'message' => ((int)$curl_errno === 28) ? '기업 정보 조회 시간이 초과되었습니다.' : '기업 정보 조회 중 통신 오류가 발생했습니다.',
			);
		}

		$parsed = function_exists('macomaex_parse_kodata_xml')
			? macomaex_parse_kodata_xml($raw, array('context' => (string)$context, 'bzno' => $bzno))
			: array('success' => false, 'error_code' => 'PARSER_NOT_FOUND', 'error_message' => 'KODATA 파서 함수를 찾을 수 없습니다.');
		if (empty($parsed['success'])) {
			return array(
				'success' => false,
				'error_code' => $parsed['error_code'] ?? 'XML_PARSE_ERROR',
				'message' => $parsed['error_message'] ?? '기업 정보 응답을 해석하지 못했습니다.',
			);
		}

		$xml = $parsed['xml'];
		if ((string)($xml->HEADER->errcd ?? '') !== '00') {
			return array(
				'success' => false,
				'error_code' => (string)($xml->HEADER->errcd ?? 'KODATA_ERROR'),
				'message' => (string)($xml->HEADER->errctt ?? '기업 정보 조회에 실패했습니다.'),
			);
		}

		$company = macomaex_kodata_company_from_json($parsed['json'], $bzno);
		if (empty($company['KEDCD']) || empty($company['ENP_NM_TRD'])) {
			return array('success' => false, 'error_code' => 'INVALID_PAYLOAD', 'message' => '기업 정보 응답에 필수 정보가 없습니다.');
		}

		return array('success' => true, 'json' => $parsed['json'], 'company' => $company, 'source' => 'kodata_live');
	}
}

if (!function_exists('macomaex_kodata_upsert_company')) {
	function macomaex_kodata_upsert_company($bzno, $json_text) {
		$company = macomaex_kodata_company_from_json($json_text, $bzno);
		if (empty($company['KEDCD'])) return false;

		$fields = array();
		foreach ($company as $name => $value) {
			$fields[] = $name . "='" . sql_escape_string((string)$value) . "'";
		}
		$fields[] = "ext_1='API 입력'";

		$exists = sql_fetch("select KEDCD from company where KEDCD='" . sql_escape_string($company['KEDCD']) . "' or BZNO='" . sql_escape_string($company['BZNO']) . "' limit 1");
		if (!empty($exists['KEDCD'])) {
			return (bool)sql_query("update company set " . implode(', ', $fields) . " where KEDCD='" . sql_escape_string($exists['KEDCD']) . "'", false);
		}

		return (bool)sql_query("insert company set " . implode(', ', $fields), false);
	}
}

if (!function_exists('macomaex_kodata_resolve_company')) {
	function macomaex_kodata_resolve_company($bzno, $options = array()) {
		$bzno = macomaex_kodata_normalize_bzno($bzno);
		$persist_company = !empty($options['persist_company']);
		$allow_live = !array_key_exists('allow_live', $options) || !empty($options['allow_live']);
		$context = trim((string)($options['context'] ?? 'kodata_resolve'));
		if (!preg_match('/^[0-9]{10}$/', $bzno)) {
			return array('success' => false, 'error_code' => 'INVALID_BIZNO', 'message' => '사업자등록번호 형식을 확인해 주세요.');
		}

		$recent = macomaex_kodata_find_cache($bzno, true);
		if (!empty($recent['c_content'])) {
			if ($persist_company && !macomaex_kodata_upsert_company($bzno, $recent['c_content'])) {
				return array('success' => false, 'error_code' => 'COMPANY_SAVE_FAILED', 'message' => '기업 정보 저장에 실패했습니다.');
			}
			return array('success' => true, 'source' => 'company_api_log_1m', 'json' => $recent['c_content'], 'company' => macomaex_kodata_company_from_json($recent['c_content'], $bzno));
		}

		$live = $allow_live
			? macomaex_kodata_fetch_live($bzno, $context)
			: array('success' => false, 'error_code' => 'KODATA_LIVE_DISABLED', 'message' => 'KODATA 실시간 조회가 비활성화되어 있습니다.');
		if (!empty($live['success'])) {
			macomaex_kodata_save_cache($bzno, $live['json']);
			if ($persist_company && !macomaex_kodata_upsert_company($bzno, $live['json'])) {
				return array('success' => false, 'error_code' => 'COMPANY_SAVE_FAILED', 'message' => '기업 정보 저장에 실패했습니다.');
			}
			return $live;
		}

		$latest = macomaex_kodata_find_cache($bzno, false);
		if (!empty($latest['c_content'])) {
			if ($persist_company && !macomaex_kodata_upsert_company($bzno, $latest['c_content'])) {
				return array('success' => false, 'error_code' => 'COMPANY_SAVE_FAILED', 'message' => '기업 정보 저장에 실패했습니다.');
			}
			return array('success' => true, 'source' => 'company_api_log_latest', 'json' => $latest['c_content'], 'company' => macomaex_kodata_company_from_json($latest['c_content'], $bzno));
		}

		$company = sql_fetch("select * from company where BZNO='" . sql_escape_string($bzno) . "' limit 1");
		if (!empty($company['KEDCD'])) {
			return array('success' => true, 'source' => 'company', 'json' => '', 'company' => $company);
		}

		return array(
			'success' => false,
			'error_code' => $live['error_code'] ?? 'COMPANY_NOT_FOUND',
			'message' => $live['message'] ?? '기업 정보를 찾지 못했습니다.',
		);
	}
}
