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

// KODATA XML→JSON 결과가 객체/배열 어느 형태여도 지정 섹션을 동일하게 찾는다.
if (!function_exists('macomaex_kodata_find_content_section')) {
	function macomaex_kodata_find_content_section($decoded, $section_name) {
		if (!is_object($decoded) || empty($decoded->CONTENTS) || !is_string($section_name) || $section_name === '') {
			return null;
		}

		$contents = is_array($decoded->CONTENTS) ? $decoded->CONTENTS : array($decoded->CONTENTS);
		foreach ($contents as $content) {
			if (is_object($content) && isset($content->{$section_name})) {
				return $content->{$section_name};
			}
		}

		return null;
	}
}

if (!function_exists('macomaex_kodata_number_string')) {
	function macomaex_kodata_number_string($value) {
		$value = macomaex_kodata_scalar($value);
		if ($value === '') return '';

		$value = str_replace(array(',', ' '), '', $value);
		return preg_match('/^-?[0-9]+(?:\.[0-9]+)?$/', $value) ? $value : '';
	}
}

// 기업개요 응답을 company의 API 소유 컬럼으로 변환한다.
if (!function_exists('macomaex_kodata_map_company_sync_row')) {
	function macomaex_kodata_map_company_sync_row($decoded, $fallback_bzno = '') {
		$base = macomaex_kodata_company_from_json(json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $fallback_bzno);
		if (empty($base['KEDCD'])) return array();

		$intr = macomaex_kodata_find_intr($decoded, $fallback_bzno);
		if (!is_object($intr)) return array();

		$field_map = array(
			'ENP_NM_ENG' => 'enp_nm_eng',
			'ENP_SZE' => 'enp_sze',
			'ENP_FCD' => 'enp_fcd',
			'ENP_SCD' => 'enp_scd',
			'ENP_SCD_CHG_DT' => 'enp_scd_chg_dt',
			'ENP_FORM_FR' => 'enp_form_fr',
			'GRP_NM' => 'grp_nm',
			'CONO_PID' => 'cono_pid',
			'IPO_CD' => 'ipo_cd',
			'EMAIL' => 'email',
			'STD_DT' => 'std_dt',
			'LOC_ZIP' => 'loc_zip',
			'TEL_NO' => 'tel_no',
			'FAX_NO' => 'fax_no',
		);

		foreach ($field_map as $column => $json_field) {
			$base[$column] = macomaex_kodata_scalar($intr->{$json_field} ?? '');
		}

		return $base;
	}
}

// 최근·직전·직직전 결산값을 company_financial 행 단위로 펼친다.
if (!function_exists('macomaex_kodata_map_financial_sync_rows')) {
	function macomaex_kodata_map_financial_sync_rows($decoded, $kedcd) {
		$kedcd = macomaex_kodata_scalar($kedcd);
		$section = macomaex_kodata_find_content_section($decoded, 'ENPfsdt0001');
		if (!is_object($section) || !preg_match('/^[0-9]{10}$/', $kedcd)) return array();

		$rows = array();
		foreach (array('fs', 'fs1', 'fs2') as $prefix) {
			$acct_dt = preg_replace('/[^0-9]/', '', macomaex_kodata_scalar($section->{$prefix . '_acct_dt'} ?? ''));
			if ($acct_dt === '') continue;

			$rows[] = array(
				'KEDCD' => $kedcd,
				'ACCT_DT' => $acct_dt,
				'SUMASSET' => macomaex_kodata_number_string($section->{$prefix . '_val3'} ?? ''),
				'PAYMENTFUND' => macomaex_kodata_number_string($section->{$prefix . '_val7'} ?? ''),
				'FUNDTOTAL' => macomaex_kodata_number_string($section->{$prefix . '_val8'} ?? ''),
				'SALES' => macomaex_kodata_number_string($section->{$prefix . '_val9'} ?? ''),
				'PROFIT' => macomaex_kodata_number_string($section->{$prefix . '_val11'} ?? ''),
				'TERMNETPROFIT' => macomaex_kodata_number_string($section->{$prefix . '_val16'} ?? ''),
			);
		}

		return $rows;
	}
}

if (!function_exists('macomaex_kodata_normalize_list')) {
	function macomaex_kodata_normalize_list($value) {
		if (is_array($value)) return $value;
		if (is_object($value)) return array($value);
		return array();
	}
}

// 단일/복수 주주 목록을 정규화해 기준일별 주식 합계 1행으로 만든다.
if (!function_exists('macomaex_kodata_map_stock_sync_rows')) {
	function macomaex_kodata_map_stock_sync_rows($decoded, $kedcd) {
		$kedcd = macomaex_kodata_scalar($kedcd);
		$section = macomaex_kodata_find_content_section($decoded, 'ENPsthr0001');
		if (!is_object($section) || !preg_match('/^[0-9]{10}$/', $kedcd)) return array();

		$list = macomaex_kodata_normalize_list($section->list ?? null);
		$std_dt = '';
		$issued = 0.0;
		$common = 0.0;
		$preferred = 0.0;
		$has_count = false;

		foreach ($list as $item) {
			if (!is_object($item)) continue;
			if ($std_dt === '') {
				$std_dt = preg_replace('/[^0-9]/', '', macomaex_kodata_scalar($item->std_dt ?? ''));
			}
			$own_value = macomaex_kodata_number_string($item->own_stk_cn ?? '');
			$common_value = macomaex_kodata_number_string($item->cstk_cn ?? '');
			$preferred_value = macomaex_kodata_number_string($item->pstk_cn ?? '');
			if ($own_value !== '' || $common_value !== '' || $preferred_value !== '') $has_count = true;
			$issued += $own_value === '' ? 0 : (float)$own_value;
			$common += $common_value === '' ? 0 : (float)$common_value;
			$preferred += $preferred_value === '' ? 0 : (float)$preferred_value;
		}

		if ($std_dt === '' || !$has_count) return array();

		$format_number = function ($number) {
			return ((float)$number === (float)(int)$number)
				? (string)(int)$number
				: rtrim(rtrim(number_format((float)$number, 6, '.', ''), '0'), '.');
		};

		return array(array(
			'KEDCD' => $kedcd,
			'STD_DT' => $std_dt,
			'ISS_STK_CN' => $format_number($issued),
			'CSTK_CN' => $format_number($common),
			'PSTK_CN' => $format_number($preferred),
		));
	}
}

// 한 번의 KODATA 응답을 세 원천 테이블용 DTO로 묶는다.
if (!function_exists('macomaex_kodata_map_sync_payload')) {
	function macomaex_kodata_map_sync_payload($json_text, $fallback_bzno = '') {
		$decoded = json_decode((string)$json_text);
		if (!is_object($decoded)) {
			return array('company' => array(), 'financial' => array(), 'stock' => array());
		}

		$company = macomaex_kodata_map_company_sync_row($decoded, $fallback_bzno);
		$kedcd = macomaex_kodata_scalar($company['KEDCD'] ?? '');
		$financial = macomaex_kodata_map_financial_sync_rows($decoded, $kedcd);
		if (!empty($company) && !empty($financial[0]['SALES'])) {
			$company['SALES'] = $financial[0]['SALES'];
		} else if (!empty($company)) {
			$company['SALES'] = '';
		}

		return array(
			'company' => $company,
			'financial' => $financial,
			'stock' => macomaex_kodata_map_stock_sync_rows($decoded, $kedcd),
		);
	}
}

// KODATA 원문 출처와 기업 식별값이 확실한 응답만 저장 대상으로 허용한다.
if (!function_exists('macomaex_kodata_validate_sync_payload')) {
	function macomaex_kodata_validate_sync_payload($bzno, $json_text, $source) {
		$bzno = macomaex_kodata_normalize_bzno($bzno);
		$source = trim((string)$source);
		$allowed_sources = array('kodata_live', 'company_api_log_1m', 'company_api_log_latest');

		if (!preg_match('/^[0-9]{10}$/', $bzno)) {
			return array('eligible' => false, 'error_code' => 'INVALID_BIZNO', 'message' => '사업자등록번호는 10자리 숫자여야 합니다.');
		}
		if (!in_array($source, $allowed_sources, true)) {
			return array('eligible' => false, 'error_code' => 'SOURCE_NOT_PERSISTABLE', 'message' => 'KODATA 원문 출처가 아니므로 저장하지 않습니다.');
		}
		if (trim((string)$json_text) === '') {
			return array('eligible' => false, 'error_code' => 'EMPTY_JSON', 'message' => '저장할 KODATA 원문이 없습니다.');
		}

		$decoded = json_decode((string)$json_text);
		if (!is_object($decoded)) {
			return array('eligible' => false, 'error_code' => 'INVALID_PAYLOAD', 'message' => 'KODATA 원문을 기업 동기화 데이터로 변환하지 못했습니다.');
		}
		$raw_intr = macomaex_kodata_find_intr($decoded, '');
		$raw_bzno = is_object($raw_intr)
			? macomaex_kodata_normalize_bzno(macomaex_kodata_scalar($raw_intr->bzno ?? ($raw_intr->bsno ?? '')))
			: '';
		if ($raw_bzno !== '' && $raw_bzno !== $bzno) {
			return array('eligible' => false, 'error_code' => 'BIZNO_MISMATCH', 'message' => '요청 사업자번호와 KODATA 응답이 일치하지 않습니다.');
		}

		$mapped = macomaex_kodata_map_sync_payload($json_text, $bzno);
		$company = is_array($mapped['company'] ?? null) ? $mapped['company'] : array();
		if (empty($company)) {
			return array('eligible' => false, 'error_code' => 'INVALID_PAYLOAD', 'message' => 'KODATA 원문을 기업 동기화 데이터로 변환하지 못했습니다.');
		}
		$response_bzno = macomaex_kodata_normalize_bzno($company['BZNO'] ?? '');
		$kedcd = macomaex_kodata_scalar($company['KEDCD'] ?? '');
		$company_name = trim((string)($company['ENP_NM_TRD'] ?? ''));
		if ($company_name === '') $company_name = trim((string)($company['ENP_NM'] ?? ''));

		if ($response_bzno !== $bzno) {
			return array('eligible' => false, 'error_code' => 'BIZNO_MISMATCH', 'message' => '요청 사업자번호와 KODATA 응답이 일치하지 않습니다.');
		}
		if (!preg_match('/^[0-9]{10}$/', $kedcd)) {
			return array('eligible' => false, 'error_code' => 'INVALID_KEDCD', 'message' => 'KODATA 기업코드가 유효하지 않습니다.');
		}
		if ($company_name === '') {
			return array('eligible' => false, 'error_code' => 'EMPTY_COMPANY_NAME', 'message' => 'KODATA 응답에 기업명이 없습니다.');
		}

		return array(
			'eligible' => true,
			'error_code' => '',
			'message' => '',
			'mapped' => $mapped,
		);
	}
}

// UPDATE에서는 빈 API 값이 기존 값을 지우지 않도록 대입문에서 제외한다.
if (!function_exists('macomaex_kodata_sync_assignments')) {
	function macomaex_kodata_sync_assignments($row, $include_empty = false) {
		$assignments = array();
		foreach ((array)$row as $column => $value) {
			if (!preg_match('/^[A-Z0-9_]+$/', (string)$column)) continue;
			$value = is_scalar($value) ? (string)$value : '';
			if (!$include_empty && $value === '') continue;
			$assignments[] = $column . "='" . sql_escape_string($value) . "'";
		}
		return $assignments;
	}
}

// KEDCD/BZNO가 다른 행을 가리키면 자동 병합하지 않고 식별 충돌로 중단한다.
if (!function_exists('macomaex_kodata_sync_company_row')) {
	function macomaex_kodata_sync_company_row($company) {
		$kedcd = macomaex_kodata_scalar($company['KEDCD'] ?? '');
		$bzno = macomaex_kodata_normalize_bzno($company['BZNO'] ?? '');
		$kedcd_sql = sql_escape_string($kedcd);
		$bzno_sql = sql_escape_string($bzno);
		$matches = array();
		$result = sql_query("select c_id, KEDCD, BZNO from company where KEDCD='{$kedcd_sql}' or BZNO='{$bzno_sql}' order by c_id", false);
		if (!$result) return array('success' => false, 'error_code' => 'COMPANY_LOOKUP_FAILED');
		while ($row = sql_fetch_array($result)) $matches[] = $row;

		if (count($matches) > 1) {
			return array('success' => false, 'error_code' => 'COMPANY_IDENTITY_CONFLICT');
		}
		if (!empty($matches[0])) {
			$existing_kedcd = macomaex_kodata_scalar($matches[0]['KEDCD'] ?? '');
			$existing_bzno = macomaex_kodata_normalize_bzno($matches[0]['BZNO'] ?? '');
			if ($existing_kedcd !== $kedcd || ($existing_bzno !== '' && $existing_bzno !== $bzno)) {
				return array('success' => false, 'error_code' => 'COMPANY_IDENTITY_CONFLICT');
			}

			$update = $company;
			unset($update['KEDCD']);
			if ($existing_bzno !== '') unset($update['BZNO']);
			$assignments = macomaex_kodata_sync_assignments($update, false);
			$assignments[] = "ext_1='API 입력'";
			$ok = sql_query("update company set " . implode(', ', $assignments) . " where c_id='" . (int)$matches[0]['c_id'] . "'", false);
			return array('success' => (bool)$ok, 'action' => 'updated', 'error_code' => $ok ? '' : 'COMPANY_UPDATE_FAILED');
		}

		$insert = $company;
		$assignments = macomaex_kodata_sync_assignments($insert, true);
		$assignments[] = "ext_1='API 입력'";
		$ok = sql_query("insert company set " . implode(', ', $assignments), false);
		return array('success' => (bool)$ok, 'action' => 'inserted', 'error_code' => $ok ? '' : 'COMPANY_INSERT_FAILED');
	}
}

// 복합 UNIQUE 키가 없으므로 기준키별 1행만 남기며 재무·주식 데이터를 갱신한다.
if (!function_exists('macomaex_kodata_sync_child_rows')) {
	function macomaex_kodata_sync_child_rows($table, $rows, $date_column) {
		if (!in_array($table, array('company_financial', 'company_stock'), true)) {
			return array('success' => false, 'error_code' => 'INVALID_SYNC_TABLE');
		}
		if (!in_array($date_column, array('ACCT_DT', 'STD_DT'), true)) {
			return array('success' => false, 'error_code' => 'INVALID_SYNC_DATE_COLUMN');
		}

		$stats = array('inserted' => 0, 'updated' => 0, 'deduplicated' => 0, 'skipped' => 0);
		foreach ((array)$rows as $row) {
			$kedcd = macomaex_kodata_scalar($row['KEDCD'] ?? '');
			$date = preg_replace('/[^0-9]/', '', macomaex_kodata_scalar($row[$date_column] ?? ''));
			if (!preg_match('/^[0-9]{10}$/', $kedcd) || $date === '') {
				$stats['skipped']++;
				continue;
			}

			$kedcd_sql = sql_escape_string($kedcd);
			$date_sql = sql_escape_string($date);
			$ids = array();
			$result = sql_query("select c_id from {$table} where KEDCD='{$kedcd_sql}' and {$date_column}='{$date_sql}' order by c_id", false);
			if (!$result) return array('success' => false, 'error_code' => strtoupper($table) . '_LOOKUP_FAILED', 'stats' => $stats);
			while ($existing = sql_fetch_array($result)) $ids[] = (int)$existing['c_id'];

			if (!empty($ids)) {
				$update = $row;
				unset($update['KEDCD'], $update[$date_column]);
				$assignments = macomaex_kodata_sync_assignments($update, false);
				$assignments[] = "ext_1='API 입력'";
				$ok = sql_query("update {$table} set " . implode(', ', $assignments) . " where c_id='" . $ids[0] . "'", false);
				if (!$ok) return array('success' => false, 'error_code' => strtoupper($table) . '_UPDATE_FAILED', 'stats' => $stats);
				$stats['updated']++;

				if (count($ids) > 1) {
					$duplicates = array_slice($ids, 1);
					$ok = sql_query("delete from {$table} where c_id in (" . implode(',', $duplicates) . ")", false);
					if (!$ok) return array('success' => false, 'error_code' => strtoupper($table) . '_DEDUP_FAILED', 'stats' => $stats);
					$stats['deduplicated'] += count($duplicates);
				}
				continue;
			}

			$insert = $row;
			// 신규 행의 숫자 컬럼은 NOT NULL 스키마에 맞춰 누락값을 0으로 채운다.
			foreach ($insert as $column => $value) {
				if ($column === 'KEDCD' || $column === $date_column) continue;
				if ((string)$value === '') $insert[$column] = '0';
			}
			$assignments = macomaex_kodata_sync_assignments($insert, true);
			$assignments[] = "ext_1='API 입력'";
			$ok = sql_query("insert {$table} set " . implode(', ', $assignments), false);
			if (!$ok) return array('success' => false, 'error_code' => strtoupper($table) . '_INSERT_FAILED', 'stats' => $stats);
			$stats['inserted']++;
		}

		return array('success' => true, 'error_code' => '', 'stats' => $stats);
	}
}

// company와 부속 두 테이블을 하나의 트랜잭션으로 반영한다.
if (!function_exists('macomaex_kodata_sync_payload')) {
	function macomaex_kodata_sync_payload($bzno, $json_text, $source, $options = array()) {
		$validation = macomaex_kodata_validate_sync_payload($bzno, $json_text, $source);
		if (empty($validation['eligible'])) {
			return array(
				'success' => false,
				'error_code' => $validation['error_code'] ?? 'SYNC_NOT_ELIGIBLE',
				'message' => $validation['message'] ?? 'KODATA 동기화 대상이 아닙니다.',
			);
		}

		$manage_transaction = !array_key_exists('manage_transaction', (array)$options) || !empty($options['manage_transaction']);
		if ($manage_transaction && !sql_query('START TRANSACTION', false)) {
			return array('success' => false, 'error_code' => 'TRANSACTION_START_FAILED', 'message' => '기업정보 동기화를 시작하지 못했습니다.');
		}

		$mapped = $validation['mapped'];
		$company_result = macomaex_kodata_sync_company_row($mapped['company']);
		$financial_result = array();
		$stock_result = array();
		$error_code = '';
		if (empty($company_result['success'])) {
			$error_code = $company_result['error_code'] ?? 'COMPANY_SYNC_FAILED';
		} else {
			$financial_result = macomaex_kodata_sync_child_rows('company_financial', $mapped['financial'], 'ACCT_DT');
			if (empty($financial_result['success'])) {
				$error_code = $financial_result['error_code'] ?? 'FINANCIAL_SYNC_FAILED';
			} else {
				$stock_result = macomaex_kodata_sync_child_rows('company_stock', $mapped['stock'], 'STD_DT');
				if (empty($stock_result['success'])) $error_code = $stock_result['error_code'] ?? 'STOCK_SYNC_FAILED';
			}
		}

		if ($error_code !== '') {
			if ($manage_transaction) sql_query('ROLLBACK', false);
			if (function_exists('macomaex_kodata_log')) {
				macomaex_kodata_log('sync_failed', array('bzno' => macomaex_kodata_normalize_bzno($bzno), 'source' => (string)$source, 'error_code' => $error_code));
			}
			return array('success' => false, 'error_code' => $error_code, 'message' => '기업정보 동기화에 실패했습니다.');
		}

		if ($manage_transaction && !sql_query('COMMIT', false)) {
			sql_query('ROLLBACK', false);
			return array('success' => false, 'error_code' => 'TRANSACTION_COMMIT_FAILED', 'message' => '기업정보 동기화를 완료하지 못했습니다.');
		}

		$result = array(
			'success' => true,
			'error_code' => '',
			'company' => $company_result,
			'financial' => $financial_result,
			'stock' => $stock_result,
		);
		if (function_exists('macomaex_kodata_log')) {
			macomaex_kodata_log('sync_success', array(
				'bzno' => macomaex_kodata_normalize_bzno($bzno),
				'kedcd' => (string)($mapped['company']['KEDCD'] ?? ''),
				'source' => (string)$source,
				'company_action' => (string)($company_result['action'] ?? ''),
				'financial_stats' => $financial_result['stats'] ?? array(),
				'stock_stats' => $stock_result['stats'] ?? array(),
			));
		}
		return $result;
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

if (!function_exists('macomaex_kodata_resolve_company')) {
	function macomaex_kodata_resolve_company($bzno, $options = array()) {
		$bzno = macomaex_kodata_normalize_bzno($bzno);
		$allow_live = !array_key_exists('allow_live', $options) || !empty($options['allow_live']);
		$context = trim((string)($options['context'] ?? 'kodata_resolve'));
		if (!preg_match('/^[0-9]{10}$/', $bzno)) {
			return array('success' => false, 'error_code' => 'INVALID_BIZNO', 'message' => '사업자등록번호 형식을 확인해 주세요.');
		}

		$recent = macomaex_kodata_find_cache($bzno, true);
		if (!empty($recent['c_content'])) {
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
			return $live;
		}

		$latest = macomaex_kodata_find_cache($bzno, false);
		if (!empty($latest['c_content'])) {
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
