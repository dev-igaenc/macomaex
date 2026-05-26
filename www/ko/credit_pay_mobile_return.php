<?php
include_once("./_common.php");

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
			$value = str_replace(array("\r\n", "\r", "\n"), '\\n', (string)$value);
			$lines[] = $key . ': ' . $value;
		}
		$lines[] = '---';
		$lines[] = '';
		@file_put_contents($log_file, implode("\n", $lines), FILE_APPEND | LOCK_EX);
	}
}

if (!function_exists('macomaex_payment_mask_value')) {
	function macomaex_payment_mask_value($key, $value) {
		$sensitive = array('P_CARD_NUM', 'CARD_Num', 'cardNumber', 'card_num', 'buyertel', 'buyeremail', 'P_MOBILE', 'P_EMAIL');
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

if (!function_exists('macomaex_parse_nvp')) {
	function macomaex_parse_nvp($raw) {
		$raw = trim((string)$raw);
		if ($raw === '') return array();

		$normalized = str_replace(array("\r\n", "\r", "\n"), '&', $raw);
		$normalized = preg_replace('/[&]+/', '&', (string)$normalized);
		$normalized = trim((string)$normalized, '&');
		if ($normalized === '') return array();

		$out = array();
		parse_str($normalized, $out);
		if (!is_array($out) || empty($out)) {
			$out = array();
			$pairs = explode('&', $normalized);
			foreach ($pairs as $pair) {
				if ($pair === '') continue;
				$parts = explode('=', $pair, 2);
				$k = isset($parts[0]) ? urldecode((string)$parts[0]) : '';
				$v = isset($parts[1]) ? urldecode((string)$parts[1]) : '';
				if ($k === '') continue;
				$out[$k] = $v;
			}
		}

		return is_array($out) ? $out : array();
	}
}

$is_test_pay = defined('_test_pay') && strtoupper((string)_test_pay) === 'Y';
$stdpay_mid = (defined('_stdpay_mid') && trim((string)_stdpay_mid) !== '') ? trim((string)_stdpay_mid) : ((defined('_mid') && trim((string)_mid) !== '') ? trim((string)_mid) : '');
$stdpay_mid_test = (defined('_stdpay_mid_test') && trim((string)_stdpay_mid_test) !== '') ? trim((string)_stdpay_mid_test) : ($is_test_pay ? $stdpay_mid : '');

$payment_log_base = array(
	'event_type' => 'mobile_credit_pay_return',
	'context' => 'credit_pay_mobile_return',
	'request_uri' => isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '',
	'request_method' => isset($_SERVER['REQUEST_METHOD']) ? (string)$_SERVER['REQUEST_METHOD'] : '',
	'mb_id' => isset($member['mb_id']) ? (string)$member['mb_id'] : '',
	'order_no' => isset($_REQUEST['P_OID']) ? (string)$_REQUEST['P_OID'] : '',
	'pg_result_code' => isset($_REQUEST['P_STATUS']) ? (string)$_REQUEST['P_STATUS'] : '',
	'pg_result_msg' => isset($_REQUEST['P_RMESG1']) ? (string)$_REQUEST['P_RMESG1'] : ''
);

function credit_mobile_pay_fail($msg, $reason = 'mobile_payment_failed', $extra = array()) {
	global $payment_log_base;
	macomaex_payment_log_write(array_merge($payment_log_base, (array)$extra, array(
		'event_status' => 'fail',
		'reason' => (string)$reason,
		'message' => (string)$msg
	)));
	$alert_message = '결제실패: ' . (string)$msg;
	$alert_json = json_encode(
		$alert_message,
		JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	);
	if ($alert_json === false) {
		$alert_json = json_encode('결제실패', JSON_UNESCAPED_UNICODE);
	}
	echo "<script>
		alert({$alert_json});
		location.href='/ko/plan.php';
	</script>";
	exit;
}

if(!$member['mb_id']) {
	credit_mobile_pay_fail("로그인 후 이용하세요.", 'not_logged_in');
}

// 1) 인증단계 실패는 주문번호 검증보다 먼저 처리해서 PG 원문 오류를 그대로 노출한다.
$callbackPayload = (array)$_REQUEST;
$callbackPayloadMasked = macomaex_payment_mask_array($callbackPayload);
$p_status = isset($_REQUEST['P_STATUS']) ? (string)$_REQUEST['P_STATUS'] : '';
$p_result_msg = isset($_REQUEST['P_RMESG1']) ? trim((string)$_REQUEST['P_RMESG1']) : '';

if($p_status !== '00'){
	$resultmsg = $p_result_msg !== '' ? $p_result_msg : '인증 실패';
	credit_mobile_pay_fail($resultmsg, 'mobile_auth_failed', array(
		'pg_callback_raw' => json_encode($callbackPayloadMasked, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
	));
}

// 카드사 앱 복귀 경로에서는 P_OID가 누락될 수 있어, 승인응답(P_OID)으로 최종 주문번호를 확정한다.
$orderid = isset($_REQUEST['P_OID']) ? preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$_REQUEST['P_OID']) : '';
if($orderid === '' && isset($_REQUEST['orderid'])) {
	$orderid = preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$_REQUEST['orderid']);
}
$payment_log_base['order_no'] = (string)$orderid;

$p_mid = isset($_REQUEST['P_MID']) ? trim((string)$_REQUEST['P_MID']) : '';
$p_tid = isset($_REQUEST['P_TID']) ? trim((string)$_REQUEST['P_TID']) : '';
$p_amt = isset($_REQUEST['P_AMT']) ? (int)preg_replace('/[^0-9]/', '', (string)$_REQUEST['P_AMT']) : 0;
$idc_name = strtolower(trim((string)(isset($_REQUEST['idc_name']) ? $_REQUEST['idc_name'] : '')));
$p_req_url = trim((string)(isset($_REQUEST['P_REQ_URL']) ? $_REQUEST['P_REQ_URL'] : ''));

$allowed_mids = array_filter(array($stdpay_mid, $stdpay_mid_test));
// 일부 카드사 앱 복귀 콜백은 P_MID를 누락하므로, TID에 포함된 MID 또는 허용 MID 1개값으로 보완한다.
$mid_resolve_source = '';
if($p_mid === '' && $p_tid !== ''){
	foreach ($allowed_mids as $cand_mid) {
		$cand_mid = trim((string)$cand_mid);
		if($cand_mid !== '' && stripos($p_tid, $cand_mid) !== false){
			$p_mid = $cand_mid;
			$mid_resolve_source = 'tid';
			break;
		}
	}
}
if($p_mid === '' && count($allowed_mids) === 1){
	$only_mid = array_values($allowed_mids);
	$p_mid = trim((string)$only_mid[0]);
	if($p_mid !== '') $mid_resolve_source = 'single_allowed';
}
if(!$p_mid || !in_array($p_mid, $allowed_mids, true)){
	credit_mobile_pay_fail("유효하지 않은 MID", 'invalid_mid', array(
		'mid' => (string)$p_mid,
		'mid_resolve_source' => (string)$mid_resolve_source,
		'p_tid' => (string)$p_tid,
		'allowed_mids' => json_encode(array_values($allowed_mids), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		'pg_callback_raw' => json_encode($callbackPayloadMasked, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
	));
}

if($p_tid === '' || !preg_match('/^[A-Za-z0-9_\-]+$/', $p_tid)){
	credit_mobile_pay_fail("유효하지 않은 인증거래번호", 'invalid_tid', array('p_tid' => (string)$p_tid));
}
if($p_amt < 1){
	credit_mobile_pay_fail("유효하지 않은 결제금액", 'invalid_amount', array('p_amt' => (string)$p_amt));
}

$parsedReqUrl = parse_url($p_req_url);
$reqHost = (is_array($parsedReqUrl) && isset($parsedReqUrl['host'])) ? strtolower((string)$parsedReqUrl['host']) : '';
$reqPath = (is_array($parsedReqUrl) && isset($parsedReqUrl['path'])) ? (string)$parsedReqUrl['path'] : '';
$isAllowedHost = (bool)preg_match('/^[a-z0-9-]*mobile\.inicis\.com$/', $reqHost);
$isValidReqUrl = is_array($parsedReqUrl)
	&& isset($parsedReqUrl['scheme'], $parsedReqUrl['host'])
	&& strtolower((string)$parsedReqUrl['scheme']) === 'https'
	&& $isAllowedHost
	&& stripos($reqPath, '/smart/') === 0;
if(!$isValidReqUrl){
	credit_mobile_pay_fail("승인 URL 검증 실패", 'invalid_req_url', array('p_req_url' => (string)$p_req_url));
}

if($idc_name !== '') {
	$idcHostMap = array(
		'stg' => 'stgmobile.inicis.com',
		'fc' => 'fcmobile.inicis.com',
		'ks' => 'ksmobile.inicis.com'
	);
	if(isset($idcHostMap[$idc_name]) && $reqHost !== $idcHostMap[$idc_name]) {
		credit_mobile_pay_fail("IDC 정보 검증 실패", 'invalid_idc_vs_req_url', array(
			'idc_name' => (string)$idc_name,
			'req_host' => (string)$reqHost
		));
	}
}

$approveRequestMap = array(
	'P_MID' => $p_mid,
	'P_TID' => $p_tid
);
$postData = http_build_query($approveRequestMap);

// 2) 인증 성공 후 P_REQ_URL로 승인요청을 보내 최종 승인 결과를 받는다.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $p_req_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=UTF-8'));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
$approvalRaw = curl_exec($ch);
$curl_error = curl_error($ch);
$curl_errno = curl_errno($ch);
curl_close($ch);

if($approvalRaw === false || $curl_errno){
	credit_mobile_pay_fail("승인 요청 중 통신 오류가 발생했습니다.", 'approve_http_failed', array(
		'curl_errno' => (string)$curl_errno,
		'curl_error' => (string)$curl_error,
		'req_url' => (string)$p_req_url,
		'pg_request_raw' => json_encode(macomaex_payment_mask_array($approveRequestMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
	));
}

$resultMap = macomaex_parse_nvp((string)$approvalRaw);
if(!is_array($resultMap) || !isset($resultMap['P_STATUS']) || (string)$resultMap['P_STATUS'] !== '00'){
	$resultMsg = is_array($resultMap) && isset($resultMap['P_RMESG1']) ? (string)$resultMap['P_RMESG1'] : '승인 실패';
	credit_mobile_pay_fail($resultMsg, 'approve_failed', array(
		'pg_request_raw' => json_encode(macomaex_payment_mask_array($approveRequestMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		'pg_response_raw' => json_encode(macomaex_payment_mask_array((array)$resultMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
	));
}

$order_no = isset($resultMap['P_OID']) ? preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$resultMap['P_OID']) : $orderid;
if($order_no === ''){
	credit_mobile_pay_fail("주문번호가 없습니다.", 'missing_order_no_after_approve', array(
		'pg_callback_raw' => json_encode($callbackPayloadMasked, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		'pg_response_raw' => json_encode(macomaex_payment_mask_array((array)$resultMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
	));
}
if($orderid !== '' && $order_no !== $orderid) {
	credit_mobile_pay_fail("주문번호 검증 실패", 'order_mismatch', array(
		'order_no_request' => (string)$orderid,
		'order_no_result' => (string)$order_no
	));
}
$payment_log_base['order_no'] = (string)$order_no;

$pinfo = sql_fetch("select * from g5_membership_payment where order_no='{$order_no}' and uid='{$member['mb_id']}'");
if(!$pinfo['order_no']){
	credit_mobile_pay_fail("결제 주문 정보를 찾을 수 없습니다.", 'order_not_found_after_approve');
}

$already_charged_pre = sql_fetch("select id from credit_history where order_no='{$order_no}' and tx_type='CHARGE' limit 1");
if(!is_array($already_charged_pre)) $already_charged_pre = array();
if(!is_array($pinfo)) $pinfo = array();
if(!empty($already_charged_pre['id']) || ((string)($pinfo['complete_yn'] ?? '') === 'Y' && trim((string)($pinfo['tid'] ?? '')) !== '')){
	macomaex_payment_log_write(array_merge($payment_log_base, array(
		'event_status' => 'skip',
		'reason' => 'already_paid_or_charged',
		'message' => '이미 처리된 주문으로 재승인을 건너뜁니다.'
	)));
	echo "<script>location.href='/ko/my_plan.php';</script>";
	exit;
}

$minfo = sql_fetch("select * from g5_membership_manage where no='{$pinfo['membership_parent_no']}'");
if(!is_array($minfo)) $minfo = array();
$charge_credit = (int)$minfo['matching_cnt'];
if($charge_credit < 1){
	credit_mobile_pay_fail("충전 수량이 0입니다.", 'invalid_charge_credit', array('membership_parent_no' => (string)$pinfo['membership_parent_no']));
}

$authDt = isset($resultMap['P_AUTH_DT']) ? preg_replace('/[^0-9]/', '', (string)$resultMap['P_AUTH_DT']) : '';
$applDate = strlen($authDt) >= 8 ? substr($authDt, 0, 8) : '';
$applTime = strlen($authDt) >= 14 ? substr($authDt, 8, 6) : '';
$payMethod = addslashes((string)(isset($resultMap['P_TYPE']) ? $resultMap['P_TYPE'] : 'CARD'));
$tid = addslashes((string)(isset($resultMap['P_TID']) ? $resultMap['P_TID'] : ''));
$applNum = addslashes((string)(isset($resultMap['P_AUTH_NO']) ? $resultMap['P_AUTH_NO'] : ''));
$cardNum = addslashes((string)(isset($resultMap['P_CARD_NUM']) ? $resultMap['P_CARD_NUM'] : ''));
$cardCode = addslashes((string)(isset($resultMap['P_FN_CD1']) ? $resultMap['P_FN_CD1'] : ''));
$cardCheckFlag = addslashes((string)(isset($resultMap['P_CARD_USE_FLAG']) ? $resultMap['P_CARD_USE_FLAG'] : ''));
$cardApplPrice = isset($resultMap['P_AMT']) ? (int)preg_replace('/[^0-9]/', '', (string)$resultMap['P_AMT']) : (int)$pinfo['price'];
$expectedPrice = (int)$pinfo['price'];
if($expectedPrice < 1){
	credit_mobile_pay_fail("주문 결제금액이 올바르지 않습니다.", 'invalid_order_amount', array(
		'order_no' => (string)$order_no,
		'order_price' => (string)$expectedPrice
	));
}
if($cardApplPrice !== $expectedPrice){
	credit_mobile_pay_fail("승인 금액이 주문 금액과 일치하지 않습니다.", 'approval_amount_mismatch', array(
		'order_no' => (string)$order_no,
		'order_price' => (string)$expectedPrice,
		'approved_price' => (string)$cardApplPrice,
		'callback_price' => (string)$p_amt
	));
}

sql_query("START TRANSACTION");

$already_charge = sql_fetch("select id from credit_history where order_no='{$order_no}' and tx_type='CHARGE' limit 1");
if(!is_array($already_charge)) $already_charge = array();
if(!empty($already_charge['id'])){
	sql_query("COMMIT");
	macomaex_payment_log_write(array_merge($payment_log_base, array(
		'event_status' => 'skip',
		'reason' => 'already_charged',
			'uid' => (string)$pinfo['uid'],
			'membership_parent_no' => (string)$pinfo['membership_parent_no'],
			'credit_delta' => (string)$charge_credit,
			'db_tx' => 'commit',
			'pg_request_raw' => json_encode(macomaex_payment_mask_array($approveRequestMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			'pg_response_raw' => json_encode(macomaex_payment_mask_array((array)$resultMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			'message' => '이미 충전 원장이 존재하여 중복 지급을 건너뛰었습니다.'
		)));
	echo "<script>location.href='/ko/my_plan.php';</script>";
	exit;
}

$payment_update_sql = "update g5_membership_payment set
	complete_yn='Y',
	complete_date=now(),
	mid='".addslashes($p_mid)."',
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
	credit_mobile_pay_fail("결제정보 저장 실패", 'payment_update_failed', array('db_tx' => 'rollback'));
}

sql_query("insert into g5_membership_paybill select * from g5_membership_payment where order_no='{$order_no}'", false);
sql_query("update g5_membership_paybill set
	mid='".addslashes($p_mid)."',
	tid='{$tid}',
	card_appl_price='{$cardApplPrice}',
	app_date='{$applDate}',
	appl_time='{$applTime}',
	appl_num='{$applNum}',
	card_Code='{$cardCode}',
	card_check_flag='{$cardCheckFlag}',
	card_num='{$cardNum}'
	where order_no='{$order_no}'", false);

$ledger_last = credit_ledger_get_latest_row($pinfo['uid'], true);
$current_credit = isset($ledger_last['credit_after']) ? (int)$ledger_last['credit_after'] : 0;
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
$charge_memo_text = $plan_title_for_memo . ' 플랜 충전 (2배 이벤트)';
$memo = addslashes($charge_memo_text);
$history_sql = "insert into credit_history set
	uid='{$pinfo['uid']}',
	tx_type='CHARGE',
	service_code='CREDIT_CHARGE',
	credit_delta='{$charge_credit}',
	credit_after='{$credit_after}',
	order_no='{$order_no}',
	tr_no='',
	memo='{$memo}',
	created_at=now()";
$history_ok = sql_query($history_sql, false);
if(!$history_ok){
	sql_query("ROLLBACK");
	credit_mobile_pay_fail("원장 기록 실패", 'credit_history_insert_failed', array('db_tx' => 'rollback'));
}
// CHARGE 원장 id로 LOT 1건을 생성해 환불/사용 추적 기준을 만든다.
$charge_history_id = (int)sql_insert_id();
if($charge_history_id < 1){
	$history_row = sql_fetch("select id from credit_history where order_no='{$order_no}' and tx_type='CHARGE' order by id desc limit 1");
	$charge_history_id = (int)$history_row['id'];
}
$lot_charge = credit_lot_register_charge($pinfo['uid'], $charge_history_id, $order_no, $charge_credit);
if(empty($lot_charge['ok'])){
	sql_query("ROLLBACK");
	credit_mobile_pay_fail("충전 LOT 기록 실패", 'credit_lot_charge_insert_failed', array('db_tx' => 'rollback'));
}

$log_send = addslashes(json_encode(macomaex_payment_mask_array($approveRequestMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
$log_recv = addslashes(json_encode(macomaex_payment_mask_array((array)$resultMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
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
	'mid' => (string)$p_mid,
	'tid' => (string)$tid,
	'appl_date' => (string)$applDate,
	'appl_time' => (string)$applTime,
	'card_code' => (string)$cardCode,
	'card_num_masked' => (string)macomaex_payment_mask_value('P_CARD_NUM', isset($resultMap['P_CARD_NUM']) ? $resultMap['P_CARD_NUM'] : ''),
	'card_appl_price' => (string)$cardApplPrice,
	'credit_delta' => (string)$charge_credit,
	'credit_before' => (string)$current_credit,
	'credit_after' => (string)$credit_after,
	'db_tx' => 'commit',
	'pg_request_raw' => json_encode(macomaex_payment_mask_array($approveRequestMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
	'pg_response_raw' => json_encode(macomaex_payment_mask_array((array)$resultMap), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
	'message' => '모바일 일반결제 승인 후 결제 및 크레딧 DB 반영을 완료했습니다.'
)));

echo "<script>
	window.dataLayer = window.dataLayer || [];
	window.dataLayer.push({
		event: 'purchase',
		ecommerce: {
			transaction_id: " . json_encode((string)$order_no, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ",
			value: " . (float)$cardApplPrice . ",
			currency: 'KRW',
			items: [{
				item_name: " . json_encode((string)$pinfo['order_goods'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "
			}]
		}
	});
</script>";
echo "<script>location.href='/ko/my_plan.php';</script>";
exit;
