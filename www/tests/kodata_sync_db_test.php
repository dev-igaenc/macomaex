<?php
$www_path = dirname(__DIR__);
chdir($www_path);

$_SERVER['SCRIPT_FILENAME'] = $www_path . '/tests/kodata_sync_db_test.php';
$_SERVER['SCRIPT_NAME'] = '/tests/kodata_sync_db_test.php';
$_SERVER['REQUEST_URI'] = '/tests/kodata_sync_db_test.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = 80;
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once $www_path . '/common.php';
require_once $www_path . '/lib/kodata.lib.php';

$assertions = 0;
$test_kedcd = '9999999988';
$conflict_kedcd = '9999999987';
$test_bzno = '9999999988';

function db_contract_assert_same($expected, $actual, $message) {
	global $assertions;
	$assertions++;
	if ($expected === $actual) return;
	throw new RuntimeException("{$message}; expected=" . var_export($expected, true) . '; actual=' . var_export($actual, true));
}

$fixture_path = __DIR__ . '/fixtures/kodata_company_full.json';
$fixture = json_decode((string)file_get_contents($fixture_path), true);
if (!is_array($fixture)) throw new RuntimeException('KODATA DB test fixture를 읽지 못했습니다.');
$fixture['CONTENTS']['ENPintr0000']['kedcd'] = $test_kedcd;
$fixture['CONTENTS']['ENPintr0000']['bzno'] = $test_bzno;
$json = json_encode($fixture, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$collision = sql_fetch("select count(*) as cnt from company where KEDCD in ('{$test_kedcd}', '{$conflict_kedcd}') or BZNO='{$test_bzno}'", false);
if ((int)($collision['cnt'] ?? 0) > 0) {
	throw new RuntimeException('테스트용 KEDCD/BZNO가 이미 존재하여 DB test를 중단합니다.');
}

if (!sql_query('START TRANSACTION', false)) throw new RuntimeException('DB test transaction을 시작하지 못했습니다.');

try {
	// 신규 기업은 세 원천 테이블에 필요한 행 수만큼 생성한다.
	$first = macomaex_kodata_sync_payload($test_bzno, $json, 'kodata_live', array('manage_transaction' => false));
	db_contract_assert_same(true, !empty($first['success']), 'first sync succeeds');
	db_contract_assert_same('inserted', $first['company']['action'] ?? '', 'first sync inserts company');
	db_contract_assert_same(3, (int)($first['financial']['stats']['inserted'] ?? 0), 'first sync inserts financial rows');
	db_contract_assert_same(1, (int)($first['stock']['stats']['inserted'] ?? 0), 'first sync inserts stock row');

	$company_count = sql_fetch("select count(*) as cnt from company where KEDCD='{$test_kedcd}' and BZNO='{$test_bzno}'", false);
	$financial_count = sql_fetch("select count(*) as cnt from company_financial where KEDCD='{$test_kedcd}'", false);
	$stock_count = sql_fetch("select count(*) as cnt from company_stock where KEDCD='{$test_kedcd}'", false);
	db_contract_assert_same(1, (int)$company_count['cnt'], 'one company row after first sync');
	db_contract_assert_same(3, (int)$financial_count['cnt'], 'three financial rows after first sync');
	db_contract_assert_same(1, (int)$stock_count['cnt'], 'one stock row after first sync');

	sql_query("update company set BZNO='' where KEDCD='{$test_kedcd}'", false);
	// 기존 기업은 빈 API 값으로 정상 값을 지우지 않고 누락 식별값은 복구한다.
	$fixture['CONTENTS']['ENPintr0000']['enp_nm'] = '수정된 테스트산업';
	$fixture['CONTENTS']['ENPintr0000']['hpage_url'] = '';
	$updated_json = json_encode($fixture, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	$second = macomaex_kodata_sync_payload($test_bzno, $updated_json, 'company_api_log_1m', array('manage_transaction' => false));
	db_contract_assert_same(true, !empty($second['success']), 'second sync succeeds');
	db_contract_assert_same('updated', $second['company']['action'] ?? '', 'second sync updates company');

	$company = sql_fetch("select ENP_NM, HPAGE_URL, BZNO from company where KEDCD='{$test_kedcd}'", false);
	db_contract_assert_same('수정된 테스트산업', $company['ENP_NM'], 'non-empty API value updates company');
	db_contract_assert_same('https://example.test', $company['HPAGE_URL'], 'empty API value preserves company value');
	db_contract_assert_same($test_bzno, $company['BZNO'], 'empty existing BZNO is repaired');

	sql_query("insert company_financial set KEDCD='{$test_kedcd}', ACCT_DT='20251231', SUMASSET=1, PAYMENTFUND=1, FUNDTOTAL=1, SALES=1, PROFIT=1, TERMNETPROFIT=1, ext_1='duplicate'", false);
	sql_query("insert company_stock set KEDCD='{$test_kedcd}', STD_DT='20251231', ISS_STK_CN=1, CSTK_CN=1, PSTK_CN=0, ext_1='duplicate'", false);
	// 같은 기준키의 기존 중복은 재동기화 시 1행으로 정리한다.
	$third = macomaex_kodata_sync_payload($test_bzno, $updated_json, 'company_api_log_latest', array('manage_transaction' => false));
	db_contract_assert_same(true, !empty($third['success']), 'third sync succeeds with duplicates');
	db_contract_assert_same(1, (int)($third['financial']['stats']['deduplicated'] ?? 0), 'financial duplicate removed');
	db_contract_assert_same(1, (int)($third['stock']['stats']['deduplicated'] ?? 0), 'stock duplicate removed');

	$latest_financial_count = sql_fetch("select count(*) as cnt from company_financial where KEDCD='{$test_kedcd}' and ACCT_DT='20251231'", false);
	$latest_stock_count = sql_fetch("select count(*) as cnt from company_stock where KEDCD='{$test_kedcd}' and STD_DT='20251231'", false);
	db_contract_assert_same(1, (int)$latest_financial_count['cnt'], 'financial key normalized to one row');
	db_contract_assert_same(1, (int)$latest_stock_count['cnt'], 'stock key normalized to one row');

	$conflict_insert = sql_query("insert company set KEDCD='{$conflict_kedcd}', BZNO='{$test_bzno}', ENP_NM='충돌기업', ENP_NM_TRD='충돌기업'", false);
	db_contract_assert_same(true, (bool)$conflict_insert, 'identity conflict fixture inserted');
	// 동일 BZNO가 다른 KEDCD를 가리키면 덮어쓰지 않고 충돌로 중단한다.
	$conflict = macomaex_kodata_sync_payload($test_bzno, $updated_json, 'kodata_live', array('manage_transaction' => false));
	db_contract_assert_same(false, !empty($conflict['success']), 'identity conflict rejected');
	db_contract_assert_same('COMPANY_IDENTITY_CONFLICT', $conflict['error_code'] ?? '', 'identity conflict error code');

	sql_query('ROLLBACK', false);
} catch (Throwable $e) {
	sql_query('ROLLBACK', false);
	fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
	exit(1);
}

$after = sql_fetch("select count(*) as cnt from company where KEDCD in ('{$test_kedcd}', '{$conflict_kedcd}') or BZNO='{$test_bzno}'", false);
db_contract_assert_same(0, (int)$after['cnt'], 'DB test leaves no company rows after rollback');
$after_financial = sql_fetch("select count(*) as cnt from company_financial where KEDCD='{$test_kedcd}'", false);
$after_stock = sql_fetch("select count(*) as cnt from company_stock where KEDCD='{$test_kedcd}'", false);
db_contract_assert_same(0, (int)$after_financial['cnt'], 'DB test leaves no financial rows after rollback');
db_contract_assert_same(0, (int)$after_stock['cnt'], 'DB test leaves no stock rows after rollback');

echo "OK: {$assertions} assertions (transaction rolled back)\n";
