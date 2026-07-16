<?php
$root = dirname(__DIR__);
$assertions = 0;

function path_assert($condition, $message) {
	global $assertions;
	$assertions++;
	if ($condition) return;
	fwrite(STDERR, "FAIL: {$message}\n");
	exit(1);
}

function path_source($relative_path) {
	global $root;
	$content = file_get_contents($root . '/' . $relative_path);
	if ($content === false) {
		fwrite(STDERR, "FAIL: {$relative_path} 파일을 읽지 못했습니다.\n");
		exit(1);
	}
	return $content;
}

$get_set = path_source('get_set.php');
$trade_helper = path_source('ko/helper/trade_request_helper.php');
$company_detail_helper = path_source('ko/helper/company_detail_helper.php');
$my_company_connect = path_source('ko/my_company_connect.php');
$get_company_name = path_source('get_company_name.php');
$ai_helper = path_source('ko/helper/company_list_ai_helper.php');
$head = path_source('theme/prix/head.php');

$active_sources = array($get_set, $trade_helper, $company_detail_helper, $my_company_connect, $get_company_name, $ai_helper);
foreach ($active_sources as $source) {
	path_assert(strpos($source, 'persist_company') === false, '활성 경로에 구 persist_company 옵션이 없어야 한다.');
	path_assert(!preg_match("/'persist'\\s*=>\\s*true/", $source), '활성 경로에 persist=true가 없어야 한다.');
}

path_assert(strpos($get_set, 'macomaex_kodata_resolve_company') !== false, 'join/main은 공통 read-only resolver를 사용한다.');
path_assert(strpos($get_set, 'company_not_synced') !== false, 'join_comp_chk는 미동기화 기업을 구분한다.');
path_assert(strpos($get_set, 'COMPANY_NOT_SYNCED') !== false, 'join_comp_chk는 안정적인 미동기화 오류 코드를 반환한다.');

path_assert(strpos($my_company_connect, 'linkable_company') !== false, '내 회사 연결은 기존 company 행을 확인한다.');
path_assert(strpos($my_company_connect, "array('persist' => false)") !== false, '내 회사 연결 resolver는 read-only다.');

path_assert(strpos($company_detail_helper, 'requester_company_persist_failed') === false, '거래 요청에 company persist 실패 분기가 없어야 한다.');
path_assert(strpos($company_detail_helper, 'linkableRequesterCompany') !== false, '거래 요청은 회원 회사 연결 가능성을 별도로 확인한다.');

path_assert(strpos($get_company_name, 'macomaex_kodata_sync_payload') === false, '기업명 후속 조회는 sync하지 않는다.');
path_assert(strpos($ai_helper, 'macomaex_kodata_sync_payload') === false, 'AI 추천 후속 조회는 sync하지 않는다.');
path_assert(substr_count($get_set, 'macomaex_kodata_sync_payload(') === 1, '서버 자동 sync 호출은 get_set 한 곳이다.');
path_assert(strpos($head, 'sync_company: "main_recommendation"') !== false, '메인 추천 검색이 명시적 sync context를 보낸다.');
path_assert(strpos($get_set, 'KODATA_MAIN_SYNC_ENABLED') !== false, '메인 sync는 운영 비활성화 플래그를 제공한다.');

echo "OK: {$assertions} assertions\n";
