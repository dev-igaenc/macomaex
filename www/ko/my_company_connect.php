<?php
include_once('./_common.php');
include_once(G5_PATH . '/ko/helper/trade_request_helper.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function my_company_connect_response($status, $payload)
{
	http_response_code((int)$status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

if (empty($member['mb_id'])) {
	my_company_connect_response(401, array('success' => false, 'message' => '로그인 후 이용해 주세요.'));
}

$request = $_POST;
$posted_token = trim((string)($request['csrf_token'] ?? ''));
$session_token = trim((string)get_session('ss_my_company_connect_token'));
if ($posted_token === '' || $session_token === '' || !hash_equals($session_token, $posted_token)) {
	my_company_connect_response(403, array('success' => false, 'message' => '요청이 만료되었습니다. 페이지를 새로고침해 주세요.'));
}

$action = trim((string)($request['action'] ?? 'preview'));
if (!in_array($action, array('preview', 'connect'), true)) {
	my_company_connect_response(422, array('success' => false, 'message' => '지원하지 않는 요청입니다.'));
}

$bizno = preg_replace('/[^0-9]/', '', (string)($request['bizno'] ?? ''));
if (!preg_match('/^[0-9]{10}$/', $bizno)) {
	my_company_connect_response(422, array('success' => false, 'message' => '사업자등록번호 10자리를 확인해 주세요.'));
}

$current_company_code = trim((string)($member['mb_company'] ?? ''));
$current_company = array();
if ($current_company_code !== '' && !preg_match('/^M[0-9]{9}$/', $current_company_code)) {
	$current_company_code_sql = sql_escape_string($current_company_code);
	$current_company = sql_fetch("SELECT KEDCD FROM company WHERE KEDCD='{$current_company_code_sql}' LIMIT 1");
}
if (!empty($current_company['KEDCD'])) {
	my_company_connect_response(409, array('success' => false, 'message' => '이미 회사 정보가 연결된 계정입니다.'));
}

$persist = ($action === 'connect');
if ($persist) {
	sql_query('START TRANSACTION');
}

try {
	$resolved = trade_request_resolve_company_data_by_bzno($bizno, array('persist' => $persist));
} catch (Throwable $e) {
	if ($persist) {
		sql_query('ROLLBACK');
	}
	error_log('[my_company_connect] ' . json_encode(array(
		'action' => $action,
		'bizno' => $bizno,
		'error' => $e->getMessage(),
		'file' => $e->getFile(),
		'line' => $e->getLine(),
	), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	my_company_connect_response(500, array(
		'success' => false,
		'message' => '기업 정보 확인 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.',
	));
}
if (empty($resolved['success']) || empty($resolved['company'])) {
	if ($persist) {
		sql_query('ROLLBACK');
	}
	my_company_connect_response(422, array(
		'success' => false,
		'message' => trim((string)($resolved['message'] ?? '기업 정보를 확인하지 못했습니다.')),
	));
}

$company = $resolved['company'];
if (!$persist) {
	my_company_connect_response(200, array(
		'success' => true,
		'company' => $company,
		'source' => (string)($resolved['source'] ?? ''),
		'cache_saved' => $resolved['cache_saved'] ?? null,
		'fallback_error_code' => (string)($resolved['fallback_error_code'] ?? ''),
	));
}

$kedcd = trim((string)($company['kedcd'] ?? ''));
$formatted_bizno = function_exists('licenseNum') ? licenseNum($bizno) : $bizno;
if ($kedcd === '') {
	sql_query('ROLLBACK');
	my_company_connect_response(422, array('success' => false, 'message' => '기업코드를 확인하지 못했습니다.'));
}

$member_id_sql = sql_escape_string((string)$member['mb_id']);
$update_fields = array(
	"mb_company='" . sql_escape_string($kedcd) . "'",
	"mb_4='" . sql_escape_string($formatted_bizno) . "'",
);
$company_name = trim((string)($company['company'] ?? ''));
if ($company_name !== '' && (trim((string)($member['mb_name'] ?? '')) === '' || $member['mb_name'] === $member['mb_id'])) {
	$update_fields[] = "mb_name='" . sql_escape_string($company_name) . "'";
}
if ($company_name !== '' && (trim((string)($member['mb_nick'] ?? '')) === '' || $member['mb_nick'] === $member['mb_id'])) {
	$update_fields[] = "mb_nick='" . sql_escape_string($company_name) . "'";
}

$update_ok = sql_query(
	"UPDATE {$g5['member_table']}
	 SET " . implode(",\n	     ", $update_fields) . "
	 WHERE mb_id='{$member_id_sql}'",
	false
);
if (!$update_ok) {
	sql_query('ROLLBACK');
	my_company_connect_response(500, array('success' => false, 'message' => '회원 회사정보 연결에 실패했습니다. 잠시 후 다시 시도해 주세요.'));
}

sql_query('COMMIT');
set_session('ss_my_company_connect_token', '');
my_company_connect_response(200, array(
	'success' => true,
	'company' => $company,
	'redirect' => '/account/company',
));
?>
