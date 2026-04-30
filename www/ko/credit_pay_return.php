<?php
include_once("./_common.php");
require_once('libs/INIStdPayUtil.php');
require_once('libs/HttpClient.php');
require_once('libs/properties.php');

if (!function_exists('macomaex_payment_log_write')) {
	function macomaex_payment_log_write($fields) {
		$log_dir = G5_PATH . '/logs/payments';
		if (!is_dir($log_dir)) {
			@mkdir($log_dir, 0777, true);
		}
		$log_file = $log_dir . '/payments_log_' . date('Ymd') . '.php';
		if (!file_exists($log_file)) {
			@file_put_contents($log_file, "<?php exit; ?>\n\n", FILE_APPEND | LOCK_EX);
		}

		$lines = array();
		$lines[] = '[' . date('Y-m-d H:i:s') . ']';
		foreach ((array)$fields as $key => $value) {
			if ($value === null) $value = '';
			if (is_array($value) || is_object($value)) {
				$value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}
			$value = str_replace(array("\r\n", "\r", "\n"), '\n', (string)$value);
			$lines[] = $key . ': ' . $value;
		}
		$lines[] = '---';
		$lines[] = '';
		@file_put_contents($log_file, implode("\n", $lines), FILE_APPEND | LOCK_EX);
	}
}

if (!function_exists('macomaex_payment_mask_value')) {
	function macomaex_payment_mask_value($key, $value) {
		$sensitive = array('authToken', 'CARD_Num', 'cardNumber', 'card_num', 'buyertel', 'buyeremail', 'buyeremail', 'buyerTel', 'buyerEmail');
		if (in_array((string)$key, $sensitive, true)) {
			$raw = (string)$value;
			$len = strlen($raw);
			if ($len <= 4) return str_repeat('*', $len);
			return substr($raw, 0, 2) . str_repeat('*', $len - 4) . substr($raw, -2);
		}
		return $value;
	}
}

if (!function_exists('macomaex_payment_mask_array')) {
	function macomaex_payment_mask_array($data) {
		if (!is_array($data)) return $data;
		$masked = array();
		foreach ($data as $k => $v) {
			if (is_array($v)) {
				$masked[$k] = macomaex_payment_mask_array($v);
				continue;
			}
			$masked[$k] = macomaex_payment_mask_value($k, $v);
		}
		return $masked;
	}
}

if (!function_exists('macomaex_payment_strip_token_array')) {
	function macomaex_payment_strip_token_array($data) {
		if (!is_array($data)) return $data;
		$stripped = array();
		foreach ($data as $k => $v) {
			if ((string)$k === 'authToken') {
				continue;
			}
			if (is_array($v)) {
				$stripped[$k] = macomaex_payment_strip_token_array($v);
			} else {
				$stripped[$k] = $v;
			}
		}
		return $stripped;
	}
}

$util = new INIStdPayUtil();
$prop = new properties();
$is_test_pay = defined('_test_pay') && strtoupper((string)_test_pay) === 'Y';
$stdpay_mid = (defined('_stdpay_mid') && trim((string)_stdpay_mid) !== '') ? trim((string)_stdpay_mid) : ((defined('_mid') && trim((string)_mid) !== '') ? trim((string)_mid) : '');
$stdpay_mid_test = (defined('_stdpay_mid_test') && trim((string)_stdpay_mid_test) !== '') ? trim((string)_stdpay_mid_test) : ($is_test_pay ? $stdpay_mid : '');
$stdpay_signkey = (defined('_stdpay_signkey') && trim((string)_stdpay_signkey) !== '') ? trim((string)_stdpay_signkey) : ((defined('_key') && trim((string)_key) !== '') ? trim((string)_key) : '');
$stdpay_signkey_test = (defined('_stdpay_signkey_test') && trim((string)_stdpay_signkey_test) !== '') ? trim((string)_stdpay_signkey_test) : (($is_test_pay && defined('_key') && trim((string)_key) !== '') ? trim((string)_key) : '');

$payment_log_base = array(
	'event_type' => 'credit_payment_approve',
	'context' => 'credit_pay_return',
	'request_uri' => isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '',
	'request_method' => isset($_SERVER['REQUEST_METHOD']) ? (string)$_SERVER['REQUEST_METHOD'] : '',
	'mb_id' => isset($member['mb_id']) ? (string)$member['mb_id'] : '',
	'order_no' => isset($_REQUEST['oid']) ? (string)$_REQUEST['oid'] : '',
	'pg_result_code' => isset($_REQUEST['resultCode']) ? (string)$_REQUEST['resultCode'] : '',
	'pg_result_msg' => isset($_REQUEST['resultMsg']) ? (string)$_REQUEST['resultMsg'] : ''
);

function credit_pay_fail($msg, $reason = 'payment_failed', $extra = array()) {
	global $payment_log_base;
	$log_fields = array_merge($payment_log_base, (array)$extra, array(
		'event_status' => 'fail',
		'reason' => (string)$reason,
		'message' => (string)$msg
	));
	macomaex_payment_log_write($log_fields);
	$msg = addslashes($msg);
	echo "<script>
		alert('결제실패: {$msg}');
		if(window.opener && !window.opener.closed){
			window.close();
		}else{
			location.href='/ko/plan.php';
		}
	</script>";
	exit;
}

try {
	if (strcmp("0000", $_REQUEST["resultCode"]) != 0) {
		$msg = isset($_REQUEST["resultMsg"]) ? $_REQUEST["resultMsg"] : "인증 실패";
		credit_pay_fail($msg, 'auth_result_not_ok');
	}

	$mid = $_REQUEST["mid"];
	$allowed_mids = array_filter(array($stdpay_mid, $stdpay_mid_test));
	if(!$mid || !in_array($mid, $allowed_mids, true)){
		credit_pay_fail("유효하지 않은 MID", 'invalid_mid', array('mid' => (string)$mid));
	}
	$signKey = ($stdpay_mid_test && $mid === $stdpay_mid_test) ? $stdpay_signkey_test : $stdpay_signkey;
	$timestamp = $util->getTimestamp();
	$authToken = $_REQUEST["authToken"];
	$idc_name = isset($_REQUEST["idc_name"]) ? (string)$_REQUEST["idc_name"] : '';
	$authUrl = isset($_REQUEST["authUrl"]) ? trim((string)$_REQUEST["authUrl"]) : '';
	$charset = "UTF-8";
	$format = "JSON";

	$parsedAuthUrl = parse_url($authUrl);
	$authHost = (is_array($parsedAuthUrl) && isset($parsedAuthUrl['host'])) ? strtolower((string)$parsedAuthUrl['host']) : '';
	$authPath = (is_array($parsedAuthUrl) && isset($parsedAuthUrl['path'])) ? (string)$parsedAuthUrl['path'] : '';
	$isAllowedHost = (bool)preg_match('/^[a-z0-9-]*stdpay\.inicis\.com$/', $authHost);
	$normalizedPath = rtrim($authPath, '/');
	$isValidAuthUrl = is_array($parsedAuthUrl)
		&& isset($parsedAuthUrl['scheme'], $parsedAuthUrl['host'], $parsedAuthUrl['path'])
		&& strtolower($parsedAuthUrl['scheme']) === 'https'
		&& $isAllowedHost
		&& preg_match('#/api/payAuth$#', $normalizedPath);
	if (!$isValidAuthUrl) {
		credit_pay_fail("authUrl 형식 검증 실패", 'invalid_auth_url', array('auth_url' => (string)$authUrl));
	}

	$signParam = array(
		"authToken" => $authToken,
		"timestamp" => $timestamp
	);
	$signature = $util->makeSignature($signParam);

	$veriParam = array(
		"authToken" => $authToken,
		"signKey" => $signKey,
		"timestamp" => $timestamp
	);
	$verification = $util->makeSignature($veriParam);

	$authMap = array(
		"mid" => $mid,
		"authToken" => $authToken,
		"signature" => $signature,
		"verification" => $verification,
		"timestamp" => $timestamp,
		"charset" => $charset,
		"format" => $format
	);
	$authMapLog = macomaex_payment_strip_token_array($authMap);

	$httpUtil = new HttpClient();
	if (!$httpUtil->processHTTP($authUrl, $authMap)) {
		credit_pay_fail("승인 요청 실패", 'http_process_failed', array(
			'auth_url' => (string)$authUrl,
			'pg_request_raw' => json_encode(macomaex_payment_mask_array($authMapLog), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
		));
	}

	$authResultString = $httpUtil->body;
	$resultMap = json_decode($authResultString, true);
	if (!is_array($resultMap) || $resultMap['resultCode'] !== '0000') {
		$msg = isset($resultMap['resultMsg']) ? $resultMap['resultMsg'] : '승인 실패';
		credit_pay_fail($msg, 'approve_failed', array(
			'pg_request_raw' => json_encode(macomaex_payment_mask_array($authMapLog), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			'pg_response_raw' => json_encode(macomaex_payment_mask_array((array)$resultMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
		));
	}

	$order_no = isset($resultMap['MOID']) ? $resultMap['MOID'] : '';
	$payment_log_base['order_no'] = (string)$order_no;
	$pinfo = sql_fetch("select * from g5_membership_payment where order_no='{$order_no}'");
	if(!$pinfo['order_no']){
		credit_pay_fail("주문 정보 없음", 'order_not_found');
	}
	$minfo = sql_fetch("select * from g5_membership_manage where no='{$pinfo['membership_parent_no']}'");
	$charge_credit = (int)$minfo['matching_cnt'];
	if($charge_credit < 1){
		credit_pay_fail("충전 수량이 0입니다.", 'invalid_charge_credit', array('membership_parent_no' => (string)$pinfo['membership_parent_no']));
	}

	$tid = addslashes($resultMap['tid']);
	$payMethod = addslashes($resultMap['payMethod']);
	$applDate = addslashes($resultMap['applDate']);
	$applTime = addslashes($resultMap['applTime']);
	$applNum = addslashes($resultMap['applNum']);
	$cardNum = addslashes($resultMap['CARD_Num']);
	$cardCode = addslashes($resultMap['CARD_Code']);
	$cardCheckFlag = addslashes($resultMap['CARD_CheckFlag']);
	$cardApplPrice = (int)$resultMap['TotPrice'];

	sql_query("START TRANSACTION");

	$already_charge = sql_fetch("select id from g5_matching_credit_history where order_no='{$order_no}' and tx_type='CHARGE' limit 1");
	if($already_charge['id']){
		sql_query("COMMIT");
		macomaex_payment_log_write(array_merge($payment_log_base, array(
			'event_status' => 'skip',
			'reason' => 'already_charged',
			'uid' => (string)$pinfo['uid'],
			'membership_parent_no' => (string)$pinfo['membership_parent_no'],
			'credit_delta' => (string)$charge_credit,
			'db_tx' => 'commit',
			'pg_request_raw' => json_encode(macomaex_payment_mask_array($authMapLog), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			'pg_response_raw' => json_encode(macomaex_payment_mask_array((array)$resultMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			'message' => '이미 충전 원장이 존재하여 중복 지급을 건너뛰었습니다.'
		)));
		echo "<script>
			if(window.opener && !window.opener.closed){
				window.opener.location.href='/ko/my_plan.php';
				window.close();
			}else{
				location.href='/ko/my_plan.php';
			}
		</script>";
		exit;
	}

	$payment_update_sql = "update g5_membership_payment set
		complete_yn='Y',
		complete_date=now(),
		mid='{$mid}',
		tid='{$tid}',
		app_date='{$applDate}',
		appl_time='{$applTime}',
		pay_method='{$payMethod}',
		appl_num='{$applNum}',
		card_num='{$cardNum}',
		card_Code='{$cardCode}',
		card_check_flag='{$cardCheckFlag}',
		card_appl_price='{$cardApplPrice}'
		where order_no='{$order_no}'";
	$payment_update = sql_query($payment_update_sql, false);
	if(!$payment_update){
		sql_query("ROLLBACK");
		credit_pay_fail("결제정보 저장 실패", 'payment_update_failed', array('db_tx' => 'rollback'));
	}

	sql_query("insert into g5_membership_paybill select * from g5_membership_payment where order_no='{$order_no}'", false);
	sql_query("update g5_membership_paybill set
		mid='{$mid}',
		tid='{$tid}',
		card_appl_price='{$cardApplPrice}',
		app_date='{$applDate}',
		appl_time='{$applTime}',
		appl_num='{$applNum}',
		card_Code='{$cardCode}',
		card_check_flag='{$cardCheckFlag}',
		card_num='{$cardNum}'
		where order_no='{$order_no}'", false);

	$status = sql_fetch("select * from g5_membership_status where uid='{$pinfo['uid']}' for update");
	if(!$status['uid']){
		$init_sql = "insert into g5_membership_status set
			uid='{$pinfo['uid']}',
			order_no='{$order_no}',
			date_begin='".date("Y-m-d")."',
			date_end='9999-12-31 23:59:59',
			month='0',
			open_able_cnt='0',
			open_cnt='0',
			matching_able_cnt='0',
			matching_cnt='0',
			expert_consulting_able_cnt='0',
			expert_consulting_cnt='0',
			selected_able_cnt='0',
			selected_cnt='0',
			regdate=now(),
			status='S',
			membership_parent_no='{$pinfo['membership_parent_no']}'";
		$init_ok = sql_query($init_sql, false);
		if(!$init_ok){
			sql_query("ROLLBACK");
			credit_pay_fail("잔액 상태 초기화 실패", 'status_init_failed', array('db_tx' => 'rollback'));
		}
		$status = sql_fetch("select * from g5_membership_status where uid='{$pinfo['uid']}' for update");
	}

	$current_credit = (int)$status['matching_able_cnt'];
	$credit_after = $current_credit + $charge_credit;

	$plan_title_en = trim((string)$minfo['title_en']);
	$plan_title_for_memo = preg_replace('/^CREDIT_/i', '', $plan_title_en);
	$plan_title_for_memo = str_replace('_', ' ', $plan_title_for_memo);
	if (preg_match('/^[A-Za-z0-9 ]+$/', $plan_title_for_memo)) {
		$plan_title_for_memo = ucwords(strtolower($plan_title_for_memo));
	}
	if ($plan_title_for_memo === '') {
		$plan_title_for_memo = '크레딧';
	}
	$charge_memo_text = $plan_title_for_memo . ' 플랜 충전';
	$memo = addslashes($charge_memo_text);
	$history_sql = "insert into g5_matching_credit_history set
		uid='{$pinfo['uid']}',
		tx_type='CHARGE',
		credit_delta='{$charge_credit}',
		credit_after='{$credit_after}',
		order_no='{$order_no}',
		matching_no='',
		memo='{$memo}',
		created_at=now()";
	$history_ok = sql_query($history_sql, false);
	if(!$history_ok){
		sql_query("ROLLBACK");
		credit_pay_fail("원장 기록 실패", 'credit_history_insert_failed', array('db_tx' => 'rollback'));
	}

	$status_update_sql = "update g5_membership_status set
		order_no='{$order_no}',
		date_begin='".date("Y-m-d")."',
		date_end='9999-12-31 23:59:59',
		status='S',
		membership_parent_no='{$pinfo['membership_parent_no']}',
		matching_able_cnt='{$credit_after}',
		matching_cnt=matching_cnt + {$charge_credit}
		where uid='{$pinfo['uid']}'";
	$status_ok = sql_query($status_update_sql, false);
	if(!$status_ok){
		sql_query("ROLLBACK");
		credit_pay_fail("잔액 반영 실패", 'status_update_failed', array('db_tx' => 'rollback'));
	}

	$snapshot = sql_fetch("select * from g5_membership_status where uid='{$pinfo['uid']}'");
	$memo_text = addslashes($charge_memo_text . "\n");
	$history_status_sql = "insert g5_membership_status_history set
		uid='{$pinfo['uid']}',
		order_no='{$order_no}',
		date_begin='{$snapshot['date_begin']}',
		date_end='{$snapshot['date_end']}',
		month='{$snapshot['month']}',
		open_able_cnt='{$snapshot['open_able_cnt']}',
		open_cnt='{$snapshot['open_cnt']}',
		matching_able_cnt='{$snapshot['matching_able_cnt']}',
		matching_cnt='{$snapshot['matching_cnt']}',
		expert_consulting_able_cnt='{$snapshot['expert_consulting_able_cnt']}',
		expert_consulting_cnt='{$snapshot['expert_consulting_cnt']}',
		selected_able_cnt='{$snapshot['selected_able_cnt']}',
		selected_cnt='{$snapshot['selected_cnt']}',
		regdate=now(),
		status='S',
		membership_parent_no='{$snapshot['membership_parent_no']}',
		memo='{$memo_text}',
		ext_1='".G5_TIME_YMDHIS."\\n'";
	sql_query($history_status_sql, false);

	$log_send = addslashes(json_encode($authMapLog, JSON_UNESCAPED_UNICODE));
	$log_recv = addslashes($authResultString);
	$log_sql = "insert g5_membership_payment_log set
		uid='{$pinfo['uid']}',
		order_no='{$order_no}',
		order_goods='".addslashes($pinfo['order_goods'])."',
		price='{$pinfo['price']}',
		send='{$log_send}',
		recive='{$log_recv}',
		regdate=now()";
	sql_query($log_sql, false);

	sql_query("COMMIT");
	macomaex_payment_log_write(array_merge($payment_log_base, array(
		'event_status' => 'success',
		'uid' => (string)$pinfo['uid'],
		'membership_parent_no' => (string)$pinfo['membership_parent_no'],
		'tid' => (string)$resultMap['tid'],
		'pay_method' => (string)$resultMap['payMethod'],
		'appl_date' => (string)$resultMap['applDate'],
		'appl_time' => (string)$resultMap['applTime'],
		'card_code' => (string)$resultMap['CARD_Code'],
		'card_num_masked' => (string)macomaex_payment_mask_value('CARD_Num', isset($resultMap['CARD_Num']) ? $resultMap['CARD_Num'] : ''),
		'card_appl_price' => (string)$cardApplPrice,
		'credit_delta' => (string)$charge_credit,
		'credit_before' => (string)$current_credit,
		'credit_after' => (string)$credit_after,
		'db_tx' => 'commit',
		'pg_request_raw' => json_encode(macomaex_payment_mask_array($authMapLog), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		'pg_response_raw' => json_encode(macomaex_payment_mask_array((array)$resultMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		'message' => '승인 완료 후 결제 및 크레딧 DB 반영을 완료했습니다.'
	)));
	echo "<script>
		if(window.opener && !window.opener.closed){
			window.opener.location.href='/ko/my_plan.php';
			window.close();
		}else{
			location.href='/ko/my_plan.php';
		}
	</script>";
	exit;

} catch (Exception $e) {
	sql_query("ROLLBACK", false);
	credit_pay_fail($e->getMessage(), 'exception', array('db_tx' => 'rollback'));
}
?>
