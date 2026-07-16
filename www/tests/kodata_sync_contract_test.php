<?php
define('_GNUBOARD_', true);
require_once dirname(__DIR__) . '/lib/kodata.lib.php';

$assertions = 0;

function contract_assert_same($expected, $actual, $message) {
	global $assertions;
	$assertions++;
	if ($expected === $actual) return;

	fwrite(STDERR, "FAIL: {$message}\nEXPECTED: " . var_export($expected, true) . "\nACTUAL: " . var_export($actual, true) . "\n");
	exit(1);
}

function contract_fixture($name) {
	$path = __DIR__ . '/fixtures/' . $name;
	$content = file_get_contents($path);
	if ($content === false) {
		fwrite(STDERR, "FAIL: fixture를 읽을 수 없습니다: {$path}\n");
		exit(1);
	}
	return $content;
}

// 정상 전체 응답: 기업개요, 3개년 재무, 주식 합계 매핑을 검증한다.
$full_json = contract_fixture('kodata_company_full.json');
$full = macomaex_kodata_map_sync_payload($full_json, '1234567890');

contract_assert_same('0001234567', $full['company']['KEDCD'], 'full company KEDCD');
contract_assert_same('1234567890', $full['company']['BZNO'], 'full company BZNO');
contract_assert_same('테스트산업', $full['company']['ENP_NM_TRD'], 'full company name');
contract_assert_same('42', $full['company']['LABORER_SUM'], 'full company laborer sum');
contract_assert_same('TEST INDUSTRY CO., LTD.', $full['company']['ENP_NM_ENG'], 'extended company field');
contract_assert_same('1500000', $full['company']['SALES'], 'latest financial sales copied to company');
contract_assert_same(3, count($full['financial']), 'three financial rows');
contract_assert_same('20251231', $full['financial'][0]['ACCT_DT'], 'latest financial date');
contract_assert_same('1000000', $full['financial'][0]['SUMASSET'], 'financial commas normalized');
contract_assert_same('1500000', $full['financial'][0]['SALES'], 'financial sales mapping');
contract_assert_same(1, count($full['stock']), 'one stock aggregate row');
contract_assert_same('100000', $full['stock'][0]['ISS_STK_CN'], 'issued stock aggregate');
contract_assert_same('90000', $full['stock'][0]['CSTK_CN'], 'common stock aggregate');
contract_assert_same('10000', $full['stock'][0]['PSTK_CN'], 'preferred stock aggregate');

// 부분 응답: 빈 객체와 누락 필드를 안전하게 보정하는지 검증한다.
$partial_json = contract_fixture('kodata_company_partial.json');
$partial = macomaex_kodata_map_sync_payload($partial_json, '9876543210');

contract_assert_same('부분응답기업', $partial['company']['ENP_NM'], 'partial company fallback name');
contract_assert_same('부분응답기업', $partial['company']['ENP_NM_TRD'], 'object trade name falls back to company name');
contract_assert_same('', $partial['company']['BZC_CD'], 'object industry code becomes empty');
contract_assert_same('', $partial['company']['SALES'], 'missing latest sales does not invent a company value');
contract_assert_same(1, count($partial['financial']), 'partial payload keeps valid financial year only');
contract_assert_same('20241231', $partial['financial'][0]['ACCT_DT'], 'financial date normalized');
contract_assert_same('', $partial['financial'][0]['SALES'], 'object numeric value becomes empty');
contract_assert_same('1000', $partial['stock'][0]['ISS_STK_CN'], 'single stock object normalized');
contract_assert_same('0', $partial['stock'][0]['PSTK_CN'], 'missing preferred stock aggregates to zero');

// 실제 KODATA 원문만 허용하고 DB fallback이나 잘못된 식별값은 거부한다.
$valid_sources = array('kodata_live', 'company_api_log_1m', 'company_api_log_latest');
foreach ($valid_sources as $source) {
	$result = macomaex_kodata_validate_sync_payload('123-45-67890', $full_json, $source);
	contract_assert_same(true, $result['eligible'], "{$source} is persistable");
}

$company_fallback = macomaex_kodata_validate_sync_payload('1234567890', $full_json, 'company');
contract_assert_same(false, $company_fallback['eligible'], 'company fallback is read-only');
contract_assert_same('SOURCE_NOT_PERSISTABLE', $company_fallback['error_code'], 'company fallback error code');

$mismatch = macomaex_kodata_validate_sync_payload('1111111111', $full_json, 'kodata_live');
contract_assert_same(false, $mismatch['eligible'], 'BIZNO mismatch rejected');
contract_assert_same('BIZNO_MISMATCH', $mismatch['error_code'], 'BIZNO mismatch error code');

$invalid_kedcd_data = json_decode($full_json, true);
$invalid_kedcd_data['CONTENTS']['ENPintr0000']['kedcd'] = '123';
$invalid_kedcd_json = json_encode($invalid_kedcd_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$invalid_kedcd = macomaex_kodata_validate_sync_payload('1234567890', $invalid_kedcd_json, 'kodata_live');
contract_assert_same(false, $invalid_kedcd['eligible'], 'invalid KEDCD rejected');
contract_assert_same('INVALID_KEDCD', $invalid_kedcd['error_code'], 'invalid KEDCD error code');

$empty_name_data = json_decode($full_json, true);
$empty_name_data['CONTENTS']['ENPintr0000']['enp_nm'] = '';
$empty_name_data['CONTENTS']['ENPintr0000']['enp_nm_trd'] = '';
$empty_name_json = json_encode($empty_name_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$empty_name = macomaex_kodata_validate_sync_payload('1234567890', $empty_name_json, 'kodata_live');
contract_assert_same(false, $empty_name['eligible'], 'empty company name rejected');
contract_assert_same('EMPTY_COMPANY_NAME', $empty_name['error_code'], 'empty company name error code');

$invalid_json = macomaex_kodata_validate_sync_payload('1234567890', '{', 'kodata_live');
contract_assert_same(false, $invalid_json['eligible'], 'invalid JSON rejected');
contract_assert_same('INVALID_PAYLOAD', $invalid_json['error_code'], 'invalid JSON has stable validation result');

echo "OK: {$assertions} assertions\n";
