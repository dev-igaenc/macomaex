<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('macomaex_kodata_normalize_bzno')) {
	function macomaex_kodata_normalize_bzno($value) {
		return preg_replace('/[^0-9]/', '', is_scalar($value) ? (string)$value : '');
	}
}

if (!function_exists('macomaex_kodata_scalar')) {
	function macomaex_kodata_scalar($value) {
		return is_scalar($value) ? trim((string)$value) : '';
	}
}

if (!function_exists('macomaex_kodata_find_intr')) {
	function macomaex_kodata_find_intr($decoded, $expected_bzno = '') {
		if (!is_object($decoded) || empty($decoded->CONTENTS)) return null;

		$expected_bzno = macomaex_kodata_normalize_bzno($expected_bzno);
		$contents = is_array($decoded->CONTENTS) ? $decoded->CONTENTS : array($decoded->CONTENTS);
		$candidates = array();
		foreach ($contents as $content) {
			if (!is_object($content) || empty($content->ENPintr0000)) continue;
			$intr_list = is_array($content->ENPintr0000) ? $content->ENPintr0000 : array($content->ENPintr0000);
			foreach ($intr_list as $intr) {
				if (is_object($intr)) $candidates[] = $intr;
			}
		}

		if ($expected_bzno !== '') {
			foreach ($candidates as $intr) {
				$response_bzno = macomaex_kodata_normalize_bzno(macomaex_kodata_scalar($intr->bzno ?? ''));
				if ($response_bzno === '') {
					$response_bzno = macomaex_kodata_normalize_bzno(macomaex_kodata_scalar($intr->bsno ?? ''));
				}
				if ($response_bzno === $expected_bzno) return $intr;
			}
			return null;
		}

		return $candidates[0] ?? null;
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
		if (!is_object($decoded) || empty($decoded->CONTENTS)) return array();

		$expected_bzno = macomaex_kodata_normalize_bzno($fallback_bzno);
		$intr = macomaex_kodata_find_intr($decoded, $expected_bzno);
		if (!is_object($intr)) return array();

		$bzno_value = macomaex_kodata_scalar($intr->bzno ?? '');
		if ($bzno_value === '') $bzno_value = macomaex_kodata_scalar($intr->bsno ?? '');
		$bzno = macomaex_kodata_normalize_bzno($bzno_value);
		if ($bzno === '') $bzno = $expected_bzno;
		$name = macomaex_kodata_scalar($intr->enp_nm_trd ?? '');
		if ($name === '') $name = macomaex_kodata_scalar($intr->enp_nm ?? '');

		return array(
			'KEDCD' => macomaex_kodata_scalar($intr->kedcd ?? ''),
			'ENP_NM' => macomaex_kodata_scalar($intr->enp_nm ?? ''),
			'ENP_NM_TRD' => $name,
			'BZNO' => $bzno,
			'BZC_CD' => macomaex_kodata_scalar($intr->bzc_cd ?? ''),
			'ENP_TYP' => macomaex_kodata_scalar($intr->enp_typ ?? ''),
			'ESTB_DT' => macomaex_kodata_scalar($intr->estb_dt ?? ''),
			'LABORER_SUM' => macomaex_kodata_scalar($intr->laborer_sum ?? ''),
			'PD_NM' => macomaex_kodata_scalar($intr->mpd ?? ''),
			'HPAGE_URL' => macomaex_kodata_scalar($intr->hpage_url ?? ''),
			'LOC_ADDRA' => macomaex_kodata_scalar($intr->loc_addra ?? ''),
			'LOC_ADDRB' => macomaex_kodata_scalar($intr->loc_addrb ?? ''),
		);
	}
}

if (!function_exists('macomaex_kodata_company_to_json')) {
	function macomaex_kodata_company_to_json($company) {
		if (!is_array($company) || empty($company['KEDCD'])) return '';

		$field_map = array(
			'KEDCD' => 'kedcd',
			'ENP_NM' => 'enp_nm',
			'ENP_NM_TRD' => 'enp_nm_trd',
			'BZNO' => 'bzno',
			'BZC_CD' => 'bzc_cd',
			'ENP_TYP' => 'enp_typ',
			'ESTB_DT' => 'estb_dt',
			'LABORER_SUM' => 'laborer_sum',
			'PD_NM' => 'mpd',
			'HPAGE_URL' => 'hpage_url',
			'LOC_ADDRA' => 'loc_addra',
			'LOC_ADDRB' => 'loc_addrb',
		);
		$intr = array();
		foreach ($field_map as $company_field => $json_field) {
			$intr[$json_field] = (string)($company[$company_field] ?? '');
		}

		$json = json_encode(array('CONTENTS' => array('ENPintr0000' => $intr)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		return is_string($json) ? $json : '';
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
			 order by c_datetime desc",
			false
		);
		if (!$result) return array();

		while ($row = sql_fetch_array($result)) {
			$company = macomaex_kodata_company_from_json($row['c_content'] ?? '', $bzno);
			if (!empty($company['KEDCD']) && !empty($company['ENP_NM_TRD']) && (string)$company['BZNO'] === $bzno) return $row;
		}
		return array();
	}
}

if (!function_exists('macomaex_kodata_save_cache')) {
	function macomaex_kodata_save_cache($bzno, $json_text) {
		$bzno = macomaex_kodata_normalize_bzno($bzno);
		$company = macomaex_kodata_company_from_json($json_text, $bzno);
		if (!preg_match('/^[0-9]{10}$/', $bzno) || empty($company['KEDCD']) || (string)$company['BZNO'] !== $bzno) return false;

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
	function macomaex_kodata_fetch_live($bzno, $context = '', $options = array()) {
		$bzno = macomaex_kodata_normalize_bzno($bzno);
		if (!preg_match('/^[0-9]{10}$/', $bzno)) {
			return array('success' => false, 'error_code' => 'INVALID_BIZNO', 'message' => '사업자등록번호 형식을 확인해 주세요.');
		}

		$options = is_array($options) ? $options : array();
		$default_connect_timeout = function_exists('macomaex_env') ? (int)macomaex_env('KODATA_CONNECT_TIMEOUT', 3) : 3;
		$default_timeout = function_exists('macomaex_env') ? (int)macomaex_env('KODATA_TIMEOUT', 30) : 30;
		$connect_timeout = isset($options['connect_timeout']) ? (int)$options['connect_timeout'] : $default_connect_timeout;
		$timeout = isset($options['timeout']) ? (int)$options['timeout'] : $default_timeout;
		if ($connect_timeout < 1) $connect_timeout = 3;
		if ($timeout < $connect_timeout) $timeout = max(30, $connect_timeout);

		$cookie_path = tempnam(sys_get_temp_dir(), 'macomaex_kodata_');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, macomaex_kodata_build_url($bzno));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		if (is_string($cookie_path) && $cookie_path !== '') {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_path);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_path);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$raw = curl_exec($ch);
		$curl_error = curl_error($ch);
		$curl_errno = curl_errno($ch);
		curl_close($ch);
		if (is_string($cookie_path) && $cookie_path !== '') @unlink($cookie_path);

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
		if (empty($company['KEDCD']) || empty($company['ENP_NM_TRD']) || (string)$company['BZNO'] !== $bzno) {
			if (function_exists('macomaex_kodata_log')) {
				$fallback_intr = macomaex_kodata_find_intr(json_decode($parsed['json']), '');
				macomaex_kodata_log('bizno_mismatch_or_invalid_payload', array(
					'context' => (string)$context,
					'requested_bzno' => $bzno,
					'response_bzno' => is_object($fallback_intr) ? macomaex_kodata_scalar($fallback_intr->bzno ?? $fallback_intr->bsno ?? '') : '',
					'response_company' => is_object($fallback_intr) ? macomaex_kodata_scalar($fallback_intr->enp_nm_trd ?? $fallback_intr->enp_nm ?? '') : '',
				));
			}
			return array('success' => false, 'error_code' => 'INVALID_PAYLOAD', 'message' => '기업 정보 응답에 필수 정보가 없습니다.');
		}
		if (function_exists('macomaex_kodata_log')) {
			macomaex_kodata_log('live_response_validated', array(
				'context' => (string)$context,
				'requested_bzno' => $bzno,
				'response_bzno' => (string)$company['BZNO'],
				'kedcd' => (string)$company['KEDCD'],
				'company' => (string)$company['ENP_NM_TRD'],
			));
		}

		return array('success' => true, 'json' => $parsed['json'], 'company' => $company, 'source' => 'kodata_live');
	}
}

if (!function_exists('macomaex_kodata_upsert_company')) {
	function macomaex_kodata_upsert_company($bzno, $json_text) {
		$bzno = macomaex_kodata_normalize_bzno($bzno);
		$company = macomaex_kodata_company_from_json($json_text, $bzno);
		if (empty($company['KEDCD']) || (string)$company['BZNO'] !== $bzno) return false;

		$insert_fields = array();
		$update_fields = array();
		foreach ($company as $name => $value) {
			$escaped_value = sql_escape_string((string)$value);
			$insert_fields[] = $name . "='" . $escaped_value . "'";
			if ((string)$value !== '' && !in_array($name, array('KEDCD', 'BZNO'), true)) {
				$update_fields[] = $name . "='" . $escaped_value . "'";
			}
		}
		$insert_fields[] = "ext_1='API 입력'";
		$update_fields[] = "ext_1='API 입력'";

		$exists = sql_fetch("select KEDCD, BZNO from company where KEDCD='" . sql_escape_string($company['KEDCD']) . "' or BZNO='" . sql_escape_string($company['BZNO']) . "' limit 1");
		if (!empty($exists['KEDCD'])) {
			$existing_bzno = macomaex_kodata_normalize_bzno($exists['BZNO'] ?? '');
			if ((string)$exists['KEDCD'] !== (string)$company['KEDCD'] || ($existing_bzno !== '' && $existing_bzno !== (string)$company['BZNO'])) {
				if (function_exists('macomaex_kodata_log')) {
					macomaex_kodata_log('company_identity_conflict', array(
						'requested_bzno' => $bzno,
						'existing_kedcd' => (string)$exists['KEDCD'],
						'existing_bzno' => $existing_bzno,
						'kodata_kedcd' => (string)$company['KEDCD'],
						'kodata_bzno' => (string)$company['BZNO'],
					));
				}
				return false;
			}
			return (bool)sql_query("update company set " . implode(', ', $update_fields) . " where KEDCD='" . sql_escape_string($exists['KEDCD']) . "'", false);
		}

		return (bool)sql_query("insert company set " . implode(', ', $insert_fields), false);
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
			? macomaex_kodata_fetch_live($bzno, $context, $options)
			: array('success' => false, 'error_code' => 'KODATA_LIVE_DISABLED', 'message' => 'KODATA 실시간 조회가 비활성화되어 있습니다.');
		if (!empty($live['success'])) {
			$live['cache_saved'] = macomaex_kodata_save_cache($bzno, $live['json']);
			if (!$live['cache_saved'] && function_exists('macomaex_kodata_log')) {
				macomaex_kodata_log('cache_save_failed', array('context' => $context, 'bzno' => $bzno));
			}
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
			return array(
				'success' => true,
				'source' => 'company_api_log_latest',
				'json' => $latest['c_content'],
				'company' => macomaex_kodata_company_from_json($latest['c_content'], $bzno),
				'fallback_error_code' => $live['error_code'] ?? '',
			);
		}

		$company = sql_fetch("select * from company where BZNO='" . sql_escape_string($bzno) . "' limit 1");
		if (!empty($company['KEDCD'])) {
			return array(
				'success' => true,
				'source' => 'company',
				'json' => '',
				'company' => $company,
				'fallback_error_code' => $live['error_code'] ?? '',
			);
		}

		return array(
			'success' => false,
			'error_code' => $live['error_code'] ?? 'COMPANY_NOT_FOUND',
			'message' => $live['message'] ?? '기업 정보를 찾지 못했습니다.',
		);
	}
}
